<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentAttempt;
use App\Models\Setting;
use App\Services\BillingService;
use App\Services\Payment\Gateways\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PublicInvoicePaymentController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    /**
     * Display public invoice payment page
     */
    public function show(string $identifier): View
    {
        $invoice = $this->resolveInvoice($identifier);
        $invoice->load(['customer', 'subscription.package', 'items', 'payments', 'paymentAttempts']);

        $companyInfo = Setting::getCompanyInfo();
        $paystackActive = (bool) Setting::get('paystack_active', false);
        $monnifyActive = (bool) Setting::get('monnify_active', false);

        $publicUrl = route('public.invoices.pay', $invoice->uuid);
        $qrCodeSvg = QrCode::size(120)->margin(1)->generate($publicUrl);

        return view('public.invoices.pay', compact(
            'invoice',
            'companyInfo',
            'paystackActive',
            'monnifyActive',
            'publicUrl',
            'qrCodeSvg'
        ));
    }

    /**
     * Process checkout from public invoice payment page
     */
    public function checkout(Request $request, string $identifier): RedirectResponse
    {
        $invoice = $this->resolveInvoice($identifier);

        if ($invoice->isPaid()) {
            return redirect()->route('public.invoices.pay', $invoice->uuid)
                ->with('info', 'This invoice has already been settled in full.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:paystack,monnify'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{10,20}$/'],
        ], [
            'payment_method.required' => 'A payment gateway must be selected.',
            'payment_method.in' => 'Selected payment gateway is invalid.',
            'customer_email.required' => 'Customer email address is strictly required to proceed with payment.',
            'customer_email.email' => 'Please enter a valid email address format (e.g. subscriber@domain.com).',
            'customer_phone.required' => 'Customer phone number is strictly required to proceed with payment.',
            'customer_phone.regex' => 'Please enter a valid phone number containing at least 10 digits.',
        ]);

        $rawDigits = preg_replace('/\D/', '', (string) $validated['customer_phone']);
        if (strlen($rawDigits) < 10 || strlen($rawDigits) > 15) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['customer_phone' => 'Phone number must contain between 10 and 15 digits.']);
        }

        $method = $validated['payment_method'];
        $email = trim($validated['customer_email']);
        $phone = trim($validated['customer_phone']);

        $reference = $invoice->invoice_number . '-' . time() . '-' . strtoupper(Str::random(4));
        $amount = (float) $invoice->balance_due;

        $callbackUrl = route('public.invoices.callback', [
            'identifier' => $invoice->uuid,
            'gateway' => $method,
        ]);

        $requestPayload = [
            'gateway' => $method,
            'amount' => $amount,
            'reference' => $reference,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'callback_url' => $callbackUrl,
            'client_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ];

        // 1. Record forensic PaymentAttempt immediately
        $attempt = PaymentAttempt::create([
            'organization_id' => $invoice->organization_id,
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'payment_method' => $method,
            'reference' => $reference,
            'amount' => $amount,
            'currency' => 'NGN',
            'status' => 'initiated',
            'customer_email' => $email,
            'customer_phone' => $phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_payload' => $requestPayload,
            'initiated_at' => now(),
        ]);

        try {
            $gateway = PaymentGatewayFactory::create($method);

            $initResult = $gateway->initialize(
                invoice: $invoice,
                email: $email,
                phone: $phone,
                callbackUrl: $callbackUrl,
                reference: $reference
            );

            $rawResponse = $initResult->rawResponse
                ? (json_decode($initResult->rawResponse, true) ?? ['raw' => $initResult->rawResponse])
                : null;

            $attempt->update([
                'status' => 'pending',
                'response_payload' => $rawResponse,
            ]);

            if (!empty($initResult->redirectUrl)) {
                return redirect()->away($initResult->redirectUrl);
            }
        } catch (\Throwable $e) {
            Log::error("Public Invoice Checkout Error ({$method}): " . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'attempt_id' => $attempt->id,
            ]);

            $attempt->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()->route('public.invoices.pay', $invoice->uuid)
                ->with('error', "Payment gateway error: " . $e->getMessage());
        }

        $attempt->update([
            'status' => 'failed',
            'error_message' => 'Unable to initiate online payment session with gateway.',
            'completed_at' => now(),
        ]);

        return redirect()->route('public.invoices.pay', $invoice->uuid)
            ->with('error', 'Unable to initiate online payment session. Please try again.');
    }

    /**
     * Handle return callback from payment gateways
     */
    public function callback(Request $request, string $identifier, string $gateway): RedirectResponse
    {
        $invoice = $this->resolveInvoice($identifier);

        $payRef = $request->query('paymentReference') ?? $request->query('reference') ?? $request->query('trxref');
        $txnRef = $request->query('transactionReference');
        $reference = $payRef ?: $txnRef;

        // Check if gateway explicitly notified of user cancellation / failure
        $statusParam = strtoupper((string) ($request->query('paymentStatus') ?? $request->query('status') ?? ''));
        $isCancelledOrDeclined = in_array($statusParam, ['USER_CANCELLED', 'CANCELLED', 'FAILED', 'EXPIRED'], true)
            || $request->query('cancelled') === 'true';

        // Find the forensic PaymentAttempt
        $attempt = null;
        if ($payRef) {
            $attempt = PaymentAttempt::where('reference', $payRef)
                ->where('invoice_id', $invoice->id)
                ->latest()
                ->first();
        }

        if (!$attempt && $txnRef) {
            $attempt = PaymentAttempt::where('reference', $txnRef)
                ->where('invoice_id', $invoice->id)
                ->latest()
                ->first();
        }

        if (!$attempt) {
            $attempt = PaymentAttempt::where('invoice_id', $invoice->id)
                ->where('payment_method', $gateway)
                ->latest()
                ->first();
        }

        // If user explicitly cancelled or declined on the gateway checkout modal
        if ($isCancelledOrDeclined) {
            if ($attempt) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => "Payment session was cancelled or declined on {$gateway} (Status: {$statusParam}).",
                    'verification_payload' => $request->query(),
                    'completed_at' => now(),
                ]);
            }

            return redirect()->route('public.invoices.pay', $invoice->uuid)
                ->with('info', "Payment was cancelled or was not completed on {$gateway}. You may try again whenever you are ready.");
        }

        if (!$reference) {
            return redirect()->route('public.invoices.pay', $invoice->uuid)
                ->with('info', 'No completed payment transaction was detected.');
        }

        try {
            $paymentGateway = PaymentGatewayFactory::create($gateway);
            // Verify with reference (if Monnify sent internal transactionReference, verify with that, else merchant reference)
            $verifyRef = ($gateway === 'monnify' && $txnRef) ? (string) $txnRef : (string) $reference;
            $result = $paymentGateway->verify($verifyRef);

            $decodedRaw = $result->rawResponse
                ? (json_decode($result->rawResponse, true) ?? ['raw' => $result->rawResponse])
                : null;

            if ($attempt) {
                $attempt->update([
                    'verification_payload' => $decodedRaw,
                ]);
            }

            if ($result->amountPaid > 0 && !$invoice->isPaid()) {
                $payment = $this->billingService->recordPayment($invoice, [
                    'amount' => $result->amountPaid,
                    'payment_method' => $gateway,
                    'reference' => $result->reference ?? (string) $reference,
                    'paid_at' => $result->paymentDate ?? now(),
                    'notes' => "Public online payment settled via {$gateway} (Ref: {$reference})",
                    'raw_payload' => $decodedRaw,
                    'payment_attempt_id' => $attempt?->id,
                ]);

                if ($attempt) {
                    $attempt->update([
                        'status' => 'successful',
                        'payment_id' => $payment->id,
                        'completed_at' => now(),
                    ]);
                }

                return redirect()->route('public.invoices.pay', $invoice->uuid)
                    ->with('success', "Payment of ₦" . number_format($result->amountPaid, 2) . " received successfully! Your subscription and access have been renewed.");
            }

            // Transaction was queried but not paid (declined/unpaid)
            if ($attempt) {
                $isSuperseded = $attempt->isSuperseded();
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => $isSuperseded
                        ? $attempt->error_message
                        : "Transaction not completed or was declined on {$gateway}.",
                    'completed_at' => now(),
                ]);

                if ($isSuperseded) {
                    return redirect()->route('public.invoices.pay', $invoice->uuid)
                        ->with('info', "This payment session has expired or was replaced by a newer checkout attempt. You may complete payment using your latest session.");
                }
            }

            return redirect()->route('public.invoices.pay', $invoice->uuid)
                ->with('info', "Payment was not completed or was declined on {$gateway}. You may try again whenever you are ready.");
        } catch (\Throwable $e) {
            Log::error("Public Invoice Callback Verification Error: " . $e->getMessage(), [
                'reference' => $reference,
                'gateway' => $gateway,
            ]);

            if ($attempt) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            return redirect()->route('public.invoices.pay', $invoice->uuid)
                ->with('info', "Payment was cancelled or was not completed on {$gateway}. You may try again whenever you are ready.");
        }
    }

    /**
     * Resolve invoice by UUID, ID, or invoice number
     */
    protected function resolveInvoice(string $identifier): Invoice
    {
        if (Str::isUuid($identifier)) {
            return Invoice::where('uuid', $identifier)->firstOrFail();
        }

        if (is_numeric($identifier)) {
            $invoice = Invoice::find((int) $identifier);
            if ($invoice) {
                return $invoice;
            }
        }

        return Invoice::where('invoice_number', $identifier)->firstOrFail();
    }
}
