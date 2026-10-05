<!DOCTYPE html>
<html lang="en" x-data="{
    darkMode: localStorage.getItem('portal_theme') === 'dark' || (!('portal_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    mobileMenu: false,
    toggleTheme() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('portal_theme', this.darkMode ? 'dark' : 'light');
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

    <title>{{ config('app.name', 'ISP Platform') }} — @yield('title', 'Subscriber Portal')</title>

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
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
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
    <nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo & Brand -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white shadow-md shadow-brand-500/20 font-black">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-sm sm:text-base tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                                Subscriber Portal
                                <span class="text-[9px] uppercase font-bold tracking-widest bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-1.5 py-0.5 rounded border border-emerald-500/20">Self-Service</span>
                            </span>
                            <span class="text-[10px] text-slate-400 truncate">{{ Auth::guard('customer')->user()->account_number ?? 'Broadband' }}</span>
                        </div>
                    </a>

                    <!-- Desktop Nav Links -->
                    <div class="hidden md:flex items-center gap-1 text-xs font-bold">
                        <a href="{{ route('portal.dashboard') }}" class="px-3.5 py-2 rounded-xl transition-all {{ request()->routeIs('portal.dashboard') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('portal.invoices') }}" class="px-3.5 py-2 rounded-xl transition-all {{ request()->routeIs('portal.invoices*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            Invoices & Pay
                        </a>
                        <a href="{{ route('portal.payments') }}" class="px-3.5 py-2 rounded-xl transition-all {{ request()->routeIs('portal.payments*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            Payment Receipts
                        </a>
                        <a href="{{ route('portal.hotspot') }}" class="px-3.5 py-2 rounded-xl transition-all {{ request()->routeIs('portal.hotspot*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            Hotspot Vouchers
                        </a>
                        <a href="{{ route('portal.profile') }}" class="px-3.5 py-2 rounded-xl transition-all {{ request()->routeIs('portal.profile*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            Router / PPPoE Info
                        </a>
                    </div>
                </div>

                <!-- Right Action Bar -->
                <div class="flex items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button @click="toggleTheme()" class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                        <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="darkMode" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>

                    <!-- User Pill & Logout -->
                    <div class="hidden sm:flex items-center gap-2 pl-3 border-l border-slate-200 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-full bg-brand-500/10 text-brand-600 font-bold flex items-center justify-center text-xs">
                            {{ substr(Auth::guard('customer')->user()->first_name, 0, 1) }}
                        </div>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ Auth::guard('customer')->user()->first_name }}</span>
                    </div>

                    <form action="{{ route('portal.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 text-xs font-semibold transition-colors">
                            Logout
                        </button>
                    </form>

                    <!-- Mobile Hamburger -->
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenu" x-cloak class="md:hidden border-t border-slate-200 dark:border-slate-800 px-4 py-3 space-y-1 text-xs font-bold">
            <a href="{{ route('portal.dashboard') }}" class="block px-3 py-2 rounded-xl {{ request()->routeIs('portal.dashboard') ? 'bg-brand-600 text-white' : 'text-slate-700 dark:text-slate-300' }}">Dashboard</a>
            <a href="{{ route('portal.invoices') }}" class="block px-3 py-2 rounded-xl {{ request()->routeIs('portal.invoices*') ? 'bg-brand-600 text-white' : 'text-slate-700 dark:text-slate-300' }}">Invoices & Pay</a>
            <a href="{{ route('portal.payments') }}" class="block px-3 py-2 rounded-xl {{ request()->routeIs('portal.payments*') ? 'bg-brand-600 text-white' : 'text-slate-700 dark:text-slate-300' }}">Payment Receipts</a>
            <a href="{{ route('portal.hotspot') }}" class="block px-3 py-2 rounded-xl {{ request()->routeIs('portal.hotspot*') ? 'bg-brand-600 text-white' : 'text-slate-700 dark:text-slate-300' }}">Hotspot Vouchers</a>
            <a href="{{ route('portal.profile') }}" class="block px-3 py-2 rounded-xl {{ request()->routeIs('portal.profile*') ? 'bg-brand-600 text-white' : 'text-slate-700 dark:text-slate-300' }}">Router Credentials</a>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold flex items-center justify-between mb-4">
            <span>&check; {{ session('success') }}</span>
        </div>
        @endif

        @if(session('info'))
        <div class="p-4 rounded-2xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-400 text-xs font-semibold flex items-center justify-between mb-4">
            <span>{{ session('info') }}</span>
        </div>
        @endif

        @if(session('error') || $errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs font-semibold mb-4">
            {{ session('error') ?? $errors->first() }}
        </div>
        @endif
    </div>

    <!-- Main View Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 dark:border-slate-800 py-6 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. High-Speed Broadband & Hotspot Service Network.</p>
    </footer>

</body>
</html>
