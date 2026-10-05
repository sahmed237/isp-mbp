<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Package;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PackageService
{
    public function getPaginatedPackages(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Package::withCount('customers')
            ->orderBy('price', 'asc');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['connection_type'])) {
            $query->where('connection_type', $filters['connection_type']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function createPackage(array $data): Package
    {
        return DB::transaction(function () use ($data) {
            $package = Package::create($data);

            AuditLog::record(
                action: 'created',
                description: "Internet Package '{$package->name}' ({$package->formatted_download_speed}) created.",
                model: $package,
                newValues: $package->only(['name', 'download_speed', 'upload_speed', 'price', 'status', 'connection_type'])
            );

            return $package;
        });
    }

    public function updatePackage(Package $package, array $data): Package
    {
        return DB::transaction(function () use ($package, $data) {
            $oldValues = $package->only(['name', 'download_speed', 'upload_speed', 'price', 'status', 'burst_download', 'burst_upload']);

            $package->update($data);

            AuditLog::record(
                action: 'updated',
                description: "Internet Package '{$package->name}' updated.",
                model: $package,
                oldValues: $oldValues,
                newValues: $package->only(array_keys($oldValues))
            );

            return $package;
        });
    }

    public function deletePackage(Package $package): bool
    {
        return DB::transaction(function () use ($package) {
            AuditLog::record(
                action: 'deleted',
                description: "Internet Package '{$package->name}' deleted.",
                model: $package,
                oldValues: $package->only(['name', 'price', 'download_speed', 'status'])
            );

            return $package->delete();
        });
    }
}
