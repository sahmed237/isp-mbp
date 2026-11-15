<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\RadiusService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Package extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

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

        static::saved(function (self $package): void {
            if (Schema::hasTable('radgroupreply')) {
                app(RadiusService::class)->syncPackageToRadius($package);
            }
        });

        static::deleted(function (self $package): void {
            if (Schema::hasTable('radgroupreply')) {
                app(RadiusService::class)->deprovisionPackageRadius($package);
            }
        });

        static::saved(function (self $package): void {
            if (\Illuminate\Support\Facades\Schema::hasTable('radgroupreply')) {
                app(\App\Services\RadiusService::class)->syncPackageToRadius($package);
            }
        });

        static::deleted(function (self $package): void {
            if (\Illuminate\Support\Facades\Schema::hasTable('radgroupreply')) {
                app(\App\Services\RadiusService::class)->deprovisionPackageRadius($package);
            }
        });
    }

    public function getRadiusGroupName(): string
    {
        return ! empty($this->code) ? trim($this->code) : 'PKG-'.$this->id;
    }

    /**
     * Standard ISP package catalog deployed during migration and seeding.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaultCatalog(): array
    {
        return [
            [
                'name' => 'Home Basic 5 Mbps',
                'code' => 'PKG-HOME-5M',
                'description' => 'Reliable entry-level broadband for light browsing, SD streaming, and remote schooling.',
                'download_speed' => 5120,
                'upload_speed' => 2048,
                'burst_download' => 7168,
                'burst_upload' => 3072,
                'burst_threshold' => 4096,
                'burst_time' => 20,
                'price' => 12500.00,
                'installation_fee' => 25000.00,
                'activation_fee' => 0.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'pppoe',
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'name' => 'Home Standard 10 Mbps',
                'code' => 'PKG-HOME-10M',
                'description' => 'Fast broadband for multiple HD streaming devices, Zoom calls, and gaming.',
                'download_speed' => 10240,
                'upload_speed' => 5120,
                'burst_download' => 15360,
                'burst_upload' => 7680,
                'burst_threshold' => 8192,
                'burst_time' => 25,
                'price' => 22000.00,
                'installation_fee' => 25000.00,
                'activation_fee' => 0.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'pppoe',
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'name' => 'Home Premium 20 Mbps',
                'code' => 'PKG-HOME-20M',
                'description' => 'Ultra-fast home connectivity for 4K streaming and power smart home households.',
                'download_speed' => 20480,
                'upload_speed' => 10240,
                'burst_download' => 30720,
                'burst_upload' => 15360,
                'burst_threshold' => 16384,
                'burst_time' => 30,
                'price' => 38500.00,
                'installation_fee' => 20000.00,
                'activation_fee' => 0.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'fibre',
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'name' => 'Business Pro 50 Mbps',
                'code' => 'PKG-BIZ-50M',
                'description' => 'Symmetric enterprise link with 99.8% SLA, static public IP, and priority queuing.',
                'download_speed' => 51200,
                'upload_speed' => 51200,
                'burst_download' => 61440,
                'burst_upload' => 61440,
                'burst_threshold' => 45000,
                'burst_time' => 30,
                'price' => 95000.00,
                'installation_fee' => 50000.00,
                'activation_fee' => 10000.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'ptp',
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'name' => 'Enterprise Leased Line 100 Mbps',
                'code' => 'PKG-CORP-100M',
                'description' => 'Dedicated 1:1 contention ratio, dual-homed redundant fiber, 24/7 proactive NOC monitoring.',
                'download_speed' => 102400,
                'upload_speed' => 102400,
                'burst_download' => null,
                'burst_upload' => null,
                'burst_threshold' => null,
                'burst_time' => null,
                'price' => 220000.00,
                'installation_fee' => 100000.00,
                'activation_fee' => 25000.00,
                'validity_period' => 30,
                'billing_cycle' => 'monthly',
                'connection_type' => 'dedicated',
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'name' => 'Hotspot 1 Hour Pass',
                'code' => 'PKG-HS-1H',
                'description' => 'Fast 5 Mbps Wi-Fi access valid for 1 hour on guest captive portal.',
                'download_speed' => 5120,
                'upload_speed' => 2048,
                'burst_download' => null,
                'burst_upload' => null,
                'burst_threshold' => null,
                'burst_time' => null,
                'price' => 200.00,
                'installation_fee' => 0.00,
                'activation_fee' => 0.00,
                'validity_period' => 1,
                'billing_cycle' => 'custom',
                'connection_type' => 'hotspot',
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'name' => 'Hotspot 24 Hours Unlimited',
                'code' => 'PKG-HS-24H',
                'description' => '10 Mbps uncapped 24-hour day pass for Wi-Fi hotspot subscribers.',
                'download_speed' => 10240,
                'upload_speed' => 5120,
                'burst_download' => null,
                'burst_upload' => null,
                'burst_threshold' => null,
                'burst_time' => null,
                'price' => 800.00,
                'installation_fee' => 0.00,
                'activation_fee' => 0.00,
                'validity_period' => 1,
                'billing_cycle' => 'custom',
                'connection_type' => 'hotspot',
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'name' => 'Hotspot 7 Days Weekly Pass',
                'code' => 'PKG-HS-7D',
                'description' => '7-day unlimited high-speed access for guest and residential hotspot areas.',
                'download_speed' => 15360,
                'upload_speed' => 7680,
                'burst_download' => null,
                'burst_upload' => null,
                'burst_threshold' => null,
                'burst_time' => null,
                'price' => 3500.00,
                'installation_fee' => 0.00,
                'activation_fee' => 0.00,
                'validity_period' => 7,
                'billing_cycle' => 'custom',
                'connection_type' => 'hotspot',
                'status' => 'active',
                'is_featured' => false,
            ],
        ];
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
            ? ($this->download_speed / 1024).' Mbps'
            : $this->download_speed.' Kbps';
    }

    public function getFormattedUploadSpeedAttribute(): string
    {
        return $this->upload_speed >= 1024
            ? ($this->upload_speed / 1024).' Mbps'
            : $this->upload_speed.' Kbps';
    }

    /**
     * Standard MikroTik rate-limit string representation (for future RADIUS/MikroTik phases).
     * e.g. 5M/10M 10M/20M 4M/8M 10/10
     */
    public function toMikrotikRateLimitString(): string
    {
        $rx = $this->formatSpeedInMegabits((int) $this->upload_speed);
        $tx = $this->formatSpeedInMegabits((int) $this->download_speed);

        if ($this->burst_upload && $this->burst_download) {
            $burstRx = $this->formatSpeedInMegabits((int) $this->burst_upload);
            $burstTx = $this->formatSpeedInMegabits((int) $this->burst_download);

            $thresholdTxKbps = min(
                (int) ($this->burst_threshold ?? ($this->download_speed * 0.75)),
                (int) $this->burst_download
            );
            $thresholdRxKbps = $this->upload_speed === $this->download_speed
                ? min($thresholdTxKbps, (int) $this->burst_upload)
                : min((int) ($this->upload_speed * 0.75), (int) $this->burst_upload);

            $thresholdRx = $this->formatSpeedInMegabits($thresholdRxKbps);
            $thresholdTx = $this->formatSpeedInMegabits($thresholdTxKbps);
            $time = $this->burst_time ?? 15;

            return "{$rx}/{$tx} {$burstRx}/{$burstTx} {$thresholdRx}/{$thresholdTx} {$time}/{$time}";
        }

        return "{$rx}/{$tx}";
    }

    protected function formatSpeedInMegabits(int $speedKbps): string
    {
        if ($speedKbps <= 0) {
            return '1M';
        }

        if ($speedKbps <= 100) {
            return "{$speedKbps}M";
        }

        if ($speedKbps % 1024 === 0) {
            return (int) ($speedKbps / 1024).'M';
        }

        return "{$speedKbps}k";
    }
}
