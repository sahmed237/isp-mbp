<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\NetworkDevice;
use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    public function test_tenant_user_cannot_access_customers_belonging_to_another_organization(): void
    {
        $apex = Organization::where('code', 'APEX-NET')->first();
        $nexus = Organization::where('code', 'NEXUS-FIBRE')->first();

        $this->assertNotNull($apex);
        $this->assertNotNull($nexus);

        // Apex user
        $apexAdmin = User::where('email', 'orgadmin@isp-mbp.local')->first();
        // Nexus user
        $nexusAdmin = User::where('email', 'nexusadmin@isp-mbp.local')->first();

        // Customer in Apex
        $apexCustomer = Customer::where('organization_id', $apex->id)->first();

        // Create or find a Customer belonging to Nexus
        $nexusCustomer = Customer::where('organization_id', $nexus->id)->first() ?? Customer::create([
            'organization_id' => $nexus->id,
            'customer_number' => 'NEX-TST-001',
            'first_name' => 'Nexus',
            'last_name' => 'Subscriber',
            'email' => 'nexus_sub@test.local',
            'account_type' => 'individual',
            'status' => 'active',
        ]);

        // Nexus admin CAN see their own customer
        $this->actingAs($nexusAdmin)->get("/customers/{$nexusCustomer->id}")->assertStatus(200);

        // Apex admin CANNOT view Nexus customer (Tenant Scoped & IDOR Protection)
        $res = $this->actingAs($apexAdmin)->get("/customers/{$nexusCustomer->id}");
        $this->assertTrue(in_array($res->status(), [403, 404]), 'Cross-tenant customer access must return 403 or 404.');

        // Apex admin CANNOT edit or update Nexus customer
        $editRes = $this->actingAs($apexAdmin)->get("/customers/{$nexusCustomer->id}/edit");
        $this->assertTrue(in_array($editRes->status(), [403, 404]), 'Cross-tenant customer edit must return 403 or 404.');

        $putRes = $this->actingAs($apexAdmin)->put("/customers/{$nexusCustomer->id}", [
            'first_name' => 'Hacked',
            'last_name' => 'Name',
            'email' => 'hacked@test.local',
            'account_type' => 'individual',
            'status' => 'active',
        ]);
        $this->assertTrue(in_array($putRes->status(), [403, 404]), 'Cross-tenant customer update must return 403 or 404.');

        // Nexus admin CANNOT view Apex customer
        if ($apexCustomer) {
            $otherRes = $this->actingAs($nexusAdmin)->get("/customers/{$apexCustomer->id}");
            $this->assertTrue(in_array($otherRes->status(), [403, 404]), 'Cross-tenant customer access must return 403 or 404.');
        }
    }

    public function test_super_admin_can_access_any_tenant_customer(): void
    {
        $superAdmin = User::where('email', 'superadmin@isp-mbp.local')->first();
        $nexus = Organization::where('code', 'NEXUS-FIBRE')->first();

        $nexusCustomer = Customer::where('organization_id', $nexus->id)->first();

        if ($nexusCustomer) {
            $this->actingAs($superAdmin)->get("/customers/{$nexusCustomer->id}")->assertStatus(200);
        }
    }

    public function test_tenant_user_cannot_access_network_devices_of_another_organization(): void
    {
        $apex = Organization::where('code', 'APEX-NET')->first();
        $nexus = Organization::where('code', 'NEXUS-FIBRE')->first();

        $apexAdmin = User::where('email', 'orgadmin@isp-mbp.local')->first();
        $nexusAdmin = User::where('email', 'nexusadmin@isp-mbp.local')->first();

        $nexusDevice = NetworkDevice::where('organization_id', $nexus->id)->first() ?? NetworkDevice::create([
            'organization_id' => $nexus->id,
            'name' => 'Nexus Edge Router',
            'device_type' => 'mikrotik_router',
            'ip_address' => '10.99.0.1',
            'status' => 'online',
        ]);

        // Apex admin cannot view Nexus device
        $devRes = $this->actingAs($apexAdmin)->get("/network-devices/{$nexusDevice->id}");
        $this->assertTrue(in_array($devRes->status(), [403, 404]), 'Cross-tenant device access must return 403 or 404.');

        // Apex admin cannot trigger test connection on Nexus device
        $testRes = $this->actingAs($apexAdmin)->post("/network-devices/{$nexusDevice->id}/test-connection");
        $this->assertTrue(in_array($testRes->status(), [403, 404]), 'Cross-tenant device connection test must return 403 or 404.');
    }
}
