@extends('layouts.app')

@section('title', "Role: {$role->name}")
@section('page_title', "Role Details — {$role->name}")

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('roles.index') }}" class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">{{ $role->name }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                    @if($role->name === 'Super Administrator') bg-rose-500/10 text-rose-600 border border-rose-500/20
                    @elseif($role->name === 'Organization Administrator') bg-purple-500/10 text-purple-600 border border-purple-500/20
                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 @endif
                ">
                    {{ in_array($role->name, ['Super Administrator', 'Organization Administrator']) ? 'Core Standard' : 'Custom Role' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 pl-11">Assigned permissions and active staff members holding this role.</p>
        </div>

        <div class="flex items-center gap-2">
            @can('roles.update')
            <a href="{{ route('roles.edit', $role) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Role</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Assigned Staff</span>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100 mt-1">{{ $role->users->count() }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Granted Permissions</span>
                <div class="text-2xl font-black text-brand-600 dark:text-brand-400 mt-1">{{ $role->permissions->count() }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
        </div>
    </div>

    <!-- Permissions Matrix -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">Assigned Capabilities & Permissions</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Granular operation rights granted to this role.</p>
        </div>

        @if($role->permissions->isEmpty())
            <div class="p-6 text-center text-xs text-slate-400">No permissions explicitly assigned to this role.</div>
        @else
            @php
                $grouped = $role->permissions->groupBy(function($item) {
                    return explode('.', $item->name)[0] ?? 'general';
                });
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
                @foreach($grouped as $domain => $permissions)
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/60 space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">{{ ucfirst(str_replace('_', ' ', $domain)) }}</span>
                        <span class="text-[10px] font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/50 px-2 py-0.5 rounded">{{ $permissions->count() }}</span>
                    </div>
                    <div class="space-y-1 pt-1">
                        @foreach($permissions as $perm)
                        <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                            <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span class="font-mono text-[11px]">{{ $perm->name }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Assigned Staff Users -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">Assigned Staff Members</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Users currently operating with this role privilege.</p>
        </div>

        @if($role->users->isEmpty())
            <div class="p-6 text-center text-xs text-slate-400">No staff members currently assigned this role.</div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($role->users as $user)
                <a href="{{ route('users.show', $user) }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-extrabold flex items-center justify-center flex-shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-900 dark:text-slate-100 group-hover:text-brand-600 truncate">{{ $user->name }}</div>
                        <div class="text-[11px] text-slate-400 truncate">{{ $user->email }}</div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
