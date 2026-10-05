<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Radius\Nas;
use App\Models\Radius\RadCheck;
use App\Models\Radius\RadReply;
use App\Models\User;
use Tests\TestCase;

class RadiusManagementTest extends TestCase
{
    protected User $adminUser;
    protected Organization $org;
    protected Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'superadmin@isp-mbp.local')->first()
            ?? User::factory()->create(['status' => 'active']);

        $this->org = Organization::first() ?? Organization::create([
            'name' => 'Test ISP Telecommunications',
            'code' => 'TESTISP',
            'status' => 'active',
            'contact_email' => 'admin@testisp.local',
        ]);

        $this->package = Package::first() ?? Package::create([
            'organization_id' => $this->org->id,
            'name' => 'Standard Fibre 25M',
            'code' => 'FIBRE-25M',
            'download_speed' => 25600, // 25 Mbps
            'upload_speed' => 25600,   // 25 Mbps
            'price' => 15000.00,
            'validity_period' => 30,
            'billing_cycle' => 'monthly',
            'connection_type' => 'pppoe',
            'status' => 'active',
        ]);
    }

    public function test_guest_is_redirected_from_radius_management(): void
    {
        $response = $this->get(route('radius.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_radius_management_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('radius.index'));

        $response->assertStatus(200);
        $response->assertSee('Online Subscribers');
        $response->assertSee('NAS Routers');
        $response->assertSee('Active Sessions');
    }

    public function test_admin_can_create_update_and_delete_nas_client(): void
    {
        $nasData = [
            'shortname' => 'Edge-Mikrotik-01',
            'nasname' => '192.168.88.1',
            'type' => 'mikrotik',
            'secret' => 'supersecretpass123',
            'description' => 'Edge PPPoE Gateway',
        ];

        // 1. Create NAS
        $response = $this->actingAs($this->adminUser)->post(route('radius.nas.store'), $nasData);
        $response->assertRedirect(route('radius.index', ['tab' => 'nas']));
        $this->assertDatabaseHas('nas', [
            'nasname' => '192.168.88.1',
            'shortname' => 'Edge-Mikrotik-01',
        ]);

        $nas = Nas::where('nasname', '192.168.88.1')->first();
        $this->assertNotNull($nas);

        // 2. Update NAS
        $updateData = array_merge($nasData, ['shortname' => 'Edge-Mikrotik-Renamed']);
        $updateResponse = $this->actingAs($this->adminUser)->put(route('radius.nas.update', $nas), $updateData);
        $updateResponse->assertRedirect(route('radius.index', ['tab' => 'nas']));
        $this->assertDatabaseHas('nas', ['shortname' => 'Edge-Mikrotik-Renamed']);

        // 3. Delete NAS
        $deleteResponse = $this->actingAs($this->adminUser)->delete(route('radius.nas.destroy', $nas));
        $deleteResponse->assertRedirect(route('radius.index', ['tab' => 'nas']));
        $this->assertDatabaseMissing('nas', ['id' => $nas->id]);
    }

    public function test_customer_creation_auto_provisions_freeradius_radcheck_and_radreply(): void
    {
        $uniqueUser = 'ibrahim_' . uniqid();
        $customerData = [
            'first_name' => 'Ibrahim',
            'last_name' => 'Musa',
            'customer_type' => 'individual',
            'phone' => '+2348011223344',
            'email' => 'ibrahim@example.com',
            'connection_type' => 'pppoe',
            'status' => 'active',
            'mark_paid_immediately' => '1',
            'current_package_id' => $this->package->id,
            'radius_username' => $uniqueUser,
            'radius_password' => 'secretPass123',
            'static_ip' => '192.168.88.99',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('customers.store'), $customerData);
        $response->assertSessionHasNoErrors();

        // Verify radcheck has Cleartext-Password
        $this->assertDatabaseHas('radcheck', [
            'username' => $uniqueUser,
            'attribute' => 'Cleartext-Password',
            'value' => 'secretPass123',
        ]);

        // Verify radreply has Mikrotik-Rate-Limit matching package
        $this->assertDatabaseHas('radreply', [
            'username' => $uniqueUser,
            'attribute' => 'Mikrotik-Rate-Limit',
            'value' => $this->package->toMikrotikRateLimitString(),
        ]);

        // Verify radreply has Framed-IP-Address
        $this->assertDatabaseHas('radreply', [
            'username' => $uniqueUser,
            'attribute' => 'Framed-IP-Address',
            'value' => '192.168.88.99',
        ]);
    }

    public function test_customer_suspension_immediately_sets_reject_in_radcheck(): void
    {
        $uniqueUser = 'fatima_' . uniqid();
        $customer = Customer::create([
            'organization_id' => $this->org->id,
            'first_name' => 'Fatima',
            'last_name' => 'Bello',
            'customer_type' => 'individual',
            'phone' => '+2348099887766',
            'connection_type' => 'pppoe',
            'status' => 'active',
            'current_package_id' => $this->package->id,
            'radius_username' => $uniqueUser,
            'radius_password' => 'fatimaPass123',
        ]);

        // Trigger update to suspended
        $updateData = [
            'first_name' => 'Fatima',
            'last_name' => 'Bello',
            'customer_type' => 'individual',
            'phone' => '+2348099887766',
            'connection_type' => 'pppoe',
            'status' => 'suspended',
            'current_package_id' => $this->package->id,
            'radius_username' => $uniqueUser,
            'radius_password' => 'fatimaPass123',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('customers.update', $customer), $updateData);
        $response->assertSessionHasNoErrors();

        // Verify Auth-Type := Reject is created in radcheck
        $this->assertDatabaseHas('radcheck', [
            'username' => $uniqueUser,
            'attribute' => 'Auth-Type',
            'value' => 'Reject',
        ]);

        // Verify Cleartext-Password was removed
        $this->assertDatabaseMissing('radcheck', [
            'username' => $uniqueUser,
            'attribute' => 'Cleartext-Password',
        ]);
    }

    public function test_customer_deletion_deprovisions_radius_records(): void
    {
        $uniqueUser = 'aliyu_' . uniqid();
        $customer = Customer::create([
            'organization_id' => $this->org->id,
            'first_name' => 'Aliyu',
            'last_name' => 'Danladi',
            'customer_type' => 'individual',
            'phone' => '+2348055667788',
            'connection_type' => 'pppoe',
            'status' => 'active',
            'radius_username' => $uniqueUser,
            'radius_password' => 'aliyuPass123',
        ]);

        RadCheck::create([
            'username' => $uniqueUser,
            'attribute' => 'Cleartext-Password',
            'op' => ':=',
            'value' => 'aliyuPass123',
        ]);

        $this->actingAs($this->adminUser)->delete(route('customers.destroy', $customer));

        $this->assertDatabaseMissing('radcheck', ['username' => $uniqueUser]);
    }
}
