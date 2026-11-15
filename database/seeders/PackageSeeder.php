<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Package;
use App\Services\RadiusService;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $apex = Organization::where('code', 'APEX-NET')->first();

        if (! $apex) {
            return;
        }

        $radiusService = app(RadiusService::class);

        foreach (Package::defaultCatalog() as $pkg) {
            $package = Package::updateOrCreate(
                ['organization_id' => $apex->id, 'code' => $pkg['code']],
                $pkg
            );

            $radiusService->syncPackageToRadius($package);
        }
    }
}
