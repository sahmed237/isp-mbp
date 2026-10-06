<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\HotspotVoucher;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Setting;
use App\Services\BillingService;
use App\Services\HotspotVoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function __construct(
        protected BillingService $billingService,
        protected HotspotVoucherService $voucherService
    ) {}

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);

        $customer = Customer::where(function ($q) use ($login) {
            $q->where('portal_username', $login)
              ->orWhere('email', $login)
              ->orWhere('account_number', $login)
              ->orWhere('radius_username', $login)
              ->orWhere('phone', $login);
        })->first();

        if ($customer && Hash::check($credentials['password'], (string) $customer->portal_password)) {
            Auth::guard('customer')->login($customer, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('portal.dashboard'));
        }

        return back()->withErrors([
            'login' => 'Invalid subscriber credentials. Please check your Account Number, Username or Password.',
        ])->onlyInput('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')->with('info', 'You have been securely signed out.');
    }

    public function dashboard(): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $customer->load(['package', 'activeSubscription.package', 'branch']);

        $activeSession = $customer->activeRadiusSession;
        $unpaidInvoices = $customer->unpaidInvoices()->take(3)->get();
        $recentPayments = $customer->payments()->take(5)->get();

        return view('portal.dashboard', compact('customer', 'activeSession', 'unpaidInvoices', 'recentPayments'));
    }

    public function invoices(): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $invoices = $customer->invoices()->with('items')->paginate(10);

        return view('portal.invoices', compact('customer', 'invoices'));
    }

    public function showInvoice(Invoice $invoice): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        abort_if($invoice->customer_id !== $customer->id, 403, 'Unauthorized access to invoice.');

        $invoice->load(['items', 'payments', 'subscription.package']);

        return view('portal.invoice_show', compact('customer', 'invoice'));
    }

    public function payInvoice(Invoice $invoice, Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        abort_if($invoice->customer_id !== $customer->id, 403, 'Unauthorized access to invoice.');

        if ($invoice->isPaid()) {
            return back()->with('info', 'This invoice is already marked as paid.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:card,bank_transfer,paystack,monnify,moniepoint'],
        ]);

        $method = $validated['payment_method'];

        // If paying via online automated gateway (Paystack or Monnify)
        if (in_array($method, ['paystack', 'monnify'], true)) {
            $reference = $invoice->invoice_number . '-' . time() . '-' . strtoupper(Str::random(4));
            $amount = (float) $invoice->balance_due;

            $attempt = PaymentAttempt::create([
                'organization_id' => $invoice->organization_id,
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
                'payment_method' => $method,
                'reference' => $reference,
                'amount' => $amount,
                'currency' => 'NGN',
                'status' => 'initiated',
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_payload' => [
                    'source' => 'customer_portal',
                    'amount' => $amount,
                    'reference' => $reference,
                    'customer_id' => $customer->id,
                    'timestamp' => now()->toIso8601String(),
                ],
                'initiated_at' => now(),
            ]);

            try {
                $gateway = \App\Services\Payment\Gateways\PaymentGatewayFactory::create($method);
                $callbackUrl = route('portal.payment.callback', ['gateway' => $method]);

                $initResult = $gateway->initialize(
                    invoice: $invoice,
                    email: $customer->email ?: '',
                    phone: $customer->phone ?: '',
                    callbackUrl: $callbackUrl,
                    reference: $reference
                );

                $decodedInit = $initResult->rawResponse
                    ? (json_decode($initResult->rawResponse, true) ?? ['raw' => $initResult->rawResponse])
                    : null;

                $attempt->update([
                    'status' => 'pending',
                    'response_payload' => $decodedInit,
                ]);

                if (!empty($initResult->redirectUrl)) {
                    return redirect()->away($initResult->redirectUrl);
                }
            } catch (\Throwable $e) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);

                return back()->with('error', "{$method} Gateway Error: " . $e->getMessage());
            }
        }

        // Direct / Offline payment recording
        $payment = $this->billingService->recordPayment($invoice, [
            'amount' => $invoice->balance_due,
            'payment_method' => $method,
            'reference' => 'PORTAL-' . strtoupper(Str::random(10)),
            'paid_at' => now(),
            'notes' => 'Customer self-service portal checkout.',
            'raw_payload' => [
                'source' => 'customer_portal_direct',
                'customer_id' => $customer->id,
                'ip' => $request->ip(),
            ],
        ]);

        return redirect()->route('portal.dashboard')
            ->with('success', "Payment successful! ₦" . number_format((float)$invoice->balance_due, 2) . " paid. Your subscription is active and synced to the broadband network.");
    }

    public function paymentCallback(Request $request, string $gateway): RedirectResponse
    {
        $payRef = $request->query('paymentReference') ?? $request->query('reference') ?? $request->query('trxref');
        $txnRef = $request->query('transactionReference');
        $reference = $payRef ?: $txnRef;

        $statusParam = strtoupper((string) ($request->query('paymentStatus') ?? $request->query('status') ?? ''));
        $isCancelledOrDeclined = in_array($statusParam, ['USER_CANCELLED', 'CANCELLED', 'FAILED', 'EXPIRED'], true)
            || $request->query('cancelled') === 'true';

        $attempt = PaymentAttempt::where('reference', $payRef ?: $reference)->latest()->first();
        if (!$attempt && $txnRef) {
            $attempt = PaymentAttempt::where('reference', $txnRef)->latest()->first();
        }

        if ($isCancelledOrDeclined) {
            if ($attempt) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => "Portal payment session cancelled or declined on {$gateway} (Status: {$statusParam}).",
                    'verification_payload' => $request->query(),
                    'completed_at' => now(),
                ]);
            }

            return redirect()->route('portal.dashboard')
                ->with('info', "Payment was cancelled or was not completed on {$gateway}. You may try again whenever you are ready.");
        }

        if (!$reference) {
            return redirect()->route('portal.dashboard')->with('info', 'No completed payment transaction was detected.');
        }

        try {
            $paymentGateway = \App\Services\Payment\Gateways\PaymentGatewayFactory::create($gateway);
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

            if ($result->amountPaid > 0) {
                // Find invoice by reference or invoice number
                $invoiceNumber = explode('-', (string) $reference)[0] ?? '';
                $invoice = $attempt?->invoice
                    ?? Invoice::where('invoice_number', $invoiceNumber)
                    ->orWhere('id', (int) str_replace('INV-', '', $invoiceNumber))
                    ->first();

                if ($invoice && !$invoice->isPaid()) {
                    $payment = $this->billingService->recordPayment($invoice, [
                        'amount' => $result->amountPaid,
                        'payment_method' => $gateway,
                        'reference' => $result->reference ?? (string) $reference,
                        'paid_at' => $result->paymentDate,
                        'notes' => "Online payment verified via {$gateway}",
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

                    return redirect()->route('portal.dashboard')
                        ->with('success', "Payment of ₦" . number_format($result->amountPaid, 2) . " verified successfully! Your broadband subscription is now active.");
                }
            }

            if ($attempt) {
                $isSuperseded = $attempt->isSuperseded();
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => $isSuperseded
                        ? $attempt->error_message
                        : "Payment verification returned unpaid status or was declined on {$gateway}.",
                    'completed_at' => now(),
                ]);

                if ($isSuperseded) {
                    return redirect()->route('portal.dashboard')
                        ->with('info', "This payment session has expired or was replaced by a newer checkout session. You may try again with your latest checkout.");
                }
            }

            return redirect()->route('portal.dashboard')->with('info', "Payment was not completed or was declined on {$gateway}. You may try again whenever you are ready.");
        } catch (\Throwable $e) {
            if ($attempt) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            return redirect()->route('portal.dashboard')->with('info', "Payment was cancelled or was not completed on {$gateway}. You may try again whenever you are ready.");
        }
    }

    public function payments(): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $payments = $customer->payments()->with('invoice')->paginate(10);

        return view('portal.payments', compact('customer', 'payments'));
    }

    public function hotspot(): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $vouchers = $customer->vouchers()->with('package')->paginate(10);
        $hotspotPackages = Package::where('connection_type', 'hotspot')->orWhere('connection_type', 'all')->get();
        if ($hotspotPackages->isEmpty()) {
            $hotspotPackages = Package::where('status', 'active')->get();
        }

        return view('portal.hotspot', compact('customer', 'vouchers', 'hotspotPackages'));
    }

    public function buyHotspot(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'payment_method' => ['required', 'string', 'in:card,bank_transfer,paystack'],
        ]);

        $package = Package::findOrFail($validated['package_id']);

        $voucher = $this->voucherService->purchaseVoucherForCustomer(
            customer: $customer,
            package: $package,
            paymentMethod: $validated['payment_method']
        );

        return redirect()->route('portal.hotspot')
            ->with('success', "Hotspot Voucher purchased successfully! Voucher Code: {$voucher->code} | Username: {$voucher->username} | PIN: {$voucher->password}");
    }

    public function profile(): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $customer->load('package');

        return view('portal.profile', compact('customer'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, (string) $customer->portal_password)) {
            return back()->withErrors(['current_password' => 'Current password does not match our records.']);
        }

        $customer->update([
            'portal_password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Portal security password updated successfully.');
    }

    /**
     * Reseller Hotspot Hub: Manage voucher batches and prepaid wallet
     */
    public function resellerVouchers(Request $request): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        abort_if(!$customer->isReseller(), 403, 'Reseller privileges are required to access this portal.');

        // Distinct batches owned by this reseller
        $batches = HotspotVoucher::where('customer_id', $customer->id)
            ->whereNotNull('batch_id')
            ->select(
                'batch_id',
                DB::raw('count(*) as total_vouchers'),
                DB::raw("count(case when status = 'unused' then 1 end) as unused_count"),
                DB::raw("count(case when status in ('active', 'used') then 1 end) as used_count"),
                DB::raw('sum(price) as total_value'),
                DB::raw('max(created_at) as created_at')
            )
            ->groupBy('batch_id')
            ->orderByRaw('max(created_at) desc')
            ->paginate(10, ['*'], 'batches_page');

        $packages = Package::where('status', 'active')
            ->where(function ($q) {
                $q->where('connection_type', 'hotspot')
                  ->orWhere('connection_type', 'all');
            })
            ->orderBy('price', 'asc')
            ->get();

        if ($packages->isEmpty()) {
            $packages = Package::where('status', 'active')->orderBy('price', 'asc')->get();
        }

        $paystackActive = (bool) Setting::get('paystack_active', false);
        $monnifyActive = (bool) Setting::get('monnify_active', false);

        return view('portal.reseller.vouchers', compact('customer', 'batches', 'packages', 'paystackActive', 'monnifyActive'));
    }

    /**
     * Purchase a bulk batch of vouchers for a reseller (Wallet or Gateway)
     */
    public function resellerBuyBatch(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        abort_if(!$customer->isReseller(), 403, 'Reseller privileges are required to perform this action.');

        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'payment_method' => ['required', 'string', 'in:wallet,paystack,monnify'],
            'prefix' => ['nullable', 'string', 'max:6'],
        ]);

        /** @var Package $package */
        $package = Package::findOrFail($validated['package_id']);
        $quantity = (int) $validated['quantity'];
        $totalCost = (float) $package->price * $quantity;
        $method = $validated['payment_method'];
        $prefix = !empty($validated['prefix']) ? strtoupper(trim($validated['prefix'])) : 'RS';

        // OPTION 1: Pay using Reseller Prepaid Wallet Balance
        if ($method === 'wallet') {
            if (!$customer->hasSufficientBalance($totalCost)) {
                return redirect()->route('portal.reseller.vouchers')
                    ->withInput()
                    ->with('error', "Insufficient wallet balance (Available: ₦" . number_format((float)$customer->balance, 2) . ", Required: ₦" . number_format($totalCost, 2) . "). Please top up your wallet or choose an online payment gateway.");
            }

            // Deduct wallet balance
            $customer->deductBalance($totalCost, "Batch voucher purchase ({$quantity}x {$package->name})");

            $batchId = 'RES-' . date('Ymd-His') . '-' . strtoupper(Str::random(3));
            $this->voucherService->generateBatch(
                package: $package,
                quantity: $quantity,
                options: [
                    'batch_id' => $batchId,
                    'prefix' => $prefix,
                ],
                customerId: $customer->id
            );

            // Record accounting payment
            Payment::create([
                'organization_id' => $customer->organization_id,
                'customer_id' => $customer->id,
                'amount' => $totalCost,
                'payment_method' => 'wallet',
                'reference' => 'WAL-' . strtoupper(Str::random(8)),
                'status' => 'successful',
                'paid_at' => now(),
                'notes' => "Reseller batch purchase: {$quantity}x {$package->name} (Batch: {$batchId}) paid via Wallet",
            ]);

            return redirect()->route('portal.reseller.vouchers')
                ->with('success', "Batch of {$quantity} vouchers ({$batchId}) generated successfully! You can now print the cards.")
                ->with('just_created_batch', $batchId);
        }

        // OPTION 2: Pay online via Payment Gateway (Paystack or Monnify)
        $batchId = 'RES-' . date('Ymd-His') . '-' . strtoupper(Str::random(3));
        $reference = 'RSB-' . time() . '-' . strtoupper(Str::random(5));
        $callbackUrl = route('portal.reseller.callback', ['gateway' => $method]);

        $attempt = PaymentAttempt::create([
            'organization_id' => $customer->organization_id,
            'customer_id' => $customer->id,
            'batch_id' => $batchId,
            'payment_method' => $method,
            'reference' => $reference,
            'amount' => $totalCost,
            'currency' => 'NGN',
            'status' => 'initiated',
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_payload' => [
                'type' => 'reseller_batch',
                'package_id' => $package->id,
                'quantity' => $quantity,
                'prefix' => $prefix,
                'batch_id' => $batchId,
                'total_cost' => $totalCost,
                'callback_url' => $callbackUrl,
                'timestamp' => now()->toIso8601String(),
            ],
            'initiated_at' => now(),
        ]);

        $dummyInvoice = (object) [
            'id' => 0,
            'balance_due' => $totalCost,
            'total_amount' => $totalCost,
            'invoice_number' => $batchId,
            'customer' => (object) ['full_name' => $customer->full_name],
        ];

        try {
            $gateway = \App\Services\Payment\Gateways\PaymentGatewayFactory::create($method);
            $initResult = $gateway->initialize(
                invoice: $dummyInvoice,
                email: $customer->email ?: "reseller.{$customer->id}@isp-mbp.ng",
                phone: $customer->phone ?: '',
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
            $attempt->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()->route('portal.reseller.vouchers')
                ->with('error', "Payment gateway error: " . $e->getMessage());
        }

        return redirect()->route('portal.reseller.vouchers')
            ->with('error', 'Unable to initiate online payment session. Please try again.');
    }

    /**
     * Top up reseller prepaid wallet via payment gateway
     */
    public function resellerTopupWallet(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        abort_if(!$customer->isReseller(), 403, 'Reseller privileges are required to perform this action.');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:500'],
            'payment_method' => ['required', 'string', 'in:paystack,monnify'],
        ]);

        $amount = (float) $validated['amount'];
        $method = $validated['payment_method'];
        $reference = 'WALTOP-' . time() . '-' . strtoupper(Str::random(5));
        $callbackUrl = route('portal.reseller.callback', ['gateway' => $method]);

        $attempt = PaymentAttempt::create([
            'organization_id' => $customer->organization_id,
            'customer_id' => $customer->id,
            'payment_method' => $method,
            'reference' => $reference,
            'amount' => $amount,
            'currency' => 'NGN',
            'status' => 'initiated',
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_payload' => [
                'type' => 'wallet_topup',
                'amount' => $amount,
                'callback_url' => $callbackUrl,
                'timestamp' => now()->toIso8601String(),
            ],
            'initiated_at' => now(),
        ]);

        $dummyInvoice = (object) [
            'id' => 0,
            'balance_due' => $amount,
            'total_amount' => $amount,
            'invoice_number' => 'TOPUP-' . strtoupper(Str::random(6)),
            'customer' => (object) ['full_name' => $customer->full_name],
        ];

        try {
            $gateway = \App\Services\Payment\Gateways\PaymentGatewayFactory::create($method);
            $initResult = $gateway->initialize(
                invoice: $dummyInvoice,
                email: $customer->email ?: "reseller.{$customer->id}@isp-mbp.ng",
                phone: $customer->phone ?: '',
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
            $attempt->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()->route('portal.reseller.vouchers')
                ->with('error', "Payment gateway error: " . $e->getMessage());
        }

        return redirect()->route('portal.reseller.vouchers')
            ->with('error', 'Unable to initiate online top-up session. Please try again.');
    }

    /**
     * Handle gateway callback for Reseller batch purchases or wallet top-ups
     */
    public function resellerCallback(Request $request, string $gateway): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        abort_if(!$customer || !$customer->isReseller(), 403, 'Unauthorized.');

        $payRef = $request->query('paymentReference') ?? $request->query('reference') ?? $request->query('trxref');
        $txnRef = $request->query('transactionReference');
        $reference = $payRef ?: $txnRef;

        $statusParam = strtoupper((string) ($request->query('paymentStatus') ?? $request->query('status') ?? ''));
        $isCancelled = in_array($statusParam, ['USER_CANCELLED', 'CANCELLED', 'FAILED', 'EXPIRED'], true) || $request->query('cancelled') === 'true';

        $attempt = PaymentAttempt::where('customer_id', $customer->id)
            ->where(function ($q) use ($payRef, $reference, $txnRef) {
                if ($payRef) $q->where('reference', $payRef);
                elseif ($txnRef) $q->where('reference', $txnRef);
                elseif ($reference) $q->where('reference', $reference);
            })
            ->latest()
            ->first();

        if ($isCancelled) {
            if ($attempt) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => "Payment was cancelled or declined on {$gateway}.",
                    'completed_at' => now(),
                ]);
            }
            return redirect()->route('portal.reseller.vouchers')->with('info', "Payment was cancelled on {$gateway}.");
        }

        if (!$reference) {
            return redirect()->route('portal.reseller.vouchers')->with('info', 'No payment reference detected.');
        }

        try {
            $paymentGateway = \App\Services\Payment\Gateways\PaymentGatewayFactory::create($gateway);
            $verifyRef = ($gateway === 'monnify' && $txnRef) ? (string) $txnRef : (string) $reference;
            $result = $paymentGateway->verify($verifyRef);

            $decodedRaw = $result->rawResponse
                ? (json_decode($result->rawResponse, true) ?? ['raw' => $result->rawResponse])
                : null;

            if ($attempt) {
                $attempt->update(['verification_payload' => $decodedRaw]);
            }

            if ($result->amountPaid > 0) {
                $type = $attempt?->request_payload['type'] ?? 'reseller_batch';

                if ($type === 'wallet_topup') {
                    $customer->creditBalance($result->amountPaid, "Online wallet top-up via {$gateway} (Ref: {$reference})");

                    Payment::create([
                        'organization_id' => $customer->organization_id,
                        'customer_id' => $customer->id,
                        'amount' => $result->amountPaid,
                        'payment_method' => $gateway,
                        'reference' => $result->reference ?? (string) $reference,
                        'status' => 'successful',
                        'paid_at' => now(),
                        'notes' => "Reseller prepaid wallet top-up via {$gateway}",
                        'raw_payload' => $decodedRaw,
                    ]);

                    if ($attempt) {
                        $attempt->update(['status' => 'successful', 'completed_at' => now()]);
                    }

                    return redirect()->route('portal.reseller.vouchers')
                        ->with('success', "Wallet top-up successful! ₦" . number_format($result->amountPaid, 2) . " has been credited to your reseller account.");
                }

                // Reseller batch creation
                $packageId = $attempt?->request_payload['package_id'] ?? null;
                $quantity = (int) ($attempt?->request_payload['quantity'] ?? 1);
                $prefix = $attempt?->request_payload['prefix'] ?? 'RS';
                $batchId = $attempt?->request_payload['batch_id'] ?? ('RES-' . date('Ymd-His'));

                $package = Package::find($packageId);
                if (!$package) {
                    throw new \Exception('Package not found for this batch order.');
                }

                $this->voucherService->generateBatch(
                    package: $package,
                    quantity: $quantity,
                    options: [
                        'batch_id' => $batchId,
                        'prefix' => $prefix,
                    ],
                    customerId: $customer->id
                );

                Payment::create([
                    'organization_id' => $customer->organization_id,
                    'customer_id' => $customer->id,
                    'amount' => $result->amountPaid,
                    'payment_method' => $gateway,
                    'reference' => $result->reference ?? (string) $reference,
                    'status' => 'successful',
                    'paid_at' => now(),
                    'notes' => "Reseller batch purchase: {$quantity}x {$package->name} (Batch: {$batchId}) via {$gateway}",
                    'raw_payload' => $decodedRaw,
                ]);

                if ($attempt) {
                    $attempt->update(['status' => 'successful', 'completed_at' => now()]);
                }

                return redirect()->route('portal.reseller.vouchers')
                    ->with('success', "Payment confirmed! Batch of {$quantity} vouchers ({$batchId}) generated successfully! You can now print your cards.")
                    ->with('just_created_batch', $batchId);
            }

            return redirect()->route('portal.reseller.vouchers')->with('error', "Payment was not verified by {$gateway}.");
        } catch (\Throwable $e) {
            if ($attempt) {
                $attempt->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'completed_at' => now()]);
            }
            return redirect()->route('portal.reseller.vouchers')->with('error', 'Payment verification error: ' . $e->getMessage());
        }
    }

    /**
     * Render high-resolution printable perforated card grid for a reseller batch
     */
    public function resellerPrintBatch(string $batchId): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        abort_if(!$customer->isReseller(), 403, 'Reseller privileges are required to print batches.');

        $vouchers = HotspotVoucher::with('package')
            ->where('batch_id', $batchId)
            ->where('customer_id', $customer->id)
            ->orderBy('id', 'asc')
            ->get();

        abort_if($vouchers->isEmpty(), 404, 'No vouchers found for this batch.');

        $package = $vouchers->first()->package;
        $companyInfo = Setting::getCompanyInfo();
        $hotspotSsid = Setting::get('hotspot_ssid', 'ISP-MBP-WiFi');
        $hotspotLoginUrl = Setting::get('hotspot_login_url', 'http://10.0.0.1/login');

        return view('portal.reseller.print_batch', compact('vouchers', 'batchId', 'customer', 'package', 'companyInfo', 'hotspotSsid', 'hotspotLoginUrl'));
    }
}

