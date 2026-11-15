<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Package;
use App\Models\Radius\Nas;
use App\Models\Radius\RadGroupCheck;
use App\Models\Radius\RadGroupReply;
use App\Services\RadiusService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Deploy default ISP packages and NAS entries into FreeRADIUS during migration.
     */
    public function up(): void
    {
        if (! Schema::hasTable('packages') || ! Schema::hasTable('radgroupreply')) {
            return;
        }

        // 1. Ensure a default organization exists so packages can be created during `php artisan migrate`
        $org = Organization::withoutGlobalScopes()->where('code', 'APEX-NET')->first()
            ?? Organization::withoutGlobalScopes()->first();

        if (! $org) {
            $org = Organization::create([
                'name' => 'Apex Broadband Networks Ltd',
                'code' => 'APEX-NET',
                'registration_number' => 'RC-1884920',
                'tax_id' => 'TIN-99281726-0001',
                'contact_email' => 'noc@apex-broadband.ng',
                'contact_phone' => '+234 809 000 1100',
                'address' => 'Plot 1042 Adetokunbo Ademola Crescent, Wuse 2, Abuja, FCT',
                'status' => 'active',
            ]);
        }

        $radiusService = app(RadiusService::class);

        // 2. Seed default packages and deploy them into FreeRADIUS (radgroupreply & radgroupcheck)
        foreach (Package::defaultCatalog() as $pkgData) {
            $package = Package::withoutGlobalScopes()->updateOrCreate(
                ['organization_id' => $org->id, 'code' => $pkgData['code']],
                $pkgData
            );

            $radiusService->syncPackageToRadius($package);
        }

        // 3. Also sync any other existing packages in the database to FreeRADIUS
        $existingPackages = Package::withoutGlobalScopes()->get();
        foreach ($existingPackages as $existingPackage) {
            $radiusService->syncPackageToRadius($existingPackage);
        }

        // 4. Deploy default NAS router entries from .env into FreeRADIUS `nas` table
        if (Schema::hasTable('nas')) {
            $defaultNasIp = (string) config('services.radius.default_nas_ip', '192.168.88.1');
            $tunnelNasIp = (string) config('services.radius.tunnel_nas_ip', '192.168.88.254');
            $shortName = (string) config('services.radius.default_nas_shortname', 'ISP-MBP-CCR2116-CORE');
            $secret = (string) config('services.radius.default_nas_secret', 'testing123');

            if ($defaultNasIp !== '') {
                Nas::updateOrCreate(
                    ['nasname' => $defaultNasIp],
                    [
                        'shortname' => $shortName,
                        'type' => 'mikrotik',
                        'secret' => $secret,
                        'description' => 'Primary MikroTik RouterOS NAS Gateway',
                    ]
                );
            }

            if ($tunnelNasIp !== '' && $tunnelNasIp !== $defaultNasIp) {
                Nas::updateOrCreate(
                    ['nasname' => $tunnelNasIp],
                    [
                        'shortname' => $shortName.'-SSTP',
                        'type' => 'mikrotik',
                        'secret' => $secret,
                        'description' => 'MikroTik RADIUS Tunnel Endpoint',
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('radgroupreply')) {
            $codes = array_column(Package::defaultCatalog(), 'code');
            RadGroupReply::whereIn('groupname', $codes)->delete();
            RadGroupCheck::whereIn('groupname', $codes)->delete();
        }
    }
};
