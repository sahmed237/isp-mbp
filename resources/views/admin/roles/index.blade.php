@extends('layouts.app')

@section('title', 'Roles & RBAC Permissions')
@section('page_title', 'Access Control & Roles')

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Security Roles & Permissions</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Define operational roles, assign granular permissions, and restrict administrative abilities.</p>
        </div>
        @can('roles.create')
        <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Create New Role</span>
        </a>
        @endcan
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                        @if($role->name === 'Super Administrator') bg-rose-500/10 text-rose-600 border border-rose-500/20
                        @elseif($role->name === 'Organization Administrator') bg-purple-500/10 text-purple-600 border border-purple-500/20
                        @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 @endif
                    ">
                        {{ in_array($role->name, ['Super Administrator', 'Organization Administrator']) ? 'Core Standard' : 'Custom Role' }}
                    </span>
                    <span class="text-xs font-semibold text-slate-400">{{ $role->users_count }} Staff Members</span>
                </div>

                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">{{ $role->name }}</h3>

                <p class="text-xs text-slate-500 dark:text-slate-400 min-h-[32px]">
                    @if($role->name === 'Super Administrator') Full unrestricted access across all tenants, network devices, and security logs.
                    @elseif($role->name === 'Organization Administrator') Full administrative management within the organization.
                    @elseif($role->name === 'Operations Manager') Manages subscribers, packages, and operational network monitoring.
                    @elseif($role->name === 'Billing Manager') Controls customer accounts, invoicing, payments, and financial reconciliation.
                    @elseif($role->name === 'Network Administrator') Administers MikroTik routers, core switches, OLTs, and RADIUS users.
                    @elseif($role->name === 'Customer Support') Assists subscribers, reviews services, and logs customer support tickets.
                    @elseif($role->name === 'Accountant') Financial reporting, invoice analysis, and payment auditing.
                    @else Permitted view-only access across system entities. @endif
                </p>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Permissions:</span>
                    <span class="font-extrabold text-brand-600 dark:text-brand-400">{{ $role->permissions_count }} Active</span>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('roles.show', $role) }}" class="text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-brand-600">
                    Inspect Permissions &rarr;
                </a>

                <div class="flex items-center gap-1.5">
                    @can('roles.update')
                    <a href="{{ route('roles.edit', $role) }}" class="p-2 rounded-xl text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit Role">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    @endcan

                    @can('roles.delete')
                    @if(!in_array($role->name, ['Super Administrator', 'Organization Administrator']))
                    <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete role {{ $role->name }}?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Delete Role">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
