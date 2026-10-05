@extends('layouts.app')

@section('title', "Payment: {$payment->payment_number}")
@section('page_title', "Payment {$payment->payment_number}")

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('payments.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
            &larr; Back to Payments
        </a>
        <a href="{{ route('payments.print', $payment) }}" target="_blank" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md">
            Print Official Receipt
        </a>
    </div>

    <!-- Official Receipt Slip Box -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-sm space-y-6">
        <div class="text-center pb-6 border-b border-slate-100 dark:border-slate-800">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="text-xs uppercase font-extrabold tracking-widest text-slate-400 block">Official Payment Receipt</span>
            <span class="text-2xl font-black font-mono text-slate-900 dark:text-slate-100 block">{{ $payment->payment_number }}</span>
            <span class="text-xs text-slate-400">{{ $payment->paid_at->format('l, F j, Y \a\t g:i A') }}</span>
        </div>

        <div class="text-center py-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-500/20 rounded-2xl">
            <span class="text-xs text-slate-500 block uppercase font-bold">Amount Paid</span>
            <span class="text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400">₦{{ number_format((float)$payment->amount, 2) }}</span>
            <span class="text-xs font-bold capitalize text-slate-600 dark:text-slate-300 block mt-1">Paid via {{ str_replace('_', ' ', $payment->payment_method) }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs pt-2">
            <div>
                <span class="text-slate-400 block">Subscriber</span>
                <strong class="text-slate-900 dark:text-slate-100">{{ $payment->customer->full_name }}</strong>
                <span class="block font-mono text-slate-400">ID: {{ $payment->customer->account_number }}</span>
            </div>
            <div class="text-right">
                <span class="text-slate-400 block">Settled Invoice</span>
                @if($payment->invoice)
                <a href="{{ route('invoices.show', $payment->invoice) }}" class="font-mono text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                    {{ $payment->invoice->invoice_number }}
                </a>
                @else
                <span class="text-slate-400">Direct Deposit</span>
                @endif
            </div>
            <div>
                <span class="text-slate-400 block">Reference</span>
                <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $payment->reference ?? 'None' }}</span>
            </div>
            <div class="text-right">
                <span class="text-slate-400 block">Processed By</span>
                <span class="text-slate-700 dark:text-slate-300">{{ $payment->recordedBy?->name ?? 'Online Self-Service Checkout' }}</span>
            </div>
        </div>

        @if($payment->notes)
        <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl text-xs text-slate-600 dark:text-slate-300 italic">
            "{{ $payment->notes }}"
        </div>
        @endif
    </div>

</div>
@endsection
