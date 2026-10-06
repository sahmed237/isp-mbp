<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\HotspotVoucher;
use App\Models\Organization;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HotspotVoucherTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'superadmin@isp-mbp.local')->first()
            ?? User::factory()->create();

        $this->package = Package::first()
            ?? Package::create([
                'organization_id' => Organization::first()->id,
                'name' => '1 Hour Voucher Plan',
                'code' => 'VOUCHER-1H',
                'download_speed' => 5120,
                'upload_speed' => 2048,
                'price' => 200.00,
                'billing_cycle' => 'custom',
                'validity_days' => 1,
                'connection_type' => 'hotspot',
                'status' => 'active',
            ]);
    }

    public function test_vouchers_index_loads_successfully_with_distinct_batches(): void
    {
        $batchA = 'BATCH-' . uniqid();
        $batchB = 'BATCH-' . uniqid();

        // Create vouchers across multiple batches
        HotspotVoucher::create([
            'organization_id' => $this->package->organization_id,
            'package_id' => $this->package->id,
            'code' => 'HS-' . uniqid(),
            'batch_id' => $batchA,
            'duration_minutes' => 60,
            'price' => 200.00,
            'status' => 'active',
        ]);

        HotspotVoucher::create([
            'organization_id' => $this->package->organization_id,
            'package_id' => $this->package->id,
            'code' => 'HS-' . uniqid(),
            'batch_id' => $batchA,
            'duration_minutes' => 60,
            'price' => 200.00,
            'status' => 'active',
        ]);

        HotspotVoucher::create([
            'organization_id' => $this->package->organization_id,
            'package_id' => $this->package->id,
            'code' => 'HS-' . uniqid(),
            'batch_id' => $batchB,
            'duration_minutes' => 120,
            'price' => 400.00,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('vouchers.index'));

        $response->assertStatus(200);
        $response->assertSee($batchA);
        $response->assertSee($batchB);
    }

    public function test_admin_can_generate_voucher_batch(): void
    {
        $prefix = 'TC' . rand(10, 99);
        $response = $this->actingAs($this->adminUser)->post(route('vouchers.store'), [
            'package_id' => $this->package->id,
            'quantity' => 5,
            'prefix' => $prefix,
            'duration_value' => 2,
            'duration_unit' => 'hours',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(5, HotspotVoucher::where('code', 'like', "{$prefix}%")->count());
    }
}
