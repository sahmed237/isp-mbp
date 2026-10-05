<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $apex = Organization::where('code', 'APEX-NET')->first();
        $abujaBranch = Branch::where('code', 'ABJ-HQ')->first();
        $yolaBranch = Branch::where('code', 'YLA-01')->first();
        $nexus = Organization::where('code', 'NEXUS-FIBRE')->first();

        $defaultPassword = Hash::make('Password123!');

        $users = [
            [
                'name' => 'Adamu Bello',
                'email' => 'superadmin@isp-mbp.local',
                'phone' => '+234 803 000 0001',
                'role' => 'Super Administrator',
                'organization_id' => null, // Super Admin is system wide
                'branch_id' => null,
            ],
            [
                'name' => 'Chioma Okonkwo',
                'email' => 'orgadmin@isp-mbp.local',
                'phone' => '+234 803 000 0002',
                'role' => 'Organization Administrator',
                'organization_id' => $apex->id,
                'branch_id' => $abujaBranch->id,
            ],
            [
                'name' => 'Tunde Fashola',
                'email' => 'opsmanager@isp-mbp.local',
                'phone' => '+234 803 000 0003',
                'role' => 'Operations Manager',
                'organization_id' => $apex->id,
                'branch_id' => $abujaBranch->id,
            ],
            [
                'name' => 'Fatima Aliyu',
                'email' => 'billing@isp-mbp.local',
                'phone' => '+234 803 000 0004',
                'role' => 'Billing Manager',
                'organization_id' => $apex->id,
                'branch_id' => $abujaBranch->id,
            ],
            [
                'name' => 'Emeka Okafor',
                'email' => 'netadmin@isp-mbp.local',
                'phone' => '+234 803 000 0005',
                'role' => 'Network Administrator',
                'organization_id' => $apex->id,
                'branch_id' => $abujaBranch->id,
            ],
            [
                'name' => 'Zainab Danjuma',
                'email' => 'support@isp-mbp.local',
                'phone' => '+234 803 000 0006',
                'role' => 'Customer Support',
                'organization_id' => $apex->id,
                'branch_id' => $yolaBranch->id,
            ],
            [
                'name' => 'Babatunde Adeleke',
                'email' => 'accountant@isp-mbp.local',
                'phone' => '+234 803 000 0007',
                'role' => 'Accountant',
                'organization_id' => $apex->id,
                'branch_id' => $abujaBranch->id,
            ],
            [
                'name' => 'Khadijah Usman',
                'email' => 'readonly@isp-mbp.local',
                'phone' => '+234 803 000 0008',
                'role' => 'Read Only',
                'organization_id' => $apex->id,
                'branch_id' => $abujaBranch->id,
            ],
            // Secondary Organization User (to verify tenant isolation)
            [
                'name' => 'Femi Bakare',
                'email' => 'nexusadmin@isp-mbp.local',
                'phone' => '+234 809 000 0099',
                'role' => 'Organization Administrator',
                'organization_id' => $nexus->id,
                'branch_id' => null,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'password' => $defaultPassword,
                    'organization_id' => $userData['organization_id'],
                    'branch_id' => $userData['branch_id'],
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$userData['role']]);

            AuditLog::create([
                'organization_id' => $user->organization_id,
                'user_id' => null,
                'user_name' => 'System Seeder',
                'action' => 'created',
                'auditable_type' => User::class,
                'auditable_id' => (string) $user->id,
                'description' => "Initial user {$user->name} ({$userData['role']}) seeded.",
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Console Seeder',
                'created_at' => now(),
            ]);
        }
    }
}
