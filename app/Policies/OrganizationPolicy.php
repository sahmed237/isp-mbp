<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('organizations.view');
    }

    public function view(User $user, Organization $organization): bool
    {
        if (!$user->can('organizations.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $organization->id;
    }

    public function create(User $user): bool
    {
        // Only Super Admin can create new tenant organizations
        return $user->isSuperAdmin() && $user->can('organizations.create');
    }

    public function update(User $user, Organization $organization): bool
    {
        if (!$user->can('organizations.update')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $organization->id;
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin() && $user->can('organizations.delete');
    }
}
