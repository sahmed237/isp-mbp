<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Package;
use App\Models\Subscription;
use App\Services\BillingService;
use App\Services\RadiusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected BillingService $billingService,
        protected RadiusService $radiusService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Subscription::class);

        $filters = $request->only(['search', 'status', 'package_id']);
        $subscriptions = $this->billingService->getPaginatedSubscriptions($filters);
        $packages = Package::where('status', 'active')->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'packages', 'filters'));
    }

    public function show(Subscription $subscription): View
    {
        Gate::authorize('view', $subscription);

        $subscription->load(['customer', 'package', 'invoices.payments']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function activate(Subscription $subscription): RedirectResponse
    {
        Gate::authorize('update', $subscription);

        $this->billingService->activateSubscription($subscription);

        return redirect()->back()
            ->with('success', "Subscription {$subscription->subscription_number} activated successfully. Subscriber authenticated on RADIUS.");
    }

    public function suspend(Subscription $subscription): RedirectResponse
    {
        Gate::authorize('update', $subscription);

        $subscription->update(['status' => 'suspended']);

        // Suspend customer and reject RADIUS
        $customer = $subscription->customer;
        $customer->update(['status' => 'suspended']);
        $this->radiusService->syncCustomerToRadius($customer);

        AuditLog::record(
            action: 'updated',
            description: "Subscription {$subscription->subscription_number} suspended. RADIUS access revoked.",
            model: $subscription
        );

        return redirect()->back()
            ->with('warning', "Subscription suspended and customer blocked on RADIUS.");
    }

    public function renew(Subscription $subscription, Request $request): RedirectResponse
    {
        Gate::authorize('update', $subscription);

        $markPaid = $request->boolean('mark_paid');
        $paymentData = [];

        if ($markPaid) {
            $paymentData = [
                'amount' => $subscription->package->price,
                'payment_method' => $request->input('payment_method', 'cash'),
                'reference' => 'REN-' . strtoupper(uniqid()),
                'paid_at' => now(),
                'notes' => 'Subscription renewal payment.',
            ];
        }

        $invoice = $this->billingService->renewSubscription($subscription, $paymentData);

        $msg = $markPaid
            ? "Subscription {$subscription->subscription_number} renewed and activated successfully."
            : "Renewal invoice {$invoice->invoice_number} generated. Awaiting payment to activate.";

        return redirect()->route('subscriptions.show', $subscription)->with('success', $msg);
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        Gate::authorize('cancel', $subscription);

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $customer = $subscription->customer;
        $customer->update(['status' => 'suspended']);
        $this->radiusService->syncCustomerToRadius($customer);

        AuditLog::record(
            action: 'updated',
            description: "Subscription {$subscription->subscription_number} cancelled.",
            model: $subscription
        );

        return redirect()->back()
            ->with('info', "Subscription cancelled.");
    }
}
