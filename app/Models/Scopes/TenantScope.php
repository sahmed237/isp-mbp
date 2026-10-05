<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Super Administrators can view cross-tenant records
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return;
            }

            if ($user->organization_id) {
                $builder->where($model->qualifyColumn('organization_id'), $user->organization_id);
            }
        }
    }
}
