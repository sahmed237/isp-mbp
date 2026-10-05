@extends('layouts.app')

@section('title', 'Payments')
@section('page_title', 'Payment Transactions')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Customer Payments</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">All cleared cash receipts, bank transfers, POS receipts, and online automated settlements.</p>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('payments.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Search Payments</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Payment #, Ref, Customer..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Payment Method</label>
                <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Channels</option>
                    <option value="cash" {{ ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank_transfer" {{ ($filters['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="card" {{ ($filters['payment_method'] ?? '') === 'card' ? 'selected' : '' }}>POS / Card Terminal</option>
                    <option value="paystack" {{ ($filters['payment_method'] ?? '') === 'paystack' ? 'selected' : '' }}>Paystack Online</option>
                    <option value="moniepoint" {{ ($filters['payment_method'] ?? '') === 'moniepoint' ? 'selected' : '' }}>Moniepoint</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white dark:bg-brand-600 dark:hover:bg-brand-500 font-bold text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('payments.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-700 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Receipt / Payment #</th>
                        <th class="px-5 py-3">Subscriber</th>
                        <th class="px-5 py-3">Invoice #</th>
                        <th class="px-5 py-3">Amount Paid</th>
                        <th class="px-5 py-3">Method</th>
                        <th class="px-5 py-3">Reference</th>
                        <th class="px-5 py-3">Date & Time</th>
                        <th class="px-5 py-3 text-right">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($payments as $pmt)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('payments.show', $pmt) }}" class="font-mono font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                {{ $pmt->payment_number }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('customers.show', $pmt->customer) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:underline">
                                {{ $pmt->customer->full_name }}
                            </a>
                            <span class="block text-[10px] font-mono text-slate-400">{{ $pmt->customer->account_number }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($pmt->invoice)
                            <a href="{{ route('invoices.show', $pmt->invoice) }}" class="font-mono text-slate-700 dark:text-slate-300 hover:underline font-bold">
                                {{ $pmt->invoice->invoice_number }}
                            </a>
                            @else
                            <span class="text-slate-400">Unlinked Direct</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-bold font-mono text-emerald-600 dark:text-emerald-400">
                            ₦{{ number_format((float)$pmt->amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold capitalize {{ $pmt->method_badge_class }}">
                                {{ str_replace('_', ' ', $pmt->payment_method) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-500">
                            {{ $pmt->reference ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500">
                            {{ $pmt->paid_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('payments.print', $pmt) }}" target="_blank" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800" title="Print Receipt">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                            No payment records found matching your query.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
