<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\Setting;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTOs\PaymentInitResult;
use App\Services\Payment\DTOs\PaymentVerifyResult;
use App\Services\Payment\PaymentApi\MonnifyApi;
use Exception;
use Illuminate\Support\Facades\Log;

class MonnifyGateway implements PaymentGatewayInterface
{
    public function initialize(mixed $invoice, string $email, string $phone, string $callbackUrl, ?string $reference = null): PaymentInitResult
    {
        $invoiceId = is_object($invoice) ? $invoice->id : ($invoice['id'] ?? 0);
        $amount = is_object($invoice)
            ? (float) ($invoice->balance_due ?? $invoice->total_amount ?? 0)
            : (float) ($invoice['balance_due'] ?? $invoice['total_amount'] ?? 0);

        $invNumber = is_object($invoice)
            ? ($invoice->invoice_number ?? 'INV-' . $invoiceId)
            : ($invoice['invoice_number'] ?? 'INV-' . $invoiceId);

        $reference = $reference ?: ($invNumber . '-' . time() . '-' . strtoupper(Str::random(4)));
        $contractCode = (string) (Setting::get('monnify_contract_code') ?: '');

        if (empty($contractCode)) {
            throw new Exception('Monnify Contract Code is not configured.');
        }

        $customerName = is_object($invoice) && isset($invoice->customer)
            ? $invoice->customer->full_name
            : 'ISP Subscriber';

        try {
            $api = MonnifyApi::getInstance();

            $payload = [
                'amount' => $amount,
                'customerName' => $customerName,
                'customerEmail' => $email ?: 'subscriber@isp-mbp.ng',
                'paymentReference' => $reference,
                'paymentDescription' => "Payment for Invoice #{$invNumber}",
                'currencyCode' => 'NGN',
                'contractCode' => $contractCode,
                'redirectUrl' => $callbackUrl,
                'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER'],
            ];

            $response = $api->post('/api/v1/merchant/transactions/init-transaction', $payload);

            if (!empty($response['requestSuccessful']) && $response['requestSuccessful'] === true) {
                $body = $response['responseBody'];
                return new PaymentInitResult(
                    reference: $reference,
                    redirectUrl: $body['checkoutUrl'],
                    rawResponse: json_encode($response)
                );
            }

            throw new Exception('Monnify initialization error: ' . ($response['responseMessage'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            throw new Exception('Monnify initialization failed: ' . $e->getMessage());
        }
    }

    public function verify(string $reference): PaymentVerifyResult
    {
        try {
            $api = MonnifyApi::getInstance();
            $response = null;

            // 1. If reference looks like Monnify internal transactionReference (starts with MNFY or has pipe)
            if (str_starts_with($reference, 'MNFY') || str_contains($reference, '|')) {
                $response = $api->get('/api/v2/transactions/' . urlencode($reference));
            } else {
                // Query by merchant paymentReference
                try {
                    $response = $api->get('/api/v1/merchant/transactions/query', [
                        'paymentReference' => $reference,
                    ]);
                } catch (\Throwable $qEx) {
                    // Fallback to /api/v2/transactions/{reference}
                    $response = $api->get('/api/v2/transactions/' . urlencode($reference));
                }
            }

            if (!empty($response['requestSuccessful']) && $response['requestSuccessful'] === true) {
                $body = $response['responseBody'] ?? [];
                $isPaid = strtoupper((string) ($body['paymentStatus'] ?? '')) === 'PAID';

                return new PaymentVerifyResult(
                    amountPaid: $isPaid ? (float) ($body['amountPaid'] ?? 0) : 0.0,
                    paymentDate: $body['paidOn'] ?? date('Y-m-d H:i:s'),
                    gateway: 'monnify',
                    rawResponse: json_encode($body),
                    reference: $body['paymentReference'] ?? $reference,
                    fee: (float) ($body['fee'] ?? 0)
                );
            }

            // Monnify returned non-success response
            return new PaymentVerifyResult(
                amountPaid: 0.0,
                paymentDate: date('Y-m-d H:i:s'),
                gateway: 'monnify',
                rawResponse: json_encode($response ?? ['status' => 'UNVERIFIED', 'reference' => $reference]),
                reference: $reference
            );
        } catch (\Throwable $e) {
            Log::info('Monnify verification returned non-paid/declined: ' . $e->getMessage(), ['reference' => $reference]);

            return new PaymentVerifyResult(
                amountPaid: 0.0,
                paymentDate: date('Y-m-d H:i:s'),
                gateway: 'monnify',
                rawResponse: json_encode([
                    'status' => 'FAILED_OR_DECLINED',
                    'message' => $e->getMessage(),
                    'reference' => $reference,
                ]),
                reference: $reference
            );
        }
    }

    public function webhook(array $payload, array $headers, string $rawBody): ?PaymentVerifyResult
    {
        $body = $payload['eventData'] ?? [];
        $reference = $body['paymentReference'] ?? null;

        if (!$reference) {
            return null;
        }

        try {
            return $this->verify((string) $reference);
        } catch (\Throwable $e) {
            Log::error('Monnify Webhook verification failed: ' . $e->getMessage());
            return null;
        }
    }
}
