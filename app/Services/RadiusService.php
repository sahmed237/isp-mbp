<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Radius\Nas;
use App\Models\Radius\RadAcct;
use App\Models\Radius\RadCheck;
use App\Models\Radius\RadPostAuth;
use App\Models\Radius\RadReply;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RadiusService
{
    public function getStats(): array
    {
        $today = now()->startOfDay();

        $activeSessions = RadAcct::whereNull('acctstoptime')->count();
        $totalNas = Nas::count();
        $todaySuccess = RadPostAuth::where('authdate', '>=', $today)
            ->where('reply', 'ilike', '%accept%')
            ->count();
        $todayFailed = RadPostAuth::where('authdate', '>=', $today)
            ->where('reply', 'not ilike', '%accept%')
            ->count();

        $todayUpload = (int) RadAcct::where('acctstarttime', '>=', $today)->sum('acctinputoctets');
        $todayDownload = (int) RadAcct::where('acctstarttime', '>=', $today)->sum('acctoutputoctets');

        return [
            'active_sessions' => $activeSessions,
            'total_nas' => $totalNas,
            'today_success' => $todaySuccess,
            'today_failed' => $todayFailed,
            'today_upload_formatted' => RadAcct::formatBytes($todayUpload),
            'today_download_formatted' => RadAcct::formatBytes($todayDownload),
            'today_total_formatted' => RadAcct::formatBytes($todayUpload + $todayDownload),
        ];
    }

    public function getActiveSessions(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = RadAcct::whereNull('acctstoptime')->latest('acctstarttime');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('framedipaddress', 'like', "%{$search}%")
                  ->orWhere('nasipaddress', 'like', "%{$search}%")
                  ->orWhere('callingstationid', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'sessions_page')->withQueryString();
    }

    public function getHistoricalSessions(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = RadAcct::whereNotNull('acctstoptime')->latest('acctstoptime');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('framedipaddress', 'like', "%{$search}%")
                  ->orWhere('callingstationid', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['terminate_cause'])) {
            $query->where('acctterminatecause', $filters['terminate_cause']);
        }

        return $query->paginate($perPage, ['*'], 'history_page')->withQueryString();
    }

    public function getAuthLogs(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = RadPostAuth::latest('authdate');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('callingstationid', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'success') {
                $query->where('reply', 'ilike', '%accept%');
            } elseif ($filters['status'] === 'failed') {
                $query->where('reply', 'not ilike', '%accept%');
            }
        }

        return $query->paginate($perPage, ['*'], 'auth_page')->withQueryString();
    }

    public function getNasClients(int $perPage = 15): LengthAwarePaginator
    {
        return Nas::orderBy('nasname')->paginate($perPage, ['*'], 'nas_page')->withQueryString();
    }

    public function createNas(array $data): Nas
    {
        return DB::transaction(function () use ($data) {
            $nas = Nas::create($data);

            AuditLog::record(
                action: 'created',
                description: "RADIUS NAS router {$nas->shortname} ({$nas->nasname}) was created.",
                model: $nas,
                newValues: $nas->only(['nasname', 'shortname', 'type', 'description'])
            );

            return $nas;
        });
    }

    public function updateNas(Nas $nas, array $data): Nas
    {
        return DB::transaction(function () use ($nas, $data) {
            $oldValues = $nas->only(['nasname', 'shortname', 'type', 'description']);
            $nas->update($data);

            AuditLog::record(
                action: 'updated',
                description: "RADIUS NAS router {$nas->shortname} ({$nas->nasname}) was updated.",
                model: $nas,
                oldValues: $oldValues,
                newValues: $nas->only(['nasname', 'shortname', 'type', 'description'])
            );

            return $nas;
        });
    }

    public function deleteNas(Nas $nas): bool
    {
        return DB::transaction(function () use ($nas) {
            AuditLog::record(
                action: 'deleted',
                description: "RADIUS NAS router {$nas->shortname} ({$nas->nasname}) was removed.",
                model: $nas,
                oldValues: $nas->only(['nasname', 'shortname', 'type'])
            );

            return (bool) $nas->delete();
        });
    }

    public function syncCustomerToRadius(Customer $customer): void
    {
        if (empty($customer->radius_username)) {
            return;
        }

        $username = trim($customer->radius_username);

        DB::transaction(function () use ($customer, $username) {
            if ($customer->status === 'active') {
                // Remove any explicit Reject entries
                RadCheck::where('username', $username)->where('attribute', 'Auth-Type')->delete();

                // 1. Password check
                if (!empty($customer->radius_password)) {
                    RadCheck::updateOrCreate(
                        ['username' => $username, 'attribute' => 'Cleartext-Password'],
                        ['op' => ':=', 'value' => $customer->radius_password]
                    );
                }

                // 2. Package speed rate-limit reply
                if ($customer->package) {
                    RadReply::updateOrCreate(
                        ['username' => $username, 'attribute' => 'Mikrotik-Rate-Limit'],
                        ['op' => '=', 'value' => $customer->package->toMikrotikRateLimitString()]
                    );
                }

                // 3. Static IP Assignment
                if (!empty($customer->static_ip)) {
                    RadReply::updateOrCreate(
                        ['username' => $username, 'attribute' => 'Framed-IP-Address'],
                        ['op' => '=', 'value' => trim($customer->static_ip)]
                    );
                } else {
                    RadReply::where('username', $username)->where('attribute', 'Framed-IP-Address')->delete();
                }
            } else {
                // If suspended, expired, or terminated: reject RADIUS authentication immediately
                RadCheck::where('username', $username)->delete();
                RadReply::where('username', $username)->delete();

                RadCheck::create([
                    'username' => $username,
                    'attribute' => 'Auth-Type',
                    'op' => ':=',
                    'value' => 'Reject',
                ]);
            }
        });
    }

    public function deprovisionCustomerRadius(string $username): void
    {
        if (empty($username)) {
            return;
        }

        DB::transaction(function () use ($username) {
            RadCheck::where('username', $username)->delete();
            RadReply::where('username', $username)->delete();
        });
    }

    public function syncVoucherToRadius(\App\Models\HotspotVoucher $voucher): void
    {
        if (empty($voucher->username) || empty($voucher->password)) {
            return;
        }

        $username = trim($voucher->username);

        DB::transaction(function () use ($voucher, $username) {
            if ($voucher->status === 'unused' || $voucher->status === 'active') {
                RadCheck::where('username', $username)->where('attribute', 'Auth-Type')->delete();

                // 1. Password check
                RadCheck::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Cleartext-Password'],
                    ['op' => ':=', 'value' => $voucher->password]
                );

                // 2. Package speed rate-limit reply
                if ($voucher->package) {
                    RadReply::updateOrCreate(
                        ['username' => $username, 'attribute' => 'Mikrotik-Rate-Limit'],
                        ['op' => '=', 'value' => $voucher->package->toMikrotikRateLimitString()]
                    );
                }

                // 3. Session-Timeout calculation based on duration
                $durationSeconds = match ($voucher->duration_unit) {
                    'minutes' => $voucher->duration_value * 60,
                    'hours' => $voucher->duration_value * 3600,
                    'days' => $voucher->duration_value * 86400,
                    default => 3600,
                };

                RadReply::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Session-Timeout'],
                    ['op' => '=', 'value' => (string) $durationSeconds]
                );

                // 4. Simultaneous-Use = 1 (1 device per voucher)
                RadCheck::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Simultaneous-Use'],
                    ['op' => ':=', 'value' => '1']
                );
            } else {
                // If expired, used, or disabled: reject authentication
                RadCheck::where('username', $username)->delete();
                RadReply::where('username', $username)->delete();

                RadCheck::create([
                    'username' => $username,
                    'attribute' => 'Auth-Type',
                    'op' => ':=',
                    'value' => 'Reject',
                ]);
            }
        });
    }

    public function deprovisionVoucherRadius(string $username): void
    {
        if (empty($username)) {
            return;
        }

        DB::transaction(function () use ($username) {
            RadCheck::where('username', $username)->delete();
            RadReply::where('username', $username)->delete();
        });
    }
}
