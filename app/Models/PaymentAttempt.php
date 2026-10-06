<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentAttempt extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'customer_id',
        'reseller_id',
        'invoice_id',
        'payment_id',
        'voucher_id',
        'batch_id',
        'payment_method',
        'reference',
        'amount',
        'currency',
        'status',
        'customer_email',
        'customer_phone',
        'ip_address',
        'user_agent',
        'request_payload',
        'response_payload',
        'verification_payload',
        'error_message',
        'initiated_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'verification_payload' => 'array',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->currency)) {
                $model->currency = 'NGN';
            }

            if (empty($model->initiated_at)) {
                $model->initiated_at = now();
            }
        });

        static::created(function (self $model) {
            if ($model->invoice_id) {
                static::invalidatePreviousAttemptsForInvoice(
                    invoiceId: (int) $model->invoice_id,
                    currentReference: (string) $model->reference
                );
            }
        });
    }

    /**
     * Invalidate and mark unfinalized previous payment attempts as failed/superseded
     */
    public static function invalidatePreviousAttemptsForInvoice(int|string $invoiceId, ?string $currentReference = null): int
    {
        $query = static::where('invoice_id', $invoiceId)
            ->whereIn('status', ['initiated', 'pending'])
            ->whereNull('payment_id');

        if ($currentReference) {
            $query->where('reference', '!=', $currentReference);
        }

        return $query->update([
            'status' => 'failed',
            'error_message' => $currentReference
                ? "Invalidated / Superseded: A newer payment attempt (#{$currentReference}) was initiated."
                : 'Invalidated / Superseded: A newer payment attempt was initiated.',
            'completed_at' => now(),
        ]);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(HotspotVoucher::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['initiated', 'pending'], true);
    }

    public function isSuperseded(): bool
    {
        return $this->status === 'failed' && str_contains(strtolower($this->error_message ?? ''), 'superseded');
    }
}

