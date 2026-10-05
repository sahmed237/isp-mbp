<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('branches.view');
    }

    public function view(User $user, Branch $branch): bool
    {
        if (!$user->can('branches.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $branch->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('branches.create');
    }

    public function update(User $user, Branch $branch): bool
    {
        if (!$user->can('branches.update')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $branch->organization_id;
    }

    public function delete(User $user, Branch $branch): bool
    {
        if (!$user->can('branches.delete')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $branch->organization_id;
    }
}
