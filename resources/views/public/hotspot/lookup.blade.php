<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retrieve Hotspot Voucher — {{ $companyInfo['company_name'] ?? 'ISP Hotspot' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
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
</head>
<body class="bg-slate-950 text-slate-100 min-h-full flex flex-col font-sans selection:bg-brand-500 selection:text-white">

    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('public.hotspot.index') }}" class="flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Plans</span>
            </a>
            <span class="text-xs font-bold text-slate-300">{{ $companyInfo['company_name'] ?? 'ISP High-Speed WiFi' }}</span>
        </div>
    </header>

    <main class="flex-1 max-w-md w-full mx-auto px-4 py-12 flex flex-col items-center justify-center">
        <div class="w-full bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">

            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-400 border border-brand-500/20 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h1 class="text-xl font-extrabold text-white">Find Your Voucher</h1>
                <p class="text-xs text-slate-400">Enter the phone number used during checkout, or your voucher PIN / transaction reference.</p>
            </div>

            @if(session('error'))
            <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold">
                {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('public.hotspot.lookup') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Phone Number, Voucher PIN or Reference</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ old('search') }}"
                        required
                        placeholder="e.g. 08031234567 or VCH-..."
                        class="w-full px-3.5 py-3 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg shadow-brand-600/30 transition flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Retrieve Voucher Details</span>
                </button>
            </form>

            <div class="text-center pt-2">
                <a href="{{ route('public.hotspot.index') }}" class="text-xs font-semibold text-brand-400 hover:underline">
                    Need a new voucher? Browse Plans &rarr;
                </a>
            </div>

        </div>
    </main>

    <footer class="border-t border-slate-900 bg-slate-950 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} {{ $companyInfo['company_name'] ?? 'ISP-MBP' }}</p>
    </footer>

</body>
</html>
