<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $apex = Organization::where('code', 'APEX-NET')->first();
        $abujaBranch = Branch::where('code', 'ABJ-HQ')->first();
        $yolaBranch = Branch::where('code', 'YLA-01')->first();
        $kanoBranch = Branch::where('code', 'KAN-01')->first();

        $pkgBasic = Package::where('code', 'PKG-HOME-5M')->first();
        $pkgStandard = Package::where('code', 'PKG-HOME-10M')->first();
        $pkgPremium = Package::where('code', 'PKG-HOME-20M')->first();
        $pkgBiz = Package::where('code', 'PKG-BIZ-50M')->first();
        $pkgCorp = Package::where('code', 'PKG-CORP-100M')->first();

        $customers = [
            [
                'account_number' => 'CUST-001001',
                'first_name' => 'Ibrahim',
                'last_name' => 'Shehu',
                'customer_type' => 'individual',
                'email' => 'ibrahim.shehu@example.ng',
                'phone' => '+234 803 111 4455',
                'installation_address' => 'House 14, 3rd Avenue, Gwarinpa Estate',
                'city' => 'Abuja',
                'state' => 'FCT',
                'gps_coordinates' => '9.1124,7.4112',
                'connection_type' => 'pppoe',
                'status' => 'active',
                'branch_id' => $abujaBranch->id,
                'current_package_id' => $pkgStandard->id,
                'balance' => 0.00,
            ],
            [
                'account_number' => 'CUST-001002',
                'first_name' => 'Ngozi',
                'last_name' => 'Eze',
                'customer_type' => 'individual',
                'email' => 'ngozi.eze@example.ng',
                'phone' => '+234 802 222 5566',
                'installation_address' => 'Flat 2B, Ocean View Apartments, Wuse 2',
                'city' => 'Abuja',
                'state' => 'FCT',
                'gps_coordinates' => '9.0765,7.4812',
                'connection_type' => 'fibre',
                'status' => 'active',
                'branch_id' => $abujaBranch->id,
                'current_package_id' => $pkgPremium->id,
                'balance' => 15000.00,
            ],
            [
                'account_number' => 'CUST-001003',
                'first_name' => 'Ahmed',
                'last_name' => 'Mustapha',
                'customer_type' => 'individual',
                'email' => 'ahmed.mustapha@example.ng',
                'phone' => '+234 813 333 6677',
                'installation_address' => 'Plot 55 Lamido Crescent, Jimeta',
                'city' => 'Yola',
                'state' => 'Adamawa',
                'gps_coordinates' => '9.2084,12.4818',
                'connection_type' => 'wireless',
                'status' => 'active',
                'branch_id' => $yolaBranch->id,
                'current_package_id' => $pkgBasic->id,
                'balance' => 0.00,
            ],
            [
                'account_number' => 'CUST-001004',
                'first_name' => 'Suleiman',
                'last_name' => 'Garko',
                'company_name' => 'Garko Agro Logistics Ltd',
                'customer_type' => 'corporate',
                'email' => 'tech@garkoagro.ng',
                'phone' => '+234 809 444 7788',
                'installation_address' => '102 Bompai Industrial Area',
                'city' => 'Kano',
                'state' => 'Kano',
                'gps_coordinates' => '12.0022,8.5920',
                'connection_type' => 'ptp',
                'status' => 'active',
                'branch_id' => $kanoBranch->id,
                'current_package_id' => $pkgBiz->id,
                'balance' => 0.00,
            ],
            [
                'account_number' => 'CUST-001005',
                'first_name' => 'Funke',
                'last_name' => 'Adeyemi',
                'customer_type' => 'individual',
                'email' => 'funke.adeyemi@example.ng',
                'phone' => '+234 805 555 8899',
                'installation_address' => 'Block 4, Mabushi Ministers Quarters',
                'city' => 'Abuja',
                'state' => 'FCT',
                'gps_coordinates' => '9.0833,7.4500',
                'connection_type' => 'pppoe',
                'status' => 'suspended',
                'branch_id' => $abujaBranch->id,
                'current_package_id' => $pkgStandard->id,
                'balance' => -22000.00, // overdue invoice
            ],
            [
                'account_number' => 'CUST-001006',
                'first_name' => 'Mansur',
                'last_name' => 'Ribadu',
                'company_name' => 'Savannah Microfinance Bank',
                'customer_type' => 'corporate',
                'email' => 'it@savannahmfb.ng',
                'phone' => '+234 803 666 9900',
                'installation_address' => '22 Galadima Aminu Way',
                'city' => 'Yola',
                'state' => 'Adamawa',
                'gps_coordinates' => '9.2155,12.4932',
                'connection_type' => 'dedicated',
                'status' => 'active',
                'branch_id' => $yolaBranch->id,
                'current_package_id' => $pkgCorp->id,
                'balance' => 0.00,
            ],
            [
                'account_number' => 'CUST-001007',
                'first_name' => 'Chinedu',
                'last_name' => 'Nwosu',
                'customer_type' => 'individual',
                'email' => 'chinedu.nwosu@example.ng',
                'phone' => '+234 807 777 0011',
                'installation_address' => 'Plot 8, Guzape Hills Extension',
                'city' => 'Abuja',
                'state' => 'FCT',
                'gps_coordinates' => '9.0345,7.5211',
                'connection_type' => 'fibre',
                'status' => 'expired',
                'branch_id' => $abujaBranch->id,
                'current_package_id' => $pkgPremium->id,
                'balance' => 0.00,
            ],
            [
                'account_number' => 'CUST-001008',
                'first_name' => 'Kareem',
                'last_name' => 'Lawal',
                'customer_type' => 'individual',
                'email' => 'kareem.lawal@example.ng',
                'phone' => '+234 812 888 1122',
                'installation_address' => 'No 3 State Lowcost Estate',
                'city' => 'Yola',
                'state' => 'Adamawa',
                'gps_coordinates' => '9.2311,12.4722',
                'connection_type' => 'hotspot',
                'status' => 'lead',
                'branch_id' => $yolaBranch->id,
                'current_package_id' => $pkgBasic->id,
                'balance' => 0.00,
            ],
            [
                'account_number' => 'CUST-001009',
                'first_name' => 'Amina',
                'last_name' => 'Bala',
                'company_name' => 'North Gate Academy',
                'customer_type' => 'corporate',
                'email' => 'info@northgateacademy.ng',
                'phone' => '+234 802 999 2233',
                'installation_address' => 'Plot 18 Zaria Road',
                'city' => 'Kano',
                'state' => 'Kano',
                'gps_coordinates' => '11.9812,8.5111',
                'connection_type' => 'ptmp',
                'status' => 'active',
                'branch_id' => $kanoBranch->id,
                'current_package_id' => $pkgBiz->id,
                'balance' => 0.00,
            ],
            [
                'account_number' => 'CUST-001010',
                'first_name' => 'David',
                'last_name' => 'Mark',
                'customer_type' => 'individual',
                'email' => 'david.mark@example.ng',
                'phone' => '+234 803 101 3344',
                'installation_address' => '7 Jabi Lake Estate, Jabi',
                'city' => 'Abuja',
                'state' => 'FCT',
                'gps_coordinates' => '9.0688,7.4255',
                'connection_type' => 'fibre',
                'status' => 'terminated',
                'branch_id' => $abujaBranch->id,
                'current_package_id' => $pkgBasic->id,
                'balance' => 0.00,
            ],
        ];

        foreach ($customers as $cust) {
            Customer::firstOrCreate(
                ['organization_id' => $apex->id, 'account_number' => $cust['account_number']],
                array_merge($cust, [
                    'portal_username' => strtolower($cust['account_number']),
                    'portal_password' => Hash::make('Portal123!'),
                ])
            );
        }

        // Customer in Organization 2 (Nexus) to verify multi-tenant isolation
        $nexus = Organization::where('code', 'NEXUS-FIBRE')->first();
        if ($nexus) {
            Customer::firstOrCreate(
                ['organization_id' => $nexus->id, 'account_number' => 'NEX-009001'],
                [
                    'first_name' => 'Olumide',
                    'last_name' => 'Ogunlesi',
                    'customer_type' => 'individual',
                    'email' => 'olumide@example.ng',
                    'phone' => '+234 809 999 8877',
                    'installation_address' => '15 Admiralty Way, Lekki Phase 1',
                    'city' => 'Lagos',
                    'state' => 'Lagos',
                    'connection_type' => 'fibre',
                    'status' => 'active',
                    'portal_username' => 'nex-009001',
                    'portal_password' => Hash::make('Portal123!'),
                ]
            );
        }
    }
}
