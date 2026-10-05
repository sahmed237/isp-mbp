<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with(['roles', 'organization', 'branch'])->latest();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $roles = $data['roles'] ?? [];
            unset($data['roles']);

            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            if (!empty($roles)) {
                $user->syncRoles($roles);
            }

            AuditLog::record(
                action: 'created',
                description: "Staff user '{$user->name}' ({$user->email}) created with roles: " . implode(', ', $roles),
                model: $user,
                newValues: $user->only(['name', 'email', 'status', 'organization_id', 'branch_id'])
            );

            return $user;
        });
    }

    public function updateUser(User $user, array $data, ?User $actor = null): User
    {
        return DB::transaction(function () use ($user, $data, $actor) {
            $oldValues = $user->only(['name', 'email', 'status', 'branch_id']);

            // Self-lockout prevention: cannot deactivate or remove admin from self
            if ($actor && $actor->id === $user->id) {
                if (isset($data['status']) && $data['status'] !== 'active') {
                    throw ValidationException::withMessages(['status' => 'You cannot deactivate your own administrative account.']);
                }
            }

            $roles = $data['roles'] ?? null;
            unset($data['roles']);

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            if ($roles !== null) {
                // Self-lockout prevention on Super Admin
                if ($actor && $actor->id === $user->id && $user->isSuperAdmin() && !in_array('Super Administrator', $roles)) {
                    throw ValidationException::withMessages(['roles' => 'You cannot revoke your own Super Administrator role.']);
                }
                $user->syncRoles($roles);
            }

            AuditLog::record(
                action: 'updated',
                description: "Staff user '{$user->name}' ({$user->email}) updated.",
                model: $user,
                oldValues: $oldValues,
                newValues: $user->only(array_keys($oldValues))
            );

            return $user;
        });
    }

    public function deleteUser(User $user, User $actor): bool
    {
        if ($user->id === $actor->id) {
            throw ValidationException::withMessages(['general' => 'You cannot delete your own administrative account.']);
        }

        return DB::transaction(function () use ($user) {
            AuditLog::record(
                action: 'deleted',
                description: "Staff user '{$user->name}' ({$user->email}) deleted.",
                model: $user,
                oldValues: $user->only(['name', 'email', 'status'])
            );

            return $user->delete();
        });
    }
}
