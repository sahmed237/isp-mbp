@extends('layouts.app')

@section('title', 'Staff Users Directory')
@section('page_title', 'Staff Users & Access Control')

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Staff & Administrative Users</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage administrator, manager, accountant, network engineer, and support desk staff accounts.</p>
        </div>
        @can('users.create')
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            <span>Create Staff User</span>
        </a>
        @endcan
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('users.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

            <div class="relative lg:col-span-2">
                <svg class="w-4 h-4 absolute left-3 top-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search name, email, phone..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
            </div>

            <div>
                <select name="role" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Operational Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ ($filters['role'] ?? '') === $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 text-white font-semibold text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('users.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-700 text-xs text-center font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="py-3.5 px-4">Staff Member</th>
                        <th class="py-3.5 px-4">Contact Phone</th>
                        <th class="py-3.5 px-4">Assigned Roles</th>
                        <th class="py-3.5 px-4">Organization / Branch</th>
                        <th class="py-3.5 px-4">Last Login</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('users.show', $user) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:text-brand-600 dark:hover:text-brand-400">
                                        {{ $user->name }}
                                    </a>
                                    <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            {{ $user->phone ?? 'None' }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold
                                        @if($role->name === 'Super Administrator') bg-rose-500/10 text-rose-600 border border-rose-500/20
                                        @elseif($role->name === 'Organization Administrator') bg-purple-500/10 text-purple-600 border border-purple-500/20
                                        @elseif($role->name === 'Operations Manager') bg-indigo-500/10 text-indigo-600 border border-indigo-500/20
                                        @elseif($role->name === 'Billing Manager') bg-emerald-500/10 text-emerald-600 border border-emerald-500/20
                                        @elseif($role->name === 'Network Administrator') bg-cyan-500/10 text-cyan-600 border border-cyan-500/20
                                        @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 @endif
                                    ">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 italic">No roles</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ $user->organization?->name ?? 'System Master' }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                {{ $user->branch?->name ?? 'All Operational Branches' }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                            @if($user->last_login_at)
                                <div>{{ $user->last_login_at->diffForHumans() }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $user->last_login_ip }}</div>
                            @else
                                <span class="italic text-slate-400">Never logged in</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize
                                @if($user->status === 'active') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400
                                @elseif($user->status === 'suspended') bg-amber-500/10 text-amber-600 dark:text-amber-400
                                @else bg-slate-100 text-slate-500 @endif
                            ">
                                {{ $user->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @can('users.view')
                                <a href="{{ route('users.show', $user) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800" title="View Profile">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @endcan

                                @can('users.update')
                                <a href="{{ route('users.edit', $user) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit Staff">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan

                                @can('users.delete')
                                @if(Auth::id() !== $user->id)
                                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete staff user {{ $user->name }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Delete User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            No staff members match the selected criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
