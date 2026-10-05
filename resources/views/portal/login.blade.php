<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 font-sans antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscriber Portal — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full flex flex-col justify-center items-center p-4 relative overflow-hidden bg-slate-950 text-slate-100">

    <!-- Ambient Glowing Background Circles -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full space-y-6 relative z-10">

        <!-- Brand Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white mx-auto shadow-xl shadow-indigo-500/25">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Customer Self-Service Portal</h1>
            <p class="text-xs text-slate-400">View active subscription, check live speeds, download invoices & pay online.</p>
        </div>

        @if(session('info'))
        <div class="p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-xl text-xs text-indigo-400 text-center">
            {{ session('info') }}
        </div>
        @endif

        @if($errors->any())
        <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-400 text-center">
            {{ $errors->first() }}
        </div>
        @endif

        <!-- Login Card -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl space-y-6">
            <form action="{{ route('portal.login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Account ID, Email, or Username</label>
                    <input type="text" name="login" value="{{ old('login') }}" required placeholder="e.g. CUST-XXXXXX or user@example.ng" class="w-full px-4 py-3 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Portal Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 bg-slate-800 border-slate-700">
                        <span>Remember me</span>
                    </label>
                    <span class="text-[11px] text-slate-400">Default password: <strong class="text-slate-300">123456</strong></span>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all">
                    Sign In to Portal &rarr;
                </button>
            </form>

            <div class="pt-4 border-t border-slate-800/80 text-center">
                <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-slate-300">
                    Are you an ISP Staff Member? <strong class="text-indigo-400 underline">Admin Login</strong>
                </a>
            </div>
        </div>

    </div>

</body>
</html>
