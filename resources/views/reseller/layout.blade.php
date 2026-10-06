<!DOCTYPE html>
<html lang="en" x-data="{
    darkMode: localStorage.getItem('reseller_theme') === 'dark' || (!('reseller_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    mobileMenu: false,
    fundWalletModal: false,
    toggleTheme() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('reseller_theme', this.darkMode ? 'dark' : 'light');
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" :class="{ 'dark': darkMode }" class="h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ISP Platform') }} — @yield('title', 'Reseller & Agent Portal')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full flex flex-col">

    <!-- Top Navigation Bar -->
    <nav class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo & Brand -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('reseller.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-md shadow-emerald-500/20 font-black">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-sm sm:text-base tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                                Agent Portal
                                <span class="text-[9px] uppercase font-bold tracking-widest bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-1.5 py-0.5 rounded border border-emerald-500/20">Authorized Reseller</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono truncate">{{ Auth::guard('reseller')->user()?->reseller_code }}</span>
                        </div>
                    </a>

                    <!-- Desktop Nav Links -->
                    <div class="hidden md:flex items-center gap-1 text-xs font-bold">
                        <a href="{{ route('reseller.dashboard') }}" class="px-3.5 py-2 rounded-xl transition-all {{ request()->routeIs('reseller.dashboard') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('reseller.vouchers') }}" class="px-3.5 py-2 rounded-xl transition-all {{ request()->routeIs('reseller.vouchers*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            Generate Vouchers & Batches
                        </a>
                        <a href="{{ route('reseller.profile') }}" class="px-3.5 py-2 rounded-xl transition-all {{ request()->routeIs('reseller.profile*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            Store Profile
                        </a>
                    </div>
                </div>

                <!-- Right Side Actions & Wallet Pill -->
                <div class="flex items-center gap-3">

                    <!-- Live Wallet Balance Pill -->
                    @if(Auth::guard('reseller')->check())
                    <div class="flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl px-3 py-1.5">
                        <div class="flex flex-col text-right">
                            <span class="text-[9px] uppercase tracking-wider font-bold text-emerald-600 dark:text-emerald-400">Prepaid Wallet</span>
                            <span class="text-xs sm:text-sm font-extrabold font-mono text-emerald-700 dark:text-emerald-300">
                                ₦{{ number_format((float) Auth::guard('reseller')->user()->balance, 2) }}
                            </span>
                        </div>
                        <button @click="fundWalletModal = true" title="Top up wallet balance" class="p-1 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                    @endif

                    <!-- Dark Mode Toggle -->
                    <button @click="toggleTheme()" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Toggle theme">
                        <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>

                    <!-- Reseller Dropdown -->
                    @if(Auth::guard('reseller')->check())
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                                {{ strtoupper(substr(Auth::guard('reseller')->user()->business_name, 0, 1)) }}
                            </div>
                            <div class="hidden lg:flex flex-col text-left">
                                <span class="text-xs font-bold leading-tight truncate max-w-[120px]">{{ Auth::guard('reseller')->user()->business_name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ Auth::guard('reseller')->user()->reseller_code }}</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 py-1.5 z-50 text-xs">
                            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700/60">
                                <p class="font-bold text-slate-900 dark:text-white truncate">{{ Auth::guard('reseller')->user()->business_name }}</p>
                                <p class="text-[10px] text-slate-400">{{ Auth::guard('reseller')->user()->email }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                    {{ Auth::guard('reseller')->user()->status }}
                                </span>
                            </div>
                            <a href="{{ route('reseller.profile') }}" class="flex items-center gap-2 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Account & Security
                            </a>
                            <form action="{{ route('reseller.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-left font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Mobile Hamburger -->
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Nav Menu -->
            <div x-show="mobileMenu" x-cloak class="md:hidden border-t border-slate-200 dark:border-slate-800 py-3 space-y-1 text-xs font-bold">
                <a href="{{ route('reseller.dashboard') }}" class="block px-3 py-2 rounded-xl {{ request()->routeIs('reseller.dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-600 dark:text-slate-300' }}">
                    Dashboard
                </a>
                <a href="{{ route('reseller.vouchers') }}" class="block px-3 py-2 rounded-xl {{ request()->routeIs('reseller.vouchers*') ? 'bg-emerald-600 text-white' : 'text-slate-600 dark:text-slate-300' }}">
                    Generate Vouchers & Batches
                </a>
                <a href="{{ route('reseller.profile') }}" class="block px-3 py-2 rounded-xl {{ request()->routeIs('reseller.profile*') ? 'bg-emerald-600 text-white' : 'text-slate-600 dark:text-slate-300' }}">
                    Store Profile
                </a>
            </div>
        </div>
    </nav>

    <!-- Top Up Wallet Modal (Reusable Across Portal) -->
    @if(Auth::guard('reseller')->check())
    <div x-show="fundWalletModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="fundWalletModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Top Up Prepaid Wallet</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Current balance: ₦{{ number_format((float) Auth::guard('reseller')->user()->balance, 2) }}</p>
                    </div>
                </div>
                <button @click="fundWalletModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">&times;</button>
            </div>

            <form action="{{ route('reseller.wallet.topup') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Top Up Amount (₦) *</label>
                    <div class="grid grid-cols-3 gap-2 mb-2">
                        <button type="button" @click="$refs.topupAmount.value = 5000" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">₦5,000</button>
                        <button type="button" @click="$refs.topupAmount.value = 10000" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">₦10,000</button>
                        <button type="button" @click="$refs.topupAmount.value = 25000" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">₦25,000</button>
                    </div>
                    <input type="number" x-ref="topupAmount" name="amount" min="500" step="100" placeholder="e.g. 5000" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[10px] text-slate-400 mt-1">Minimum top-up is ₦500.00.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Payment Gateway *</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-emerald-500">
                            <input type="radio" name="payment_method" value="paystack" checked class="text-emerald-600 focus:ring-emerald-500">
                            <div class="flex items-center justify-between w-full">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Paystack Checkout</span>
                                <span class="text-[10px] text-slate-400">Cards, USSD, Bank Transfer</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-emerald-500">
                            <input type="radio" name="payment_method" value="monnify" class="text-emerald-600 focus:ring-emerald-500">
                            <div class="flex items-center justify-between w-full">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Monnify Gateway</span>
                                <span class="text-[10px] text-slate-400">Dynamic Virtual Accounts & Cards</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="fundWalletModal = false" class="w-1/3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/20 transition-all">
                        Proceed to Checkout &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Flash Notifications -->
        @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-xs text-emerald-800 dark:text-emerald-300">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="flex-1 font-medium">{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl flex items-center gap-3 text-xs text-rose-800 dark:text-rose-300">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="flex-1 font-medium">{{ session('error') }}</div>
        </div>
        @endif

        @if(session('info'))
        <div class="p-4 bg-sky-500/10 border border-sky-500/20 rounded-2xl flex items-center gap-3 text-xs text-sky-800 dark:text-sky-300">
            <svg class="w-5 h-5 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="flex-1 font-medium">{{ session('info') }}</div>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white/50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-800 py-6 text-center text-xs text-slate-400">
        <div class="max-w-7xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'ISP Platform') }} — Authorized Voucher Reseller & Agent Portal.</p>
        </div>
    </footer>

</body>
</html>
