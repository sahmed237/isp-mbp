<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        if (!$user->can('users.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        if (!$user->can('users.update')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, User $model): bool
    {
        // Prevent deleting self!
        if ($user->id === $model->id) {
            return false;
        }

        if (!$user->can('users.delete')) {
            return false;
        }

        // Only super admin can delete a super admin
        if ($model->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $model->organization_id;
    }
}
