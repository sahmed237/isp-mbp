@extends('layouts.app')

@section('title', 'Invoices')
@section('page_title', 'Billing Invoices')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Customer Invoices</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage billing statements, service fee invoicing, and automated subscriber account receivables.</p>
        </div>
        @can('invoices.create')
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Generate Invoice</span>
            </a>
        </div>
        @endcan
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Search Invoices</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Invoice #, Customer, Account..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Payment Status</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid / Awaiting Payment</option>
                    <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Paid in Full</option>
                    <option value="partially_paid" {{ ($filters['status'] ?? '') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="overdue" {{ ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white dark:bg-brand-600 dark:hover:bg-brand-500 font-bold text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('invoices.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-700 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Invoice #</th>
                        <th class="px-5 py-3">Subscriber</th>
                        <th class="px-5 py-3">Issue Date</th>
                        <th class="px-5 py-3">Due Date</th>
                        <th class="px-5 py-3">Total Amount</th>
                        <th class="px-5 py-3">Paid Amount</th>
                        <th class="px-5 py-3">Balance Due</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('invoices.show', $inv) }}" class="font-mono font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                {{ $inv->invoice_number }}
                            </a>
                            @if($inv->subscription)
                            <span class="block text-[10px] text-slate-400 font-mono">{{ $inv->subscription->subscription_number }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('customers.show', $inv->customer) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:underline">
                                {{ $inv->customer->full_name }}
                            </a>
                            <span class="block text-[10px] font-mono text-slate-400">{{ $inv->customer->account_number }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500">
                            {{ $inv->issue_date->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="{{ $inv->status === 'unpaid' && $inv->due_date->isPast() ? 'text-rose-500 font-bold' : 'text-slate-500' }}">
                                {{ $inv->due_date->format('M d, Y') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-slate-100">
                            ₦{{ number_format((float)$inv->total_amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-bold text-emerald-600 dark:text-emerald-400">
                            ₦{{ number_format((float)$inv->paid_amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5 font-bold text-slate-800 dark:text-slate-200 font-mono">
                            ₦{{ number_format((float)$inv->balance_due, 2) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $inv->status_badge_class }}">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('invoices.show', $inv) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="View Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('invoices.print', $inv) }}" target="_blank" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800" title="Print Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-8 text-center text-slate-400">
                            No invoices found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
