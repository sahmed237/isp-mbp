<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\HotspotVoucher;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HotspotVoucherService
{
    public function __construct(
        protected RadiusService $radiusService
    ) {}

    public function generateBatch(Package $package, int $quantity, array $options = []): Collection
    {
        return DB::transaction(function () use ($package, $quantity, $options) {
            $batchId = 'BATCH-' . date('Ymd-His');
            $createdVouchers = collect();

            $durationValue = (int) ($options['duration_value'] ?? $package->validity_period ?: 1);
            $durationUnit = $options['duration_unit'] ?? 'days';
            $prefix = !empty($options['prefix']) ? strtoupper(trim($options['prefix'])) : 'HS';

            for ($i = 0; $i < $quantity; $i++) {
                $uniqueCode = $prefix . '-' . strtoupper(Str::random(4)) . '-' . random_int(1000, 9999);
                $uniqueUsername = strtolower($prefix) . '_' . strtolower(Str::random(5));
                $password = (string) random_int(100000, 999999);

                $voucher = HotspotVoucher::create([
                    'organization_id' => $package->organization_id,
                    'package_id' => $package->id,
                    'batch_id' => $batchId,
                    'code' => $uniqueCode,
                    'username' => $uniqueUsername,
                    'password' => $password,
                    'price' => $package->price,
                    'duration_value' => $durationValue,
                    'duration_unit' => $durationUnit,
                    'data_limit_mb' => $options['data_limit_mb'] ?? null,
                    'status' => 'unused',
                    'created_by_user_id' => Auth::guard('web')->id(),
                ]);

                // Sync with FreeRADIUS immediately
                $voucher->load('package');
                $this->radiusService->syncVoucherToRadius($voucher);

                $createdVouchers->push($voucher);
            }

            AuditLog::record(
                action: 'created',
                description: "Generated batch of {$quantity} hotspot vouchers ({$batchId}) for package {$package->name}.",
                newValues: [
                    'batch_id' => $batchId,
                    'package_id' => $package->id,
                    'quantity' => $quantity,
                ]
            );

            return $createdVouchers;
        });
    }

    public function purchaseVoucherForCustomer(Customer $customer, Package $package, string $paymentMethod = 'card'): HotspotVoucher
    {
        return DB::transaction(function () use ($customer, $package, $paymentMethod) {
            $code = 'VCH-' . strtoupper(Str::random(4)) . '-' . random_int(1000, 9999);
            $username = 'cust_' . strtolower(Str::random(5));
            $password = (string) random_int(100000, 999999);

            $voucher = HotspotVoucher::create([
                'organization_id' => $customer->organization_id,
                'package_id' => $package->id,
                'customer_id' => $customer->id,
                'batch_id' => 'PORTAL-' . date('Ymd-His'),
                'code' => $code,
                'username' => $username,
                'password' => $password,
                'price' => $package->price,
                'duration_value' => $package->validity_period ?: 1,
                'duration_unit' => 'days',
                'status' => 'unused',
            ]);

            // Sync with FreeRADIUS
            $voucher->load('package');
            $this->radiusService->syncVoucherToRadius($voucher);

            // Record customer invoice & payment
            $invoice = Invoice::create([
                'organization_id' => $customer->organization_id,
                'customer_id' => $customer->id,
                'status' => 'paid',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'paid_at' => now(),
                'subtotal' => $package->price,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => $package->price,
                'paid_amount' => $package->price,
                'payment_method' => $paymentMethod,
                'notes' => "Purchased Hotspot Voucher #{$code}",
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "Hotspot Voucher ({$package->name}) - Code: {$code}",
                'quantity' => 1,
                'unit_price' => $package->price,
                'total_price' => $package->price,
            ]);

            Payment::create([
                'organization_id' => $customer->organization_id,
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
                'amount' => $package->price,
                'payment_method' => $paymentMethod,
                'reference' => 'VCHPAY-' . strtoupper(Str::random(8)),
                'status' => 'successful',
                'paid_at' => now(),
                'notes' => "Payment for Hotspot Voucher #{$code}",
            ]);

            AuditLog::record(
                action: 'created',
                description: "Customer {$customer->full_name} purchased hotspot voucher {$code} ({$package->name}).",
                model: $voucher,
                newValues: [
                    'code' => $code,
                    'customer_id' => $customer->id,
                    'amount' => $package->price,
                ]
            );

            return $voucher;
        });
    }

    public function getPaginatedVouchers(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = HotspotVoucher::with(['package', 'customer', 'createdBy'])
            ->latest();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('username', 'ilike', "%{$search}%")
                  ->orWhere('batch_id', 'ilike', "%{$search}%")
                  ->orWhereHas('package', function ($pq) use ($search) {
                      $pq->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['package_id'])) {
            $query->where('package_id', $filters['package_id']);
        }

        if (!empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }

        return $query->paginate($perPage, ['*'], 'vouchers_page')->withQueryString();
    }

    public function deleteVoucher(HotspotVoucher $voucher): bool
    {
        return DB::transaction(function () use ($voucher) {
            $this->radiusService->deprovisionVoucherRadius($voucher->username);

            AuditLog::record(
                action: 'deleted',
                description: "Hotspot voucher {$voucher->code} ({$voucher->username}) was deleted.",
                model: $voucher,
                oldValues: $voucher->only(['code', 'username', 'status'])
            );

            return (bool) $voucher->delete();
        });
    }
}
