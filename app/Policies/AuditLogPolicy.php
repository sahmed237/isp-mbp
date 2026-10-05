<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('audit_logs.view');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        if (!$user->can('audit_logs.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $auditLog->organization_id;
    }

    // Immutable audit trail: strictly denied for all users
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    public function delete(User $user, AuditLog $auditLog): bool
    {
        return false;
    }
}
