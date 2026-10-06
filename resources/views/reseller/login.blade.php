<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 font-sans antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Voucher Reseller & Agent Portal — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full flex flex-col justify-center items-center p-4 relative overflow-hidden bg-slate-950 text-slate-100">

    <!-- Ambient Glowing Background Circles -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full space-y-6 relative z-10">

        <!-- Brand Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white mx-auto shadow-xl shadow-emerald-500/25">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Voucher Reseller & Agent Portal</h1>
            <p class="text-xs text-slate-400">Generate Wi-Fi hotspot batches, manage your prepaid balance, and print perforated cards.</p>
        </div>

        @if(session('info'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-xs text-emerald-400 text-center font-medium">
            {{ session('info') }}
        </div>
        @endif

        @if(session('success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-xs text-emerald-400 text-center font-medium">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-400 text-center font-medium">
            {{ $errors->first() }}
        </div>
        @endif

        <!-- Login Card -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl space-y-6">
            <form action="{{ route('reseller.login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Reseller Code, Email, or Phone</label>
                    <input type="text" name="login" value="{{ old('login') }}" required placeholder="e.g. RSL-XXXXXX or agent@shop.ng" class="w-full px-4 py-3 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Portal Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 bg-slate-800 border-slate-700">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2">
                    <span>Sign In to Agent Portal</span>
                    <span>&rarr;</span>
                </button>
            </form>

            <!-- Apply CTA -->
            <div class="pt-4 border-t border-slate-800/80 text-center space-y-2">
                <p class="text-xs text-slate-400">Want to sell Wi-Fi vouchers in your shop or business?</p>
                <a href="{{ route('reseller.apply') }}" class="inline-flex items-center justify-center gap-1.5 w-full py-2.5 px-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 font-bold text-xs transition-all">
                    <span>Apply for an Agent Account & KYC</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <!-- Bottom Links -->
            <div class="pt-2 flex items-center justify-between text-[11px] text-slate-500 border-t border-slate-800/40">
                <a href="{{ route('portal.login') }}" class="hover:text-slate-300 transition-colors">Subscriber Portal</a>
                <a href="{{ route('login') }}" class="hover:text-slate-300 transition-colors">ISP Staff Login</a>
            </div>
        </div>

    </div>

</body>
</html>
