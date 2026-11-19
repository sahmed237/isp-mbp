<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Services\BillingService;
use App\Services\HotspotVoucherService;
use App\Services\Payment\Gateways\PaymentGatewayFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected BillingService $billingService,
        protected HotspotVoucherService $voucherService
    ) {}

    /**
     * Handle incoming server-to-server payment gateway webhooks (Paystack, Monnify, Zainpay)
     * with strict row-level idempotency so duplicate callbacks/webhooks never issue double value.
     */
    public function handle(Request $request, string $gateway): JsonResponse
    {
        $gateway = strtolower(trim($gateway));

        try {
            $paymentGateway = PaymentGatewayFactory::create($gateway);
            $result = $paymentGateway->webhook(
                payload: $request->all(),
                headers: array_change_key_case($request->headers->all(), CASE_LOWER),
                rawBody: (string) $request->getContent()
            );

            if (! $result || $result->amountPaid <= 0 || empty($result->reference)) {
                return response()->json(['status' => 'ignored']);
            }

            $reference = (string) $result->reference;

            DB::transaction(function () use ($reference, $gateway, $result) {
                $attempt = PaymentAttempt::withoutGlobalScopes()
                    ->where('reference', $reference)
                    ->lockForUpdate()
                    ->first();

                if (! $attempt) {
                    return;
                }

                // IDEMPOTENCY GUARD: If already marked successful or already linked to a payment/voucher, stop immediately
                if ($attempt->status === 'successful' || ! empty($attempt->payment_id) || ! empty($attempt->voucher_id)) {
                    return;
                }

                if (Payment::withoutGlobalScopes()->where('reference', $reference)->exists()) {
                    $attempt->update(['status' => 'successful', 'completed_at' => now()]);

                    return;
                }

                $decodedRaw = $result->rawResponse
                    ? (json_decode($result->rawResponse, true) ?? ['raw' => $result->rawResponse])
                    : null;

                $type = $attempt->request_payload['type'] ?? null;

                // 1. Customer Portal Hotspot Voucher Purchase
                if ($type === 'portal_hotspot_voucher' && $attempt->customer_id) {
                    $customer = Customer::withoutGlobalScopes()->find($attempt->customer_id);
                    $packageId = $attempt->request_payload['package_id'] ?? null;
                    $package = $packageId ? Package::withoutGlobalScopes()->find($packageId) : null;

                    if ($customer && $package) {
                        $voucher = $this->voucherService->purchaseVoucherForCustomer(
                            customer: $customer,
                            package: $package,
                            paymentMethod: $gateway,
                            reference: $reference
                        );

                        $payment = Payment::withoutGlobalScopes()
                            ->where('reference', $reference)
                            ->latest('id')
                            ->first();

                        $attempt->update([
                            'status' => 'successful',
                            'voucher_id' => $voucher->id,
                            'payment_id' => $payment?->id,
                            'verification_payload' => $decodedRaw,
                            'completed_at' => now(),
                        ]);
                    }

                    return;
                }

                // 2. Public Self-Service Guest Hotspot Voucher Purchase
                if (! $attempt->invoice_id && ! $attempt->customer_id && ! $attempt->reseller_id && isset($attempt->request_payload['package_id'])) {
                    $package = Package::withoutGlobalScopes()->find($attempt->request_payload['package_id']);
                    if ($package) {
                        $voucher = $this->voucherService->createGuestVoucher(
                            package: $package,
                            paymentMethod: $gateway,
                            details: [
                                'reference' => $reference,
                                'phone' => $attempt->customer_phone,
                                'email' => $attempt->customer_email,
                                'raw_payload' => $decodedRaw,
                            ]
                        );

                        $payment = Payment::withoutGlobalScopes()
                            ->where('reference', $reference)
                            ->latest('id')
                            ->first();

                        $attempt->update([
                            'status' => 'successful',
                            'voucher_id' => $voucher->id,
                            'payment_id' => $payment?->id,
                            'verification_payload' => $decodedRaw,
                            'completed_at' => now(),
                        ]);
                    }

                    return;
                }

                // 3. Invoice Payment
                if ($attempt->invoice_id) {
                    $invoice = Invoice::withoutGlobalScopes()->find($attempt->invoice_id);
                    if ($invoice && ! $invoice->isPaid()) {
                        $payment = $this->billingService->recordPayment($invoice, [
                            'amount' => $result->amountPaid,
                            'payment_method' => $gateway,
                            'reference' => $reference,
                            'paid_at' => $result->paymentDate,
                            'notes' => "Webhook payment verified via {$gateway}",
                            'raw_payload' => $decodedRaw,
                            'payment_attempt_id' => $attempt->id,
                        ]);

                        $attempt->update([
                            'status' => 'successful',
                            'payment_id' => $payment->id,
                            'verification_payload' => $decodedRaw,
                            'completed_at' => now(),
                        ]);
                    }
                }
            });

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            Log::error("Payment webhook error ({$gateway}): ".$e->getMessage());

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
