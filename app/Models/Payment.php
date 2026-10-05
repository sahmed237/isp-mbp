<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'customer_id',
        'invoice_id',
        'payment_number',
        'amount',
        'payment_method',
        'reference',
        'status',
        'paid_at',
        'notes',
        'raw_payload',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->payment_number)) {
                $model->payment_number = 'PAY-' . date('Ym') . '-' . strtoupper(Str::random(5));
            }

            if (empty($model->paid_at)) {
                $model->paid_at = now();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function paymentAttempt(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PaymentAttempt::class);
    }

    public function getMethodBadgeClassAttribute(): string
    {
        return match ($this->payment_method) {
            'card', 'paystack', 'moniepoint' => 'bg-indigo-500/10 text-indigo-600 border border-indigo-500/20 dark:text-indigo-400',
            'bank_transfer' => 'bg-blue-500/10 text-blue-600 border border-blue-500/20 dark:text-blue-400',
            'cash' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400',
            'voucher' => 'bg-purple-500/10 text-purple-600 border border-purple-500/20 dark:text-purple-400',
            default => 'bg-slate-500/10 text-slate-600 border border-slate-500/20 dark:text-slate-400',
        };
    }
}
