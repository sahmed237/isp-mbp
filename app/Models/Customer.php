<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'branch_id',
        'current_package_id',
        'account_number',
        'first_name',
        'last_name',
        'company_name',
        'customer_type',
        'email',
        'phone',
        'alternate_phone',
        'installation_address',
        'city',
        'state',
        'gps_coordinates',
        'connection_type',
        'status',
        'radius_username',
        'radius_password',
        'static_ip',
        'portal_username',
        'portal_password',
        'balance',
        'notes',
    ];

    protected $hidden = [
        'portal_password',
        'remember_token',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'portal_password' => 'hashed',
    ];

    public function getAuthPassword(): string
    {
        return (string) $this->portal_password;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->account_number)) {
                $model->account_number = 'CUST-' . strtoupper(Str::random(6));
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'current_package_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->latest();
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('id');
    }

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest('id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest();
    }

    public function unpaidInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class)
            ->whereIn('status', ['unpaid', 'overdue', 'partially_paid'])
            ->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(HotspotVoucher::class)->latest();
    }

    public function radiusSessions(): HasMany
    {
        return $this->hasMany(\App\Models\Radius\RadAcct::class, 'username', 'radius_username');
    }

    public function activeRadiusSession(): HasOne
    {
        return $this->hasOne(\App\Models\Radius\RadAcct::class, 'username', 'radius_username')
            ->whereNull('acctstoptime')
            ->latest('acctstarttime');
    }

    public function radiusAuthLogs(): HasMany
    {
        return $this->hasMany(\App\Models\Radius\RadPostAuth::class, 'username', 'radius_username')
            ->latest('authdate');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(\App\Models\CustomerDocument::class)->latest();
    }

    public function getFullNameAttribute(): string
    {
        if ($this->customer_type === 'corporate' && !empty($this->company_name)) {
            return "{$this->company_name} ({$this->first_name} {$this->last_name})";
        }
        return "{$this->first_name} {$this->last_name}";
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400',
            'suspended' => 'bg-amber-500/10 text-amber-600 border border-amber-500/20 dark:text-amber-400',
            'expired' => 'bg-rose-500/10 text-rose-600 border border-rose-500/20 dark:text-rose-400',
            'lead' => 'bg-blue-500/10 text-blue-600 border border-blue-500/20 dark:text-blue-400',
            'terminated' => 'bg-slate-500/10 text-slate-600 border border-slate-500/20 dark:text-slate-400',
            default => 'bg-gray-500/10 text-gray-600 border border-gray-500/20 dark:text-gray-400',
        };
    }
}
