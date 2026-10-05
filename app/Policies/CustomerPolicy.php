<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        if (!$user->can('customers.view')) {
            return false;
        }

        // Multi-tenant check: super admin can view any, otherwise must match organization
        return $user->isSuperAdmin() || $user->organization_id === $customer->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('customers.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        if (!$user->can('customers.update')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $customer->organization_id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        if (!$user->can('customers.delete')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $customer->organization_id;
    }

    public function export(User $user): bool
    {
        return $user->can('customers.export');
    }
}
