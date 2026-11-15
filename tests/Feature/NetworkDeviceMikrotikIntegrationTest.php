<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\HotspotVoucher;
use App\Models\NetworkDevice;
use App\Models\Organization;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NetworkDeviceMikrotikIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected Organization $org;

    protected Branch $branch;

    protected NetworkDevice $router;

    protected Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'superadmin@isp-mbp.local')->first()
            ?? User::factory()->create(['status' => 'active']);

        $this->org = Organization::first() ?? Organization::create([
            'name' => 'Apex Broadband',
            'code' => 'APEX-NET',
            'status' => 'active',
            'contact_email' => 'noc@apex.local',
        ]);

        $this->branch = Branch::first() ?? Branch::create([
            'organization_id' => $this->org->id,
            'name' => 'Abuja HQ',
            'code' => 'ABJ-HQ',
            'status' => 'active',
        ]);

        $this->router = NetworkDevice::updateOrCreate(
            ['organization_id' => $this->org->id, 'name' => 'TEST-CCR2116-ROUTER'],
            [
                'branch_id' => $this->branch->id,
                'device_type' => 'mikrotik_router',
                'ip_address' => '192.168.88.1',
                'mac_address' => '04:F4:1C:E6:A9:ED',
                'vendor' => 'MikroTik',
                'model' => 'CCR2116-12G-4S+',
                'api_port' => 8728,
                'ssh_port' => 22,
                'web_port' => 80,
                'username' => 'admin',
                'password' => 'password',
                'status' => 'offline',
            ]
        );

        $this->package = Package::first() ?? Package::create([
            'organization_id' => $this->org->id,
            'name' => 'Fibre Turbo 50M',
            'code' => 'FIBRE-50M',
            'download_speed' => 51200,
            'upload_speed' => 51200,
            'price' => 25000.00,
            'validity_period' => 30,
            'billing_cycle' => 'monthly',
            'connection_type' => 'pppoe',
            'status' => 'active',
        ]);
    }

    public function test_mikrotik_test_connection_queries_routeros_rest_api_and_updates_status(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            '*/rest/system/resource' => Http::response([
                'board-name' => 'CCR2116-12G-4S+',
                'version' => '7.19.6 (stable)',
                'cpu-load' => '1',
                'uptime' => '1h10m',
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('network-devices.test-connection', $this->router));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertStringContainsString('CCR2116-12G-4S+', (string) $response->json('message'));
        $this->assertEquals('online', $this->router->fresh()->status);
    }

    public function test_mikrotik_test_connection_marks_warning_on_authentication_failure(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            '*/rest/system/resource' => Http::response([], 401),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('network-devices.test-connection', $this->router));

        $response->assertOk()
            ->assertJsonPath('success', false);

        $this->assertEquals('warning', $this->router->fresh()->status);
    }

    public function test_sync_endpoint_pushes_packages_customers_and_vouchers_to_routeros(): void
    {
        Http::preventStrayRequests();

        Customer::updateOrCreate(
            ['organization_id' => $this->org->id, 'radius_username' => 'sync_test_pppoe'],
            [
                'first_name' => 'Test',
                'last_name' => 'Subscriber',
                'customer_type' => 'individual',
                'phone' => '+2348000000001',
                'connection_type' => 'pppoe',
                'status' => 'active',
                'current_package_id' => $this->package->id,
                'radius_password' => 'pass123',
            ]
        );

        HotspotVoucher::updateOrCreate(
            ['organization_id' => $this->org->id, 'code' => 'HS-TEST-9999'],
            [
                'package_id' => $this->package->id,
                'batch_id' => 'BATCH-TEST',
                'username' => 'hs_test99',
                'password' => '999999',
                'price' => 1000,
                'duration_value' => 1,
                'duration_unit' => 'days',
                'status' => 'unused',
            ]
        );

        Http::fake([
            '*/rest/system/resource' => Http::response([
                'board-name' => 'CCR2116-12G-4S+',
                'version' => '7.19.6 (stable)',
                'cpu-load' => '0',
                'uptime' => '1h',
            ], 200),
            '*/rest/*' => Http::response([], 200),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('network-devices.sync', $this->router));

        $response->assertOk()
            ->assertJsonPath('success', true);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/rest/ppp/secret'));
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/rest/ip/hotspot/user'));
    }
}
