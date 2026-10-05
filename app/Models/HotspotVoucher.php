<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HotspotVoucher extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'package_id',
        'batch_id',
        'code',
        'username',
        'password',
        'price',
        'duration_value',
        'duration_unit',
        'data_limit_mb',
        'status',
        'first_used_at',
        'expires_at',
        'used_by_mac',
        'customer_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_value' => 'integer',
        'data_limit_mb' => 'integer',
        'first_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->code)) {
                $model->code = 'VCH-' . strtoupper(Str::random(8));
            }

            if (empty($model->username)) {
                $model->username = 'hs_' . strtolower(Str::random(6));
            }

            if (empty($model->password)) {
                $model->password = (string) random_int(100000, 999999);
            }
        });
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getDurationFormattedAttribute(): string
    {
        return "{$this->duration_value} " . ucfirst($this->duration_unit);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'unused' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400',
            'active' => 'bg-blue-500/10 text-blue-600 border border-blue-500/20 dark:text-blue-400',
            'used' => 'bg-slate-500/10 text-slate-600 border border-slate-500/20 dark:text-slate-400',
            'expired' => 'bg-rose-500/10 text-rose-600 border border-rose-500/20 dark:text-rose-400',
            'disabled' => 'bg-gray-500/10 text-gray-600 border border-gray-500/20 dark:text-gray-400',
            default => 'bg-gray-500/10 text-gray-600 border border-gray-500/20 dark:text-gray-400',
        };
    }
}
