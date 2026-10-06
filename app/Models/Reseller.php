<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Reseller extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'branch_id',
        'reseller_code',
        'business_name',
        'contact_person',
        'email',
        'phone',
        'alternate_phone',
        'password',
        'business_type',
        'shop_address',
        'city',
        'state',
        'id_type',
        'id_number',
        'id_card_path',
        'balance',
        'status',
        'rejection_reason',
        'approved_by_user_id',
        'approved_at',
        'notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'password' => 'hashed',
        'approved_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->reseller_code)) {
                $model->reseller_code = 'RSL-' . strtoupper(Str::random(6));
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(HotspotVoucher::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class)->latest();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasSufficientBalance(float|int|string $amount): bool
    {
        return (float) $this->balance >= (float) $amount;
    }

    public function deductBalance(float $amount, string $reason = ''): void
    {
        $newBalance = max(0, (float) $this->balance - $amount);
        $this->update(['balance' => $newBalance]);

        AuditLog::record(
            action: 'updated',
            description: "Deducted ₦" . number_format($amount, 2) . " from reseller wallet ({$this->business_name}). Reason: {$reason}",
            model: $this,
            newValues: ['balance' => $newBalance, 'deducted' => $amount, 'reason' => $reason]
        );
    }

    public function creditBalance(float $amount, string $reason = ''): void
    {
        $newBalance = (float) $this->balance + $amount;
        $this->update(['balance' => $newBalance]);

        AuditLog::record(
            action: 'updated',
            description: "Credited ₦" . number_format($amount, 2) . " to reseller wallet ({$this->business_name}). Reason: {$reason}",
            model: $this,
            newValues: ['balance' => $newBalance, 'credited' => $amount, 'reason' => $reason]
        );
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400',
            'pending' => 'bg-amber-500/10 text-amber-600 border border-amber-500/20 dark:text-amber-400',
            'suspended' => 'bg-rose-500/10 text-rose-600 border border-rose-500/20 dark:text-rose-400',
            'rejected' => 'bg-slate-500/10 text-slate-600 border border-slate-500/20 dark:text-slate-400',
            default => 'bg-gray-500/10 text-gray-600 border border-gray-500/20 dark:text-gray-400',
        };
    }
}
