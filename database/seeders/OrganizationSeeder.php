<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Primary ISP Tenant
        $apex = Organization::firstOrCreate(
            ['code' => 'APEX-NET'],
            [
                'name' => 'Apex Broadband Communications Ltd',
                'email' => 'ops@apex-broadband.ng',
                'phone' => '+234 803 123 4567',
                'address' => 'Plot 412, Constitution Avenue, Central Business District',
                'city' => 'Abuja',
                'state' => 'FCT',
                'country' => 'Nigeria',
                'currency' => 'NGN',
                'currency_symbol' => '₦',
                'timezone' => 'Africa/Lagos',
                'status' => 'active',
                'settings' => [
                    'vat_rate' => 7.5,
                    'tax_identification_number' => 'TIN-98471928-001',
                    'support_email' => 'support@apex-broadband.ng',
                ],
            ]
        );

        // 3 Branches
        Branch::firstOrCreate(
            ['organization_id' => $apex->id, 'code' => 'ABJ-HQ'],
            [
                'name' => 'Abuja Central Branch (HQ)',
                'email' => 'abuja@apex-broadband.ng',
                'phone' => '+234 803 123 4567',
                'address' => 'Plot 412 Constitution Ave, CBD',
                'city' => 'Abuja',
                'state' => 'FCT',
                'status' => 'active',
            ]
        );

        Branch::firstOrCreate(
            ['organization_id' => $apex->id, 'code' => 'YLA-01'],
            [
                'name' => 'Yola Regional Branch',
                'email' => 'yola@apex-broadband.ng',
                'phone' => '+234 803 987 6543',
                'address' => '14 Atiku Abubakar Way, Jimeta',
                'city' => 'Yola',
                'state' => 'Adamawa',
                'status' => 'active',
            ]
        );

        Branch::firstOrCreate(
            ['organization_id' => $apex->id, 'code' => 'KAN-01'],
            [
                'name' => 'Kano Commercial Branch',
                'email' => 'kano@apex-broadband.ng',
                'phone' => '+234 802 345 6789',
                'address' => '28 Bompai Road, Industrial Layout',
                'city' => 'Kano',
                'state' => 'Kano',
                'status' => 'active',
            ]
        );

        // Secondary Tenant (to test strict cross-organization server-side isolation)
        $nexus = Organization::firstOrCreate(
            ['code' => 'NEXUS-FIBRE'],
            [
                'name' => 'Nexus Fibre Networks Ltd',
                'email' => 'info@nexusfibre.ng',
                'phone' => '+234 809 111 2233',
                'address' => '12 Marina Street, Lagos Island',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'currency' => 'NGN',
                'currency_symbol' => '₦',
                'timezone' => 'Africa/Lagos',
                'status' => 'active',
            ]
        );

        Branch::firstOrCreate(
            ['organization_id' => $nexus->id, 'code' => 'LOS-01'],
            [
                'name' => 'Lagos Mainland Branch',
                'email' => 'lagos@nexusfibre.ng',
                'phone' => '+234 809 111 2233',
                'address' => '12 Marina Street',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'status' => 'active',
            ]
        );
    }
}
