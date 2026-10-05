<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\NetworkDevice;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class NetworkDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $apex = Organization::where('code', 'APEX-NET')->first();
        $abujaBranch = Branch::where('code', 'ABJ-HQ')->first();
        $yolaBranch = Branch::where('code', 'YLA-01')->first();
        $kanoBranch = Branch::where('code', 'KAN-01')->first();

        $devices = [
            [
                'name' => 'ABJ-CCR2004-CORE-01',
                'device_type' => 'mikrotik_router',
                'ip_address' => '10.250.0.1',
                'hostname' => 'core01.abj.apex-broadband.ng',
                'mac_address' => '48:8F:5A:11:22:33',
                'vendor' => 'MikroTik',
                'model' => 'CCR2004-16G-2S+',
                'location' => 'Abuja HQ Server Room, Rack A1',
                'api_port' => 8728,
                'ssh_port' => 22,
                'web_port' => 80,
                'username' => 'api_admin',
                'password' => 'RouterOSSecret123!',
                'status' => 'online',
                'last_seen_at' => now(),
                'branch_id' => $abujaBranch->id,
                'notes' => 'Primary PPPoE and BGP gateway for FCT region.',
            ],
            [
                'name' => 'ABJ-GPON-OLT-01',
                'device_type' => 'olt',
                'ip_address' => '10.250.0.10',
                'hostname' => 'olt01.abj.apex-broadband.ng',
                'mac_address' => '70:7B:E8:44:55:66',
                'vendor' => 'Huawei',
                'model' => 'SmartAX MA5800-X7',
                'location' => 'Abuja HQ POP',
                'api_port' => 8728,
                'ssh_port' => 22,
                'web_port' => 443,
                'username' => 'root',
                'password' => 'HuaweiGponSecret123!',
                'status' => 'online',
                'last_seen_at' => now(),
                'branch_id' => $abujaBranch->id,
                'notes' => 'Serving Gwarinpa, Wuse 2, and Maitama FTTH zones.',
            ],
            [
                'name' => 'YLA-CCR1036-DIST-01',
                'device_type' => 'mikrotik_router',
                'ip_address' => '10.251.0.1',
                'hostname' => 'dist01.yla.apex-broadband.ng',
                'mac_address' => '6C:3B:6B:77:88:99',
                'vendor' => 'MikroTik',
                'model' => 'CCR1036-8G-2S+',
                'location' => 'Yola POP, Jimeta Tower',
                'api_port' => 8728,
                'ssh_port' => 22,
                'web_port' => 80,
                'username' => 'api_admin',
                'password' => 'RouterOSSecret123!',
                'status' => 'online',
                'last_seen_at' => now(),
                'branch_id' => $yolaBranch->id,
                'notes' => 'PtP Backhaul terminator and hotspot aggregator for Yola town.',
            ],
            [
                'name' => 'YLA-TOWER-SECTOR-NORTH',
                'device_type' => 'access_point',
                'ip_address' => '10.251.10.5',
                'hostname' => 'ap-north.yla.apex-broadband.ng',
                'mac_address' => 'B4:FB:E4:AA:BB:CC',
                'vendor' => 'Cambium Networks',
                'model' => 'ePMP 3000 4x4 MU-MIMO',
                'location' => 'Jimeta Main Tower, 45m Elevation',
                'api_port' => 8728,
                'ssh_port' => 22,
                'web_port' => 80,
                'username' => 'admin',
                'password' => 'CambiumSecret123!',
                'status' => 'online',
                'last_seen_at' => now(),
                'branch_id' => $yolaBranch->id,
                'notes' => '90-degree sector antenna facing Government House and Dougirei.',
            ],
            [
                'name' => 'KAN-CORE-SWITCH-01',
                'device_type' => 'switch',
                'ip_address' => '10.252.0.2',
                'hostname' => 'sw01.kan.apex-broadband.ng',
                'mac_address' => 'F4:8E:38:DD:EE:FF',
                'vendor' => 'Cisco',
                'model' => 'Catalyst CBS350-24T-4X',
                'location' => 'Kano Branch NOC Rack',
                'api_port' => 8728,
                'ssh_port' => 22,
                'web_port' => 80,
                'username' => 'cisco_admin',
                'password' => 'CiscoSwitchSecret123!',
                'status' => 'online',
                'last_seen_at' => now(),
                'branch_id' => $kanoBranch->id,
                'notes' => 'Distribution switch for Bompai industrial ring.',
            ],
            [
                'name' => 'KAN-TOWER-PTP-GRA',
                'device_type' => 'tower',
                'ip_address' => '10.252.20.1',
                'hostname' => 'ptp-gra.kan.apex-broadband.ng',
                'mac_address' => 'DC:2C:6E:12:34:56',
                'vendor' => 'Ubiquiti',
                'model' => 'airFiber 60 HD (AF60-HD)',
                'location' => 'Bompai Tower to Nassarawa GRA',
                'api_port' => 8728,
                'ssh_port' => 22,
                'web_port' => 80,
                'username' => 'ubnt',
                'password' => 'UbntSecret123!',
                'status' => 'warning',
                'last_seen_at' => now()->subMinutes(12),
                'branch_id' => $kanoBranch->id,
                'notes' => 'Heavy rain fade detected on 60GHz millimeter wave link.',
            ],
        ];

        foreach ($devices as $dev) {
            NetworkDevice::firstOrCreate(
                ['organization_id' => $apex->id, 'name' => $dev['name']],
                $dev
            );
        }
    }
}
