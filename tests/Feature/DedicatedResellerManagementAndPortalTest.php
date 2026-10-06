<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\HotspotVoucher;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DedicatedResellerManagementAndPortalTest extends TestCase
{
    protected User $superAdmin;
    protected Organization $organization;
    protected Package $hotspotPackage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::firstOrCreate(
            ['code' => 'ORG-MAIN'],
            ['name' => 'Main Telecom ISP', 'email' => 'admin@isp-mbp.ng']
        );

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@isp-mbp.local'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'organization_id' => $this->organization->id,
            ]
        );
        $this->superAdmin->assignRole('Super Administrator');

        $this->hotspotPackage = Package::firstOrCreate(
            ['name' => '1 Hour Unlimited High Speed'],
            [
                'organization_id' => $this->organization->id,
                'connection_type' => 'hotspot',
                'price' => 300.00,
                'download_speed' => 10,
                'upload_speed' => 5,
                'validity_period' => 1,
                'validity_unit' => 'hours',
                'status' => 'active',
            ]
        );
    }

    public function test_prospective_agent_can_submit_kyc_application(): void
    {
        Storage::fake('public');

        $suffix = uniqid();
        $kycDoc = UploadedFile::fake()->create('national_id_slip.pdf', 1024, 'application/pdf');
        $phone = '08' . random_int(100000000, 999999999);

        $response = $this->post(route('reseller.apply.submit'), [
            'business_name' => 'Alpha Cyber Hub ' . $suffix,
            'contact_person' => 'Usman Danfodio',
            'email' => "alpha_{$suffix}@agent.ng",
            'phone' => $phone,
            'alternate_phone' => '08011223344',
            'business_type' => 'cybercafe',
            'shop_address' => 'Shop 15, Students Centre, University Campus',
            'city' => 'Zaria',
            'state' => 'Kaduna',
            'id_type' => 'nin',
            'id_number' => '12345678901',
            'id_document' => $kycDoc,
            'password' => 'secretPassword123',
            'password_confirmation' => 'secretPassword123',
            'notes' => 'High footfall campus location with over 500 daily students.',
        ]);

        $response->assertRedirect(route('reseller.applied'));

        $this->assertDatabaseHas('resellers', [
            'business_name' => 'Alpha Cyber Hub ' . $suffix,
            'email' => "alpha_{$suffix}@agent.ng",
            'phone' => $phone,
            'status' => 'pending',
            'id_type' => 'nin',
            'id_number' => '12345678901',
        ]);

        $reseller = Reseller::where('email', "alpha_{$suffix}@agent.ng")->first();
        $this->assertNotNull($reseller->id_card_path);
        Storage::disk('public')->assertExists($reseller->id_card_path);

        // Verify pending reseller cannot log in yet
        $loginAttempt = $this->post(route('reseller.login'), [
            'login' => "alpha_{$suffix}@agent.ng",
            'password' => 'secretPassword123',
        ]);
        $loginAttempt->assertSessionHasErrors('login');
        $this->assertGuest('reseller');
    }

    public function test_admin_can_review_kyc_and_approve_reseller(): void
    {
        $suffix = uniqid();
        $reseller = Reseller::create([
            'organization_id' => $this->organization->id,
            'business_name' => 'Apex Phone Kiosk ' . $suffix,
            'contact_person' => 'Zainab Ahmed',
            'email' => "apex_{$suffix}@kiosk.ng",
            'phone' => '08' . random_int(100000000, 999999999),
            'password' => 'agentPass123',
            'business_type' => 'retail_agent',
            'shop_address' => 'Plaza 2, Main Market',
            'city' => 'Kano',
            'state' => 'Kano',
            'id_type' => 'voters_card',
            'id_number' => 'VIN-99887766',
            'status' => 'pending',
        ]);

        // Admin views reseller index
        $indexResponse = $this->actingAs($this->superAdmin)->get(route('resellers.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Apex Phone Kiosk ' . $suffix);

        // Admin views reseller details & KYC review
        $showResponse = $this->actingAs($this->superAdmin)->get(route('resellers.show', $reseller));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('VIN-99887766');

        // Admin approves reseller
        $approveResponse = $this->actingAs($this->superAdmin)->post(route('resellers.approve', $reseller));
        $approveResponse->assertSessionHas('success');

        $reseller->refresh();
        $this->assertEquals('active', $reseller->status);
        $this->assertEquals($this->superAdmin->id, $reseller->approved_by_user_id);
        $this->assertNotNull($reseller->approved_at);

        // Now reseller can log in successfully
        $loginResponse = $this->post(route('reseller.login'), [
            'login' => $reseller->reseller_code,
            'password' => 'agentPass123',
        ]);
        $loginResponse->assertRedirect(route('reseller.dashboard'));
        $this->assertAuthenticatedAs($reseller, 'reseller');
    }

    public function test_admin_can_adjust_reseller_wallet_balance(): void
    {
        $suffix = uniqid();
        $reseller = Reseller::create([
            'organization_id' => $this->organization->id,
            'business_name' => 'Prepaid Center ' . $suffix,
            'contact_person' => 'Chidi Eze',
            'email' => "chidi_{$suffix}@center.ng",
            'phone' => '08' . random_int(100000000, 999999999),
            'password' => 'walletPass123',
            'status' => 'active',
            'balance' => 0.00,
        ]);

        // Admin credits ₦15,000 to reseller's prepaid wallet
        $creditResponse = $this->actingAs($this->superAdmin)->post(route('resellers.wallet', $reseller), [
            'action' => 'credit',
            'amount' => 15000.00,
            'notes' => 'Cash deposit confirmed at accounts office',
        ]);
        $creditResponse->assertSessionHas('success');

        $reseller->refresh();
        $this->assertEquals(15000.00, (float) $reseller->balance);

        // Reseller logs into dashboard and sees updated balance
        $dashboardResponse = $this->actingAs($reseller, 'reseller')->get(route('reseller.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('15,000.00');
    }

    public function test_reseller_can_order_voucher_batch_via_wallet_and_print(): void
    {
        $suffix = uniqid();
        $reseller = Reseller::create([
            'organization_id' => $this->organization->id,
            'business_name' => 'Metro Cybercafe ' . $suffix,
            'contact_person' => 'Emeka Obi',
            'email' => "metro_{$suffix}@cyber.ng",
            'phone' => '08' . random_int(100000000, 999999999),
            'password' => 'metroPass123',
            'status' => 'active',
            'balance' => 5000.00, // Sufficient balance
        ]);

        // Reseller orders 10 vouchers of ₦300 = ₦3,000
        $batchResponse = $this->actingAs($reseller, 'reseller')->post(route('reseller.vouchers.buy'), [
            'package_id' => $this->hotspotPackage->id,
            'quantity' => 10,
            'prefix' => 'MET',
            'payment_method' => 'wallet',
        ]);

        $batchResponse->assertRedirect(route('reseller.vouchers'));
        $batchResponse->assertSessionHas('just_created_batch');

        $reseller->refresh();
        $this->assertEquals(2000.00, (float) $reseller->balance); // 5000 - 3000

        $batchId = session('just_created_batch');
        $this->assertNotNull($batchId);

        // Check 10 vouchers were generated assigned to this reseller
        $vouchersCount = HotspotVoucher::where('reseller_id', $reseller->id)
            ->where('batch_id', $batchId)
            ->count();
        $this->assertEquals(10, $vouchersCount);

        // Reseller views print sheet
        $printResponse = $this->actingAs($reseller, 'reseller')->get(route('reseller.vouchers.print', $batchId));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Print Sheet (A4 / Perforated Cards)');
        $printResponse->assertSee($batchId);
    }

    public function test_unauthorized_user_cannot_access_reseller_management(): void
    {
        $suffix = uniqid();
        $regularUser = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Customer Care Agent ' . $suffix,
            'email' => "care_{$suffix}@isp-mbp.local",
            'password' => Hash::make('password123'),
        ]);
        $regularUser->assignRole('Customer Support'); // Only has customers, subscriptions, invoices

        // Should receive 403 Forbidden
        $response = $this->actingAs($regularUser)->get(route('resellers.index'));
        $response->assertStatus(403);
    }
}
