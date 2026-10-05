@php
    $companyInfo = \App\Models\Setting::getCompanyInfo();
    $publicPayUrl = route('public.invoices.pay', $invoice->uuid);
@endphp

@extends('portal.layout')

@section('title', "Invoice {$invoice->invoice_number}")

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ payModalOpen: false }">

    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portal.invoices') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
            &larr; Back to Invoices
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Invoice</span>
            </a>

            @if(!$invoice->isPaid())
            <button @click="payModalOpen = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition-all">
                Pay ₦{{ number_format((float)$invoice->balance_due, 2) }} Now
            </button>
            @endif
        </div>
    </div>

    <!-- Invoice Detail Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-8">

        <!-- Top Row: ISP Company Details & Invoice Meta -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-slate-100">{{ $companyInfo['name'] }}</h2>
                        <span class="text-[10px] text-slate-400">Broadband Internet Service</span>
                    </div>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 space-y-0.5">
                    <p>{{ $companyInfo['address'] }}</p>
                    <p>Phone: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $companyInfo['phone'] }}</span></p>
                    <p>Email: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $companyInfo['email'] }}</span></p>
                </div>
            </div>

            <div class="sm:text-right space-y-1">
                <span class="text-xs uppercase font-extrabold tracking-widest text-slate-400 block">Broadband Invoice</span>
                <span class="text-2xl font-black font-mono text-indigo-600 dark:text-indigo-400 block">{{ $invoice->invoice_number }}</span>
                <span class="text-xs text-slate-400 block">Issued: {{ $invoice->issue_date->format('M d, Y') }} &bull; Due: {{ $invoice->due_date->format('M d, Y') }}</span>
                <div class="pt-1">
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold capitalize {{ $invoice->status_badge_class }}">
                        {{ $invoice->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-y border-slate-200 dark:border-slate-800 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-3">Description</th>
                        <th class="py-2.5 px-3 text-center">Qty</th>
                        <th class="py-2.5 px-3 text-right">Unit Price</th>
                        <th class="py-2.5 px-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ $item->description }}</td>
                        <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $item->quantity }}</td>
                        <td class="py-3 px-3 text-right font-mono text-slate-500">₦{{ number_format((float)$item->unit_price, 2) }}</td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 dark:text-slate-100">₦{{ number_format((float)$item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="w-72 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-extrabold text-slate-900 dark:text-slate-100 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span>Total Amount:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-emerald-600 dark:text-emerald-400">
                    <span>Amount Paid:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-black text-sm text-rose-600 dark:text-rose-400 pt-1 border-t border-slate-100 dark:border-slate-800">
                    <span>Balance Due:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->balance_due, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Scan to Pay & Public Invoice Link -->
        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="p-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm flex-shrink-0">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->margin(0)->generate($publicPayUrl) !!}
                </div>
                <div class="text-xs space-y-1">
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 block text-xs">Scan & Pay on Mobile</span>
                    <p class="text-slate-400 text-[11px] max-w-sm">
                        Scan this QR code with your smartphone camera to open and pay this invoice immediately without logging in.
                    </p>
                    <a href="{{ $publicPayUrl }}" target="_blank" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-mono font-bold hover:underline block break-all">
                        {{ $publicPayUrl }}
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ $publicPayUrl }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                    Open Public Pay Page &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- Interactive Payment Modal -->
    <div x-show="payModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div @click.away="payModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Pay Outstanding Invoice</h3>
                <button type="button" @click="payModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('portal.invoices.pay', $invoice) }}" method="POST" class="space-y-4">
                @csrf
                <div class="p-4 rounded-2xl bg-brand-500/10 border border-brand-500/20 text-center">
                    <span class="text-xs text-brand-600 dark:text-brand-400 block font-semibold">Total Payable</span>
                    <span class="text-3xl font-black font-mono text-slate-900 dark:text-slate-100">₦{{ number_format((float)$invoice->balance_due, 2) }}</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Payment Method</label>
                    <select name="payment_method" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="card">Debit Card (Mastercard / Visa / Verve)</option>
                        <option value="bank_transfer">Direct Bank Transfer</option>
                        <option value="paystack">Paystack Gateway</option>
                    </select>
                </div>

                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-[11px] text-emerald-700 dark:text-emerald-400">
                    &check; Instant Automated Activation: Your subscription will be renewed immediately and your router connection re-enabled on the network.
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="payModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-500">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30">Confirm & Complete Payment</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
