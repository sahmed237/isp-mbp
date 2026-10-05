<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $package = $this->route('package');
        if ($package) {
            return $this->user()?->can('update', $package) ?? false;
        }

        return $this->user()?->can('create', \App\Models\Package::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'download_speed' => ['required', 'integer', 'min:128'],
            'upload_speed' => ['required', 'integer', 'min:128'],
            'burst_download' => ['nullable', 'integer', 'min:128'],
            'burst_upload' => ['nullable', 'integer', 'min:128'],
            'burst_threshold' => ['nullable', 'integer', 'min:128'],
            'burst_time' => ['nullable', 'integer', 'min:1', 'max:3600'],
            'price' => ['required', 'numeric', 'min:0'],
            'installation_fee' => ['nullable', 'numeric', 'min:0'],
            'activation_fee' => ['nullable', 'numeric', 'min:0'],
            'validity_period' => ['required', 'integer', 'min:1'],
            'billing_cycle' => ['required', 'string', Rule::in(['monthly', 'quarterly', 'biannual', 'annual', 'custom'])],
            'connection_type' => ['required', 'string', Rule::in(['all', 'hotspot', 'pppoe', 'fibre', 'ptp', 'ptmp', 'wireless', 'dedicated', 'other'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
