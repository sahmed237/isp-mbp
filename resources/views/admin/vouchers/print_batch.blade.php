<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Vouchers Batch — {{ $batchId }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            @page { margin: 1cm; size: A4; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 text-slate-900">

    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print px-4">
        <div>
            <h1 class="text-lg font-bold">Printable Voucher Batch: {{ $batchId }}</h1>
            <p class="text-xs text-slate-500">Total Vouchers in this batch: {{ $vouchers->count() }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('vouchers.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">&larr; Back to Vouchers</a>
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md">
                Print Voucher Sheet
            </button>
        </div>
    </div>

    <!-- Printable Voucher Grid (3 per row on desktop/print) -->
    <div class="max-w-5xl mx-auto px-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($vouchers as $v)
        <div class="bg-white border-2 border-dashed border-slate-300 rounded-2xl p-4 flex flex-col justify-between shadow-sm relative overflow-hidden break-inside-avoid">

            <!-- Card Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                <div class="flex items-center gap-1.5">
                    <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                    </div>
                    <span class="font-extrabold text-xs tracking-tight text-slate-900">HOTSPOT WI-FI</span>
                </div>
                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700">
                    {{ $v->duration_formatted }}
                </span>
            </div>

            <!-- Plan & Speed -->
            <div class="text-center py-1">
                <h3 class="font-black text-sm text-slate-900">{{ $v->package->name }}</h3>
                <span class="text-[11px] font-mono text-slate-500 font-semibold block">Speed: {{ $v->package->formatted_download_speed }}</span>
                <span class="text-xs font-black text-indigo-600 font-mono block mt-1">₦{{ number_format((float)$v->price, 0) }}</span>
            </div>

            <!-- Credentials Box -->
            <div class="my-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-center space-y-1">
                <div>
                    <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">LOGIN USERNAME</span>
                    <span class="font-mono font-bold text-xs text-slate-800">{{ $v->username }}</span>
                </div>
                <div class="pt-1 border-t border-slate-200">
                    <span class="text-[9px] uppercase font-black text-indigo-500 block tracking-wider">HOTSPOT PIN / PASSWORD</span>
                    <span class="font-mono font-black text-base tracking-widest text-slate-950">{{ $v->password }}</span>
                </div>
            </div>

            <!-- Instructions Footer -->
            <div class="pt-2 border-t border-slate-100 text-[9px] text-slate-400 space-y-0.5">
                <p>1. Connect to Wi-Fi network</p>
                <p>2. Open browser & enter PIN</p>
                <div class="flex justify-between items-center pt-1 font-mono text-[8px] text-slate-400">
                    <span>Code: {{ $v->code }}</span>
                    <span>Valid: 1 Device</span>
                </div>
            </div>

        </div>
        @endforeach
    </div>

</body>
</html>
