<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            OrganizationSeeder::class,
            UserSeeder::class,
            PackageSeeder::class,
            CustomerSeeder::class,
            NetworkDeviceSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
