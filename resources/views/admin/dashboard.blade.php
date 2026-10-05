@extends('layouts.app')

@section('title', 'Operations Dashboard')
@section('page_title', 'Network & Operations Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Overview & Welcome Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-900 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white border border-brand-800/40 shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 border border-brand-400/30 text-xs font-semibold text-brand-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Broadband Infrastructure Operational</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome, {{ Auth::user()->name }}</h2>
                <p class="text-slate-300 text-sm max-w-2xl">
                    Monitoring {{ Auth::user()->organization?->name ?? 'Multi-Tenant System' }} broadband subscribers, PPPoE/Hotspot sessions, and network infrastructure.
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                @can('customers.create')
                <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-brand-950 font-bold text-xs hover:bg-slate-100 shadow-md transition-all">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>New Customer</span>
                </a>
                @endcan

                @can('packages.create')
                <a href="{{ route('packages.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600/80 hover:bg-brand-600 text-white font-bold text-xs border border-brand-400/30 shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>New Package</span>
                </a>
                @endcan

                @can('network.devices.create')
                <a href="{{ route('network-devices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-slate-700 shadow-md transition-all">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <span>Register Device</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- Decorative blur element -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- 4 Core Metric Panels (Customers, Subscriptions, Revenue, Network) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- 1. Customers Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Customers</div>
                        <div class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $customer_stats['total'] }}</div>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    +{{ $customer_stats['new'] }} new
                </span>
            </div>
            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-center text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Active</span>
                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400">{{ $customer_stats['active'] }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Suspended</span>
                    <span class="font-extrabold text-amber-600 dark:text-amber-400">{{ $customer_stats['suspended'] }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Expired</span>
                    <span class="font-extrabold text-rose-600 dark:text-rose-400">{{ $customer_stats['expired'] }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Subscriptions Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Subscriptions</div>
                        <div class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $subscription_stats['active'] }}</div>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    {{ $packages_count }} Plans
                </span>
            </div>
            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-center text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Active</span>
                    <span class="font-extrabold text-slate-700 dark:text-slate-300">{{ $subscription_stats['active'] }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Expiring</span>
                    <span class="font-extrabold text-amber-500">{{ $subscription_stats['expiring_soon'] }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Suspended</span>
                    <span class="font-extrabold text-slate-400">{{ $subscription_stats['suspended'] }}</span>
                </div>
            </div>
        </div>

        <!-- 3. Revenue Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">This Month</div>
                        <div class="text-2xl font-black text-slate-800 dark:text-slate-100">
                            {{ $revenue_stats['currency_symbol'] }}{{ number_format($revenue_stats['this_month'], 2) }}
                        </div>
                    </div>
                </div>
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">
                    Phase 3 Prep
                </span>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Today's Inflow</span>
                    <span class="font-extrabold text-slate-700 dark:text-slate-300">{{ $revenue_stats['currency_symbol'] }}{{ number_format($revenue_stats['today'], 2) }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Outstanding</span>
                    <span class="font-extrabold text-rose-500">{{ $revenue_stats['currency_symbol'] }}{{ number_format($revenue_stats['outstanding'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- 4. Network Health Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Online Devices</div>
                        <div class="text-2xl font-black text-slate-800 dark:text-slate-100">
                            {{ $network_stats['online_devices'] }} <span class="text-xs font-normal text-slate-400">/ {{ $network_stats['total_devices'] }}</span>
                        </div>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">
                    {{ $network_stats['active_sessions'] }} Sessions
                </span>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs flex items-center justify-between">
                <span class="text-slate-400 truncate">{{ $network_stats['network_alerts'] }}</span>
                @if($network_stats['warning_devices'] > 0)
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                @endif
            </div>
        </div>

    </div>

    <!-- Main Grid: Recent Activity Stream & Network Infrastructure Snapshot -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Recent Administrative Audit Activity (2 Columns) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Immutable Audit Trail Stream</h3>
                    <p class="text-xs text-slate-400">Real-time immutable security and operational event logs</p>
                </div>
                @can('audit_logs.view')
                <a href="{{ route('audit-logs.index') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                    View All Logs &rarr;
                </a>
                @endcan
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recent_activities as $log)
                <div class="py-3.5 flex items-start justify-between gap-4 text-xs">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5
                            @if($log->action === 'created') bg-emerald-500/10 text-emerald-600
                            @elseif($log->action === 'updated') bg-blue-500/10 text-blue-600
                            @elseif($log->action === 'deleted') bg-rose-500/10 text-rose-600
                            @elseif($log->action === 'login') bg-indigo-500/10 text-indigo-600
                            @else bg-slate-500/10 text-slate-600 @endif
                        ">
                            <span class="font-mono text-[10px] font-bold uppercase">{{ substr($log->action, 0, 3) }}</span>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $log->description }}</div>
                            <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                                <span>By: <strong class="text-slate-600 dark:text-slate-300">{{ $log->user_name }}</strong></span>
                                <span>&bull;</span>
                                <span class="font-mono">{{ $log->ip_address }}</span>
                            </div>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 whitespace-nowrap">
                        {{ $log->created_at->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    No recent audit events recorded.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right Side: Infrastructure Quick Status -->
        <div class="space-y-6">

            <!-- Operational Branches Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Operational Branches</h3>
                    <a href="{{ route('branches.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Manage</a>
                </div>

                <div class="space-y-3">
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">Abuja Central Branch (HQ)</div>
                            <div class="text-[11px] text-slate-400">Core POP & Datacenter</div>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">Yola Regional Branch</div>
                            <div class="text-[11px] text-slate-400">Jimeta Tower & PtP Backhaul</div>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">Kano Commercial Branch</div>
                            <div class="text-[11px] text-slate-400">Bompai Industrial Sector</div>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-amber-500" title="Weather Fade Warning"></span>
                    </div>
                </div>
            </div>

            <!-- Future Roadmap Status -->
            <div class="rounded-3xl bg-slate-900 border border-slate-800 p-6 text-slate-300 space-y-4 shadow-sm">
                <div class="flex items-center gap-2 text-brand-400 font-bold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Architecture Roadmap</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Phase 1 (Security, Access Control, Multi-Tenancy & UI) is active. Future modules (RADIUS, MikroTik RouterOS API, Automated Invoicing) are prepared in the database schema.
                </p>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Engineered for 10k+ Subscribers</span>
                    <span class="text-emerald-400 font-semibold">&check; Multi-Tenant Ready</span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
