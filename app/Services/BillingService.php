<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Subscription;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingService
{
    public function __construct(
        protected RadiusService $radiusService
    ) {}

    public function createInitialSubscriptionAndInvoice(
        Customer $customer,
        Package $package,
        bool $markAsPaid = false,
        string $paymentMethod = 'cash',
        ?string $reference = null,
        ?float $customPackageFee = null,
        ?float $customInstallationFee = null
    ): array {
        return DB::transaction(function () use ($customer, $package, $markAsPaid, $paymentMethod, $reference, $customPackageFee, $customInstallationFee) {
            $effectivePrice = $customPackageFee !== null ? $customPackageFee : (float) $package->price;
            $effectiveInstallation = $customInstallationFee !== null ? $customInstallationFee : (float) $package->installation_fee;

            // 1. Create Subscription (Pending or Active)
            $subscription = Subscription::create([
                'organization_id' => $customer->organization_id,
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'status' => $markAsPaid ? 'active' : 'pending',
                'starts_at' => $markAsPaid ? now() : null,
                'expires_at' => $markAsPaid ? now()->addDays($package->validity_period) : null,
                'price' => $effectivePrice,
                'billing_cycle' => $package->billing_cycle,
                'auto_renew' => true,
            ]);

            // 2. Build Invoice
            $invoice = Invoice::create([
                'organization_id' => $customer->organization_id,
                'customer_id' => $customer->id,
                'subscription_id' => $subscription->id,
                'status' => 'unpaid',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'subtotal' => 0.00,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => 0.00,
                'paid_amount' => 0.00,
                'payment_method' => $markAsPaid ? $paymentMethod : null,
                'notes' => "Initial subscription setup for {$package->name}.",
            ]);

            $total = 0.00;

            // Item 1: Package fee
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "{$package->name} Subscription ({$package->billing_cycle})",
                'quantity' => 1,
                'unit_price' => $effectivePrice,
                'total_price' => $effectivePrice,
            ]);
            $total += $effectivePrice;

            // Item 2: Installation fee if any
            if ($effectiveInstallation > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => "Installation & Setup Fee",
                    'quantity' => 1,
                    'unit_price' => $effectiveInstallation,
                    'total_price' => $effectiveInstallation,
                ]);
                $total += $effectiveInstallation;
            }

            // Item 3: Activation fee if any
            if ((float) $package->activation_fee > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => "Activation & Account Provisioning Fee",
                    'quantity' => 1,
                    'unit_price' => $package->activation_fee,
                    'total_price' => $package->activation_fee,
                ]);
                $total += (float) $package->activation_fee;
            }

            $invoice->update([
                'subtotal' => $total,
                'total_amount' => $total,
            ]);

            // 3. If marked as paid, record payment and activate
            if ($markAsPaid) {
                $this->recordPayment($invoice, [
                    'amount' => $total,
                    'payment_method' => $paymentMethod,
                    'reference' => $reference ?? ('REG-' . strtoupper(Str::random(8))),
                    'paid_at' => now(),
                    'notes' => 'Registration initial payment.',
                ]);
            }

            AuditLog::record(
                action: 'created',
                description: "Initial invoice {$invoice->invoice_number} and subscription {$subscription->subscription_number} generated for customer {$customer->full_name}.",
                model: $invoice,
                newValues: [
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $total,
                    'subscription_id' => $subscription->id,
                    'status' => $invoice->status,
                ]
            );

            return [
                'subscription' => $subscription->fresh(),
                'invoice' => $invoice->fresh(['items']),
            ];
        });
    }

    public function recordPayment(Invoice $invoice, array $paymentData): Payment
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            $amount = (float) ($paymentData['amount'] ?? $invoice->balance_due);
            $method = $paymentData['payment_method'] ?? 'card';
            $ref = $paymentData['reference'] ?? ('PAY-' . strtoupper(Str::random(10)));
            $paidAt = $paymentData['paid_at'] ?? now();

            $rawPayload = $paymentData['raw_payload'] ?? null;
            if (is_string($rawPayload)) {
                $decoded = json_decode($rawPayload, true);
                $rawPayload = $decoded !== null ? $decoded : ['raw' => $rawPayload];
            }

            $payment = Payment::create([
                'organization_id' => $invoice->organization_id,
                'customer_id' => $invoice->customer_id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_method' => $method,
                'reference' => $ref,
                'status' => 'successful',
                'paid_at' => $paidAt,
                'notes' => $paymentData['notes'] ?? null,
                'raw_payload' => $rawPayload,
                'recorded_by_user_id' => Auth::guard('web')->id(),
            ]);

            // Link matching payment attempt if exists for audit tracking
            if (!empty($paymentData['payment_attempt_id'])) {
                PaymentAttempt::where('id', $paymentData['payment_attempt_id'])->update([
                    'payment_id' => $payment->id,
                    'status' => 'successful',
                    'completed_at' => now(),
                ]);
            } elseif (!empty($ref)) {
                PaymentAttempt::where('reference', $ref)
                    ->where('invoice_id', $invoice->id)
                    ->update([
                        'payment_id' => $payment->id,
                        'status' => 'successful',
                        'completed_at' => now(),
                    ]);
            }

            $newPaidAmount = (float) $invoice->paid_amount + $amount;
            $isFullyPaid = $newPaidAmount >= (float) $invoice->total_amount;

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'payment_method' => $method,
                'status' => $isFullyPaid ? 'paid' : 'partially_paid',
                'paid_at' => $isFullyPaid ? now() : $invoice->paid_at,
            ]);

            // Activate or extend associated subscription if fully paid
            if ($isFullyPaid && $invoice->subscription) {
                $this->activateSubscription($invoice->subscription);
            }

            AuditLog::record(
                action: 'created',
                description: "Payment {$payment->payment_number} of ₦" . number_format($amount, 2) . " recorded for invoice {$invoice->invoice_number}.",
                model: $payment,
                newValues: [
                    'payment_number' => $payment->payment_number,
                    'amount' => $amount,
                    'payment_method' => $method,
                    'invoice_id' => $invoice->id,
                ]
            );

            return $payment;
        });
    }

    public function activateSubscription(Subscription $subscription): void
    {
        $package = $subscription->package;
        $customer = $subscription->customer;

        if (!$package || !$customer) {
            return;
        }

        $validityDays = $package->validity_period ?: 30;

        // If subscription is currently active with future expiry, extend it from the expiry date!
        if ($subscription->isActive() && $subscription->expires_at && $subscription->expires_at->isFuture()) {
            $startsAt = $subscription->starts_at ?? now();
            $expiresAt = $subscription->expires_at->copy()->addDays($validityDays);
        } else {
            $startsAt = now();
            $expiresAt = now()->addDays($validityDays);
        }

        $subscription->update([
            'status' => 'active',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);

        // Update Customer status to active and update current package
        $customer->update([
            'status' => 'active',
            'current_package_id' => $package->id,
        ]);

        // Immediate FreeRADIUS synchronization for PPPoE dial-in!
        $customer->load('package');
        $this->radiusService->syncCustomerToRadius($customer);

        AuditLog::record(
            action: 'updated',
            description: "Subscription {$subscription->subscription_number} activated for {$customer->full_name}. Valid until {$expiresAt->format('Y-m-d H:i')}. RADIUS authenticated.",
            model: $subscription,
            newValues: [
                'status' => 'active',
                'starts_at' => $startsAt->toDateTimeString(),
                'expires_at' => $expiresAt->toDateTimeString(),
            ]
        );
    }

    public function renewSubscription(Subscription $subscription, array $paymentData = []): Invoice
    {
        return DB::transaction(function () use ($subscription, $paymentData) {
            $package = $subscription->package;
            $customer = $subscription->customer;

            $invoice = Invoice::create([
                'organization_id' => $customer->organization_id,
                'customer_id' => $customer->id,
                'subscription_id' => $subscription->id,
                'status' => 'unpaid',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(5)->toDateString(),
                'subtotal' => $package->price,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => $package->price,
                'paid_amount' => 0.00,
                'notes' => "Subscription renewal for {$package->name}.",
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "Renewal: {$package->name} Subscription ({$package->billing_cycle})",
                'quantity' => 1,
                'unit_price' => $package->price,
                'total_price' => $package->price,
            ]);

            if (!empty($paymentData)) {
                $this->recordPayment($invoice, $paymentData);
            }

            return $invoice->fresh(['items', 'payments']);
        });
    }

    public function getPaginatedInvoices(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Invoice::with(['customer', 'subscription.package', 'items'])
            ->latest();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'ilike', "%{$search}%")
                         ->orWhere('last_name', 'ilike', "%{$search}%")
                         ->orWhere('company_name', 'ilike', "%{$search}%")
                         ->orWhere('account_number', 'ilike', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->paginate($perPage, ['*'], 'invoices_page')->withQueryString();
    }

    public function getPaginatedPayments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::with(['customer', 'invoice', 'recordedBy'])
            ->latest();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'ilike', "%{$search}%")
                  ->orWhere('reference', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'ilike', "%{$search}%")
                         ->orWhere('last_name', 'ilike', "%{$search}%")
                         ->orWhere('account_number', 'ilike', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->paginate($perPage, ['*'], 'payments_page')->withQueryString();
    }

    public function getPaginatedSubscriptions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Subscription::with(['customer', 'package'])
            ->latest();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('subscription_number', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'ilike', "%{$search}%")
                         ->orWhere('last_name', 'ilike', "%{$search}%")
                         ->orWhere('account_number', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('package', function ($pq) use ($search) {
                      $pq->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->paginate($perPage, ['*'], 'subs_page')->withQueryString();
    }
}
