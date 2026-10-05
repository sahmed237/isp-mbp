<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\Setting;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTOs\PaymentInitResult;
use App\Services\Payment\DTOs\PaymentVerifyResult;
use App\Services\Payment\PaymentApi\PaystackApi;
use Exception;
use Illuminate\Support\Facades\Log;

class PaystackGateway implements PaymentGatewayInterface
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

        // Validate or fallback email for Paystack API requirement
        $cleanEmail = trim($email);
        if (empty($cleanEmail) || !filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            $identifier = preg_replace('/[^a-zA-Z0-9]/', '', $phone ?: (string) $invoiceId);
            $cleanEmail = 'subscriber.' . strtolower($identifier ?: (string) rand(100000, 999999)) . '@isp-mbp.ng';
        }

        try {
            $api = PaystackApi::getInstance();

            $payload = [
                'amount' => (int) round($amount * 100), // Amount in kobo
                'email' => $cleanEmail,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'invoice_id' => $invoiceId,
                    'phone' => $phone,
                ],
            ];

            $response = $api->post('transaction/initialize', $payload);

            if (!empty($response['status']) && $response['status'] === true) {
                $data = $response['data'];
                return new PaymentInitResult(
                    reference: $reference,
                    redirectUrl: $data['authorization_url'],
                    rawResponse: json_encode($response)
                );
            }

            throw new Exception('Paystack initialization failed: ' . ($response['message'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            throw new Exception('Paystack initialization failed: ' . $e->getMessage());
        }
    }

    public function verify(string $reference): PaymentVerifyResult
    {
        if (empty($reference)) {
            throw new Exception('Transaction reference is required for verification.');
        }

        try {
            $api = PaystackApi::getInstance();
            $response = $api->get('transaction/verify/' . $reference);

            if (!empty($response['status']) && $response['status'] === true) {
                $data = $response['data'];

                if (($data['status'] ?? '') !== 'success') {
                    return new PaymentVerifyResult(
                        amountPaid: 0.0,
                        paymentDate: date('Y-m-d H:i:s'),
                        gateway: 'paystack',
                        rawResponse: json_encode($data),
                        reference: $reference
                    );
                }

                $fee = (float) (($data['fees'] ?? 0) / 100);

                return new PaymentVerifyResult(
                    amountPaid: (float) (($data['amount'] ?? 0) / 100),
                    paymentDate: $data['paid_at'] ?? date('Y-m-d H:i:s'),
                    gateway: 'paystack',
                    rawResponse: json_encode($data),
                    reference: $reference,
                    fee: $fee
                );
            }

            throw new Exception($response['message'] ?? 'Paystack transaction not found');
        } catch (\Throwable $e) {
            Log::error('Paystack verification error: ' . $e->getMessage());
            throw new Exception('Unable to verify Paystack transaction: ' . $e->getMessage());
        }
    }

    public function webhook(array $payload, array $headers, string $rawBody): ?PaymentVerifyResult
    {
        $signature = $headers['x-paystack-signature'] ?? null;
        if (is_array($signature)) {
            $signature = $signature[0];
        }

        $secretKey = Setting::get('paystack_secret_key') ?: config('services.paystack.secret_key');

        if (!$signature || !$secretKey) {
            return null;
        }

        $computedSignature = hash_hmac('sha512', $rawBody, (string) $secretKey);

        if (!hash_equals($computedSignature, (string) $signature)) {
            return null;
        }

        $data = $payload['data'] ?? [];
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            return null;
        }

        try {
            return $this->verify((string) $reference);
        } catch (\Throwable $e) {
            Log::error('Paystack Webhook verification failed for ' . $reference . ': ' . $e->getMessage());
            return null;
        }
    }
}
