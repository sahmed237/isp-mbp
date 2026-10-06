@extends('layouts.app')

@section('title', $reseller->business_name . ' — Reseller Details')
@section('page_title', 'Reseller Profile & KYC Review')

@section('content')
<div class="space-y-6" x-data="{
    walletModal: false,
    rejectModal: false,
    walletAction: 'credit',
    walletAmount: '',
    walletNotes: ''
}">

    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center justify-center text-xl shrink-0">
                {{ strtoupper(substr($reseller->business_name, 0, 1)) }}
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $reseller->business_name }}</h1>
                    <span class="font-mono text-xs text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-lg">{{ $reseller->reseller_code }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span>Contact: <strong class="text-slate-800 dark:text-slate-200">{{ $reseller->contact_person }}</strong></span>
                    <span>&bull;</span>
                    <span class="capitalize">{{ str_replace('_', ' ', $reseller->business_type) }}</span>
                    <span>&bull;</span>
                    @if($reseller->isPending())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-500/20 text-amber-500 border border-amber-500/30">
                            Pending Review
                        </span>
                    @elseif($reseller->isActive())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-500 border border-emerald-500/30">
                            Active
                        </span>
                    @elseif($reseller->isSuspended())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-500/20 text-rose-500 border border-rose-500/30">
                            Suspended
                        </span>
                    @elseif($reseller->isRejected())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-500/20 text-slate-400 border border-slate-500/30">
                            Rejected
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Header Actions -->
        <div class="flex flex-wrap items-center gap-2">
            @can('resellers.approve')
                @if($reseller->isPending())
                <form action="{{ route('resellers.approve', $reseller) }}" method="POST" onsubmit="return confirm('Approve this reseller account? The agent will be authorized to log in.');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Approve Application</span>
                    </button>
                </form>

                <button @click="rejectModal = true" type="button" class="px-4 py-2 rounded-xl border border-rose-300 dark:border-rose-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 font-bold text-xs transition-all">
                    Reject Application
                </button>
                @endif
            @endcan

            @can('resellers.update')
            <button @click="walletModal = true" type="button" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Adjust Wallet (₦)</span>
            </button>

            @if(!$reseller->isPending() && !$reseller->isRejected())
            <form action="{{ route('resellers.toggle-status', $reseller) }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold {{ $reseller->isActive() ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30' : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30' }}">
                    {{ $reseller->isActive() ? 'Suspend' : 'Reactivate' }}
                </button>
            </form>
            @endif
            @endcan

            <a href="{{ route('resellers.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-500 hover:text-slate-700">
                &larr; Back
            </a>
        </div>
    </div>

    <!-- Rejection Reason Banner (if rejected) -->
    @if($reseller->isRejected())
    <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-2xl text-xs text-rose-700 dark:text-rose-300">
        <strong class="font-bold block">Application Rejected:</strong>
        <p class="mt-0.5">{{ $reseller->rejection_reason ?? 'Application did not meet KYC or compliance standards.' }}</p>
    </div>
    @endif

    <!-- 4 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Wallet Balance -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-1">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Wallet Balance</span>
            <div class="text-2xl font-extrabold font-mono text-emerald-600 dark:text-emerald-400">
                ₦{{ number_format((float) $reseller->balance, 2) }}
            </div>
            <span class="text-[11px] text-slate-500">Prepaid purchasing credit</span>
        </div>

        <!-- Total Generated Cards -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-1">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Generated</span>
            <div class="text-2xl font-extrabold font-mono text-slate-900 dark:text-white">
                {{ number_format($totalVouchers) }}
            </div>
            <span class="text-[11px] text-slate-500">Voucher cards created</span>
        </div>

        <!-- Unsold / Active Stock -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-1">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Inventory</span>
            <div class="text-2xl font-extrabold font-mono text-cyan-600 dark:text-cyan-400">
                {{ number_format($activeVouchers) }}
            </div>
            <span class="text-[11px] text-slate-500">Unused cards in retail stock</span>
        </div>

        <!-- Total Gross Volume -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-1">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gross Sales Volume</span>
            <div class="text-2xl font-extrabold font-mono text-indigo-600 dark:text-indigo-400">
                ₦{{ number_format($totalSales, 2) }}
            </div>
            <span class="text-[11px] text-slate-500">Lifetime purchases & topups</span>
        </div>
    </div>

    <!-- 2 Column Details: Profile & KYC -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Store & Contact Details -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Merchant Contact & Location</h3>

            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">Primary Phone:</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $reseller->phone }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">Alternate Phone:</span>
                    <span class="font-mono text-slate-900 dark:text-white">{{ $reseller->alternate_phone ?? 'None' }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">Email Address:</span>
                    <span class="font-medium text-slate-900 dark:text-white">{{ $reseller->email }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">Shop Physical Address:</span>
                    <span class="font-medium text-slate-900 dark:text-white text-right max-w-[260px]">{{ $reseller->shop_address }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">City & State:</span>
                    <span class="font-medium text-slate-900 dark:text-white">{{ $reseller->city ?? 'N/A' }}, {{ $reseller->state ?? 'N/A' }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">Assigned Branch:</span>
                    <span class="font-medium text-slate-900 dark:text-white">{{ $reseller->branch?->name ?? 'Head Office / Universal' }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">Applied Date:</span>
                    <span class="font-mono text-slate-500">{{ $reseller->created_at->format('d M Y, h:i A') }}</span>
                </div>
                @if($reseller->approved_at)
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">Approved By:</span>
                    <span class="font-medium text-slate-900 dark:text-white">{{ $reseller->approvedBy?->name ?? 'System Administrator' }} on {{ $reseller->approved_at->format('d M Y') }}</span>
                </div>
                @endif
            </div>

            @if($reseller->notes)
            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Notes:</span>
                <p class="text-slate-700 dark:text-slate-300">{{ $reseller->notes }}</p>
            </div>
            @endif
        </div>

        <!-- KYC & Verification Documents -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">KYC Identity Documents</h3>
                @if($reseller->id_card_path)
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    File Attached
                </span>
                @else
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-400">
                    No File Attached
                </span>
                @endif
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">ID Document Type:</span>
                    <span class="font-bold uppercase text-slate-900 dark:text-white">{{ str_replace('_', ' ', $reseller->id_type ?? 'None Provided') }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500">Identification / CAC No:</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $reseller->id_number ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- KYC Document Preview / Viewer -->
            @if($reseller->id_card_path)
            <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700 dark:text-slate-300">Attached Verification Document</span>
                    <a href="{{ asset('storage/' . $reseller->id_card_path) }}" target="_blank" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline flex items-center gap-1">
                        <span>Open in New Tab</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                @php
                    $ext = strtolower(pathinfo($reseller->id_card_path, PATHINFO_EXTENSION));
                @endphp

                @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 max-h-64 flex items-center justify-center bg-slate-900">
                    <img src="{{ asset('storage/' . $reseller->id_card_path) }}" alt="KYC Document" class="max-h-64 object-contain">
                </div>
                @else
                <div class="p-6 bg-slate-100 dark:bg-slate-800 rounded-xl text-center space-y-2">
                    <svg class="w-10 h-10 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <p class="text-xs font-mono text-slate-600 dark:text-slate-300">{{ basename($reseller->id_card_path) }}</p>
                    <a href="{{ asset('storage/' . $reseller->id_card_path) }}" target="_blank" class="inline-block px-4 py-1.5 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-bold">
                        Download / View PDF Document
                    </a>
                </div>
                @endif
            </div>
            @else
            <div class="p-8 text-center text-slate-400 text-xs bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                No KYC file uploaded by this merchant.
            </div>
            @endif
        </div>

    </div>

    <!-- Recent Voucher Batches -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Voucher Batches Generated by Reseller</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Batch ID</th>
                        <th class="py-3 px-4">Plan / Package</th>
                        <th class="py-3 px-4 text-center">Total Cards</th>
                        <th class="py-3 px-4 text-center">Unsold / Active</th>
                        <th class="py-3 px-4 text-center">Used</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($recentBatches as $batch)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                            {{ $batch->batch_id }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $batch->package?->name ?? 'Plan' }}</span>
                            <span class="block text-[11px] text-slate-400 font-mono">₦{{ number_format((float) ($batch->package?->price ?? 0), 2) }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $batch->total_count }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                                {{ $batch->active_count }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400">
                                {{ $batch->used_count }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                            {{ \Carbon\Carbon::parse($batch->created_at)->format('d M Y, h:i A') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('vouchers.print_batch', $batch->batch_id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Print Batch</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-400 text-xs">
                            No batches generated yet by this reseller.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Wallet Adjustment Modal -->
    <div x-show="walletModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="walletModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Adjust Agent Wallet</h3>
                <button @click="walletModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('resellers.wallet', $reseller) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Adjustment Type *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-800 rounded-xl cursor-pointer">
                            <input type="radio" name="action" value="credit" x-model="walletAction" class="text-emerald-600">
                            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-200">+ Credit Wallet</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-300 dark:border-rose-800 rounded-xl cursor-pointer">
                            <input type="radio" name="action" value="debit" x-model="walletAction" class="text-rose-600">
                            <span class="text-xs font-bold text-rose-800 dark:text-rose-200">- Debit Wallet</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Amount (₦) *</label>
                    <input type="number" step="0.01" min="1" name="amount" x-model="walletAmount" required placeholder="e.g. 10000" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[10px] text-slate-400 mt-1">Current balance: ₦{{ number_format((float) $reseller->balance, 2) }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Reason / Notes *</label>
                    <input type="text" name="notes" x-model="walletNotes" required placeholder="e.g. Cash collected at office, Bank transfer reconciliation..." class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="walletModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md">
                        Apply Wallet Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Application Modal -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="rejectModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-extrabold text-rose-600">Reject Reseller Application</h3>
                <button @click="rejectModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('resellers.reject', $reseller) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Reason for Rejection *</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Specify why the KYC verification failed (e.g. blurred ID document, unverified business premises)..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-md">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
