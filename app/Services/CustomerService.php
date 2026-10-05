<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerService
{
    public function __construct(
        protected RadiusService $radiusService,
        protected BillingService $billingService
    ) {}

    public function getPaginatedCustomers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Customer::with(['package', 'branch'])
            ->latest();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('company_name', 'ilike', "%{$search}%")
                  ->orWhere('account_number', 'ilike', "%{$search}%")
                  ->orWhere('radius_username', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['connection_type'])) {
            $query->where('connection_type', $filters['connection_type']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['package_id'])) {
            $query->where('current_package_id', $filters['package_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function createCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $generateInvoice = isset($data['generate_invoice']) ? (bool) $data['generate_invoice'] : true;
            $markPaid = !empty($data['mark_paid_immediately']);
            $paymentMethod = $data['payment_method'] ?? 'cash';
            $packageFee = isset($data['package_fee']) && $data['package_fee'] !== '' ? (float) $data['package_fee'] : null;
            $installationFee = isset($data['installation_fee']) && $data['installation_fee'] !== '' ? (float) $data['installation_fee'] : null;

            unset($data['generate_invoice'], $data['mark_paid_immediately'], $data['payment_method'], $data['package_fee'], $data['installation_fee']);

            if (empty($data['account_number'])) {
                $data['account_number'] = 'CUST-' . strtoupper(Str::random(6));
            }

            if (empty($data['portal_username']) && !empty($data['email'])) {
                $data['portal_username'] = $data['email'];
            } elseif (empty($data['portal_username'])) {
                $data['portal_username'] = strtolower(str_replace('-', '', $data['account_number']));
            }

            if (!empty($data['portal_password'])) {
                $data['portal_password'] = Hash::make($data['portal_password']);
            } else {
                $data['portal_password'] = Hash::make('123456'); // default password
            }

            // If not marked paid immediately, set status to pending/lead or suspended until invoice is settled
            if (!empty($data['current_package_id']) && !$markPaid && $data['status'] === 'active') {
                $data['status'] = 'lead';
            }

            $customer = Customer::create($data);
            $customer->load('package');

            // 1. Create initial invoice and subscription if package is selected
            if ($customer->current_package_id && $customer->package && $generateInvoice) {
                $this->billingService->createInitialSubscriptionAndInvoice(
                    customer: $customer,
                    package: $customer->package,
                    markAsPaid: $markPaid,
                    paymentMethod: $paymentMethod,
                    reference: null,
                    customPackageFee: $packageFee,
                    customInstallationFee: $installationFee
                );
            }

            $customer->refresh();

            // 2. Sync with FreeRADIUS (if active -> accepted with speeds, if lead/suspended -> reject until paid)
            $this->radiusService->syncCustomerToRadius($customer);

            AuditLog::record(
                action: 'created',
                description: "Customer {$customer->full_name} ({$customer->account_number}) was created.",
                model: $customer,
                newValues: $customer->only(['account_number', 'first_name', 'last_name', 'status', 'connection_type', 'phone', 'radius_username'])
            );

            return $customer;
        });
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $oldValues = $customer->only(['first_name', 'last_name', 'status', 'current_package_id', 'phone', 'radius_username', 'radius_password', 'static_ip']);

            if (!empty($data['portal_password'])) {
                $data['portal_password'] = Hash::make($data['portal_password']);
            } else {
                unset($data['portal_password']);
            }

            $oldRadiusUsername = $customer->radius_username;

            $customer->update($data);
            $customer->load('package');

            // If username changed, cleanup old username first
            if (!empty($oldRadiusUsername) && $oldRadiusUsername !== $customer->radius_username) {
                $this->radiusService->deprovisionCustomerRadius($oldRadiusUsername);
            }

            // Sync new state with FreeRADIUS
            $this->radiusService->syncCustomerToRadius($customer);

            AuditLog::record(
                action: 'updated',
                description: "Customer {$customer->full_name} ({$customer->account_number}) was updated.",
                model: $customer,
                oldValues: $oldValues,
                newValues: $customer->only(array_keys($oldValues))
            );

            return $customer;
        });
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return DB::transaction(function () use ($customer) {
            if (!empty($customer->radius_username)) {
                $this->radiusService->deprovisionCustomerRadius($customer->radius_username);
            }

            AuditLog::record(
                action: 'deleted',
                description: "Customer {$customer->full_name} ({$customer->account_number}) was deleted.",
                model: $customer,
                oldValues: $customer->only(['account_number', 'first_name', 'last_name', 'status', 'radius_username'])
            );

            return $customer->delete();
        });
    }
}
