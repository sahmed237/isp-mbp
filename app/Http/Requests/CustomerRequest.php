<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');
        if ($customer) {
            return $this->user()?->can('update', $customer) ?? false;
        }

        return $this->user()?->can('create', \App\Models\Customer::class) ?? false;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $customerId = is_object($customer) ? $customer->id : $customer;

        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'customer_type' => ['required', 'string', Rule::in(['individual', 'corporate', 'government', 'reseller'])],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],
            'installation_address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'gps_coordinates' => ['nullable', 'string', 'max:50'],
            'connection_type' => ['required', 'string', Rule::in(['pppoe', 'fibre', 'ptp', 'ptmp', 'wireless', 'dedicated', 'other'])],
            'status' => ['required', 'string', Rule::in(['lead', 'active', 'suspended', 'expired', 'terminated'])],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'current_package_id' => ['nullable', 'exists:packages,id'],
            'radius_username' => ['nullable', 'string', 'max:64', Rule::unique('customers', 'radius_username')->ignore($customerId)],
            'radius_password' => ['nullable', 'string', 'max:64'],
            'static_ip' => ['nullable', 'ip'],
            'portal_username' => ['nullable', 'string', 'max:64'],
            'portal_password' => [$customerId ? 'nullable' : 'nullable', 'string', 'min:6'],
            'generate_invoice' => ['nullable', 'boolean'],
            'mark_paid_immediately' => ['nullable', 'boolean'],
            'package_fee' => ['nullable', 'numeric', 'min:0'],
            'installation_fee' => ['nullable', 'numeric', 'min:0'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', Rule::in(['cash', 'bank_transfer', 'card', 'paystack', 'moniepoint', 'other'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
