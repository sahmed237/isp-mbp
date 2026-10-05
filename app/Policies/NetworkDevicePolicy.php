<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NetworkDevice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NetworkDevicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('network.devices.view');
    }

    public function view(User $user, NetworkDevice $device): bool
    {
        if (!$user->can('network.devices.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $device->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('network.devices.create');
    }

    public function update(User $user, NetworkDevice $device): bool
    {
        if (!$user->can('network.devices.update')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $device->organization_id;
    }

    public function delete(User $user, NetworkDevice $device): bool
    {
        if (!$user->can('network.devices.delete')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $device->organization_id;
    }

    public function testConnection(User $user, NetworkDevice $device): bool
    {
        if (!$user->can('network.devices.test_connection')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $device->organization_id;
    }
}
