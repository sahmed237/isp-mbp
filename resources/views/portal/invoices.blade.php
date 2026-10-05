@extends('portal.layout')

@section('title', 'My Invoices')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100">My Invoices & Statements</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">View billing history, itemized service statements, and pay online with instant plan reactivation.</p>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Invoice #</th>
                        <th class="px-5 py-3.5">Issue Date</th>
                        <th class="px-5 py-3.5">Due Date</th>
                        <th class="px-5 py-3.5">Amount</th>
                        <th class="px-5 py-3.5">Paid</th>
                        <th class="px-5 py-3.5">Balance Due</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-4 font-mono font-bold text-brand-600 dark:text-brand-400">
                            <a href="{{ route('portal.invoices.show', $inv) }}" class="hover:underline">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-5 py-4 text-slate-500">{{ $inv->issue_date->format('M d, Y') }}</td>
                        <td class="px-5 py-4">
                            <span class="{{ $inv->status === 'unpaid' && $inv->due_date->isPast() ? 'text-rose-500 font-bold' : 'text-slate-500' }}">
                                {{ $inv->due_date->format('M d, Y') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-bold text-slate-900 dark:text-slate-100 font-mono">
                            ₦{{ number_format((float)$inv->total_amount, 2) }}
                        </td>
                        <td class="px-5 py-4 font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                            ₦{{ number_format((float)$inv->paid_amount, 2) }}
                        </td>
                        <td class="px-5 py-4 font-bold text-slate-900 dark:text-slate-100 font-mono">
                            ₦{{ number_format((float)$inv->balance_due, 2) }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $inv->status_badge_class }}">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('portal.invoices.show', $inv) }}" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold">
                                    View
                                </a>

                                @if(!$inv->isPaid())
                                <a href="{{ route('portal.invoices.show', $inv) }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm">
                                    Pay Now
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-slate-400">No invoices on file.</td>
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
