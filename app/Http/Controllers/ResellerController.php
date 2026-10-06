<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\HotspotVoucher;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Reseller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResellerController extends Controller
{
    /**
     * Display a listing of resellers with status tabs and search.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Reseller::class);

        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $query = Reseller::with(['branch', 'approvedBy'])
            ->latest();

        if ($status !== 'all' && in_array($status, ['pending', 'active', 'suspended', 'rejected'], true)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'ilike', "%{$search}%")
                  ->orWhere('contact_person', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('reseller_code', 'ilike', "%{$search}%");
            });
        }

        $resellers = $query->paginate(15)->withQueryString();

        // Status counts for filter pills
        $counts = [
            'all' => Reseller::count(),
            'pending' => Reseller::where('status', 'pending')->count(),
            'active' => Reseller::where('status', 'active')->count(),
            'suspended' => Reseller::where('status', 'suspended')->count(),
            'rejected' => Reseller::where('status', 'rejected')->count(),
        ];

        return view('admin.resellers.index', compact('resellers', 'status', 'search', 'counts'));
    }

    /**
     * Show the form for creating a new reseller directly by admin.
     */
    public function create(): View
    {
        Gate::authorize('create', Reseller::class);

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.resellers.create', compact('branches'));
    }

    /**
     * Store a newly created reseller in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Reseller::class);

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:resellers,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:resellers,phone'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'business_type' => ['required', 'string', 'in:cybercafe,pos_agent,phone_accessories,retail_agent,business_center,hotel,campus_kiosk,other'],
            'shop_address' => ['required', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'id_type' => ['nullable', 'string', 'in:nin,drivers_license,voters_card,passport,cac_registration'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'id_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'password' => ['required', 'string', 'min:8'],
            'initial_balance' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $defaultOrg = Organization::first();
        $idCardPath = null;
        if ($request->hasFile('id_document')) {
            $idCardPath = $request->file('id_document')->store('resellers/kyc', 'public');
        }

        $initialBalance = (float) ($validated['initial_balance'] ?? 0.00);

        $reseller = Reseller::create([
            'organization_id' => $defaultOrg?->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'business_name' => $validated['business_name'],
            'contact_person' => $validated['contact_person'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'alternate_phone' => $validated['alternate_phone'] ?? null,
            'password' => $validated['password'],
            'business_type' => $validated['business_type'],
            'shop_address' => $validated['shop_address'],
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'id_type' => $validated['id_type'] ?? null,
            'id_number' => $validated['id_number'] ?? null,
            'id_card_path' => $idCardPath,
            'balance' => $initialBalance,
            'status' => 'active', // Directly active since onboarded by admin
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::record(
            action: 'reseller.created',
            resourceType: 'Reseller',
            resourceId: (string) $reseller->id,
            description: "Admin " . Auth::user()->name . " onboarded reseller {$reseller->business_name} ({$reseller->reseller_code}) with initial wallet balance ₦{$initialBalance}.",
            organizationId: $reseller->organization_id
        );

        return redirect()->route('resellers.show', $reseller)
            ->with('success', "Reseller {$reseller->business_name} ({$reseller->reseller_code}) created successfully.");
    }

    /**
     * Display the specified reseller profile, KYC, wallet, and voucher history.
     */
    public function show(Reseller $reseller): View
    {
        Gate::authorize('view', $reseller);

        $reseller->load(['branch', 'approvedBy']);

        $totalVouchers = HotspotVoucher::where('reseller_id', $reseller->id)->count();
        $activeVouchers = HotspotVoucher::where('reseller_id', $reseller->id)->where('status', 'active')->count();
        $usedVouchers = HotspotVoucher::where('reseller_id', $reseller->id)->where('status', 'used')->count();
        $totalSales = (float) Payment::where('reseller_id', $reseller->id)->where('status', 'successful')->sum('amount');

        $recentBatches = HotspotVoucher::where('reseller_id', $reseller->id)
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
            ->take(10)
            ->get();

        $recentPayments = Payment::where('reseller_id', $reseller->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.resellers.show', compact(
            'reseller',
            'totalVouchers',
            'activeVouchers',
            'usedVouchers',
            'totalSales',
            'recentBatches',
            'recentPayments'
        ));
    }

    /**
     * Approve a pending reseller application.
     */
    public function approve(Request $request, Reseller $reseller): RedirectResponse
    {
        Gate::authorize('approve', $reseller);

        if (!$reseller->isPending()) {
            return back()->with('info', "Reseller is already in '{$reseller->status}' status.");
        }

        $reseller->update([
            'status' => 'active',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        AuditLog::record(
            action: 'reseller.approved',
            resourceType: 'Reseller',
            resourceId: (string) $reseller->id,
            description: "Admin " . Auth::user()->name . " approved reseller application for {$reseller->business_name} ({$reseller->reseller_code}).",
            organizationId: $reseller->organization_id
        );

        return back()->with('success', "Reseller application for {$reseller->business_name} has been APPROVED. The agent can now log in.");
    }

    /**
     * Reject a pending reseller application.
     */
    public function reject(Request $request, Reseller $reseller): RedirectResponse
    {
        Gate::authorize('approve', $reseller);

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $reseller->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        AuditLog::record(
            action: 'reseller.rejected',
            resourceType: 'Reseller',
            resourceId: (string) $reseller->id,
            description: "Admin " . Auth::user()->name . " rejected application for {$reseller->business_name} ({$reseller->reseller_code}). Reason: {$request->input('rejection_reason')}",
            organizationId: $reseller->organization_id
        );

        return back()->with('info', "Reseller application marked as REJECTED.");
    }

    /**
     * Toggle suspended/active status.
     */
    public function toggleStatus(Request $request, Reseller $reseller): RedirectResponse
    {
        Gate::authorize('update', $reseller);

        $newStatus = $reseller->isActive() ? 'suspended' : 'active';
        $reseller->update(['status' => $newStatus]);

        AuditLog::record(
            action: "reseller.status.{$newStatus}",
            resourceType: 'Reseller',
            resourceId: (string) $reseller->id,
            description: "Admin " . Auth::user()->name . " changed status of {$reseller->business_name} ({$reseller->reseller_code}) to {$newStatus}.",
            organizationId: $reseller->organization_id
        );

        return back()->with('success', "Reseller status changed to {$newStatus}.");
    }

    /**
     * Credit or debit reseller's prepaid wallet balance.
     */
    public function adjustWallet(Request $request, Reseller $reseller): RedirectResponse
    {
        Gate::authorize('update', $reseller);

        $validated = $request->validate([
            'action' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:1'],
            'notes' => ['required', 'string', 'max:255'],
        ]);

        $amount = (float) $validated['amount'];
        $notes = $validated['notes'];

        if ($validated['action'] === 'credit') {
            $reseller->creditBalance($amount, "Admin credit: {$notes}");

            Payment::create([
                'organization_id' => $reseller->organization_id,
                'reseller_id' => $reseller->id,
                'amount' => $amount,
                'payment_method' => 'manual_admin',
                'reference' => 'ADM-CR-' . time() . '-' . strtoupper(Str::random(4)),
                'status' => 'successful',
                'paid_at' => now(),
                'notes' => "Admin wallet credit: {$notes} (By: " . Auth::user()->name . ")",
            ]);

            AuditLog::record(
                action: 'reseller.wallet.credited',
                resourceType: 'Reseller',
                resourceId: (string) $reseller->id,
                description: "Admin " . Auth::user()->name . " credited ₦{$amount} to {$reseller->business_name}'s wallet. Notes: {$notes}",
                organizationId: $reseller->organization_id
            );

            return back()->with('success', "Wallet successfully credited by ₦" . number_format($amount, 2));
        }

        // Debit
        if (!$reseller->hasSufficientBalance($amount)) {
            return back()->with('error', "Cannot debit ₦" . number_format($amount, 2) . ". Reseller's current balance is only ₦" . number_format((float) $reseller->balance, 2));
        }

        $reseller->deductBalance($amount, "Admin debit: {$notes}");

        AuditLog::record(
            action: 'reseller.wallet.debited',
            resourceType: 'Reseller',
            resourceId: (string) $reseller->id,
            description: "Admin " . Auth::user()->name . " debited ₦{$amount} from {$reseller->business_name}'s wallet. Notes: {$notes}",
            organizationId: $reseller->organization_id
        );

        return back()->with('success', "Wallet successfully debited by ₦" . number_format($amount, 2));
    }

    /**
     * Remove the specified reseller (Soft delete).
     */
    public function destroy(Reseller $reseller): RedirectResponse
    {
        Gate::authorize('delete', $reseller);

        $reseller->delete();

        AuditLog::record(
            action: 'reseller.deleted',
            resourceType: 'Reseller',
            resourceId: (string) $reseller->id,
            description: "Admin " . Auth::user()->name . " deleted reseller {$reseller->business_name} ({$reseller->reseller_code}).",
            organizationId: $reseller->organization_id
        );

        return redirect()->route('resellers.index')->with('success', 'Reseller removed successfully.');
    }
}
