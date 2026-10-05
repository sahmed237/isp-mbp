<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function getGroupedPermissions(): array
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $perm) {
            $parts = explode('.', $perm->name);
            $group = ucfirst($parts[0]);
            $grouped[$group][] = $perm;
        }

        return $grouped;
    }

    public function createRole(string $name, array $permissions): Role
    {
        return DB::transaction(function () use ($name, $permissions) {
            $role = Role::create(['name' => $name, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);

            AuditLog::record(
                action: 'created',
                description: "Security Role '{$role->name}' created with " . count($permissions) . " permissions.",
                newValues: ['name' => $role->name, 'permissions_count' => count($permissions)]
            );

            return $role;
        });
    }

    public function updateRole(Role $role, string $name, array $permissions): Role
    {
        return DB::transaction(function () use ($role, $name, $permissions) {
            $oldPermsCount = $role->permissions()->count();

            // Do not rename standard roles
            if (!in_array($role->name, ['Super Administrator', 'Organization Administrator'])) {
                $role->update(['name' => $name]);
            }

            $role->syncPermissions($permissions);

            AuditLog::record(
                action: 'updated',
                description: "Security Role '{$role->name}' updated. Permissions changed from {$oldPermsCount} to " . count($permissions) . ".",
                newValues: ['name' => $role->name, 'permissions_count' => count($permissions)]
            );

            return $role;
        });
    }

    public function deleteRole(Role $role): bool
    {
        return DB::transaction(function () use ($role) {
            AuditLog::record(
                action: 'deleted',
                description: "Security Role '{$role->name}' deleted.",
                oldValues: ['name' => $role->name]
            );

            return $role->delete();
        });
    }
}
