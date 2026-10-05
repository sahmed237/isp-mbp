<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\NetworkDevice;
use App\Models\Organization;
use App\Models\Package;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\BranchPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\NetworkDevicePolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\PackagePolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Explicit policy mappings
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Package::class, PackagePolicy::class);
        Gate::policy(NetworkDevice::class, NetworkDevicePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Branch::class, BranchPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        // Super Admin bypass for general permission abilities
        Gate::before(function ($user, $ability) {
            if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
        });
    }
}
