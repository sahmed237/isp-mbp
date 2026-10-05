<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'customer_id',
        'subscription_id',
        'invoice_number',
        'status',
        'issue_date',
        'due_date',
        'paid_at',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->invoice_number)) {
                $model->invoice_number = 'INV-' . date('Ym') . '-' . strtoupper(Str::random(5));
            }

            if (empty($model->issue_date)) {
                $model->issue_date = now()->toDateString();
            }

            if (empty($model->due_date)) {
                $model->due_date = now()->addDays(7)->toDateString();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class)->latest();
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0.00, (float) $this->total_amount - (float) $this->paid_amount);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400',
            'unpaid' => 'bg-amber-500/10 text-amber-600 border border-amber-500/20 dark:text-amber-400',
            'partially_paid' => 'bg-blue-500/10 text-blue-600 border border-blue-500/20 dark:text-blue-400',
            'overdue' => 'bg-rose-500/10 text-rose-600 border border-rose-500/20 dark:text-rose-400',
            'cancelled' => 'bg-slate-500/10 text-slate-600 border border-slate-500/20 dark:text-slate-400',
            default => 'bg-gray-500/10 text-gray-600 border border-gray-500/20 dark:text-gray-400',
        };
    }
}
