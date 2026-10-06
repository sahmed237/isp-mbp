<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'user_id',
        'user_name',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an immutable audit log entry.
     */
    public static function record(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $organizationId = null,
        ?string $resourceType = null,
        ?string $resourceId = null
    ): self {
        $webUser = Auth::guard('web')->user();
        $customerUser = Auth::guard('customer')->user();
        $resellerUser = Auth::guard('reseller')->user();

        $actorName = $webUser?->name
            ?? ($resellerUser ? "Reseller: {$resellerUser->business_name}" : null)
            ?? ($customerUser ? "Subscriber: {$customerUser->full_name}" : 'System');

        $orgId = $organizationId
            ?? ($webUser?->organization_id ?? $resellerUser?->organization_id ?? $customerUser?->organization_id ?? $model?->organization_id ?? null);

        $auditableType = $resourceType ?? ($model ? get_class($model) : null);
        $auditableId = $resourceId ?? ($model ? (string) $model->getKey() : null);

        return self::create([
            'organization_id' => $orgId,
            'user_id' => $webUser?->id,
            'user_name' => $actorName,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
