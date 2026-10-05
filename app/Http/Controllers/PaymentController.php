<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Payment::class);

        $filters = $request->only(['search', 'payment_method', 'customer_id']);
        $payments = $this->billingService->getPaginatedPayments($filters);

        return view('admin.payments.index', compact('payments', 'filters'));
    }

    public function show(Payment $payment): View
    {
        Gate::authorize('view', $payment);

        $payment->load(['customer', 'invoice.items', 'recordedBy', 'organization']);

        return view('admin.payments.show', compact('payment'));
    }

    public function print(Payment $payment): View
    {
        Gate::authorize('view', $payment);

        $payment->load(['customer', 'invoice', 'recordedBy', 'organization']);

        return view('admin.payments.print', compact('payment'));
    }
}
