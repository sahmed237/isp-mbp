<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HotspotVoucher;
use App\Models\Package;
use App\Models\PaymentAttempt;
use App\Models\Setting;
use App\Services\HotspotVoucherService;
use App\Services\Payment\Gateways\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PublicHotspotController extends Controller
{
    public function __construct(
        protected HotspotVoucherService $voucherService
    ) {}

    /**
     * Display public self-service hotspot package catalog
     */
    public function index(): View
    {
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

        $companyInfo = Setting::getCompanyInfo();
        $paystackActive = (bool) Setting::get('paystack_active', false);
        $monnifyActive = (bool) Setting::get('monnify_active', false);

        return view('public.hotspot.index', compact('packages', 'companyInfo', 'paystackActive', 'monnifyActive'));
    }

    /**
     * Initiate online payment checkout for a hotspot voucher
     */
    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'payment_method' => ['required', 'string', 'in:paystack,monnify'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{10,20}$/'],
        ], [
            'package_id.required' => 'Please select a hotspot internet package.',
            'payment_method.required' => 'Please choose a payment method.',
            'payment_method.in' => 'Selected payment gateway is invalid.',
            'customer_email.required' => 'Email address is required for payment receipt.',
            'customer_phone.required' => 'Phone number is required so you can look up your voucher code.',
        ]);

        $rawDigits = preg_replace('/\D/', '', (string) $validated['customer_phone']);
        if (strlen($rawDigits) < 10 || strlen($rawDigits) > 15) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['customer_phone' => 'Phone number must contain between 10 and 15 digits.']);
        }

        /** @var Package $package */
        $package = Package::findOrFail($validated['package_id']);
        $method = $validated['payment_method'];
        $email = trim($validated['customer_email']);
        $phone = trim($validated['customer_phone']);

        $reference = 'HSP-' . time() . '-' . strtoupper(Str::random(5));
        $amount = (float) $package->price;

        $callbackUrl = route('public.hotspot.callback', ['gateway' => $method]);

        $requestPayload = [
            'package_id' => $package->id,
            'package_name' => $package->name,
            'amount' => $amount,
            'gateway' => $method,
            'reference' => $reference,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'callback_url' => $callbackUrl,
            'client_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ];

        // 1. Record forensic PaymentAttempt
        $attempt = PaymentAttempt::create([
            'organization_id' => $package->organization_id,
            'customer_id' => null,
            'invoice_id' => null,
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

        // Virtual invoice representation for PaymentGatewayFactory
        $dummyInvoice = (object) [
            'id' => 0,
            'balance_due' => $amount,
            'total_amount' => $amount,
            'invoice_number' => 'HS-VCH-' . strtoupper(Str::random(6)),
            'customer' => (object) ['full_name' => "Hotspot User ({$phone})"],
        ];

        try {
            $gateway = PaymentGatewayFactory::create($method);
            $initResult = $gateway->initialize(
                invoice: $dummyInvoice,
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
            Log::error("Public Hotspot Checkout Error ({$method}): " . $e->getMessage(), [
                'package_id' => $package->id,
                'attempt_id' => $attempt->id,
            ]);

            $attempt->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()->route('public.hotspot.index')
                ->withInput()
                ->with('error', "Payment gateway error: " . $e->getMessage());
        }

        $attempt->update([
            'status' => 'failed',
            'error_message' => 'Unable to initiate online payment session with gateway.',
            'completed_at' => now(),
        ]);

        return redirect()->route('public.hotspot.index')
            ->withInput()
            ->with('error', 'Unable to initiate online payment session. Please try again.');
    }

    /**
     * Handle payment gateway return callback for hotspot voucher purchase
     */
    public function callback(Request $request, string $gateway): RedirectResponse
    {
        $payRef = $request->query('paymentReference') ?? $request->query('reference') ?? $request->query('trxref');
        $txnRef = $request->query('transactionReference');
        $reference = $payRef ?: $txnRef;

        $statusParam = strtoupper((string) ($request->query('paymentStatus') ?? $request->query('status') ?? ''));
        $isCancelledOrDeclined = in_array($statusParam, ['USER_CANCELLED', 'CANCELLED', 'FAILED', 'EXPIRED'], true)
            || $request->query('cancelled') === 'true';

        $attempt = null;
        if ($payRef) {
            $attempt = PaymentAttempt::where('reference', $payRef)->latest()->first();
        }
        if (!$attempt && $txnRef) {
            $attempt = PaymentAttempt::where('reference', $txnRef)->latest()->first();
        }
        if (!$attempt) {
            $attempt = PaymentAttempt::where('payment_method', $gateway)
                ->whereNull('invoice_id')
                ->latest()
                ->first();
        }

        if ($isCancelledOrDeclined) {
            if ($attempt) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => "Payment session was cancelled or declined on {$gateway} (Status: {$statusParam}).",
                    'verification_payload' => $request->query(),
                    'completed_at' => now(),
                ]);
            }

            return redirect()->route('public.hotspot.index')
                ->with('info', "Payment was cancelled or was not completed on {$gateway}. You may try again whenever you are ready.");
        }

        if (!$reference) {
            return redirect()->route('public.hotspot.index')
                ->with('info', 'No completed payment transaction was detected.');
        }

        try {
            $paymentGateway = PaymentGatewayFactory::create($gateway);
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
                // If this attempt already has a voucher attached, redirect to it
                if ($attempt?->voucher_id) {
                    $existingVoucher = HotspotVoucher::find($attempt->voucher_id);
                    if ($existingVoucher) {
                        return redirect()->route('public.hotspot.voucher', $existingVoucher->code)
                            ->with('success', 'Your payment was confirmed and your voucher is ready!');
                    }
                }

                $packageId = $attempt?->request_payload['package_id'] ?? null;
                $package = $packageId ? Package::find($packageId) : null;

                if (!$package) {
                    // Fallback to finding by price or first active hotspot package
                    $package = Package::where('status', 'active')
                        ->where('price', '<=', $result->amountPaid)
                        ->latest()
                        ->first();
                }

                if (!$package) {
                    throw new \Exception('Matching hotspot package could not be resolved for this transaction.');
                }

                $voucher = $this->voucherService->createGuestVoucher(
                    package: $package,
                    paymentMethod: $gateway,
                    details: [
                        'reference' => $result->reference ?? (string) $reference,
                        'phone' => $attempt?->customer_phone,
                        'email' => $attempt?->customer_email,
                        'raw_payload' => $decodedRaw,
                    ]
                );

                if ($attempt) {
                    $attempt->update([
                        'status' => 'successful',
                        'voucher_id' => $voucher->id,
                        'error_message' => null,
                        'completed_at' => now(),
                    ]);
                }

                return redirect()->route('public.hotspot.voucher', $voucher->code)
                    ->with('success', 'Payment successful! Your hotspot access voucher has been activated.');
            }

            if ($attempt) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => 'Payment verification returned zero or unconfirmed amount.',
                    'completed_at' => now(),
                ]);
            }

            return redirect()->route('public.hotspot.index')
                ->with('error', "Payment was not confirmed by {$gateway}. Please contact support if you were debited.");
        } catch (\Throwable $e) {
            Log::error("Public Hotspot Callback Verification Error ({$gateway}): " . $e->getMessage(), [
                'reference' => $reference,
                'attempt_id' => $attempt?->id,
            ]);

            if ($attempt) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            return redirect()->route('public.hotspot.index')
                ->with('error', 'Payment verification encountered an issue: ' . $e->getMessage());
        }
    }

    /**
     * Display voucher receipt and quick-connect credentials
     */
    public function showVoucher(string $code): View
    {
        $query = HotspotVoucher::with(['package', 'organization']);
        $voucher = Str::isUuid($code)
            ? $query->where('uuid', $code)->firstOrFail()
            : $query->where('code', $code)->firstOrFail();

        $companyInfo = Setting::getCompanyInfo();
        $hotspotLoginUrl = Setting::get('hotspot_login_url', 'http://10.0.0.1/login');
        $hotspotSsid = Setting::get('hotspot_ssid', 'ISP-MBP-WiFi');

        $qrCodeSvg = QrCode::size(140)->margin(1)->generate($voucher->code);

        return view('public.hotspot.voucher', compact('voucher', 'companyInfo', 'hotspotLoginUrl', 'hotspotSsid', 'qrCodeSvg'));
    }

    /**
     * Voucher lookup for walk-in users who lost their page
     */
    public function lookup(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $validated = $request->validate([
                'search' => ['required', 'string', 'min:3'],
            ]);

            $search = trim($validated['search']);

            // Search by voucher code, username, or phone from payment attempt
            $voucher = HotspotVoucher::where('code', $search)
                ->orWhere('username', $search)
                ->first();

            if (!$voucher) {
                $attempt = PaymentAttempt::where('customer_phone', 'like', "%{$search}%")
                    ->orWhere('reference', $search)
                    ->whereNotNull('voucher_id')
                    ->latest()
                    ->first();

                if ($attempt) {
                    $voucher = HotspotVoucher::find($attempt->voucher_id);
                }
            }

            if ($voucher) {
                return redirect()->route('public.hotspot.voucher', $voucher->code);
            }

            return back()->withInput()->with('error', 'No active voucher found matching the provided code, phone number, or reference.');
        }

        $companyInfo = Setting::getCompanyInfo();
        return view('public.hotspot.lookup', compact('companyInfo'));
    }
}
