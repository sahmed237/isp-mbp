@extends('layouts.app')

@section('title', 'Organizations & Multi-Tenancy')
@section('page_title', 'Tenant Management')

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Organizations & Legal Entities</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage multi-tenant corporate entities, branch subdivisions, and regional configurations.</p>
        </div>
    </div>

    <!-- Organization Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($organizations as $org)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                        @if($org->status === 'active') bg-emerald-500/10 text-emerald-600 border border-emerald-500/20
                        @else bg-rose-500/10 text-rose-600 border border-rose-500/20 @endif
                    ">
                        {{ ucfirst($org->status ?? 'active') }}
                    </span>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ $org->currency ?? 'NGN' }} ({{ $org->currency_symbol ?? '₦' }})</span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white font-extrabold text-lg shadow-md shadow-brand-500/20 flex-shrink-0">
                        {{ strtoupper(substr($org->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-black text-slate-900 dark:text-slate-100 truncate">{{ $org->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $org->city ?? 'Abuja' }}, {{ $org->state ?? 'Nigeria' }}</p>
                    </div>
                </div>

                <!-- Stats Matrix -->
                <div class="grid grid-cols-3 gap-2 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl text-center">
                    <div>
                        <div class="text-base font-black text-slate-900 dark:text-slate-100">{{ $org->branches_count }}</div>
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Branches</div>
                    </div>
                    <div>
                        <div class="text-base font-black text-indigo-600 dark:text-indigo-400">{{ $org->customers_count }}</div>
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Subscribers</div>
                    </div>
                    <div>
                        <div class="text-base font-black text-brand-600 dark:text-brand-400">{{ $org->users_count }}</div>
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Staff</div>
                    </div>
                </div>

                <div class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                    @if($org->email)
                    <div class="flex items-center gap-2 truncate">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="truncate">{{ $org->email }}</span>
                    </div>
                    @endif
                    @if($org->phone)
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $org->phone }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('organizations.show', $org) }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:text-brand-500">
                    View Tenant Profile &rarr;
                </a>

                @can('organizations.update')
                <a href="{{ route('organizations.edit', $org) }}" class="p-2 rounded-xl text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit Organization">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                @endcan
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
