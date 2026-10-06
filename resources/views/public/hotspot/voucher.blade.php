<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotspot WiFi Voucher #{{ $voucher->code }} — {{ $companyInfo['company_name'] ?? 'ISP Hotspot' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-card { border: 2px dashed #000 !important; color: black !important; background: white !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-full flex flex-col font-sans selection:bg-brand-500 selection:text-white">

    <!-- Top Header (hidden on print) -->
    <header class="no-print border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('public.hotspot.index') }}" class="flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Plans</span>
            </a>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="text-xs font-bold px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Voucher
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-xl w-full mx-auto px-4 py-8 sm:py-12 flex flex-col items-center justify-center">

        <!-- Success Toast Indicator (no-print) -->
        <div class="no-print text-center mb-6 space-y-2">
            <div class="w-14 h-14 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white">Payment Confirmed!</h1>
            <p class="text-xs text-slate-400">Your high-speed hotspot voucher is active and ready to connect.</p>
        </div>

        <!-- The Voucher Card -->
        <div x-data="{
            copiedCode: false,
            copiedPin: false,
            copyText(text, target) {
                navigator.clipboard.writeText(text);
                if (target === 'code') { this.copiedCode = true; setTimeout(() => this.copiedCode = false, 2000); }
                if (target === 'pin') { this.copiedPin = true; setTimeout(() => this.copiedPin = false, 2000); }
            }
        }" class="print-card w-full bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden space-y-6">

            <!-- Card Watermark / Glow -->
            <div class="absolute -top-12 -right-12 w-40 h-40 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Card Header -->
            <div class="flex items-start justify-between border-b border-slate-800 pb-5">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-brand-400 bg-brand-500/10 px-2.5 py-0.5 rounded-full border border-brand-500/20">
                        OFFICIAL ACCESS VOUCHER
                    </span>
                    <h2 class="text-base sm:text-lg font-black text-white mt-1">{{ $companyInfo['company_name'] ?? 'ISP High-Speed WiFi' }}</h2>
                    <p class="text-xs text-slate-400 font-medium">{{ $voucher->package->name ?? 'Hotspot Plan' }} • {{ $voucher->duration_formatted }}</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400">Amount Paid</span>
                    <div class="text-base font-extrabold font-mono text-emerald-400">₦{{ number_format((float)$voucher->price, 2) }}</div>
                </div>
            </div>

            <!-- Big PIN / Code Highlight Area -->
            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 text-center space-y-3">
                <div class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Voucher PIN / Login Code</div>

                <div class="flex items-center justify-center gap-3">
                    <span class="text-2xl sm:text-3xl font-black font-mono tracking-widest text-brand-400 select-all">
                        {{ $voucher->code }}
                    </span>
                    <button
                        type="button"
                        @click="copyText('{{ $voucher->code }}', 'code')"
                        class="no-print p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition"
                        title="Copy Code"
                    >
                        <svg x-show="!copiedCode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-show="copiedCode" x-cloak class="text-[10px] font-bold text-emerald-400">Copied!</span>
                    </button>
                </div>

                <div class="pt-3 border-t border-slate-800/80 grid grid-cols-2 gap-3 text-left">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Username:</span>
                        <span class="text-xs font-mono font-bold text-white select-all">{{ $voucher->username }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block">Password:</span>
                        <span class="text-xs font-mono font-bold text-white select-all">{{ $voucher->password }}</span>
                    </div>
                </div>
            </div>

            <!-- Wi-Fi Network Details & Connection Instructions -->
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-white">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                    <span>How to Connect to WiFi:</span>
                </div>
                <ol class="text-xs text-slate-300 space-y-2 list-decimal list-inside bg-slate-950/50 p-4 rounded-xl border border-slate-800/60">
                    <li>Connect your device to Wi-Fi SSID: <strong class="text-white font-mono bg-slate-800 px-1.5 py-0.5 rounded">{{ $hotspotSsid }}</strong></li>
                    <li>Open your browser (or tap the WiFi notification popup).</li>
                    <li>Enter your <strong>Voucher Code / PIN</strong> or Username & Password and click <strong>Connect</strong>.</li>
                </ol>
            </div>

            <!-- QR Code Area for Mobile Users -->
            <div class="flex items-center justify-between p-4 bg-slate-950/80 rounded-2xl border border-slate-800">
                <div class="space-y-1">
                    <div class="text-xs font-bold text-white">Quick Scan PIN</div>
                    <p class="text-[10px] text-slate-400">Scan this QR code with your mobile camera to copy your voucher credentials instantly.</p>
                </div>
                <div class="p-2 bg-white rounded-xl shadow-md shrink-0">
                    {!! $qrCodeSvg !!}
                </div>
            </div>

            <!-- Direct Action Buttons (no-print) -->
            <div class="no-print pt-2 space-y-2">
                <a
                    href="{{ $hotspotLoginUrl }}"
                    target="_blank"
                    class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Open Hotspot Login Page & Connect Now</span>
                </a>
                <button
                    type="button"
                    onclick="window.print()"
                    class="w-full py-2.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs transition-all flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print or Save Voucher as PDF</span>
                </button>
            </div>

            <!-- Card Footer -->
            <div class="border-t border-slate-800 pt-4 text-center">
                <span class="text-[10px] text-slate-500 font-mono">Voucher UUID: {{ $voucher->uuid }} • Issued: {{ $voucher->created_at->format('d M Y, H:i') }}</span>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="no-print border-t border-slate-900 bg-slate-950 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} {{ $companyInfo['company_name'] ?? 'ISP-MBP' }}. Need help? Contact customer support.</p>
    </footer>

</body>
</html>
