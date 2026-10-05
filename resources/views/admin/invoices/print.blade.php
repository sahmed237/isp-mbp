@php
    $companyInfo = \App\Models\Setting::getCompanyInfo();
    $publicPayUrl = route('public.invoices.pay', $invoice->uuid);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice — {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            @page { margin: 1.5cm; size: A4; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-8">

    <div class="max-w-3xl mx-auto mb-6 flex items-center justify-between no-print px-4">
        <a href="{{ route('invoices.show', $invoice) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">&larr; Back to Platform</a>
        <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md">
            Print / Save to PDF
        </button>
    </div>

    <div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl p-10 shadow-sm space-y-8">

        <!-- Header -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6 gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-xl font-extrabold text-slate-900">{{ $companyInfo['name'] }}</span>
                </div>
                <div class="text-xs text-slate-500 space-y-0.5">
                    <p>{{ $companyInfo['address'] }}</p>
                    <p>Phone: {{ $companyInfo['phone'] }}</p>
                    <p>Email: {{ $companyInfo['email'] }}</p>
                </div>
            </div>

            <div class="text-right">
                <span class="text-xs uppercase font-extrabold tracking-widest text-slate-400 block">TAX INVOICE</span>
                <span class="text-xl font-black font-mono text-indigo-600 block">{{ $invoice->invoice_number }}</span>
                <div class="text-xs text-slate-500 space-y-0.5 mt-2">
                    <p>Issue Date: <strong>{{ $invoice->issue_date->format('M d, Y') }}</strong></p>
                    <p>Due Date: <strong>{{ $invoice->due_date->format('M d, Y') }}</strong></p>
                    <p>Status: <strong class="uppercase {{ $invoice->isPaid() ? 'text-emerald-600' : 'text-amber-600' }}">{{ $invoice->status }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="grid grid-cols-2 gap-6 text-xs border-b border-slate-200 pb-6">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Subscriber Details</span>
                <h3 class="font-extrabold text-sm text-slate-900">{{ $invoice->customer->full_name }}</h3>
                <div class="text-slate-500 space-y-0.5 mt-1">
                    <p class="font-mono text-indigo-600 font-bold">Account ID: {{ $invoice->customer->account_number }}</p>
                    <p>{{ $invoice->customer->installation_address }}</p>
                    <p>{{ $invoice->customer->city }}, {{ $invoice->customer->state }}</p>
                    <p>Phone: {{ $invoice->customer->phone }}</p>
                </div>
            </div>

            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Payment Method & Reference</span>
                <p class="font-semibold text-slate-800 capitalize">{{ str_replace('_', ' ', $invoice->payment_method ?? 'Bank Transfer / Card') }}</p>
                @if($invoice->paid_at)
                <p class="text-emerald-600 font-bold mt-1">Paid on {{ $invoice->paid_at->format('M d, Y H:i') }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-y border-slate-200 text-[10px] uppercase font-bold text-slate-500">
                <tr>
                    <th class="py-2.5 px-3">Description</th>
                    <th class="py-2.5 px-3 text-center">Qty</th>
                    <th class="py-2.5 px-3 text-right">Unit Price (₦)</th>
                    <th class="py-2.5 px-3 text-right">Amount (₦)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($invoice->items as $item)
                <tr>
                    <td class="py-3 px-3 font-semibold text-slate-800">{{ $item->description }}</td>
                    <td class="py-3 px-3 text-center font-mono text-slate-600">{{ $item->quantity }}</td>
                    <td class="py-3 px-3 text-right font-mono text-slate-600">{{ number_format((float)$item->unit_price, 2) }}</td>
                    <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">{{ number_format((float)$item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end pt-4 border-t border-slate-200">
            <div class="w-64 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Amount:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-emerald-600">
                    <span>Amount Paid:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-black text-rose-600 pt-1 border-t border-slate-200">
                    <span>Balance Due:</span>
                    <span class="font-mono">₦{{ number_format((float)$invoice->balance_due, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer / Payment Advice & Scan-to-Pay QR Code -->
        <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-6 text-[11px] text-slate-500">
            <div class="space-y-1.5 flex-1">
                <p class="font-bold text-slate-700">Bank Transfer Payment Information:</p>
                <p>Account Name: {{ $companyInfo['bank_account_name'] }} &bull; Bank: {{ $companyInfo['bank_name'] }} &bull; Account No: <strong class="font-mono text-slate-800 text-xs">{{ $companyInfo['bank_account_number'] }}</strong></p>
                <p class="text-[10px] text-slate-400 italic">Please use Invoice Number (<strong>{{ $invoice->invoice_number }}</strong>) or Account ID (<strong>{{ $invoice->customer->account_number }}</strong>) as your payment reference.</p>
                <p class="text-[10px] text-indigo-600 font-mono pt-0.5 break-all">Online Payment Link: {{ $publicPayUrl }}</p>
            </div>

            <!-- Scan to Pay QR Code -->
            <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-2xl flex-shrink-0">
                <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center bg-white p-1 rounded-xl border border-slate-200/80">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(76)->margin(0)->generate($publicPayUrl) !!}
                </div>
                <div class="text-[10px] space-y-0.5 text-left max-w-[130px]">
                    <span class="font-black text-slate-900 uppercase block tracking-wider text-[9px]">Scan & Pay Online</span>
                    <p class="text-slate-500 leading-tight">Scan with your smartphone camera to pay immediately via Card, USSD or Bank Transfer.</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
