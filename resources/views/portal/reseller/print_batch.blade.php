<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Batch {{ $batchId }} ({{ $vouchers->count() }} Cards) — {{ $companyInfo['company_name'] ?? 'ISP Hotspot' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .voucher-card {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen">

    <!-- Floating Top Bar (Hidden in Print) -->
    <header class="no-print bg-slate-900 text-white sticky top-0 z-50 border-b border-slate-800 shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.reseller.vouchers') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Reseller Hub
                </a>
                <div class="border-l border-slate-700 pl-3">
                    <span class="text-xs font-bold font-mono text-emerald-400">{{ $batchId }}</span>
                    <span class="text-xs text-slate-400 ml-2">({{ $vouchers->count() }} Cards • {{ $package->name }})</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    onclick="window.print()"
                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Sheet (A4 / Perforated Cards)
                </button>
            </div>
        </div>
    </header>

    <!-- Cards Grid Container -->
    <main class="max-w-5xl mx-auto p-4 sm:p-6">

        <!-- Grid: 2 columns on mobile/print, 2 or 3 columns on desktop -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 print:grid-cols-2 print:gap-3">
            @foreach($vouchers as $voucher)
            <div class="voucher-card relative bg-white border-2 border-dashed border-slate-400 rounded-2xl p-4 shadow-sm flex flex-col justify-between overflow-hidden">

                <!-- Scissor Cut Indicators -->
                <div class="absolute top-1 left-2 text-[10px] text-slate-400 select-none">
                    ✂ - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                </div>

                <div>
                    <!-- Card Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200 mt-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-xs text-slate-900 leading-tight">{{ $companyInfo['company_name'] ?? 'ISP WiFi Hotspot' }}</h4>
                                <span class="text-[9px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.2 rounded">SSID: {{ $hotspotSsid }}</span>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold text-slate-500 block">Price</span>
                            <span class="text-xs font-mono font-black text-emerald-700">₦{{ number_format((float)$voucher->price, 0) }}</span>
                        </div>
                    </div>

                    <!-- Package Info Banner -->
                    <div class="my-2.5 flex items-center justify-between bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-200 text-[11px]">
                        <span class="font-bold text-slate-800">{{ $package->name }}</span>
                        <span class="font-mono text-slate-600">{{ $voucher->duration_formatted }} • {{ $package->download_speed }}M</span>
                    </div>

                    <!-- Big PIN Box + QR Code Side-by-Side -->
                    <div class="flex items-center justify-between gap-3 bg-slate-900 text-white rounded-xl p-3 my-2">
                        <div class="space-y-1">
                            <span class="text-[9px] uppercase tracking-wider text-slate-400 font-bold block">VOUCHER PIN / CODE</span>
                            <div class="text-lg font-black font-mono tracking-wider text-amber-300">
                                {{ $voucher->code }}
                            </div>
                            <div class="text-[10px] text-slate-300 font-mono space-x-2">
                                <span>User: <strong>{{ $voucher->username }}</strong></span>
                                <span>PIN: <strong>{{ $voucher->password }}</strong></span>
                            </div>
                        </div>

                        <div class="bg-white p-1 rounded-lg shrink-0">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(58)->margin(0)->generate($voucher->code) !!}
                        </div>
                    </div>
                </div>

                <!-- Footer Instructions -->
                <div class="pt-2 border-t border-slate-200 text-[9px] text-slate-500 flex items-center justify-between">
                    <div>
                        <strong>To connect:</strong> 1. Connect to WiFi <strong>{{ $hotspotSsid }}</strong> &bull; 2. Enter PIN
                    </div>
                    <span class="font-mono text-slate-400 text-[8px]">{{ $voucher->batch_id }}</span>
                </div>

            </div>
            @endforeach
        </div>

    </main>

</body>
</html>
