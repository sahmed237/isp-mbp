<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\HotspotVoucher;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Radius\RadCheck;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HotspotVoucherResellerAndPublicPortalTest extends TestCase
{
    use DatabaseTransactions;

    protected Organization $organization;
    protected Package $package;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('email', 'superadmin@isp-mbp.local')->first()
            ?? User::factory()->create(['is_active' => true]);

        $this->organization = Organization::first() ?? Organization::create([
            'name' => 'Apex Broadband Ltd',
            'code' => 'APEX',
            'email' => 'admin@apex.ng',
            'phone' => '+2348011112222',
            'is_active' => true,
        ]);

        $this->package = Package::create([
            'organization_id' => $this->organization->id,
            'name' => 'Daily Unlimited Hotspot',
            'connection_type' => 'hotspot',
            'price' => 500.00,
            'download_speed' => 10,
            'upload_speed' => 5,
            'validity_period' => 1,
            'billing_cycle' => 'daily',
            'status' => 'active',
        ]);

        Setting::set('company_legal_name', 'Apex Broadband Hotspot');
        Setting::set('paystack_active', true);
        Setting::set('monnify_active', true);
    }

    public function test_public_hotspot_index_page_loads_and_displays_active_packages(): void
    {
        $response = $this->get(route('public.hotspot.index'));

        $response->assertStatus(200);
        $response->assertSee('Daily Unlimited Hotspot');
        $response->assertSee('500.00');
        $response->assertSee('Apex Broadband Hotspot');
    }

    public function test_public_hotspot_checkout_validation(): void
    {
        $response = $this->post(route('public.hotspot.checkout'), []);

        $response->assertSessionHasErrors(['package_id', 'customer_phone', 'customer_email', 'payment_method']);
    }

    public function test_public_hotspot_checkout_creates_payment_attempt(): void
    {
        // Mock Paystack Gateway initialization
        Setting::set('paystack_secret_key', 'sk_test_12345');

        $response = $this->post(route('public.hotspot.checkout'), [
            'package_id' => $this->package->id,
            'customer_phone' => '08031234567',
            'customer_email' => 'guest@example.com',
            'payment_method' => 'paystack',
        ]);

        $this->assertDatabaseHas('payment_attempts', [
            'amount' => 500.00,
            'payment_method' => 'paystack',
            'customer_phone' => '08031234567',
            'customer_email' => 'guest@example.com',
        ]);
    }

    public function test_public_hotspot_voucher_screen_renders_credentials(): void
    {
        $voucher = HotspotVoucher::create([
            'organization_id' => $this->organization->id,
            'package_id' => $this->package->id,
            'code' => 'HS-TEST-9999',
            'username' => 'hs_guest123',
            'password' => '123456',
            'price' => 500.00,
            'duration_value' => 1,
            'duration_unit' => 'days',
            'status' => 'unused',
        ]);

        $response = $this->get(route('public.hotspot.voucher', $voucher->code));

        $response->assertStatus(200);
        $response->assertSee('HS-TEST-9999');
        $response->assertSee('hs_guest123');
        $response->assertSee('123456');
    }

    public function test_reseller_can_access_reseller_hub_in_portal_while_regular_subscriber_is_forbidden(): void
    {
        $regularCustomer = Customer::create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Regular',
            'last_name' => 'User',
            'customer_type' => 'individual',
            'phone' => '08099990001',
            'email' => 'regular@apex.ng',
            'connection_type' => 'pppoe',
            'status' => 'active',
            'balance' => 0.00,
        ]);

        $resellerCustomer = Customer::create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Agent',
            'last_name' => 'Reseller',
            'customer_type' => 'reseller',
            'phone' => '08099990002',
            'email' => 'reseller@apex.ng',
            'connection_type' => 'pppoe',
            'status' => 'active',
            'balance' => 25000.00,
        ]);

        // Regular customer should get 403 Forbidden
        $responseRegular = $this->actingAs($regularCustomer, 'customer')
            ->get(route('portal.reseller.vouchers'));
        $responseRegular->assertStatus(403);

        // Reseller customer should get 200 OK
        $responseReseller = $this->actingAs($resellerCustomer, 'customer')
            ->get(route('portal.reseller.vouchers'));
        $responseReseller->assertStatus(200);
        $responseReseller->assertSee('25,000.00');
        $responseReseller->assertSee('Reseller Hotspot Hub');
    }

    public function test_reseller_can_buy_batch_using_wallet_balance(): void
    {
        $reseller = Customer::create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Agent',
            'last_name' => 'Smith',
            'customer_type' => 'reseller',
            'phone' => '08088887777',
            'email' => 'smith@apex.ng',
            'connection_type' => 'pppoe',
            'status' => 'active',
            'balance' => 15000.00,
        ]);

        $quantity = 10;
        $totalCost = 500.00 * $quantity; // 5,000

        $response = $this->actingAs($reseller, 'customer')
            ->post(route('portal.reseller.vouchers.buy'), [
                'package_id' => $this->package->id,
                'quantity' => $quantity,
                'payment_method' => 'wallet',
                'prefix' => 'SMTH',
            ]);

        $response->assertRedirect(route('portal.reseller.vouchers'));
        $response->assertSessionHas('success');

        // Verify wallet balance deducted: 15,000 - 5,000 = 10,000
        $reseller->refresh();
        $this->assertEquals(10000.00, (float) $reseller->balance);

        // Verify 10 vouchers generated with customer_id and prefix
        $vouchers = HotspotVoucher::where('customer_id', $reseller->id)->get();
        $this->assertCount(10, $vouchers);
        $this->assertStringStartsWith('SMTH-', $vouchers->first()->code);

        // Verify FreeRADIUS sync
        $this->assertDatabaseHas('radcheck', [
            'username' => $vouchers->first()->username,
            'attribute' => 'Cleartext-Password',
        ]);

        // Verify Payment record
        $this->assertDatabaseHas('payments', [
            'customer_id' => $reseller->id,
            'amount' => 5000.00,
            'payment_method' => 'wallet',
        ]);
    }

    public function test_reseller_cannot_buy_batch_with_insufficient_wallet_balance(): void
    {
        $reseller = Customer::create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Agent',
            'last_name' => 'Broke',
            'customer_type' => 'reseller',
            'phone' => '08088881111',
            'email' => 'broke@apex.ng',
            'connection_type' => 'pppoe',
            'status' => 'active',
            'balance' => 1000.00,
        ]);

        $quantity = 10; // 10 * 500 = 5,000 > 1,000 balance

        $response = $this->actingAs($reseller, 'customer')
            ->post(route('portal.reseller.vouchers.buy'), [
                'package_id' => $this->package->id,
                'quantity' => $quantity,
                'payment_method' => 'wallet',
            ]);

        $response->assertSessionHas('error');

        $reseller->refresh();
        $this->assertEquals(1000.00, (float) $reseller->balance);
        $this->assertDatabaseMissing('hotspot_vouchers', [
            'customer_id' => $reseller->id,
        ]);
    }

    public function test_reseller_can_print_batch_sheet(): void
    {
        $reseller = Customer::create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Agent',
            'last_name' => 'Print',
            'customer_type' => 'reseller',
            'phone' => '08088882222',
            'email' => 'print@apex.ng',
            'connection_type' => 'pppoe',
            'status' => 'active',
            'balance' => 0.00,
        ]);

        $batchId = 'RES-PRINT-BATCH-001';

        HotspotVoucher::create([
            'organization_id' => $this->organization->id,
            'package_id' => $this->package->id,
            'customer_id' => $reseller->id,
            'batch_id' => $batchId,
            'code' => 'PRT-1111-2222',
            'username' => 'prt_user1',
            'password' => '888999',
            'price' => 500.00,
            'duration_value' => 1,
            'duration_unit' => 'days',
            'status' => 'unused',
        ]);

        $response = $this->actingAs($reseller, 'customer')
            ->get(route('portal.reseller.vouchers.print', $batchId));

        $response->assertStatus(200);
        $response->assertSee('PRT-1111-2222');
        $response->assertSee('prt_user1');
        $response->assertSee('888999');
        $response->assertSee('Print Sheet');
    }

    public function test_admin_can_create_reseller_with_initial_wallet_balance(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->post(route('customers.store'), [
                'first_name' => 'New',
                'last_name' => 'ResellerAgent',
                'customer_type' => 'reseller',
                'phone' => '08077776666',
                'email' => 'newagent@apex.ng',
                'connection_type' => 'pppoe',
                'status' => 'active',
                'balance' => 50000.00,
            ]);

        $this->assertDatabaseHas('customers', [
            'first_name' => 'New',
            'last_name' => 'ResellerAgent',
            'customer_type' => 'reseller',
            'balance' => 50000.00,
        ]);
    }
}
