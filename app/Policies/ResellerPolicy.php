<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Reseller;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResellerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('resellers.view');
    }

    public function view(User $user, Reseller $reseller): bool
    {
        if (!$user->can('resellers.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $reseller->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('resellers.create');
    }

    public function update(User $user, Reseller $reseller): bool
    {
        if (!$user->can('resellers.update')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $reseller->organization_id;
    }

    public function delete(User $user, Reseller $reseller): bool
    {
        if (!$user->can('resellers.delete')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $reseller->organization_id;
    }

    public function approve(User $user, Reseller $reseller): bool
    {
        if (!$user->can('resellers.approve')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $reseller->organization_id;
    }
}
