<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadAcct extends Model
{
    use HasFactory;

    protected $table = 'radacct';

    protected $primaryKey = 'radacctid';

    public $timestamps = false;

    protected $fillable = [
        'acctsessionid',
        'acctuniqueid',
        'username',
        'realm',
        'nasipaddress',
        'nasportid',
        'nasporttype',
        'acctstarttime',
        'acctupdatetime',
        'acctstoptime',
        'acctinterval',
        'acctsessiontime',
        'acctauthentic',
        'connectinfo_start',
        'connectinfo_stop',
        'acctinputoctets',
        'acctoutputoctets',
        'calledstationid',
        'callingstationid',
        'acctterminatecause',
        'servicetype',
        'framedprotocol',
        'framedipaddress',
        'framedipv6address',
        'framedipv6prefix',
        'framedinterfaceid',
        'delegatedipv6prefix',
        'class',
    ];

    protected $casts = [
        'acctstarttime' => 'datetime',
        'acctupdatetime' => 'datetime',
        'acctstoptime' => 'datetime',
        'acctsessiontime' => 'integer',
        'acctinputoctets' => 'integer',
        'acctoutputoctets' => 'integer',
    ];

    public function getIsActiveAttribute(): bool
    {
        return $this->acctstoptime === null;
    }

    public function getFormattedInputOctetsAttribute(): string
    {
        return self::formatBytes((int) $this->acctinputoctets);
    }

    public function getFormattedOutputOctetsAttribute(): string
    {
        return self::formatBytes((int) $this->acctoutputoctets);
    }

    public function getFormattedTotalOctetsAttribute(): string
    {
        return self::formatBytes((int) ($this->acctinputoctets + $this->acctoutputoctets));
    }

    public function getFormattedSessionTimeAttribute(): string
    {
        $seconds = (int) $this->acctsessiontime;
        if ($seconds <= 0 && $this->acctstarttime) {
            $seconds = max(0, now()->diffInSeconds($this->acctstarttime));
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dm %02ds', $hours, $minutes, $secs);
        }

        return sprintf('%02dm %02ds', $minutes, $secs);
    }

    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        $floor = (int) floor($base);
        $unit = $units[$floor] ?? 'TB';

        return round(pow(1024, $base - $floor), $precision) . ' ' . $unit;
    }
}
