<header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30 transition-colors">

    <!-- Left: Mobile Sidebar Trigger + Breadcrumbs -->
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div class="flex items-center gap-2 text-sm">
            <span class="text-slate-400 dark:text-slate-500 hidden sm:inline">ISP-MBP</span>
            <span class="text-slate-300 dark:text-slate-600 hidden sm:inline">/</span>
            <h1 class="font-bold text-slate-800 dark:text-slate-100 text-base sm:text-lg">
                @yield('page_title', 'Dashboard')
            </h1>
        </div>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-2 sm:gap-4">

        <!-- Global Search Trigger -->
        <button @click="searchModalOpen = true" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700/80 text-xs sm:text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <span class="hidden md:inline">Quick Search...</span>
            <kbd class="hidden md:inline px-1.5 py-0.5 text-[10px] font-mono bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-slate-400">⌘K</kbd>
        </button>

        <!-- Dark Mode Toggle Button -->
        <button @click="toggleTheme()" type="button" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Toggle Dark/Light Mode">
            <template x-if="!darkMode">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </template>
            <template x-if="darkMode">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </template>
        </button>

        <!-- Notifications Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="relative p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-900"></span>
            </button>

            <!-- Notifications Menu -->
            <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl py-3 z-50">
                <div class="px-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-100">System Notifications</span>
                    <span class="text-[10px] font-semibold bg-brand-500/10 text-brand-600 dark:text-brand-400 px-2 py-0.5 rounded-full">2 Unread</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-72 overflow-y-auto">
                    <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 flex gap-3 text-xs cursor-pointer">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">KAN-TOWER-PTP-GRA Warning</div>
                            <div class="text-slate-500 dark:text-slate-400 mt-0.5">Heavy rain fade detected on 60GHz millimeter wave link.</div>
                            <div class="text-[10px] text-slate-400 mt-1">12 minutes ago</div>
                        </div>
                    </div>
                    <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 flex gap-3 text-xs cursor-pointer">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">New Subscriber Provisioned</div>
                            <div class="text-slate-500 dark:text-slate-400 mt-0.5">Ibrahim Shehu subscribed to Home Standard 10 Mbps.</div>
                            <div class="text-[10px] text-slate-400 mt-1">45 minutes ago</div>
                        </div>
                    </div>
                </div>
                @can('audit_logs.view')
                <div class="px-4 pt-2 border-t border-slate-100 dark:border-slate-800 text-center">
                    <a href="{{ route('audit-logs.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-medium">View Complete Audit Trail &rarr;</a>
                </div>
                @endcan
            </div>
        </div>

        <!-- User Menu Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="hidden md:flex flex-col text-left">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight">{{ Auth::user()->name }}</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ Auth::user()->roles->first()?->name ?? 'Staff' }}</span>
                </div>
                <svg class="w-4 h-4 text-slate-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-56 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl py-2 z-50 text-xs">
                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ Auth::user()->name }}</div>
                    <div class="text-slate-400 truncate">{{ Auth::user()->email }}</div>
                    <div class="mt-1 text-[10px] font-semibold text-brand-600 dark:text-brand-400">{{ Auth::user()->organization?->name ?? 'System Master' }}</div>
                </div>

                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>My Profile & Security</span>
                </a>

                @can('audit_logs.view')
                <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>My Audit Activity</span>
                </a>
                @endcan

                <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 text-left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
