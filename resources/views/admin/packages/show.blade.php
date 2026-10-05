@extends('layouts.app')

@section('title', "Package: {$package->name}")
@section('page_title', "Package Details — {$package->name}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs uppercase font-bold text-brand-600 dark:text-brand-400">{{ $package->code ?? 'PKG-' . $package->id }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize {{ $package->status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-100 text-slate-600' }}">
                    {{ $package->status }}
                </span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-1">{{ $package->name }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $package->description ?? 'No description specified.' }}</p>
        </div>

        <div class="flex items-center gap-2">
            @can('packages.update')
            <a href="{{ route('packages.edit', $package) }}" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
                Edit Package
            </a>
            @endcan
            <a href="{{ route('packages.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
                &larr; Back
            </a>
        </div>
    </div>

    <!-- Bandwidth & MikroTik Rate-Limit Specifications -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Bandwidth & Speeds</h3>
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="p-4 rounded-2xl bg-brand-500/10 border border-brand-500/20">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Download</span>
                    <span class="text-xl font-black text-brand-600 dark:text-brand-400">{{ $package->formatted_download_speed }}</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">({{ $package->download_speed }} Kbps)</span>
                </div>
                <div class="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Upload</span>
                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $package->formatted_upload_speed }}</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">({{ $package->upload_speed }} Kbps)</span>
                </div>
            </div>

            <!-- MikroTik Rate-Limit Engine String -->
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-slate-200 space-y-2">
                <div class="flex items-center justify-between text-[11px] text-slate-400 font-bold uppercase tracking-wider">
                    <span>RouterOS / RADIUS Rate-Limit</span>
                    <span class="text-brand-400">Mikrotik-Rate-Limit</span>
                </div>
                <div class="font-mono text-sm font-bold text-emerald-400 bg-slate-950 p-2.5 rounded-xl break-all">
                    {{ $package->toMikrotikRateLimitString() }}
                </div>
                <p class="text-[10px] text-slate-400 leading-relaxed">
                    This formatted attribute string will be dispatched to FreeRADIUS <code class="text-brand-300">radreply</code> and MikroTik Simple Queues in Phase 4 and 5.
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Pricing & Commercial Terms</h3>
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400">Recurring Price:</span>
                    <span class="text-base font-black text-slate-800 dark:text-slate-100">₦{{ number_format((float)$package->price, 2) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400">Installation Fee:</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">₦{{ number_format((float)$package->installation_fee, 2) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400">Validity Period:</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $package->validity_period }} Days</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400">Billing Cycle:</span>
                    <span class="font-semibold capitalize text-slate-700 dark:text-slate-300">{{ $package->billing_cycle }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-slate-400">Subscribers on this Plan:</span>
                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400">{{ $package->customers_count }} Active</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
