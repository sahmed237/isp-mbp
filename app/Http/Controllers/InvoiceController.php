<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Invoice::class);

        $filters = $request->only(['search', 'status', 'customer_id']);
        $invoices = $this->billingService->getPaginatedInvoices($filters);

        return view('admin.invoices.index', compact('invoices', 'filters'));
    }

    public function show(Invoice $invoice): View
    {
        Gate::authorize('view', $invoice);

        $invoice->load([
            'customer',
            'subscription.package',
            'items',
            'payments.recordedBy',
            'paymentAttempts.payment',
            'organization',
        ]);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice): View
    {
        Gate::authorize('view', $invoice);

        $invoice->load(['customer', 'subscription.package', 'items', 'payments', 'organization']);

        return view('admin.invoices.print', compact('invoice'));
    }

    public function create(): View
    {
        Gate::authorize('create', Invoice::class);

        $customers = Customer::where('status', '!=', 'terminated')->get();

        return view('admin.invoices.create', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Invoice::class);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $customer = Customer::findOrFail($validated['customer_id']);

            $invoice = Invoice::create([
                'organization_id' => $customer->organization_id,
                'customer_id' => $customer->id,
                'status' => 'unpaid',
                'issue_date' => now()->toDateString(),
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
            ]);

            $total = 0.0;
            foreach ($validated['items'] as $itemData) {
                $itemTotal = (float) $itemData['quantity'] * (float) $itemData['unit_price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => (int) $itemData['quantity'],
                    'unit_price' => (float) $itemData['unit_price'],
                    'total_price' => $itemTotal,
                ]);
                $total += $itemTotal;
            }

            $invoice->update([
                'subtotal' => $total,
                'total_amount' => $total,
            ]);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    public function recordPayment(Invoice $invoice, Request $request): RedirectResponse
    {
        Gate::authorize('update', $invoice);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $invoice->balance_due],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,card,paystack,moniepoint,other'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['raw_payload'] = [
            'type' => 'manual_admin_recording',
            'admin_user_id' => auth()->id(),
            'admin_user_name' => auth()->user()?->name,
            'client_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'recorded_at' => now()->toIso8601String(),
        ];

        $payment = $this->billingService->recordPayment($invoice, $validated);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Payment {$payment->payment_number} of ₦" . number_format((float)$payment->amount, 2) . " recorded. Subscription and RADIUS synced.");
    }
}
