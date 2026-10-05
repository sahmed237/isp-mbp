<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Branch;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $filters = $request->only(['search', 'status', 'role', 'branch_id']);
        $users = $this->userService->getPaginatedUsers($filters);
        $roles = Role::all();
        $branches = Branch::where('status', 'active')->get();

        return view('admin.users.index', compact('users', 'roles', 'branches', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        $roles = Role::all();
        $branches = Branch::where('status', 'active')->get();

        return view('admin.users.create', compact('roles', 'branches'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $data = $request->validated();
        if (Auth::user()->organization_id) {
            $data['organization_id'] = Auth::user()->organization_id;
        }

        $user = $this->userService->createUser($data);

        return redirect()->route('users.index')
            ->with('success', "Staff user '{$user->name}' created successfully.");
    }

    public function show(User $user): View
    {
        Gate::authorize('view', $user);

        $user->load(['roles', 'organization', 'branch', 'auditLogs' => fn($q) => $q->latest('created_at')->limit(10)]);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        $roles = Role::all();
        $branches = Branch::where('status', 'active')->get();

        return view('admin.users.edit', compact('user', 'roles', 'branches'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $this->userService->updateUser($user, $request->validated(), Auth::user());

        return redirect()->route('users.index')
            ->with('success', "Staff user '{$user->name}' updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $this->userService->deleteUser($user, Auth::user());

        return redirect()->route('users.index')
            ->with('success', "Staff user '{$user->name}' deleted successfully.");
    }
}
