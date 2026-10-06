<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HotspotVoucher;
use App\Models\Package;
use App\Services\HotspotVoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HotspotVoucherController extends Controller
{
    public function __construct(
        protected HotspotVoucherService $voucherService
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('radius.users.view'), 403, 'Unauthorized access to RADIUS vouchers.');

        // View vouchers
        $filters = $request->only(['search', 'status', 'package_id', 'batch_id']);
        $vouchers = $this->voucherService->getPaginatedVouchers($filters);
        $packages = Package::where('connection_type', 'hotspot')->orWhere('connection_type', 'all')->get();
        if ($packages->isEmpty()) {
            $packages = Package::where('status', 'active')->get();
        }

        $batches = HotspotVoucher::whereNotNull('batch_id')
            ->groupBy('batch_id')
            ->orderByRaw('MAX(id) DESC')
            ->take(10)
            ->pluck('batch_id');

        return view('admin.vouchers.index', compact('vouchers', 'packages', 'batches', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('radius.users.create'), 403, 'Unauthorized to generate RADIUS vouchers.');

        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'prefix' => ['nullable', 'string', 'max:6'],
            'duration_value' => ['nullable', 'integer', 'min:1'],
            'duration_unit' => ['required', 'string', 'in:minutes,hours,days'],
        ]);

        $package = Package::findOrFail($validated['package_id']);

        $vouchers = $this->voucherService->generateBatch($package, (int) $validated['quantity'], [
            'prefix' => $validated['prefix'] ?? 'HS',
            'duration_value' => (int) ($validated['duration_value'] ?? $package->validity_period),
            'duration_unit' => $validated['duration_unit'],
        ]);

        $firstVoucher = $vouchers->first();
        $batchId = $firstVoucher?->batch_id;

        return redirect()->route('vouchers.index', ['batch_id' => $batchId])
            ->with('success', "Batch of {$validated['quantity']} hotspot vouchers generated and synchronized to FreeRADIUS AAA.");
    }

    public function printBatch(Request $request, string $batchId): View
    {
        abort_unless($request->user()->can('radius.users.view'), 403, 'Unauthorized to view RADIUS vouchers.');

        $vouchers = HotspotVoucher::with('package')
            ->where('batch_id', $batchId)
            ->get();

        abort_if($vouchers->isEmpty(), 404, 'Batch not found.');

        return view('admin.vouchers.print_batch', compact('vouchers', 'batchId'));
    }

    public function destroy(Request $request, HotspotVoucher $voucher): RedirectResponse
    {
        abort_unless($request->user()->can('radius.users.delete'), 403, 'Unauthorized to delete RADIUS vouchers.');

        $this->voucherService->deleteVoucher($voucher);

        return redirect()->back()
            ->with('success', "Voucher {$voucher->code} deleted and deprovisioned from RADIUS.");
    }
}
