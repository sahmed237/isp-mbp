<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NetworkDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $device = $this->route('networkDevice') ?? $this->route('network_device');
        if ($device) {
            return $this->user()?->can('update', $device) ?? false;
        }

        return $this->user()?->can('create', \App\Models\NetworkDevice::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:150'],
            'device_type' => ['required', 'string', Rule::in(['mikrotik_router', 'core_router', 'access_router', 'switch', 'access_point', 'olt', 'tower', 'cpe', 'other'])],
            'ip_address' => ['required', 'ip'],
            'hostname' => ['nullable', 'string', 'max:150'],
            'mac_address' => ['nullable', 'string', 'max:30'],
            'vendor' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'api_port' => ['nullable', 'integer', 'between:1,65535'],
            'ssh_port' => ['nullable', 'integer', 'between:1,65535'],
            'web_port' => ['nullable', 'integer', 'between:1,65535'],
            'username' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['online', 'offline', 'warning', 'maintenance'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
