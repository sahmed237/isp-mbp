<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Package;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Customer::class);

        $filters = $request->only(['search', 'status', 'connection_type', 'branch_id', 'package_id']);
        $customers = $this->customerService->getPaginatedCustomers($filters);
        $branches = Branch::where('status', 'active')->get();
        $packages = Package::where('status', 'active')->get();

        return view('admin.customers.index', compact('customers', 'branches', 'packages', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('create', Customer::class);

        $branches = Branch::where('status', 'active')->get();
        $packages = Package::where('status', 'active')->where('connection_type', '!=', 'hotspot')->get();

        return view('admin.customers.create', compact('branches', 'packages'));
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        Gate::authorize('create', Customer::class);

        $customer = $this->customerService->createCustomer($request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', "Customer {$customer->full_name} created successfully.");
    }

    public function show(Customer $customer, Request $request): View
    {
        Gate::authorize('view', $customer);

        $customer->load(['package', 'branch', 'organization', 'subscriptions.package', 'invoices.items', 'payments.invoice']);
        $activeTab = $request->query('tab', 'overview');

        return view('admin.customers.show', compact('customer', 'activeTab'));
    }

    public function edit(Customer $customer): View
    {
        Gate::authorize('update', $customer);

        $branches = Branch::where('status', 'active')->get();
        $packages = Package::where('status', 'active')->where('connection_type', '!=', 'hotspot')->get();

        return view('admin.customers.edit', compact('customer', 'branches', 'packages'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        Gate::authorize('update', $customer);

        $this->customerService->updateCustomer($customer, $request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', "Customer {$customer->full_name} updated successfully.");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);

        $this->customerService->deleteCustomer($customer);

        return redirect()->route('customers.index')
            ->with('success', "Customer {$customer->full_name} deleted successfully.");
    }
}
