<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::withCount(['users', 'permissions'])->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        Gate::authorize('create', Role::class);

        $groupedPermissions = $this->roleService->getGroupedPermissions();

        return view('admin.roles.create', compact('groupedPermissions'));
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $role = $this->roleService->createRole(
            $request->input('name'),
            $request->input('permissions', [])
        );

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    public function show(Role $role): View
    {
        Gate::authorize('view', $role);

        $role->load(['users', 'permissions']);

        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        Gate::authorize('update', $role);

        $groupedPermissions = $this->roleService->getGroupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);

        $this->roleService->updateRole(
            $role,
            $request->input('name'),
            $request->input('permissions', [])
        );

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' updated successfully.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        $this->roleService->deleteRole($role);

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' deleted successfully.");
    }
}
