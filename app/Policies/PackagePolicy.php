<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Package;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PackagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('packages.view');
    }

    public function view(User $user, Package $package): bool
    {
        if (!$user->can('packages.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $package->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('packages.create');
    }

    public function update(User $user, Package $package): bool
    {
        if (!$user->can('packages.update')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $package->organization_id;
    }

    public function delete(User $user, Package $package): bool
    {
        if (!$user->can('packages.delete')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $package->organization_id;
    }
}
