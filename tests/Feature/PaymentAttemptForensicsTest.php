<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Setting;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentAttemptForensicsTest extends TestCase
{
    protected User $adminUser;
    protected Organization $organization;
    protected Customer $customer;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::first();
        $this->adminUser = User::where('email', 'superadmin@isp-mbp.local')->first()
            ?? User::factory()->create();

        $package = Package::first();

        $this->customer = Customer::create([
            'organization_id' => $this->organization->id,
            'current_package_id' => $package->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'customer_type' => 'individual',
            'email' => 'john.forensics@example.com',
            'phone' => '08012345678',
            'connection_type' => 'pppoe',
            'status' => 'lead',
        ]);

        $uniqueId = strtoupper(Str::random(6));
        $this->invoice = Invoice::create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-TST-' . $uniqueId,
            'status' => 'unpaid',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 25000.00,
            'total_amount' => 25000.00,
            'paid_amount' => 0.00,
        ]);
    }

    public function test_payment_attempt_is_recorded_when_checkout_is_initiated(): void
    {
        Setting::set('paystack_active', true, 'payments', 'boolean');
        Setting::set('paystack_secret_key', 'sk_test_mock_1234567890', 'payments', 'string');

        $response = $this->post(route('public.invoices.checkout', $this->invoice->uuid), [
            'payment_method' => 'paystack',
            'customer_email' => 'john.forensics@example.com',
            'customer_phone' => '08012345678',
        ]);

        // Verify a PaymentAttempt record was generated for this invoice
        $this->assertDatabaseHas('payment_attempts', [
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'payment_method' => 'paystack',
            'customer_email' => 'john.forensics@example.com',
            'customer_phone' => '08012345678',
        ]);

        $attempt = PaymentAttempt::where('invoice_id', $this->invoice->id)->first();
        $this->assertNotNull($attempt);
        $this->assertNotNull($attempt->request_payload);
        $this->assertEquals(25000.00, (float) $attempt->amount);
    }

    public function test_invoice_can_have_multiple_payment_attempts(): void
    {
        // Simulate two distinct payment attempts (e.g. customer tried Paystack, then tried Monnify)
        $ref1 = $this->invoice->invoice_number . '-ATT-1-' . strtoupper(Str::random(4));
        $ref2 = $this->invoice->invoice_number . '-ATT-2-' . strtoupper(Str::random(4));

        $attempt1 = PaymentAttempt::create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
            'invoice_id' => $this->invoice->id,
            'payment_method' => 'paystack',
            'reference' => $ref1,
            'amount' => 25000.00,
            'status' => 'failed',
            'error_message' => 'Insufficient funds on customer card',
            'customer_email' => 'john.forensics@example.com',
            'customer_phone' => '08012345678',
            'request_payload' => ['gateway' => 'paystack', 'attempt' => 1],
        ]);

        $attempt2 = PaymentAttempt::create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
            'invoice_id' => $this->invoice->id,
            'payment_method' => 'monnify',
            'reference' => $ref2,
            'amount' => 25000.00,
            'status' => 'successful',
            'customer_email' => 'john.forensics@example.com',
            'customer_phone' => '08012345678',
            'request_payload' => ['gateway' => 'monnify', 'attempt' => 2],
            'response_payload' => ['status' => 'PAID', 'transactionReference' => 'MN-12345'],
        ]);

        $this->assertEquals(2, $this->invoice->paymentAttempts()->count());

        // Now record one confirmed payment
        $billingService = app(BillingService::class);
        $payment = $billingService->recordPayment($this->invoice, [
            'amount' => 25000.00,
            'payment_method' => 'monnify',
            'reference' => $ref2,
            'raw_payload' => [
                'gateway' => 'monnify',
                'settlement_id' => 'SETTLE-987654',
                'amount_settled' => 25000.00,
            ],
            'payment_attempt_id' => $attempt2->id,
        ]);

        // Check payments table has raw_payload
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'reference' => $ref2,
        ]);

        $freshPayment = Payment::find($payment->id);
        $this->assertNotNull($freshPayment->raw_payload);
        $this->assertEquals('SETTLE-987654', $freshPayment->raw_payload['settlement_id']);

        // Check attempt2 is linked to the payment
        $freshAttempt2 = PaymentAttempt::find($attempt2->id);
        $this->assertEquals($payment->id, $freshAttempt2->payment_id);
        $this->assertEquals('successful', $freshAttempt2->status);

        // Verify invoice has 2 attempts and 1 recorded payment
        $this->assertEquals(2, $this->invoice->paymentAttempts()->count());
        $this->assertEquals(1, $this->invoice->payments()->count());
    }

    public function test_admin_invoice_detail_page_renders_payment_attempts_and_payload_data(): void
    {
        PaymentAttempt::create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
            'invoice_id' => $this->invoice->id,
            'payment_method' => 'paystack',
            'reference' => 'INV-FORENSIC-REF-999',
            'amount' => 25000.00,
            'status' => 'initiated',
            'customer_email' => 'subscriber@forensics.ng',
            'customer_phone' => '08099887766',
            'ip_address' => '192.168.1.100',
            'request_payload' => ['sample' => 'request_data'],
            'response_payload' => ['sample' => 'response_data'],
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('invoices.show', $this->invoice));

        $response->assertStatus(200);
        $response->assertSee('Payment Attempts &amp; Forensic Audit Trail', false);
        $response->assertSee('INV-FORENSIC-REF-999');
        $response->assertSee('subscriber@forensics.ng');
        $response->assertSee('192.168.1.100');
        $response->assertSee('View Payload');
        $response->assertSee('Forensic Audit Inspection');
    }

    public function test_settings_page_displays_enable_paystack_toggle(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('settings.index', ['group' => 'payments']));

        $response->assertStatus(200);
        $response->assertSee('Paystack Gateway');
        $response->assertSee('Enable Paystack');
        $response->assertSee('name="paystack_active"', false);
        $response->assertSee('name="monnify_active"', false);
    }

    public function test_monnify_user_cancelled_callback_redirects_gracefully(): void
    {
        $ref = 'INV-TEST-MONNIFY-CANCEL-123';

        $attempt = PaymentAttempt::create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
            'invoice_id' => $this->invoice->id,
            'payment_method' => 'monnify',
            'reference' => $ref,
            'amount' => 25000.00,
            'status' => 'initiated',
            'customer_email' => 'subscriber@test.ng',
            'customer_phone' => '08099887766',
        ]);

        $response = $this->get(route('public.invoices.callback', [
            'identifier' => $this->invoice->uuid,
            'gateway' => 'monnify',
            'paymentReference' => $ref,
            'paymentStatus' => 'USER_CANCELLED',
        ]));

        $response->assertRedirect(route('public.invoices.pay', $this->invoice->uuid));
        $response->assertSessionHas('info');

        $attempt->refresh();
        $this->assertEquals('failed', $attempt->status);
        $this->assertStringContainsString('cancelled', strtolower($attempt->error_message));
        $this->assertEquals('USER_CANCELLED', $attempt->verification_payload['paymentStatus']);
    }

    public function test_new_payment_attempt_automatically_invalidates_previous_uncompleted_attempts(): void
    {
        $ref1 = 'INV-FIRST-ATTEMPT-111';
        $ref2 = 'INV-SECOND-ATTEMPT-222';

        $attempt1 = PaymentAttempt::create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
            'invoice_id' => $this->invoice->id,
            'payment_method' => 'paystack',
            'reference' => $ref1,
            'amount' => 25000.00,
            'status' => 'initiated',
            'customer_email' => 'subscriber@test.ng',
            'customer_phone' => '08099887766',
        ]);

        $this->assertEquals('initiated', $attempt1->status);

        // Initiate a second attempt for the same invoice
        $attempt2 = PaymentAttempt::create([
            'organization_id' => $this->organization->id,
            'customer_id' => $this->customer->id,
            'invoice_id' => $this->invoice->id,
            'payment_method' => 'monnify',
            'reference' => $ref2,
            'amount' => 25000.00,
            'status' => 'initiated',
            'customer_email' => 'subscriber@test.ng',
            'customer_phone' => '08099887766',
        ]);

        // Attempt 1 must now be automatically marked as failed and superseded
        $attempt1->refresh();
        $this->assertEquals('failed', $attempt1->status);
        $this->assertTrue($attempt1->isSuperseded());
        $this->assertStringContainsString('Invalidated / Superseded', $attempt1->error_message);
        $this->assertStringContainsString($ref2, $attempt1->error_message);
        $this->assertNotNull($attempt1->completed_at);

        // Attempt 2 remains initiated
        $this->assertEquals('initiated', $attempt2->status);

        // Check rendering in admin invoice detail page shows Superseded badge
        $response = $this->actingAs($this->adminUser)->get(route('invoices.show', $this->invoice));
        $response->assertStatus(200);
        $response->assertSee('Superseded');
    }
}


