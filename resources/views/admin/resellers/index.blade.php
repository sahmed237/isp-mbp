@extends('layouts.app')

@section('title', 'Voucher Agents & Resellers')
@section('page_title', 'Agents & Resellers')

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Voucher Agents & Resellers</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage Wi-Fi hotspot card merchants, review KYC applications, and audit prepaid balances.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('resellers.create')
            <a href="{{ route('resellers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Onboard Reseller</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Status Tabs & Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3 shadow-sm">
        <!-- Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
            <a href="{{ route('resellers.index', ['status' => 'all', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $status === 'all' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                All ({{ $counts['all'] }})
            </a>
            <a href="{{ route('resellers.index', ['status' => 'pending', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl flex items-center gap-1.5 transition-all {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 font-extrabold shadow-sm' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' }}">
                <span>Pending KYC</span>
                @if($counts['pending'] > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-950 text-amber-200 animate-pulse">{{ $counts['pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('resellers.index', ['status' => 'active', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $status === 'active' ? 'bg-emerald-600 text-white font-extrabold' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                Active ({{ $counts['active'] }})
            </a>
            <a href="{{ route('resellers.index', ['status' => 'suspended', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $status === 'suspended' ? 'bg-rose-600 text-white font-extrabold' : 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                Suspended ({{ $counts['suspended'] }})
            </a>
            <a href="{{ route('resellers.index', ['status' => 'rejected', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl transition-all {{ $status === 'rejected' ? 'bg-slate-600 text-white font-extrabold' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Rejected ({{ $counts['rejected'] }})
            </a>
        </div>

        <!-- Search input -->
        <form action="{{ route('resellers.index') }}" method="GET" class="relative min-w-[240px]">
            <input type="hidden" name="status" value="{{ $status }}">
            <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search business, code, phone..."
                class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
        </form>
    </div>

    <!-- Resellers Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Agent / Business</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Contact Details</th>
                        <th class="py-3 px-4">Store Location</th>
                        <th class="py-3 px-4 text-right">Wallet Balance</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($resellers as $reseller)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <!-- Business & Code -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold flex items-center justify-center text-xs shrink-0">
                                    {{ strtoupper(substr($reseller->business_name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('resellers.show', $reseller) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                        {{ $reseller->business_name }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="font-mono text-[10px] text-slate-400">{{ $reseller->reseller_code }}</span>
                                        <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                        <span class="text-[11px] text-slate-500">{{ $reseller->contact_person }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Business Category -->
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 capitalize">
                            {{ str_replace('_', ' ', $reseller->business_type) }}
                        </td>

                        <!-- Contact -->
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-slate-800 dark:text-slate-200 block">{{ $reseller->phone }}</span>
                            <span class="text-[11px] text-slate-400">{{ $reseller->email }}</span>
                        </td>

                        <!-- Store Location -->
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 max-w-[200px] truncate" title="{{ $reseller->shop_address }}">
                            {{ $reseller->city ? ($reseller->city . ', ' . $reseller->state) : $reseller->shop_address }}
                        </td>

                        <!-- Wallet Balance -->
                        <td class="py-3.5 px-4 text-right">
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs">
                                ₦{{ number_format((float)$reseller->balance, 2) }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3.5 px-4 text-center">
                            @if($reseller->isPending())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-500/20 text-amber-500 border border-amber-500/30">
                                    Pending Review
                                </span>
                            @elseif($reseller->isActive())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-500 border border-emerald-500/30">
                                    Active
                                </span>
                            @elseif($reseller->isSuspended())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-rose-500/20 text-rose-500 border border-rose-500/30">
                                    Suspended
                                </span>
                            @elseif($reseller->isRejected())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-500/20 text-slate-400 border border-slate-500/30">
                                    Rejected
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('resellers.show', $reseller) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-300 hover:text-emerald-600 font-bold text-xs transition-colors">
                                <span>Details & KYC</span>
                                <span>&rarr;</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            No resellers found matching current status filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $resellers->links() }}
        </div>
    </div>

</div>
@endsection
