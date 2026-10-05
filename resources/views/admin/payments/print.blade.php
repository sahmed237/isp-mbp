<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt — {{ $payment->payment_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            @page { margin: 1.5cm; size: A5 landscape; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-8">

    <div class="max-w-xl mx-auto mb-4 flex items-center justify-between no-print px-4">
        <a href="{{ route('payments.show', $payment) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">&larr; Back</a>
        <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md">
            Print Official Receipt
        </button>
    </div>

    <div class="max-w-xl mx-auto bg-white border border-slate-200 rounded-2xl p-8 shadow-sm space-y-6">

        <div class="flex justify-between items-start border-b border-slate-200 pb-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-xs">ISP</div>
                    <span class="text-base font-extrabold text-slate-900">{{ $payment->organization?->name ?? 'Internet Service Provider' }}</span>
                </div>
                <p class="text-[11px] text-slate-500">Official Payment Clearance Receipt</p>
            </div>
            <div class="text-right">
                <span class="font-mono text-sm font-black text-indigo-600 block">{{ $payment->payment_number }}</span>
                <span class="text-[11px] text-slate-400">{{ $payment->paid_at->format('M d, Y H:i') }}</span>
            </div>
        </div>

        <div class="text-center py-4 bg-slate-50 border border-slate-200 rounded-xl">
            <span class="text-[10px] text-slate-400 block uppercase font-bold">TOTAL AMOUNT PAID</span>
            <span class="text-3xl font-black font-mono text-slate-900">₦{{ number_format((float)$payment->amount, 2) }}</span>
            <span class="text-xs font-semibold capitalize text-emerald-600 block mt-1">&check; Verified & Cleared via {{ str_replace('_', ' ', $payment->payment_method) }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Received From</span>
                <strong class="text-slate-900">{{ $payment->customer->full_name }}</strong>
                <span class="block font-mono text-slate-500">ID: {{ $payment->customer->account_number }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Applied Invoice</span>
                <span class="font-mono font-bold">{{ $payment->invoice?->invoice_number ?? 'Direct Payment' }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Reference Code</span>
                <span class="font-mono text-slate-700">{{ $payment->reference ?? '—' }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Status</span>
                <span class="uppercase font-bold text-emerald-600">CONFIRMED</span>
            </div>
        </div>

        <div class="border-t border-slate-200 pt-4 flex items-center justify-between text-[10px] text-slate-400">
            <span>Thank you for your business.</span>
            <span>Auth Stamp: {{ substr(md5($payment->payment_number . $payment->amount), 0, 10) }}</span>
        </div>

    </div>

</body>
</html>
