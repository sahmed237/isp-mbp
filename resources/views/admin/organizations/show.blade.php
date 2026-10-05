@extends('layouts.app')

@section('title', "Organization: {$organization->name}")
@section('page_title', "Tenant Profile — {$organization->name}")

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('organizations.index') }}" class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">{{ $organization->name }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                    @if($organization->status === 'active') bg-emerald-500/10 text-emerald-600 border border-emerald-500/20
                    @else bg-rose-500/10 text-rose-600 border border-rose-500/20 @endif
                ">
                    {{ ucfirst($organization->status ?? 'active') }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 pl-11">Company registration details, operational branches, and staff users.</p>
        </div>

        <div class="flex items-center gap-2">
            @can('organizations.update')
            <a href="{{ route('organizations.edit', $organization) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Settings</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Organization Meta Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="md:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Tenant Profile & Specifications</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-semibold block mb-1">Legal Entity / Organization</span>
                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $organization->name }}</span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-semibold block mb-1">Organization Code / Slug</span>
                    <span class="font-mono font-bold text-brand-600 dark:text-brand-400">{{ $organization->slug ?? 'N/A' }}</span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-semibold block mb-1">Primary Email</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $organization->email ?? 'Not set' }}</span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-semibold block mb-1">Phone Contact</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $organization->phone ?? 'Not set' }}</span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 sm:col-span-2">
                    <span class="text-slate-400 font-semibold block mb-1">Registered Physical Address</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $organization->address ?? 'Not specified' }}</span>
                    @if($organization->city || $organization->state)
                        <span class="text-slate-500 block mt-0.5">{{ $organization->city }}, {{ $organization->state }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Regional & Financial Settings -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Localization & Currency</h3>

            <div class="space-y-3 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                    <span class="text-slate-400 font-medium">Billing Currency</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-slate-100">{{ $organization->currency ?? 'NGN' }} ({{ $organization->currency_symbol ?? '₦' }})</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                    <span class="text-slate-400 font-medium">System Timezone</span>
                    <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $organization->timezone ?? 'Africa/Lagos' }}</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                    <span class="text-slate-400 font-medium">Established Date</span>
                    <span class="text-slate-700 dark:text-slate-300">{{ $organization->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Branches Section -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">Operational Branches</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Regional service areas and operational offices under this organization.</p>
            </div>
            @can('branches.create')
            <a href="{{ route('branches.create') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300">
                + Add Branch
            </a>
            @endcan
        </div>

        @if($organization->branches->isEmpty())
            <div class="p-6 text-center text-xs text-slate-400">No branches registered yet for this organization.</div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-2">
                @foreach($organization->branches as $branch)
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $branch->name }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase
                                @if($branch->status === 'active') bg-emerald-500/10 text-emerald-600 @else bg-slate-200 text-slate-600 @endif">
                                {{ $branch->status }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $branch->city ?? 'Central' }} • Code: {{ $branch->code ?? 'BR' }}</div>
                    </div>
                    @can('branches.update')
                    <a href="{{ route('branches.edit', $branch) }}" class="p-2 text-slate-400 hover:text-brand-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    @endcan
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Staff Members Section -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">Tenant Staff Members</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Personnel authorized within this organization boundary.</p>
            </div>
            @can('users.create')
            <a href="{{ route('users.create') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300">
                + Add Staff User
            </a>
            @endcan
        </div>

        @if($organization->users->isEmpty())
            <div class="p-6 text-center text-xs text-slate-400">No staff members enrolled yet.</div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-2">
                @foreach($organization->users as $staff)
                <a href="{{ route('users.show', $staff) }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-extrabold flex items-center justify-center flex-shrink-0">
                        {{ strtoupper(substr($staff->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-900 dark:text-slate-100 group-hover:text-brand-600 truncate">{{ $staff->name }}</div>
                        <div class="text-[11px] text-slate-400 truncate">{{ $staff->email }}</div>
                        <span class="inline-block mt-1 text-[9px] uppercase px-1.5 py-0.5 rounded bg-brand-500/10 text-brand-600 font-bold">
                            {{ $staff->roles->first()?->name ?? 'Staff' }}
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
