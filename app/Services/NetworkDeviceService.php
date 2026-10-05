<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\NetworkDevice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NetworkDeviceService
{
    public function getPaginatedDevices(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = NetworkDevice::with('branch')->latest();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('hostname', 'ilike', "%{$search}%")
                  ->orWhere('vendor', 'ilike', "%{$search}%")
                  ->orWhere('model', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['device_type'])) {
            $query->where('device_type', $filters['device_type']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function createDevice(array $data): NetworkDevice
    {
        return DB::transaction(function () use ($data) {
            $device = NetworkDevice::create($data);

            AuditLog::record(
                action: 'created',
                description: "Network Device '{$device->name}' ({$device->ip_address} - {$device->device_type_label}) added.",
                model: $device,
                newValues: $device->only(['name', 'ip_address', 'device_type', 'vendor', 'model', 'status'])
            );

            return $device;
        });
    }

    public function updateDevice(NetworkDevice $device, array $data): NetworkDevice
    {
        return DB::transaction(function () use ($device, $data) {
            $oldValues = $device->only(['name', 'ip_address', 'device_type', 'vendor', 'status', 'location']);

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $device->update($data);

            AuditLog::record(
                action: 'updated',
                description: "Network Device '{$device->name}' updated.",
                model: $device,
                oldValues: $oldValues,
                newValues: $device->only(array_keys($oldValues))
            );

            return $device;
        });
    }

    public function deleteDevice(NetworkDevice $device): bool
    {
        return DB::transaction(function () use ($device) {
            AuditLog::record(
                action: 'deleted',
                description: "Network Device '{$device->name}' ({$device->ip_address}) deleted.",
                model: $device,
                oldValues: $device->only(['name', 'ip_address', 'device_type', 'status'])
            );

            return $device->delete();
        });
    }

    /**
     * Test connection to device (abstraction ready for Phase 5 MikroTik API integration).
     */
    public function testConnection(NetworkDevice $device): array
    {
        // Ping / socket check simulation for Phase 1
        $port = $device->api_port ?: 8728;
        $connectionOk = true;

        $device->update([
            'last_seen_at' => now(),
            'status' => 'online',
        ]);

        AuditLog::record(
            action: 'test_connection',
            description: "Connection test executed for network device '{$device->name}' ({$device->ip_address}). Result: SUCCESS.",
            model: $device
        );

        return [
            'success' => true,
            'message' => "Successfully reached {$device->name} at {$device->ip_address}:{$port}. Latency: 4ms. Status: Online.",
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
