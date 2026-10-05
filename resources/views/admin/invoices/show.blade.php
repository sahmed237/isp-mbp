@php
    $companyInfo = \App\Models\Setting::getCompanyInfo();
    $publicPayUrl = route('public.invoices.pay', $invoice->uuid);
@endphp

@extends('layouts.app')

@section('title', "Invoice: {$invoice->invoice_number}")
@section('page_title', "Invoice {$invoice->invoice_number}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
         payModalOpen: false,
         payloadModalOpen: false,
         modalTitle: '',
         payloadTab: 'request',
         activePayloadData: null,
         copied: false,
         viewAttemptPayload(attempt) {
             this.modalTitle = 'Payment Attempt: ' + attempt.reference;
             this.activePayloadData = attempt;
             this.payloadTab = attempt.response_payload ? 'response' : (attempt.request_payload ? 'request' : 'metadata');
             this.payloadModalOpen = true;
         },
         viewPaymentPayload(payment) {
             this.modalTitle = 'Recorded Payment Payload: ' + payment.payment_number;
             this.activePayloadData = {
                 reference: payment.reference || payment.payment_number,
                 method: payment.payment_method,
                 amount: payment.amount,
                 status: payment.status,
                 raw_payload: payment.raw_payload,
                 ip_address: payment.raw_payload?.client_ip || payment.raw_payload?.ip || null,
                 user_agent: payment.raw_payload?.user_agent || null,
                 initiated_at: payment.paid_at,
                 type: 'payment'
             };
             this.payloadTab = 'raw';
             this.payloadModalOpen = true;
         },
         copyJson(content) {
             if (!content) return;
             navigator.clipboard.writeText(typeof content === 'string' ? content : JSON.stringify(content, null, 2));
             this.copied = true;
             setTimeout(() => { this.copied = false; }, 2000);
         }
     }">

    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs">
                &larr; Back
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">{{ $invoice->invoice_number }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize {{ $invoice->status_badge_class }}">
                        {{ $invoice->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-400">Issued on {{ $invoice->issue_date->format('M d, Y') }} &bull; Due {{ $invoice->due_date->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ $publicPayUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-indigo-200 dark:border-indigo-900/50 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-xs font-bold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Public Pay Page</span>
            </a>

            @if(!$invoice->isPaid())
            @can('payments.create')
            <button @click="payModalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Record Payment</span>
            </button>
            @endcan
            @endif

            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Invoice</span>
            </a>
        </div>
    </div>

    <!-- Clean Printable-Style Invoice Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-sm space-y-8">

        <!-- Top Header: ISP Organization & Invoice Meta -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center font-black">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ $companyInfo['name'] }}</span>
                </div>
                <div class="text-xs text-slate-500 space-y-0.5">
                    <p>{{ $companyInfo['address'] }}</p>
                    <p>Phone: {{ $companyInfo['phone'] }}</p>
                    <p>Email: {{ $companyInfo['email'] }}</p>
                </div>
            </div>

            <div class="sm:text-right space-y-1">
                <span class="text-xs uppercase font-extrabold tracking-widest text-slate-400 block">Tax Invoice</span>
                <span class="text-xl font-black font-mono text-slate-900 dark:text-slate-100 block">{{ $invoice->invoice_number }}</span>
                <div class="text-xs text-slate-500 space-y-0.5">
                    <p>Date: <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->issue_date->format('M d, Y') }}</strong></p>
                    <p>Due Date: <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->due_date->format('M d, Y') }}</strong></p>
                    @if($invoice->paid_at)
                    <p>Settled: <strong class="text-emerald-600 dark:text-emerald-400">{{ $invoice->paid_at->format('M d, Y H:i') }}</strong></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bill To Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-slate-100 dark:border-slate-800 text-xs">
            <div>
                <span class="text-[11px] uppercase font-bold text-slate-400 block mb-1">Billed To</span>
                <h3 class="font-extrabold text-sm text-slate-900 dark:text-slate-100">{{ $invoice->customer->full_name }}</h3>
                <div class="text-slate-500 space-y-0.5 mt-1">
                    <p class="font-mono text-brand-600 dark:text-brand-400 font-bold">Account: {{ $invoice->customer->account_number }}</p>
                    <p>{{ $invoice->customer->installation_address ?? 'No physical address' }}</p>
                    <p>{{ $invoice->customer->city }}, {{ $invoice->customer->state }}</p>
                    <p>Phone: {{ $invoice->customer->phone }}</p>
                </div>
            </div>

            <div class="sm:text-right">
                <span class="text-[11px] uppercase font-bold text-slate-400 block mb-1">Service Association</span>
                @if($invoice->subscription)
                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $invoice->subscription->package->name }}</p>
                <p class="text-slate-500 font-mono">Subscription: {{ $invoice->subscription->subscription_number }}</p>
                <p class="text-slate-500">Cycle: {{ ucfirst($invoice->subscription->billing_cycle) }}</p>
                @else
                <p class="text-slate-500">General Account Billing / Equipment</p>
                @endif
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-y border-slate-200 dark:border-slate-800 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Unit Price</th>
                        <th class="py-3 px-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                            {{ $item->description }}
                        </td>
                        <td class="py-3.5 px-4 text-center text-slate-600 dark:text-slate-400 font-mono">
                            {{ $item->quantity }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-600 dark:text-slate-400 font-mono">
                            ₦{{ number_format((float)$item->unit_price, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-900 dark:text-slate-100 font-mono">
                            ₦{{ number_format((float)$item->total_price, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Summary Totals -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pt-4 border-t border-slate-100 dark:border-slate-800 text-xs">
            <div class="text-slate-400 space-y-1">
                @if($invoice->notes)
                <p class="font-semibold text-slate-700 dark:text-slate-300">Notes / Instructions:</p>
                <p class="italic">{{ $invoice->notes }}</p>
                @endif
            </div>

            <div class="w-full sm:w-72 space-y-2">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal:</span>
                    <span class="font-mono text-slate-800 dark:text-slate-200">₦{{ number_format((float)$invoice->subtotal, 2) }}</span>
                </div>
                @if((float)$invoice->tax_amount > 0)
                <div class="flex justify-between text-slate-500">
                    <span>VAT (7.5%):</span>
                    <span class="font-mono text-slate-800 dark:text-slate-200">₦{{ number_format((float)$invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm font-extrabold text-slate-900 dark:text-slate-100 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span>Total Amount:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    <span>Amount Paid:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-black text-rose-600 dark:text-rose-400 pt-1 border-t border-slate-100 dark:border-slate-800">
                    <span>Balance Due:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->balance_due, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payments Logged for this Invoice -->
        @if($invoice->payments->isNotEmpty())
        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-3">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Payments Applied to this Invoice</h4>
            <div class="space-y-2">
                @foreach($invoice->payments as $payment)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 gap-3 text-xs">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="font-mono font-bold text-brand-600 dark:text-brand-400">{{ $payment->payment_number }}</span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="capitalize font-semibold text-slate-700 dark:text-slate-300">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="font-mono text-slate-500">Ref: {{ $payment->reference ?? 'None' }}</span>
                        @if($payment->raw_payload)
                        <button type="button"
                                @click="viewPaymentPayload({{ Js::from($payment) }})"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold text-[10px] transition-all border border-indigo-200 dark:border-indigo-800">
                            <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            <span>Payload</span>
                        </button>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">₦{{ number_format((float)$payment->amount, 2) }}</span>
                        <span class="text-slate-400 text-[11px]">{{ $payment->paid_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Payment Attempts & Forensic Audit Log -->
        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Payment Attempts &amp; Forensic Audit Trail</h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            {{ $invoice->paymentAttempts->count() }} {{ Str::plural('Attempt', $invoice->paymentAttempts->count()) }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400">
                        Immutable record of all checkout handshakes, customer metadata, and gateway API payloads for forensic dispute resolution.
                    </p>
                </div>
            </div>

            @if($invoice->paymentAttempts->isEmpty())
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
                No online payment attempts logged yet for this invoice. Handshakes will appear here as soon as a subscriber initiates checkout.
            </div>
            @else
            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">Date &amp; Time</th>
                            <th class="py-2.5 px-3">Gateway</th>
                            <th class="py-2.5 px-3">Reference</th>
                            <th class="py-2.5 px-3">Amount</th>
                            <th class="py-2.5 px-3">Customer Contact</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Forensic Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @foreach($invoice->paymentAttempts as $attempt)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-3">
                                <span class="font-mono text-slate-800 dark:text-slate-200 block font-semibold text-[11px]">{{ $attempt->created_at->format('M d, Y H:i:s') }}</span>
                                <span class="text-[10px] text-slate-400">{{ $attempt->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase {{ $attempt->payment_method === 'paystack' ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800' : 'bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800' }}">
                                    {{ $attempt->payment_method }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] text-slate-600 dark:text-slate-400 select-all">
                                {{ $attempt->reference }}
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900 dark:text-slate-100">
                                ₦{{ number_format((float)$attempt->amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-[11px]">
                                <span class="block text-slate-800 dark:text-slate-200 font-medium">{{ $attempt->customer_email ?: '—' }}</span>
                                <span class="block text-slate-400 text-[10px]">{{ $attempt->customer_phone ?: '' }}</span>
                                @if($attempt->ip_address)
                                    <span class="block text-slate-400 font-mono text-[9px]">IP: {{ $attempt->ip_address }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($attempt->status === 'successful')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Successful
                                    </span>
                                    @if($attempt->payment)
                                        <span class="block text-[9px] font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $attempt->payment->payment_number }}</span>
                                    @endif
                                @elseif($attempt->status === 'failed')
                                    @if($attempt->isSuperseded())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20" title="{{ $attempt->error_message }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Superseded
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Failed
                                        </span>
                                    @endif
                                    @if($attempt->error_message)
                                        <span class="block text-[9px] text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $attempt->error_message }}">{{ $attempt->error_message }}</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                        <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        {{ ucfirst($attempt->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                <button type="button"
                                        @click="viewAttemptPayload({{ Js::from($attempt) }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[11px] transition-all border border-slate-200 dark:border-slate-700">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                    <span>View Payload</span>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Scan to Pay & Public Invoice Link -->
        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="p-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm flex-shrink-0">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->margin(0)->generate($publicPayUrl) !!}
                </div>
                <div class="text-xs space-y-1">
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 block text-xs">Public Scan & Pay QR Code</span>
                    <p class="text-slate-400 text-[11px] max-w-sm">
                        Subscribers can scan this code with any smartphone camera to view this invoice and make an instant payment without logging in.
                    </p>
                    <a href="{{ $publicPayUrl }}" target="_blank" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-mono font-bold hover:underline block break-all">
                        {{ $publicPayUrl }}
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ $publicPayUrl }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                    Open Public Payment Page &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- Forensic Payload Inspector Modal -->
    <div x-show="payloadModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
        <div @click.away="payloadModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-2xl w-full space-y-4 max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <span class="text-[10px] uppercase font-bold text-indigo-600 dark:text-indigo-400 tracking-wider block">Forensic Audit Inspection</span>
                    <h3 class="text-sm font-black text-slate-900 dark:text-slate-100" x-text="modalTitle"></h3>
                </div>
                <button type="button" @click="payloadModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
            </div>

            <!-- Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                <template x-if="activePayloadData && activePayloadData.request_payload">
                    <button type="button" @click="payloadTab = 'request'" :class="payloadTab === 'request' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'" class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all">
                        Request Payload
                    </button>
                </template>

                <template x-if="activePayloadData && activePayloadData.response_payload">
                    <button type="button" @click="payloadTab = 'response'" :class="payloadTab === 'response' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'" class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all">
                        Init Response
                    </button>
                </template>

                <template x-if="activePayloadData && activePayloadData.verification_payload">
                    <button type="button" @click="payloadTab = 'verification'" :class="payloadTab === 'verification' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'" class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all">
                        Verification / Callback
                    </button>
                </template>

                <template x-if="activePayloadData && activePayloadData.raw_payload">
                    <button type="button" @click="payloadTab = 'raw'" :class="payloadTab === 'raw' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'" class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all">
                        Raw Payment Payload
                    </button>
                </template>

                <button type="button" @click="payloadTab = 'metadata'" :class="payloadTab === 'metadata' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'" class="px-3 py-1.5 rounded-xl font-bold text-[11px] transition-all">
                    Network &amp; Client Info
                </button>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="flex-1 overflow-y-auto space-y-3">
                <!-- Request Tab -->
                <div x-show="payloadTab === 'request'">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Parameters Sent to Gateway:</span>
                        <button type="button" @click="copyJson(activePayloadData?.request_payload)" class="text-[10px] text-indigo-600 font-bold hover:underline flex items-center gap-1">
                            <span x-text="copied ? '✓ Copied' : 'Copy JSON'"></span>
                        </button>
                    </div>
                    <pre class="bg-slate-950 text-emerald-400 p-4 rounded-2xl text-[11px] font-mono overflow-x-auto max-h-80 select-all" x-text="JSON.stringify(activePayloadData?.request_payload, null, 2)"></pre>
                </div>

                <!-- Init Response Tab -->
                <div x-show="payloadTab === 'response'">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Gateway Handshake Response:</span>
                        <button type="button" @click="copyJson(activePayloadData?.response_payload)" class="text-[10px] text-indigo-600 font-bold hover:underline flex items-center gap-1">
                            <span x-text="copied ? '✓ Copied' : 'Copy JSON'"></span>
                        </button>
                    </div>
                    <pre class="bg-slate-950 text-emerald-400 p-4 rounded-2xl text-[11px] font-mono overflow-x-auto max-h-80 select-all" x-text="JSON.stringify(activePayloadData?.response_payload, null, 2)"></pre>
                </div>

                <!-- Verification / Callback Tab -->
                <div x-show="payloadTab === 'verification'">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Gateway Callback / Verification Response:</span>
                        <button type="button" @click="copyJson(activePayloadData?.verification_payload)" class="text-[10px] text-indigo-600 font-bold hover:underline flex items-center gap-1">
                            <span x-text="copied ? '✓ Copied' : 'Copy JSON'"></span>
                        </button>
                    </div>
                    <pre class="bg-slate-950 text-emerald-400 p-4 rounded-2xl text-[11px] font-mono overflow-x-auto max-h-80 select-all" x-text="JSON.stringify(activePayloadData?.verification_payload, null, 2)"></pre>
                </div>

                <!-- Raw Tab -->
                <div x-show="payloadTab === 'raw'">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Raw Recorded Payment Payload:</span>
                        <button type="button" @click="copyJson(activePayloadData?.raw_payload)" class="text-[10px] text-indigo-600 font-bold hover:underline flex items-center gap-1">
                            <span x-text="copied ? '✓ Copied' : 'Copy JSON'"></span>
                        </button>
                    </div>
                    <pre class="bg-slate-950 text-emerald-400 p-4 rounded-2xl text-[11px] font-mono overflow-x-auto max-h-80 select-all" x-text="JSON.stringify(activePayloadData?.raw_payload, null, 2)"></pre>
                </div>

                <!-- Metadata Tab -->
                <div x-show="payloadTab === 'metadata'" class="space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                            <span class="block text-[10px] text-slate-400 uppercase font-bold">Client IP Address</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="activePayloadData?.ip_address || 'Not Recorded'"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                            <span class="block text-[10px] text-slate-400 uppercase font-bold">Initiated At</span>
                            <span class="font-mono text-slate-800 dark:text-slate-200" x-text="activePayloadData?.initiated_at || '—'"></span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-xs">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold mb-1">Device &amp; Browser User Agent</span>
                        <p class="font-mono text-[11px] text-slate-600 dark:text-slate-300 break-all" x-text="activePayloadData?.user_agent || 'None'"></p>
                    </div>

                    <template x-if="activePayloadData?.error_message">
                        <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-600 dark:text-rose-400">
                            <span class="block text-[10px] uppercase font-bold mb-1">Gateway Error Notice</span>
                            <p class="font-mono text-[11px]" x-text="activePayloadData?.error_message"></p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800 text-xs">
                <span class="text-[10px] text-slate-400">ISP-MBP Forensic Audit Engine</span>
                <button type="button" @click="payloadModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>

    @can('payments.create')
    <!-- Record Payment Modal -->
    <div x-show="payModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div @click.away="payModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Record Payment</h3>
                <button type="button" @click="payModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('invoices.payments.store', $invoice) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Amount (₦) *</label>
                    <input type="number" step="0.01" name="amount" value="{{ $invoice->balance_due }}" max="{{ $invoice->balance_due }}" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Full balance due: ₦{{ number_format((float)$invoice->balance_due, 2) }}</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="cash">Cash Received</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">POS / Card Terminal</option>
                        <option value="paystack">Paystack</option>
                        <option value="moniepoint">Moniepoint</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Transaction Reference / Receipt No</label>
                    <input type="text" name="reference" placeholder="e.g. TRF-93847291 or POS-8472" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Notes</label>
                    <input type="text" name="notes" placeholder="Optional comment" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="payModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-500">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

</div>
@endsection
