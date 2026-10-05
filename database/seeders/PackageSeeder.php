<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $apex = Organization::where('code', 'APEX-NET')->first();

        $packages = [
            [
                'name' => 'Home Basic 5 Mbps',
                'code' => 'PKG-HOME-5M',
                'description' => 'Reliable entry-level broadband for light browsing, SD streaming, and remote schooling.',
                'download_speed' => 5120, // 5 Mbps in Kbps
                'upload_speed' => 2048,   // 2 Mbps
                'burst_download' => 7168,
                'burst_upload' => 3072,
                'burst_threshold' => 4096,
                'burst_time' => 20,
                'price' => 12500.00,
                'installation_fee' => 25000.00,
                'activation_fee' => 0.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'pppoe',
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'name' => 'Home Standard 10 Mbps',
                'code' => 'PKG-HOME-10M',
                'description' => 'Fast broadband for multiple HD streaming devices, Zoom calls, and gaming.',
                'download_speed' => 10240, // 10 Mbps
                'upload_speed' => 5120,    // 5 Mbps
                'burst_download' => 15360,
                'burst_upload' => 7680,
                'burst_threshold' => 8192,
                'burst_time' => 25,
                'price' => 22000.00,
                'installation_fee' => 25000.00,
                'activation_fee' => 0.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'pppoe',
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'name' => 'Home Premium 20 Mbps',
                'code' => 'PKG-HOME-20M',
                'description' => 'Ultra-fast home connectivity for 4K streaming and power smart home households.',
                'download_speed' => 20480, // 20 Mbps
                'upload_speed' => 10240,   // 10 Mbps
                'burst_download' => 30720,
                'burst_upload' => 15360,
                'burst_threshold' => 16384,
                'burst_time' => 30,
                'price' => 38500.00,
                'installation_fee' => 20000.00,
                'activation_fee' => 0.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'fibre',
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'name' => 'Business Pro 50 Mbps',
                'code' => 'PKG-BIZ-50M',
                'description' => 'Symmetric enterprise link with 99.8% SLA, static public IP, and priority queuing.',
                'download_speed' => 51200, // 50 Mbps
                'upload_speed' => 51200,   // 50 Mbps
                'burst_download' => 61440,
                'burst_upload' => 61440,
                'burst_threshold' => 45000,
                'burst_time' => 30,
                'price' => 95000.00,
                'installation_fee' => 50000.00,
                'activation_fee' => 10000.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'ptp',
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'name' => 'Enterprise Leased Line 100 Mbps',
                'code' => 'PKG-CORP-100M',
                'description' => 'Dedicated 1:1 contention ratio, dual-homed redundant fiber, 24/7 proactive NOC monitoring.',
                'download_speed' => 102400, // 100 Mbps
                'upload_speed' => 102400,   // 100 Mbps
                'burst_download' => null,
                'burst_upload' => null,
                'burst_threshold' => null,
                'burst_time' => null,
                'price' => 220000.00,
                'installation_fee' => 100000.00,
                'activation_fee' => 25000.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'dedicated',
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'name' => 'Hotspot 1 Hour Pass',
                'code' => 'PKG-HS-1H',
                'description' => 'Fast 5 Mbps Wi-Fi access valid for 1 hour on guest captive portal.',
                'download_speed' => 5120,
                'upload_speed' => 2048,
                'burst_download' => null,
                'burst_upload' => null,
                'burst_threshold' => null,
                'burst_time' => null,
                'price' => 200.00,
                'installation_fee' => 0.00,
                'activation_fee' => 0.00,
                'validity_period' => 1,
                'billing_cycle' => 'custom',
                'connection_type' => 'hotspot',
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'name' => 'Hotspot 24 Hours Unlimited',
                'code' => 'PKG-HS-24H',
                'description' => '10 Mbps uncapped 24-hour day pass for Wi-Fi hotspot subscribers.',
                'download_speed' => 10240,
                'upload_speed' => 5120,
                'burst_download' => null,
                'burst_upload' => null,
                'burst_threshold' => null,
                'burst_time' => null,
                'price' => 800.00,
                'installation_fee' => 0.00,
                'activation_fee' => 0.00,
                'validity_period' => 1,
                'billing_cycle' => 'custom',
                'connection_type' => 'hotspot',
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'name' => 'Hotspot 7 Days Weekly Pass',
                'code' => 'PKG-HS-7D',
                'description' => '7-day unlimited high-speed access for guest and residential hotspot areas.',
                'download_speed' => 15360,
                'upload_speed' => 7680,
                'burst_download' => null,
                'burst_upload' => null,
                'burst_threshold' => null,
                'burst_time' => null,
                'price' => 3500.00,
                'installation_fee' => 0.00,
                'activation_fee' => 0.00,
                'validity_period' => 7,
                'billing_cycle' => 'custom',
                'connection_type' => 'hotspot',
                'status' => 'active',
                'is_featured' => false,
            ],
        ];

        foreach ($packages as $pkg) {
            Package::firstOrCreate(
                ['organization_id' => $apex->id, 'code' => $pkg['code']],
                $pkg
            );
        }
    }
}
