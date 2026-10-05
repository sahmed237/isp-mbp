<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 border-r border-slate-800 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:inset-auto lg:translate-x-0 select-none shadow-xl"
>
    <!-- Brand / Tenant Header -->
    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-950/40">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/25 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex flex-col">
                <span class="font-extrabold text-base tracking-tight text-white flex items-center gap-1.5">
                    ISP-MBP
                    <span class="text-[10px] uppercase font-bold tracking-widest bg-brand-500/20 text-brand-300 border border-brand-500/30 px-1.5 py-0.5 rounded">v1.0</span>
                </span>
                <span class="text-[11px] text-slate-400 truncate max-w-[150px]">
                    {{ Auth::user()->organization?->name ?? 'System Control' }}
                </span>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Active Tenant / Branch Indicator -->
    <div class="px-5 py-3 bg-slate-800/40 border-b border-slate-800/60 flex items-center justify-between text-xs">
        <div class="flex items-center gap-2 truncate">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-slate-400 truncate">{{ Auth::user()->branch?->name ?? 'All Branches' }}</span>
        </div>
        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded bg-slate-700/80 text-slate-300">
            {{ Auth::user()->roles->first()?->name ?? 'Staff' }}
        </span>
    </div>

    <!-- Navigation Scrollable Area -->
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6 text-sm">

        <!-- Core Dashboard -->
        <div>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'hover:bg-slate-800/70 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Section: CUSTOMERS -->
        <div class="space-y-1">
            <h3 class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Customers</h3>
            @can('customers.view')
            <a href="{{ route('customers.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('customers.index') || request()->routeIs('customers.show') || request()->routeIs('customers.create') || request()->routeIs('customers.edit') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Subscribers</span>
                </div>
            </a>
            @endcan
            <a href="{{ route('customers.groups.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('customers.groups.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-slate-300 hover:bg-slate-800/40' }}">
                <span class="pl-7">Customer Groups</span>
            </a>
            <a href="{{ route('customers.documents.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('customers.documents.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-slate-300 hover:bg-slate-800/40' }}">
                <span class="pl-7">Customer Documents</span>
            </a>
            <a href="{{ route('customers.locations.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('customers.locations.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-slate-300 hover:bg-slate-800/40' }}">
                <span class="pl-7">Customer Locations</span>
            </a>
        </div>

        <!-- Section: SERVICES -->
        <div class="space-y-1">
            <h3 class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Services</h3>
            @can('packages.view')
            <a href="{{ route('packages.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('packages.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Internet Packages</span>
                </div>
            </a>
            @endcan
            <a href="{{ route('branches.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('branches.*') ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-slate-300 hover:bg-slate-800/40' }}">
                <span class="pl-7">Service Areas</span>
            </a>
        </div>

        <!-- Section: NETWORK -->
        <div class="space-y-1">
            <h3 class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Network</h3>
            @can('network.devices.view')
            <a href="{{ route('network-devices.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('network-devices.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    <span>Network Devices</span>
                </div>
            </a>
            @endcan
            <a href="{{ route('modules.placeholder', 'mikrotik-api') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-slate-400 hover:text-slate-300 hover:bg-slate-800/40">
                <span class="pl-7">MikroTik RouterOS</span>
                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Phase 5</span>
            </a>
            <a href="{{ route('modules.placeholder', 'infrastructure') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-slate-400 hover:text-slate-300 hover:bg-slate-800/40">
                <span class="pl-7">Fibre & Towers</span>
                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Phase 6</span>
            </a>
        </div>

        <!-- Section: BILLING & SUBSCRIPTIONS -->
        <div class="space-y-1">
            <h3 class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Billing & Plans</h3>
            <a href="{{ route('subscriptions.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('subscriptions.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Subscriptions</span>
                </div>
                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-brand-500/20 text-brand-300 font-bold border border-brand-500/30">Active</span>
            </a>
            <a href="{{ route('invoices.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('invoices.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                    <span>Invoices</span>
                </div>
            </a>
            <a href="{{ route('payments.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('payments.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Payments</span>
                </div>
            </a>
            <a href="{{ route('vouchers.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('vouchers.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    <span>Hotspot Vouchers</span>
                </div>
                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">RADIUS</span>
            </a>
        </div>

        <!-- Section: RADIUS -->
        <div class="space-y-1">
            <h3 class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">RADIUS</h3>
            <a href="{{ route('radius.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('radius.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <span>FreeRADIUS AAA</span>
                </div>
                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">Live</span>
            </a>
        </div>

        <!-- Section: SUPPORT -->
        <div class="space-y-1">
            <h3 class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Support</h3>
            <a href="{{ route('modules.placeholder', 'tickets') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-slate-400 hover:text-slate-300 hover:bg-slate-800/40">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Support Tickets</span>
                </div>
                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Phase 2</span>
            </a>
        </div>

        <!-- Section: REPORTS -->
        <div class="space-y-1">
            <h3 class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Reports</h3>
            <a href="{{ route('modules.placeholder', 'reports') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-slate-400 hover:text-slate-300 hover:bg-slate-800/40">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Financial & Usage</span>
                </div>
                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Phase 8</span>
            </a>
            @can('audit_logs.view')
            <a href="{{ route('audit-logs.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('audit-logs.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Audit Trail Logs</span>
                </div>
            </a>
            @endcan
        </div>

        <!-- Section: SYSTEM -->
        <div class="space-y-1">
            <h3 class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">System Administration</h3>
            @can('users.view')
            <a href="{{ route('users.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Staff Users</span>
                </div>
            </a>
            @endcan
            @can('roles.view')
            <a href="{{ route('roles.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('roles.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Roles & RBAC</span>
                </div>
            </a>
            @endcan
            @can('organizations.view')
            <a href="{{ route('organizations.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('organizations.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Organizations</span>
                </div>
            </a>
            @endcan
            @can('branches.view')
            <a href="{{ route('branches.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('branches.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Branches</span>
                </div>
            </a>
            @endcan
            @if(Auth::user()->isSuperAdmin())
            <a href="{{ route('settings.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('settings.*') ? 'bg-brand-600 text-white font-medium shadow-md shadow-brand-600/30' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>System Settings</span>
                </div>
                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300 font-bold">Admin</span>
            </a>
            @endif
        </div>

    <!-- Customer Portal Quick Link -->
    <div class="px-4 py-2.5 border-t border-slate-800/60 bg-slate-900/50">
        <a href="{{ route('portal.login') }}" target="_blank" class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-800/80 hover:bg-brand-600/20 text-brand-300 border border-brand-500/30 text-xs font-semibold transition-all group">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Subscriber Portal</span>
            </div>
            <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-brand-500/20 text-brand-300">&rarr;</span>
        </a>
    </div>

    <!-- User Mini Profile Footer -->
    <div class="p-3 border-t border-slate-800/80 bg-slate-950/40 flex items-center justify-between">
        <a href="{{ route('profile') }}" class="flex items-center gap-3 truncate group">
            <div class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center font-bold text-brand-400 group-hover:ring-2 ring-brand-500/50 transition-all flex-shrink-0">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="truncate">
                <div class="text-xs font-semibold text-slate-200 truncate group-hover:text-white">{{ Auth::user()->name }}</div>
                <div class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</div>
            </div>
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800/60 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>
</aside>
