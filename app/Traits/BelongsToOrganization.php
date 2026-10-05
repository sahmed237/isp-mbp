<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Organization;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToOrganization
{
    /**
     * Boot the trait to attach global TenantScope and set default organization_id.
     */
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (empty($model->organization_id)) {
                if (Auth::check() && Auth::user()->organization_id) {
                    $model->organization_id = Auth::user()->organization_id;
                } else {
                    $model->organization_id = Organization::first()?->id;
                }
            }
        });
    }

    /**
     * Get the organization that owns this entity.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
