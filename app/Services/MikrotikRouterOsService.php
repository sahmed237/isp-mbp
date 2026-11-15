<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\HotspotVoucher;
use App\Models\NetworkDevice;
use App\Models\Package;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Throwable;

class MikrotikRouterOsService
{
    /**
     * Determine whether a device uses MikroTik RouterOS.
     */
    public function isMikrotikDevice(NetworkDevice $device): bool
    {
        return $device->device_type === 'mikrotik_router'
            || strcasecmp((string) $device->vendor, 'MikroTik') === 0;
    }

    /**
     * Build a pre-configured HTTP client for RouterOS v7 REST API.
     */
    protected function client(NetworkDevice $device, int $connectTimeout = 2, int $timeout = 4): PendingRequest
    {
        $port = $device->web_port ?: 80;
        $scheme = $port === 443 ? 'https' : 'http';
        $username = $device->username ?: 'admin';
        $password = (string) ($device->password ?? '');

        return Http::baseUrl("{$scheme}://{$device->ip_address}:{$port}/rest")
            ->withBasicAuth($username, $password)
            ->acceptJson()
            ->asJson()
            ->withoutVerifying()
            ->connectTimeout($connectTimeout)
            ->timeout($timeout);
    }

    /**
     * Test live connectivity to a network device via RouterOS REST API or TCP socket probe.
     *
     * @return array{success: bool, message: string, timestamp: string, telemetry?: array<string, mixed>}
     */
    public function testConnection(NetworkDevice $device): array
    {
        $startTime = hrtime(true);

        if ($this->isMikrotikDevice($device)) {
            try {
                $response = $this->client($device, 2, 4)->get('/system/resource');

                if ($response->successful()) {
                    $latencyMs = (int) max(1, round((hrtime(true) - $startTime) / 1_000_000));
                    /** @var array<string, mixed> $resource */
                    $resource = $response->json() ?? [];

                    $boardName = (string) ($resource['board-name'] ?? $device->model ?? 'RouterOS');
                    $version = (string) ($resource['version'] ?? 'v7');
                    $uptime = (string) ($resource['uptime'] ?? 'N/A');
                    $cpuLoad = (string) ($resource['cpu-load'] ?? '0');

                    $updates = [
                        'last_seen_at' => now(),
                        'status' => 'online',
                    ];

                    if (empty($device->model) && ! empty($resource['board-name'])) {
                        $updates['model'] = $boardName;
                    }

                    $device->update($updates);

                    return [
                        'success' => true,
                        'message' => "Connected to {$device->name} ({$boardName} • RouterOS {$version}) at {$device->ip_address}. Latency: {$latencyMs}ms | CPU: {$cpuLoad}% | Uptime: {$uptime}.",
                        'timestamp' => now()->toIso8601String(),
                        'telemetry' => $resource,
                    ];
                }

                if ($response->status() === 401) {
                    $device->update(['status' => 'warning']);

                    return [
                        'success' => false,
                        'message' => "Reached {$device->name} at {$device->ip_address}, but RouterOS authentication failed (HTTP 401). Check API username and password.",
                        'timestamp' => now()->toIso8601String(),
                    ];
                }
            } catch (Throwable) {
                // Fall through to TCP socket check if REST port is unreachable
            }
        }

        $port = $device->api_port ?: 8728;
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($device->ip_address, $port, $errno, $errstr, 2.0);

        if (is_resource($socket)) {
            fclose($socket);
            $latencyMs = (int) max(1, round((hrtime(true) - $startTime) / 1_000_000));

            $device->update([
                'last_seen_at' => now(),
                'status' => 'online',
            ]);

            return [
                'success' => true,
                'message' => "Successfully reached {$device->name} at {$device->ip_address}:{$port}. Latency: {$latencyMs}ms. Status: Online.",
                'timestamp' => now()->toIso8601String(),
            ];
        }

        $device->update(['status' => 'offline']);

        return [
            'success' => false,
            'message' => "Unable to reach {$device->name} at {$device->ip_address}:{$port} ({$errstr}). Status marked Offline.",
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Fetch live telemetry from a MikroTik RouterOS v7 device for the device dashboard.
     *
     * @return array<string, mixed>|null
     */
    public function getLiveTelemetry(NetworkDevice $device): ?array
    {
        if (! $this->isMikrotikDevice($device)) {
            return null;
        }

        try {
            $port = $device->web_port ?: 80;
            $scheme = $port === 443 ? 'https' : 'http';
            $baseUrl = "{$scheme}://{$device->ip_address}:{$port}/rest";
            $username = $device->username ?: 'admin';
            $password = (string) ($device->password ?? '');

            $responses = Http::pool(fn (Pool $pool) => [
                $pool->as('resource')->withBasicAuth($username, $password)->connectTimeout(1)->timeout(2)->get("{$baseUrl}/system/resource"),
                $pool->as('identity')->withBasicAuth($username, $password)->connectTimeout(1)->timeout(2)->get("{$baseUrl}/system/identity"),
                $pool->as('interfaces')->withBasicAuth($username, $password)->connectTimeout(1)->timeout(2)->get("{$baseUrl}/interface"),
                $pool->as('addresses')->withBasicAuth($username, $password)->connectTimeout(1)->timeout(2)->get("{$baseUrl}/ip/address"),
                $pool->as('ppp_secrets')->withBasicAuth($username, $password)->connectTimeout(1)->timeout(2)->get("{$baseUrl}/ppp/secret"),
                $pool->as('ppp_active')->withBasicAuth($username, $password)->connectTimeout(1)->timeout(2)->get("{$baseUrl}/ppp/active"),
                $pool->as('hotspot_users')->withBasicAuth($username, $password)->connectTimeout(1)->timeout(2)->get("{$baseUrl}/ip/hotspot/user"),
                $pool->as('hotspot_active')->withBasicAuth($username, $password)->connectTimeout(1)->timeout(2)->get("{$baseUrl}/ip/hotspot/active"),
            ]);

            if (! isset($responses['resource']) || $responses['resource'] instanceof Throwable || ! $responses['resource']->successful()) {
                return null;
            }

            $resource = $responses['resource']->json() ?? [];
            $identity = (! ($responses['identity'] instanceof Throwable) && $responses['identity']->successful())
                ? ($responses['identity']->json()['name'] ?? $device->name)
                : $device->name;

            $freeMem = (int) ($resource['free-memory'] ?? 0);
            $totalMem = (int) ($resource['total-memory'] ?? 1);
            $usedMemPercent = $totalMem > 0 ? round((($totalMem - $freeMem) / $totalMem) * 100, 1) : 0.0;

            return [
                'identity' => $identity,
                'board_name' => $resource['board-name'] ?? $device->model,
                'version' => $resource['version'] ?? 'Unknown',
                'cpu' => $resource['cpu'] ?? 'ARM64',
                'cpu_count' => $resource['cpu-count'] ?? 1,
                'cpu_frequency' => $resource['cpu-frequency'] ?? 0,
                'cpu_load' => (int) ($resource['cpu-load'] ?? 0),
                'uptime' => $resource['uptime'] ?? '0s',
                'free_memory_gb' => round($freeMem / 1073741824, 2),
                'total_memory_gb' => round($totalMem / 1073741824, 2),
                'memory_used_percent' => $usedMemPercent,
                'interfaces' => (! ($responses['interfaces'] instanceof Throwable) && $responses['interfaces']->successful())
                    ? ($responses['interfaces']->json() ?? [])
                    : [],
                'addresses' => (! ($responses['addresses'] instanceof Throwable) && $responses['addresses']->successful())
                    ? ($responses['addresses']->json() ?? [])
                    : [],
                'ppp_secrets' => (! ($responses['ppp_secrets'] instanceof Throwable) && $responses['ppp_secrets']->successful())
                    ? ($responses['ppp_secrets']->json() ?? [])
                    : [],
                'ppp_active' => (! ($responses['ppp_active'] instanceof Throwable) && $responses['ppp_active']->successful())
                    ? ($responses['ppp_active']->json() ?? [])
                    : [],
                'hotspot_users' => (! ($responses['hotspot_users'] instanceof Throwable) && $responses['hotspot_users']->successful())
                    ? ($responses['hotspot_users']->json() ?? [])
                    : [],
                'hotspot_active' => (! ($responses['hotspot_active'] instanceof Throwable) && $responses['hotspot_active']->successful())
                    ? ($responses['hotspot_active']->json() ?? [])
                    : [],
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Synchronize a package's bandwidth profile to a MikroTik router (PPP & Hotspot profiles).
     */
    public function syncPackageToDevice(NetworkDevice $device, Package $package): bool
    {
        if (! $this->isMikrotikDevice($device)) {
            return false;
        }

        $profileName = $this->getPackageProfileName($package);
        $rateLimit = $package->toMikrotikRateLimitString();

        try {
            $client = $this->client($device);

            // 1. Sync PPP Profile
            $existingPpp = $client->get('/ppp/profile', ['name' => $profileName])->json() ?? [];
            $pppPayload = [
                'name' => $profileName,
                'local-address' => '10.100.0.1',
                'remote-address' => 'pppoe-pool',
                'rate-limit' => $rateLimit,
                'dns-server' => '8.8.8.8,1.1.1.1',
                'comment' => "ISP-MBP Plan: {$package->name}",
            ];

            if (! empty($existingPpp[0]['.id'])) {
                $client->patch('/ppp/profile/'.$existingPpp[0]['.id'], $pppPayload);
            } else {
                $client->post('/ppp/profile/add', $pppPayload);
            }

            // 2. Sync Hotspot User Profile
            $existingHs = $client->get('/ip/hotspot/user/profile', ['name' => $profileName])->json() ?? [];
            $hsPayload = [
                'name' => $profileName,
                'rate-limit' => $rateLimit,
                'shared-users' => '1',
            ];

            if (! empty($existingHs[0]['.id'])) {
                $client->patch('/ip/hotspot/user/profile/'.$existingHs[0]['.id'], $hsPayload);
            } else {
                $client->post('/ip/hotspot/user/profile/add', $hsPayload);
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Synchronize a customer's PPPoE secret and active session state to a MikroTik router.
     */
    public function syncCustomerToDevice(NetworkDevice $device, Customer $customer): bool
    {
        if (! $this->isMikrotikDevice($device) || empty($customer->radius_username)) {
            return false;
        }

        $username = trim($customer->radius_username);
        $password = (string) ($customer->radius_password ?: '123456');
        $isActive = $customer->status === 'active';

        try {
            $client = $this->client($device);

            $profileName = 'default';
            if ($customer->package) {
                $this->syncPackageToDevice($device, $customer->package);
                $profileName = $this->getPackageProfileName($customer->package);
            }

            $existing = $client->get('/ppp/secret', ['name' => $username])->json() ?? [];
            $payload = [
                'name' => $username,
                'password' => $password,
                'service' => 'pppoe',
                'profile' => $profileName,
                'disabled' => $isActive ? 'no' : 'yes',
                'comment' => "{$customer->full_name} ({$customer->account_number}) [{$customer->status}]",
            ];

            if (! empty($customer->static_ip)) {
                $payload['remote-address'] = trim($customer->static_ip);
            }

            if (! empty($existing[0]['.id'])) {
                $client->patch('/ppp/secret/'.$existing[0]['.id'], $payload);
            } else {
                $client->post('/ppp/secret/add', $payload);
            }

            // If customer is not active, disconnect any active PPPoE session immediately
            if (! $isActive) {
                $activeSessions = $client->get('/ppp/active', ['name' => $username])->json() ?? [];
                foreach ($activeSessions as $session) {
                    if (! empty($session['.id'])) {
                        $client->delete('/ppp/active/'.$session['.id']);
                    }
                }
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Remove a customer's PPPoE secret and disconnect active sessions on a MikroTik router.
     */
    public function removeCustomerFromDevice(NetworkDevice $device, string $username): bool
    {
        if (! $this->isMikrotikDevice($device) || empty($username)) {
            return false;
        }

        try {
            $client = $this->client($device);

            $existing = $client->get('/ppp/secret', ['name' => $username])->json() ?? [];
            foreach ($existing as $secret) {
                if (! empty($secret['.id'])) {
                    $client->delete('/ppp/secret/'.$secret['.id']);
                }
            }

            $activeSessions = $client->get('/ppp/active', ['name' => $username])->json() ?? [];
            foreach ($activeSessions as $session) {
                if (! empty($session['.id'])) {
                    $client->delete('/ppp/active/'.$session['.id']);
                }
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Synchronize a hotspot voucher user to a MikroTik router.
     */
    public function syncVoucherToDevice(NetworkDevice $device, HotspotVoucher $voucher): bool
    {
        if (! $this->isMikrotikDevice($device) || empty($voucher->username)) {
            return false;
        }

        $username = trim($voucher->username);
        $isUsable = in_array($voucher->status, ['unused', 'active'], true);

        $durationSeconds = match ($voucher->duration_unit) {
            'minutes' => $voucher->duration_value * 60,
            'hours' => $voucher->duration_value * 3600,
            'days' => $voucher->duration_value * 86400,
            default => 3600,
        };

        try {
            $client = $this->client($device);

            $profileName = 'default';
            if ($voucher->package) {
                $this->syncPackageToDevice($device, $voucher->package);
                $profileName = $this->getPackageProfileName($voucher->package);
            }

            $existing = $client->get('/ip/hotspot/user', ['name' => $username])->json() ?? [];
            $payload = [
                'name' => $username,
                'password' => (string) $voucher->password,
                'profile' => $profileName,
                'limit-uptime' => "{$durationSeconds}s",
                'disabled' => $isUsable ? 'no' : 'yes',
                'comment' => "Voucher {$voucher->code} ({$voucher->status})",
            ];

            if (! empty($voucher->data_limit_mb)) {
                $payload['limit-bytes-total'] = (string) ((int) $voucher->data_limit_mb * 1048576);
            }

            if (! empty($existing[0]['.id'])) {
                $client->patch('/ip/hotspot/user/'.$existing[0]['.id'], $payload);
            } else {
                $client->post('/ip/hotspot/user/add', $payload);
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Remove a hotspot voucher user from a MikroTik router.
     */
    public function removeVoucherFromDevice(NetworkDevice $device, string $username): bool
    {
        if (! $this->isMikrotikDevice($device) || empty($username)) {
            return false;
        }

        try {
            $client = $this->client($device);
            $existing = $client->get('/ip/hotspot/user', ['name' => $username])->json() ?? [];
            foreach ($existing as $user) {
                if (! empty($user['.id'])) {
                    $client->delete('/ip/hotspot/user/'.$user['.id']);
                }
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Synchronize all packages, PPPoE subscribers, and hotspot vouchers of the organization to a MikroTik device.
     *
     * @return array{success: bool, packages_synced: int, customers_synced: int, vouchers_synced: int, message: string}
     */
    public function syncAllToDevice(NetworkDevice $device): array
    {
        if (! $this->isMikrotikDevice($device)) {
            return [
                'success' => false,
                'packages_synced' => 0,
                'customers_synced' => 0,
                'vouchers_synced' => 0,
                'message' => "Device '{$device->name}' is not a MikroTik RouterOS device.",
            ];
        }

        $ping = $this->testConnection($device);
        if (! $ping['success']) {
            return [
                'success' => false,
                'packages_synced' => 0,
                'customers_synced' => 0,
                'vouchers_synced' => 0,
                'message' => $ping['message'],
            ];
        }

        $packages = Package::withoutGlobalScopes()
            ->where('organization_id', $device->organization_id)
            ->where('status', 'active')
            ->get();

        $packagesSynced = 0;
        foreach ($packages as $package) {
            if ($this->syncPackageToDevice($device, $package)) {
                $packagesSynced++;
            }
        }

        $customers = Customer::withoutGlobalScopes()
            ->with('package')
            ->where('organization_id', $device->organization_id)
            ->whereNotNull('radius_username')
            ->get();

        $customersSynced = 0;
        foreach ($customers as $customer) {
            if ($this->syncCustomerToDevice($device, $customer)) {
                $customersSynced++;
            }
        }

        $vouchers = HotspotVoucher::withoutGlobalScopes()
            ->with('package')
            ->where('organization_id', $device->organization_id)
            ->get();

        $vouchersSynced = 0;
        foreach ($vouchers as $voucher) {
            if ($this->syncVoucherToDevice($device, $voucher)) {
                $vouchersSynced++;
            }
        }

        return [
            'success' => true,
            'packages_synced' => $packagesSynced,
            'customers_synced' => $customersSynced,
            'vouchers_synced' => $vouchersSynced,
            'message' => "Synced {$packagesSynced} bandwidth profiles, {$customersSynced} PPPoE subscribers, and {$vouchersSynced} Hotspot vouchers to {$device->name} ({$device->ip_address}).",
        ];
    }

    /**
     * Push customer changes to all online MikroTik routers in the customer's organization.
     */
    public function syncCustomerAcrossOrganization(Customer $customer): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $devices = NetworkDevice::withoutGlobalScopes()
            ->where('organization_id', $customer->organization_id)
            ->where('device_type', 'mikrotik_router')
            ->where('status', 'online')
            ->get();

        foreach ($devices as $device) {
            $this->syncCustomerToDevice($device, $customer);
        }
    }

    /**
     * Push voucher changes to all online MikroTik routers in the voucher's organization.
     */
    public function syncVoucherAcrossOrganization(HotspotVoucher $voucher): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $devices = NetworkDevice::withoutGlobalScopes()
            ->where('organization_id', $voucher->organization_id)
            ->where('device_type', 'mikrotik_router')
            ->where('status', 'online')
            ->get();

        foreach ($devices as $device) {
            $this->syncVoucherToDevice($device, $voucher);
        }
    }

    protected function getPackageProfileName(Package $package): string
    {
        $code = ! empty($package->code) ? $package->code : ('PLAN-'.$package->id);

        return 'ISPMBP-'.strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '-', $code));
    }
}
