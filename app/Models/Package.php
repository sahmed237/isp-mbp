<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'name',
        'code',
        'description',
        'download_speed',
        'upload_speed',
        'burst_download',
        'burst_upload',
        'burst_threshold',
        'burst_time',
        'price',
        'installation_fee',
        'activation_fee',
        'validity_period',
        'billing_cycle',
        'connection_type',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'download_speed' => 'integer',
        'upload_speed' => 'integer',
        'burst_download' => 'integer',
        'burst_upload' => 'integer',
        'burst_threshold' => 'integer',
        'burst_time' => 'integer',
        'price' => 'decimal:2',
        'installation_fee' => 'decimal:2',
        'activation_fee' => 'decimal:2',
        'validity_period' => 'integer',
        'is_featured' => 'boolean',
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

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'current_package_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(HotspotVoucher::class);
    }

    public function getFormattedDownloadSpeedAttribute(): string
    {
        return $this->download_speed >= 1024
            ? ($this->download_speed / 1024) . ' Mbps'
            : $this->download_speed . ' Kbps';
    }

    public function getFormattedUploadSpeedAttribute(): string
    {
        return $this->upload_speed >= 1024
            ? ($this->upload_speed / 1024) . ' Mbps'
            : $this->upload_speed . ' Kbps';
    }

    /**
     * Standard MikroTik rate-limit string representation (for future RADIUS/MikroTik phases).
     * e.g. 5M/10M 10M/20M 4M/8M 10/10
     */
    public function toMikrotikRateLimitString(): string
    {
        $rx = round($this->upload_speed / 1024, 1) . 'M';
        $tx = round($this->download_speed / 1024, 1) . 'M';

        if ($this->burst_upload && $this->burst_download) {
            $burstRx = round($this->burst_upload / 1024, 1) . 'M';
            $burstTx = round($this->burst_download / 1024, 1) . 'M';
            $threshold = round(($this->burst_threshold ?? ($this->download_speed * 0.75)) / 1024, 1) . 'M';
            $time = $this->burst_time ?? 15;

            return "{$rx}/{$tx} {$burstRx}/{$burstTx} {$threshold}/{$threshold} {$time}/{$time}";
        }

        return "{$rx}/{$tx}";
    }
}
