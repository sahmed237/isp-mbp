<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\HotspotVoucher;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Reseller;
use App\Models\Setting;
use App\Services\HotspotVoucherService;
use App\Services\Payment\Gateways\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResellerPortalController extends Controller
{
    public function __construct(
        protected HotspotVoucherService $voucherService
    ) {}

    /**
     * Display the reseller login view.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('reseller')->check()) {
            return redirect()->route('reseller.dashboard');
        }

        return view('reseller.login');
    }

    /**
     * Process reseller login attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);

        $reseller = Reseller::where(function ($q) use ($login) {
            $q->where('reseller_code', $login)
              ->orWhere('email', $login)
              ->orWhere('phone', $login);
        })->first();

        if (!$reseller || !Hash::check($credentials['password'], (string) $reseller->password)) {
            return back()->withErrors([
                'login' => 'Invalid credentials. Please verify your Reseller Code, Email, or Password.',
            ])->onlyInput('login');
        }

        if ($reseller->isPending()) {
            return back()->withErrors([
                'login' => "Your application (Code: {$reseller->reseller_code}) is currently under review. You will be notified once approved by our operations team.",
            ])->onlyInput('login');
        }

        if ($reseller->isRejected()) {
            $reason = $reseller->rejection_reason ? " Reason: {$reseller->rejection_reason}" : '';
            return back()->withErrors([
                'login' => "Your agent application was not approved.{$reason} Please contact support for assistance.",
            ])->onlyInput('login');
        }

        if ($reseller->isSuspended()) {
            return back()->withErrors([
                'login' => 'Your reseller account is temporarily suspended. Please contact the ISP administrator.',
            ])->onlyInput('login');
        }

        Auth::guard('reseller')->login($reseller, $request->boolean('remember'));
        $request->session()->regenerate();

        AuditLog::record(
            action: 'reseller.login',
            resourceType: 'Reseller',
            resourceId: (string) $reseller->id,
            description: "Reseller {$reseller->business_name} ({$reseller->reseller_code}) logged into the reseller portal.",
            organizationId: $reseller->organization_id
        );

        return redirect()->intended(route('reseller.dashboard'));
    }

    /**
     * Sign out the reseller.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('reseller')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('reseller.login')->with('info', 'You have been securely signed out.');
    }

    /**
     * Display the self-service reseller application form.
     */
    public function showApply(): View|RedirectResponse
    {
        if (Auth::guard('reseller')->check()) {
            return redirect()->route('reseller.dashboard');
        }

        return view('reseller.apply');
    }

    /**
     * Handle submission of a reseller onboarding application with KYC documents.
     */
    public function apply(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:resellers,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:resellers,phone'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],
            'business_type' => ['required', 'string', 'in:cybercafe,pos_agent,phone_accessories,retail_agent,business_center,hotel,campus_kiosk,other'],
            'shop_address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'id_type' => ['required', 'string', 'in:nin,drivers_license,voters_card,passport,cac_registration'],
            'id_number' => ['required', 'string', 'max:100'],
            'id_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB max
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $defaultOrg = Organization::first();
        if (!$defaultOrg) {
            return back()->with('error', 'Platform configuration error: No active organization detected. Please contact the ISP administrator.')->withInput();
        }

        // Store KYC document securely
        $idCardPath = null;
        if ($request->hasFile('id_document')) {
            $idCardPath = $request->file('id_document')->store('resellers/kyc', 'public');
        }

        $reseller = Reseller::create([
            'organization_id' => $defaultOrg->id,
            'business_name' => $validated['business_name'],
            'contact_person' => $validated['contact_person'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'alternate_phone' => $validated['alternate_phone'] ?? null,
            'password' => $validated['password'], // Automatically hashed by cast
            'business_type' => $validated['business_type'],
            'shop_address' => $validated['shop_address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'id_type' => $validated['id_type'],
            'id_number' => $validated['id_number'],
            'id_card_path' => $idCardPath,
            'balance' => 0.00,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::record(
            action: 'reseller.applied',
            resourceType: 'Reseller',
            resourceId: (string) $reseller->id,
            description: "New reseller application submitted: {$reseller->business_name} ({$reseller->reseller_code}) with KYC ID type {$reseller->id_type}.",
            organizationId: $reseller->organization_id
        );

        return redirect()->route('reseller.applied')->with([
            'reseller_code' => $reseller->reseller_code,
            'business_name' => $reseller->business_name,
            'email' => $reseller->email,
        ]);
    }

    /**
     * Show post-application confirmation view.
     */
    public function applied(): View
    {
        return view('reseller.applied');
    }

    /**
     * Reseller Portal Dashboard.
     */
    public function dashboard(): View
    {
        /** @var Reseller $reseller */
        $reseller = Auth::guard('reseller')->user();

        $totalVouchers = HotspotVoucher::where('reseller_id', $reseller->id)->count();
        $activeVouchers = HotspotVoucher::where('reseller_id', $reseller->id)->where('status', 'active')->count();
        $usedVouchers = HotspotVoucher::where('reseller_id', $reseller->id)->where('status', 'used')->count();
        
        $totalSpent = (float) Payment::where('reseller_id', $reseller->id)
            ->where('status', 'successful')
            ->sum('amount');

        // Recent batches generated by this reseller
        $recentBatches = HotspotVoucher::where('reseller_id', $reseller->id)
            ->whereNotNull('batch_id')
            ->select('batch_id', 'package_id', DB::raw('count(*) as count'), DB::raw('min(created_at) as created_at'))
            ->groupBy('batch_id', 'package_id')
            ->orderBy('created_at', 'desc')
            ->with('package')
            ->take(5)
            ->get();

        $recentPayments = Payment::where('reseller_id', $reseller->id)
            ->latest()
            ->take(5)
            ->get();

        $packages = Package::where('status', 'active')
            ->where(function ($q) {
                $q->where('connection_type', 'hotspot')
                  ->orWhere('connection_type', 'all')
                  ->orWhere('connection_type', 'universal');
            })
            ->orderBy('price', 'asc')
            ->get();

        return view('reseller.dashboard', compact(
            'reseller',
            'totalVouchers',
            'activeVouchers',
            'usedVouchers',
            'totalSpent',
            'recentBatches',
            'recentPayments',
            'packages'
        ));
    }

    /**
     * Voucher generation & batch inventory page.
     */
    public function vouchers(): View
    {
        /** @var Reseller $reseller */
        $reseller = Auth::guard('reseller')->user();

        $packages = Package::where('status', 'active')
            ->where(function ($q) {
                $q->where('connection_type', 'hotspot')
                  ->orWhere('connection_type', 'all')
                  ->orWhere('connection_type', 'universal');
            })
            ->orderBy('price', 'asc')
            ->get();

        $batches = HotspotVoucher::where('reseller_id', $reseller->id)
            ->whereNotNull('batch_id')
            ->select(
                'batch_id',
                'package_id',
                DB::raw('count(*) as total_count'),
                DB::raw("count(case when status = 'active' then 1 end) as active_count"),
                DB::raw("count(case when status = 'used' then 1 end) as used_count"),
                DB::raw('min(created_at) as created_at')
            )
            ->groupBy('batch_id', 'package_id')
            ->orderBy('created_at', 'desc')
            ->with('package')
            ->paginate(12);

        $paystackEnabled = Setting::get('paystack_enabled', false) && !empty(Setting::get('paystack_public_key'));
        $monnifyEnabled = Setting::get('monnify_enabled', false) && !empty(Setting::get('monnify_api_key'));

        return view('reseller.vouchers', compact(
            'reseller',
            'packages',
            'batches',
            'paystackEnabled',
            'monnifyEnabled'
        ));
    }

    /**
     * Purchase and generate a batch of hotspot vouchers.
     */
    public function buyBatch(Request $request): RedirectResponse
    {
        /** @var Reseller $reseller */
        $reseller = Auth::guard('reseller')->user();

        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'prefix' => ['nullable', 'string', 'max:5', 'alpha_num'],
            'payment_method' => ['required', 'string', 'in:wallet,paystack,monnify'],
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $quantity = (int) $validated['quantity'];
        $prefix = !empty($validated['prefix']) ? strtoupper(trim($validated['prefix'])) : 'RS';
        $totalCost = (float) $package->price * $quantity;
        $method = $validated['payment_method'];

        $batchId = 'RSL-' . date('Ymd-His') . '-' . strtoupper(Str::random(3));

        // Option 1: Instant purchase from prepaid wallet
        if ($method === 'wallet') {
            if (!$reseller->hasSufficientBalance($totalCost)) {
                $deficit = $totalCost - (float) $reseller->balance;
                return back()->with('error', "Insufficient wallet balance! Total cost is ₦" . number_format($totalCost, 2) . ", but your available balance is ₦" . number_format((float) $reseller->balance, 2) . ". Please top up ₦" . number_format($deficit, 2) . " or pay online.")->withInput();
            }

            DB::transaction(function () use ($reseller, $package, $quantity, $totalCost, $batchId, $prefix) {
                // Deduct wallet balance
                $reseller->deductBalance($totalCost, "Hotspot Batch: {$quantity}x {$package->name} (Batch: {$batchId})");

                // Generate vouchers assigned to this reseller
                $this->voucherService->generateBatch(
                    package: $package,
                    quantity: $quantity,
                    options: [
                        'batch_id' => $batchId,
                        'prefix' => $prefix,
                    ],
                    resellerId: $reseller->id
                );

                // Record payment entry
                Payment::create([
                    'organization_id' => $reseller->organization_id,
                    'reseller_id' => $reseller->id,
                    'amount' => $totalCost,
                    'payment_method' => 'wallet',
                    'reference' => 'WAL-' . time() . '-' . strtoupper(Str::random(4)),
                    'status' => 'successful',
                    'paid_at' => now(),
                    'notes' => "Wallet batch voucher purchase: {$quantity}x {$package->name} (Batch: {$batchId})",
                ]);

                AuditLog::record(
                    action: 'reseller.voucher_batch.purchased',
                    resourceType: 'Reseller',
                    resourceId: (string) $reseller->id,
                    description: "Reseller {$reseller->business_name} purchased batch {$batchId} ({$quantity}x {$package->name}) via wallet balance for ₦{$totalCost}.",
                    organizationId: $reseller->organization_id
                );
            });

            return redirect()->route('reseller.vouchers')
                ->with('success', "Batch of {$quantity} vouchers ({$batchId}) generated successfully! Deducted ₦" . number_format($totalCost, 2) . " from your wallet.")
                ->with('just_created_batch', $batchId);
        }

        // Option 2: Pay online via Paystack or Monnify
        $reference = 'RSL-ORD-' . time() . '-' . strtoupper(Str::random(5));
        $callbackUrl = route('reseller.callback', ['gateway' => $method]);

        $attempt = PaymentAttempt::create([
            'organization_id' => $reseller->organization_id,
            'reseller_id' => $reseller->id,
            'payment_method' => $method,
            'reference' => $reference,
            'amount' => $totalCost,
            'currency' => 'NGN',
            'status' => 'initiated',
            'customer_email' => $reseller->email,
            'customer_phone' => $reseller->phone,
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
            'customer' => (object) ['full_name' => $reseller->business_name],
        ];

        try {
            $gateway = PaymentGatewayFactory::create($method);
            $initResult = $gateway->initialize(
                invoice: $dummyInvoice,
                email: $reseller->email,
                phone: $reseller->phone,
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

            return redirect()->route('reseller.vouchers')
                ->with('error', "Payment gateway error: " . $e->getMessage());
        }

        return redirect()->route('reseller.vouchers')
            ->with('error', 'Unable to initiate online payment session. Please try again.');
    }

    /**
     * Top up reseller prepaid wallet via payment gateway.
     */
    public function topupWallet(Request $request): RedirectResponse
    {
        /** @var Reseller $reseller */
        $reseller = Auth::guard('reseller')->user();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:500'],
            'payment_method' => ['required', 'string', 'in:paystack,monnify'],
        ]);

        $amount = (float) $validated['amount'];
        $method = $validated['payment_method'];
        $reference = 'RSL-WAL-' . time() . '-' . strtoupper(Str::random(5));
        $callbackUrl = route('reseller.callback', ['gateway' => $method]);

        $attempt = PaymentAttempt::create([
            'organization_id' => $reseller->organization_id,
            'reseller_id' => $reseller->id,
            'payment_method' => $method,
            'reference' => $reference,
            'amount' => $amount,
            'currency' => 'NGN',
            'status' => 'initiated',
            'customer_email' => $reseller->email,
            'customer_phone' => $reseller->phone,
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
            'customer' => (object) ['full_name' => $reseller->business_name],
        ];

        try {
            $gateway = PaymentGatewayFactory::create($method);
            $initResult = $gateway->initialize(
                invoice: $dummyInvoice,
                email: $reseller->email,
                phone: $reseller->phone,
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

            return redirect()->route('reseller.vouchers')
                ->with('error', "Payment gateway error: " . $e->getMessage());
        }

        return redirect()->route('reseller.vouchers')
            ->with('error', 'Unable to initiate online top-up session. Please try again.');
    }

    /**
     * Handle gateway callback for Reseller batch purchases or wallet top-ups.
     */
    public function callback(Request $request, string $gateway): RedirectResponse
    {
        /** @var Reseller $reseller */
        $reseller = Auth::guard('reseller')->user();
        abort_if(!$reseller, 403, 'Unauthorized reseller session.');

        $payRef = $request->query('paymentReference') ?? $request->query('reference') ?? $request->query('trxref');
        $txnRef = $request->query('transactionReference');
        $reference = $payRef ?: $txnRef;

        $statusParam = strtoupper((string) ($request->query('paymentStatus') ?? $request->query('status') ?? ''));
        $isCancelled = in_array($statusParam, ['USER_CANCELLED', 'CANCELLED', 'FAILED', 'EXPIRED'], true) || $request->query('cancelled') === 'true';

        $attempt = PaymentAttempt::where('reseller_id', $reseller->id)
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
            return redirect()->route('reseller.vouchers')->with('info', "Payment was cancelled on {$gateway}.");
        }

        if (!$reference) {
            return redirect()->route('reseller.vouchers')->with('info', 'No payment reference detected.');
        }

        try {
            $paymentGateway = PaymentGatewayFactory::create($gateway);
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
                    $reseller->creditBalance($result->amountPaid, "Online wallet top-up via {$gateway} (Ref: {$reference})");

                    Payment::create([
                        'organization_id' => $reseller->organization_id,
                        'reseller_id' => $reseller->id,
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

                    AuditLog::record(
                        action: 'reseller.wallet.topup',
                        resourceType: 'Reseller',
                        resourceId: (string) $reseller->id,
                        description: "Reseller {$reseller->business_name} topped up wallet by ₦{$result->amountPaid} via {$gateway}.",
                        organizationId: $reseller->organization_id
                    );

                    return redirect()->route('reseller.vouchers')
                        ->with('success', "Wallet top-up successful! ₦" . number_format($result->amountPaid, 2) . " has been credited to your prepaid account.");
                }

                // Batch creation flow
                $packageId = $attempt?->request_payload['package_id'] ?? null;
                $quantity = (int) ($attempt?->request_payload['quantity'] ?? 1);
                $prefix = $attempt?->request_payload['prefix'] ?? 'RS';
                $batchId = $attempt?->request_payload['batch_id'] ?? ('RSL-' . date('Ymd-His'));

                $package = Package::find($packageId);
                if (!$package) {
                    throw new \Exception('Internet package not found for this batch order.');
                }

                $this->voucherService->generateBatch(
                    package: $package,
                    quantity: $quantity,
                    options: [
                        'batch_id' => $batchId,
                        'prefix' => $prefix,
                    ],
                    resellerId: $reseller->id
                );

                Payment::create([
                    'organization_id' => $reseller->organization_id,
                    'reseller_id' => $reseller->id,
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

                AuditLog::record(
                    action: 'reseller.voucher_batch.purchased',
                    resourceType: 'Reseller',
                    resourceId: (string) $reseller->id,
                    description: "Reseller {$reseller->business_name} paid ₦{$result->amountPaid} via {$gateway} for batch {$batchId} ({$quantity}x {$package->name}).",
                    organizationId: $reseller->organization_id
                );

                return redirect()->route('reseller.vouchers')
                    ->with('success', "Payment confirmed! Batch of {$quantity} vouchers ({$batchId}) generated successfully! You can now print your perforated cards.")
                    ->with('just_created_batch', $batchId);
            }

            return redirect()->route('reseller.vouchers')->with('error', "Payment was not verified by {$gateway}.");
        } catch (\Throwable $e) {
            if ($attempt) {
                $attempt->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'completed_at' => now()]);
            }
            return redirect()->route('reseller.vouchers')->with('error', 'Payment verification error: ' . $e->getMessage());
        }
    }

    /**
     * Render high-resolution printable perforated card grid for a reseller batch.
     */
    public function printBatch(string $batchId): View
    {
        /** @var Reseller $reseller */
        $reseller = Auth::guard('reseller')->user();

        $vouchers = HotspotVoucher::with('package')
            ->where('batch_id', $batchId)
            ->where('reseller_id', $reseller->id)
            ->orderBy('id', 'asc')
            ->get();

        abort_if($vouchers->isEmpty(), 404, 'No vouchers found for this batch.');

        $package = $vouchers->first()->package;
        $companyInfo = Setting::getCompanyInfo();
        $hotspotSsid = Setting::get('hotspot_ssid', 'ISP-MBP-WiFi');
        $hotspotLoginUrl = Setting::get('hotspot_login_url', 'http://10.0.0.1/login');

        return view('reseller.print_batch', compact('vouchers', 'batchId', 'reseller', 'package', 'companyInfo', 'hotspotSsid', 'hotspotLoginUrl'));
    }

    /**
     * Reseller profile view.
     */
    public function profile(): View
    {
        /** @var Reseller $reseller */
        $reseller = Auth::guard('reseller')->user();

        return view('reseller.profile', compact('reseller'));
    }

    /**
     * Update reseller profile or password.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var Reseller $reseller */
        $reseller = Auth::guard('reseller')->user();

        $validated = $request->validate([
            'alternate_phone' => ['nullable', 'string', 'max:30'],
            'shop_address' => ['required', 'string', 'max:1000'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], (string) $reseller->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match.'])->withInput();
            }

            $reseller->password = $validated['new_password']; // Automatically hashed by cast
        }

        $reseller->alternate_phone = $validated['alternate_phone'] ?? $reseller->alternate_phone;
        $reseller->shop_address = $validated['shop_address'];
        $reseller->save();

        AuditLog::record(
            action: 'reseller.profile.updated',
            resourceType: 'Reseller',
            resourceId: (string) $reseller->id,
            description: "Reseller {$reseller->business_name} updated their profile settings.",
            organizationId: $reseller->organization_id
        );

        return back()->with('success', 'Profile updated successfully.');
    }
}
