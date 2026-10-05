<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class NetworkDevice extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'branch_id',
        'name',
        'device_type',
        'ip_address',
        'hostname',
        'mac_address',
        'vendor',
        'model',
        'location',
        'api_port',
        'ssh_port',
        'web_port',
        'username',
        'password',
        'status',
        'last_seen_at',
        'notes',
    ];

    protected $casts = [
        'api_port' => 'integer',
        'ssh_port' => 'integer',
        'web_port' => 'integer',
        'password' => 'encrypted',
        'last_seen_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getDeviceTypeLabelAttribute(): string
    {
        return match ($this->device_type) {
            'mikrotik_router' => 'MikroTik Router',
            'core_router' => 'Core Router',
            'access_router' => 'Access Router',
            'switch' => 'Access Switch',
            'access_point' => 'Wireless AP',
            'olt' => 'GPON / EPON OLT',
            'tower' => 'Base Station / Tower',
            'cpe' => 'Customer CPE',
            default => ucfirst(str_replace('_', ' ', $this->device_type)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'online' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400',
            'offline' => 'bg-rose-500/10 text-rose-600 border border-rose-500/20 dark:text-rose-400',
            'warning' => 'bg-amber-500/10 text-amber-600 border border-amber-500/20 dark:text-amber-400',
            'maintenance' => 'bg-blue-500/10 text-blue-600 border border-blue-500/20 dark:text-blue-400',
            default => 'bg-gray-500/10 text-gray-600 border border-gray-500/20 dark:text-gray-400',
        };
    }
}
