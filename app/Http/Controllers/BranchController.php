<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Branch::class);

        $branches = Branch::with('organization')->withCount(['users', 'customers'])->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create(): View
    {
        Gate::authorize('create', Branch::class);

        return view('admin.branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Branch::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $branch = Branch::create($validated);

        AuditLog::record(
            action: 'created',
            description: "Branch '{$branch->name}' created.",
            model: $branch
        );

        return redirect()->route('branches.index')
            ->with('success', "Branch '{$branch->name}' created successfully.");
    }

    public function edit(Branch $branch): View
    {
        Gate::authorize('update', $branch);

        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        Gate::authorize('update', $branch);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $branch->update($validated);

        AuditLog::record(
            action: 'updated',
            description: "Branch '{$branch->name}' updated.",
            model: $branch
        );

        return redirect()->route('branches.index')
            ->with('success', "Branch '{$branch->name}' updated successfully.");
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        Gate::authorize('delete', $branch);

        AuditLog::record(
            action: 'deleted',
            description: "Branch '{$branch->name}' deleted.",
            model: $branch
        );

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', "Branch '{$branch->name}' deleted successfully.");
    }
}
