<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Organization::class);

        $organizations = Organization::withCount(['branches', 'users', 'customers'])->get();

        return view('admin.organizations.index', compact('organizations'));
    }

    public function show(Organization $organization): View
    {
        Gate::authorize('view', $organization);

        $organization->load(['branches', 'users.roles']);

        return view('admin.organizations.show', compact('organization'));
    }

    public function edit(Organization $organization): View
    {
        Gate::authorize('update', $organization);

        return view('admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        Gate::authorize('update', $organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:50'],
        ]);

        $organization->update($validated);

        AuditLog::record(
            action: 'updated',
            description: "Organization profile '{$organization->name}' updated.",
            model: $organization
        );

        return redirect()->route('organizations.show', $organization)
            ->with('success', 'Organization settings updated successfully.');
    }
}
