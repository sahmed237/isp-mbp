<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PackageRequest;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        protected PackageService $packageService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Package::class);

        $filters = $request->only(['search', 'status', 'connection_type']);
        $packages = $this->packageService->getPaginatedPackages($filters);

        return view('admin.packages.index', compact('packages', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('create', Package::class);

        return view('admin.packages.create');
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        Gate::authorize('create', Package::class);

        $package = $this->packageService->createPackage($request->validated());

        return redirect()->route('packages.index')
            ->with('success', "Package '{$package->name}' created successfully.");
    }

    public function show(Package $package): View
    {
        Gate::authorize('view', $package);

        $package->loadCount('customers');

        return view('admin.packages.show', compact('package'));
    }

    public function edit(Package $package): View
    {
        Gate::authorize('update', $package);

        return view('admin.packages.edit', compact('package'));
    }

    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        Gate::authorize('update', $package);

        $this->packageService->updatePackage($package, $request->validated());

        return redirect()->route('packages.index')
            ->with('success', "Package '{$package->name}' updated successfully.");
    }

    public function destroy(Package $package): RedirectResponse
    {
        Gate::authorize('delete', $package);

        $this->packageService->deletePackage($package);

        return redirect()->route('packages.index')
            ->with('success', "Package '{$package->name}' deleted successfully.");
    }
}
