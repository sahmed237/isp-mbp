@extends('portal.layout')

@section('title', 'Payment Receipts')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100">Payment History & Receipts</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">All cleared transactions, subscription renewal settlements, and official payment clearance vouchers.</p>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Payment #</th>
                        <th class="px-5 py-3.5">Date & Time</th>
                        <th class="px-5 py-3.5">Invoice</th>
                        <th class="px-5 py-3.5">Channel / Method</th>
                        <th class="px-5 py-3.5">Transaction Ref</th>
                        <th class="px-5 py-3.5">Amount</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($payments as $pmt)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-4 font-mono font-bold text-slate-900 dark:text-slate-100">
                            {{ $pmt->payment_number }}
                        </td>
                        <td class="px-5 py-4 text-slate-500">{{ $pmt->paid_at->format('M d, Y H:i') }}</td>
                        <td class="px-5 py-4 font-mono text-slate-600 dark:text-slate-400">
                            {{ $pmt->invoice?->invoice_number ?? 'Direct Settlement' }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold capitalize {{ $pmt->method_badge_class }}">
                                {{ str_replace('_', ' ', $pmt->payment_method) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-500">{{ $pmt->reference ?? '—' }}</td>
                        <td class="px-5 py-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            ₦{{ number_format((float)$pmt->amount, 2) }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                &check; Successful
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('payments.print', $pmt) }}" target="_blank" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold">
                                Receipt
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-slate-400">No payment records found.</td>
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
