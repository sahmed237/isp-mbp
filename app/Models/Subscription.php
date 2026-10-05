<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Subscription extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'customer_id',
        'package_id',
        'subscription_number',
        'status',
        'starts_at',
        'expires_at',
        'price',
        'billing_cycle',
        'auto_renew',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'auto_renew' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->subscription_number)) {
                $model->subscription_number = 'SUB-' . date('Ym') . '-' . strtoupper(Str::random(5));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->expires_at || $this->expires_at->isPast()) {
            return 0;
        }

        return (int) ceil(now()->diffInDays($this->expires_at, false));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400',
            'pending' => 'bg-amber-500/10 text-amber-600 border border-amber-500/20 dark:text-amber-400',
            'suspended' => 'bg-rose-500/10 text-rose-600 border border-rose-500/20 dark:text-rose-400',
            'expired' => 'bg-slate-500/10 text-slate-600 border border-slate-500/20 dark:text-slate-400',
            'cancelled' => 'bg-red-500/10 text-red-600 border border-red-500/20 dark:text-red-400',
            default => 'bg-gray-500/10 text-gray-600 border border-gray-500/20 dark:text-gray-400',
        };
    }
}
