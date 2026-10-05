<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — ISP Management & Billing Platform</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center p-4 sm:p-6 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-slate-900 to-black">

    <div class="w-full max-w-md space-y-6">

        <!-- Logo & Platform Branding -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-400 items-center justify-center text-white shadow-xl shadow-brand-500/25 mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">ISP-MBP Portal</h1>
            <p class="text-xs sm:text-sm text-slate-400">Broadband Management & Billing Platform</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">

            @if(session('info'))
                <div class="p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs font-medium">
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-medium space-y-1">
                    @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4" id="loginForm">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', 'superadmin@isp-mbp.local') }}"
                            required
                            autocomplete="email"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                            placeholder="user@isp-mbp.local"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            value="Password123!"
                            required
                            autocomplete="current-password"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-brand-600 focus:ring-brand-500">
                        <span class="text-slate-300">Remember session</span>
                    </label>
                    <span class="text-slate-400 text-[11px]">Strict 2FA Ready</span>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 rounded-xl font-bold text-sm bg-brand-600 hover:bg-brand-500 active:bg-brand-700 text-white shadow-lg shadow-brand-600/30 transition-all flex items-center justify-center gap-2"
                >
                    <span>Sign In to Console</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <!-- Quick Demo Role Switcher -->
            <div class="pt-4 border-t border-slate-700/60">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-2.5">Quick Demo Roles (Click to Fill)</span>
                <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                    <button type="button" onclick="setCredentials('superadmin@isp-mbp.local')" class="px-2.5 py-1.5 rounded-lg bg-slate-900/70 hover:bg-slate-700 border border-slate-700 text-left font-medium text-slate-300 hover:text-white transition-colors">
                        👑 Super Admin
                    </button>
                    <button type="button" onclick="setCredentials('orgadmin@isp-mbp.local')" class="px-2.5 py-1.5 rounded-lg bg-slate-900/70 hover:bg-slate-700 border border-slate-700 text-left font-medium text-slate-300 hover:text-white transition-colors">
                        🏢 Org Admin
                    </button>
                    <button type="button" onclick="setCredentials('opsmanager@isp-mbp.local')" class="px-2.5 py-1.5 rounded-lg bg-slate-900/70 hover:bg-slate-700 border border-slate-700 text-left font-medium text-slate-300 hover:text-white transition-colors">
                        ⚙️ Ops Manager
                    </button>
                    <button type="button" onclick="setCredentials('billing@isp-mbp.local')" class="px-2.5 py-1.5 rounded-lg bg-slate-900/70 hover:bg-slate-700 border border-slate-700 text-left font-medium text-slate-300 hover:text-white transition-colors">
                        💳 Billing Manager
                    </button>
                    <button type="button" onclick="setCredentials('netadmin@isp-mbp.local')" class="px-2.5 py-1.5 rounded-lg bg-slate-900/70 hover:bg-slate-700 border border-slate-700 text-left font-medium text-slate-300 hover:text-white transition-colors">
                        🌐 Net Admin
                    </button>
                    <button type="button" onclick="setCredentials('support@isp-mbp.local')" class="px-2.5 py-1.5 rounded-lg bg-slate-900/70 hover:bg-slate-700 border border-slate-700 text-left font-medium text-slate-300 hover:text-white transition-colors">
                        🎧 Support Desk
                    </button>
                    <button type="button" onclick="setCredentials('accountant@isp-mbp.local')" class="px-2.5 py-1.5 rounded-lg bg-slate-900/70 hover:bg-slate-700 border border-slate-700 text-left font-medium text-slate-300 hover:text-white transition-colors">
                        📊 Accountant
                    </button>
                    <button type="button" onclick="setCredentials('readonly@isp-mbp.local')" class="px-2.5 py-1.5 rounded-lg bg-slate-900/70 hover:bg-slate-700 border border-slate-700 text-left font-medium text-slate-300 hover:text-white transition-colors">
                        👁️ Read Only
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center text-xs text-slate-500">
            ISP Management & Billing Platform &bull; Phase 1 Production Foundation
        </div>
    </div>

    <script>
        function setCredentials(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'Password123!';
        }
    </script>
</body>
</html>
