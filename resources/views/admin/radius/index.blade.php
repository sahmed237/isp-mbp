@extends('layouts.app')

@section('title', 'FreeRADIUS AAA Management')
@section('page_title', 'FreeRADIUS AAA Infrastructure')

@section('content')
<div class="space-y-6">

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
    </div>
    @endif

    <!-- AAA Live Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Card 1: Active Sessions -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Online Subscribers</span>
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
            </div>
            <div class="text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                {{ number_format($stats['active_sessions']) }}
            </div>
            <div class="text-[11px] text-slate-400">Live active RADIUS sessions</div>
        </div>

        <!-- Card 2: NAS Routers -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">NAS Routers</span>
                <div class="p-1.5 rounded-lg bg-blue-500/10 text-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                {{ number_format($stats['total_nas']) }}
            </div>
            <div class="text-[11px] text-slate-400">Registered MikroTik / BNGs</div>
        </div>

        <!-- Card 3: Today's Auth Success -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Auth Accepted</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">24h</span>
            </div>
            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                {{ number_format($stats['today_success']) }}
            </div>
            <div class="text-[11px] text-slate-400">Successful authentications</div>
        </div>

        <!-- Card 4: Today's Auth Rejections -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Auth Rejected</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400">24h</span>
            </div>
            <div class="text-3xl font-black text-rose-600 dark:text-rose-400 tracking-tight">
                {{ number_format($stats['today_failed']) }}
            </div>
            <div class="text-[11px] text-slate-400">Failed password / blocked</div>
        </div>

        <!-- Card 5: Bandwidth Today -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Traffic Today</span>
                <div class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-brand-600 dark:text-brand-400 tracking-tight truncate">
                {{ $stats['today_total_formatted'] }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between">
                <span>&darr; {{ $stats['today_download_formatted'] }}</span>
                <span>&uarr; {{ $stats['today_upload_formatted'] }}</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation & Filter Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">

        <!-- Tab Controls & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-2 overflow-x-auto text-xs font-bold">
                <a href="{{ route('radius.index', ['tab' => 'sessions']) }}"
                   class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ $tab === 'sessions' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <span class="w-2 h-2 rounded-full {{ $stats['active_sessions'] > 0 ? 'bg-emerald-400 animate-pulse' : 'bg-slate-400' }}"></span>
                    <span>Active Sessions ({{ $stats['active_sessions'] }})</span>
                </a>
                <a href="{{ route('radius.index', ['tab' => 'nas']) }}"
                   class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ $tab === 'nas' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    <span>NAS Clients / Routers ({{ $stats['total_nas'] }})</span>
                </a>
                <a href="{{ route('radius.index', ['tab' => 'auth-logs']) }}"
                   class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ $tab === 'auth-logs' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Authentication Logs</span>
                </a>
                <a href="{{ route('radius.index', ['tab' => 'accounting']) }}"
                   class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ $tab === 'accounting' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Accounting History</span>
                </a>
            </div>

            @if($tab === 'nas')
            <button onclick="document.getElementById('modal-add-nas').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add NAS Router</span>
            </button>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: ACTIVE ONLINE SESSIONS             -->
        <!-- ========================================== -->
        @if($tab === 'sessions')
        <div class="space-y-4">
            <!-- Search Filter Bar -->
            <form action="{{ route('radius.index') }}" method="GET" class="flex items-center gap-3">
                <input type="hidden" name="tab" value="sessions">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search active subscriber by username, Framed IP, NAS IP, or MAC address..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700">Filter</button>
                @if(request('search'))
                    <a href="{{ route('radius.index', ['tab' => 'sessions']) }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                            <th class="py-3 px-4">Subscriber</th>
                            <th class="py-3 px-4">Framed IP</th>
                            <th class="py-3 px-4">NAS Router</th>
                            <th class="py-3 px-4">Calling MAC</th>
                            <th class="py-3 px-4">Connected At</th>
                            <th class="py-3 px-4">Uptime</th>
                            <th class="py-3 px-4">Download / Upload</th>
                            <th class="py-3 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($activeSessions as $s)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $s->username }}</div>
                                <span class="text-[10px] font-mono text-slate-400">SID: {{ Str::limit($s->acctsessionid, 12) }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-brand-600 dark:text-brand-400">
                                {{ $s->framedipaddress ?? 'Dynamic' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-300">
                                {{ $s->nasipaddress }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px]">
                                {{ $s->callingstationid ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $s->acctstarttime?->format('M d, H:i') ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $s->formatted_session_time }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-medium">
                                <span class="text-emerald-500">&darr; {{ $s->formatted_output_octets }}</span>
                                <span class="text-slate-400 mx-1">/</span>
                                <span class="text-blue-500">&uarr; {{ $s->formatted_input_octets }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Online
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div class="font-bold">No active subscriber sessions</div>
                                <div class="text-[11px] mt-0.5">When subscribers authenticate via MikroTik PPPoE/Hotspot, active sessions will stream here in real time.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($activeSessions->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $activeSessions->links() }}
            </div>
            @endif
        </div>
        @endif

        <!-- ========================================== -->
        <!-- TAB 2: NAS CLIENTS / ROUTERS               -->
        <!-- ========================================== -->
        @if($tab === 'nas')
        <div class="space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                            <th class="py-3 px-4">Router Name</th>
                            <th class="py-3 px-4">NAS IP Address</th>
                            <th class="py-3 px-4">Vendor / Type</th>
                            <th class="py-3 px-4">Shared Secret</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($nasClients as $nas)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-slate-100">
                                {{ $nas->shortname }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-brand-600 dark:text-brand-400">
                                {{ $nas->nasname }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $nas->type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">
                                <span class="text-xs">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
                                <span class="text-[10px] text-slate-400 ml-1">({{ Str::mask($nas->secret, '*', 2, -2) }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $nas->description ?? 'No description' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            onclick="openEditNasModal({{ json_encode($nas) }})"
                                            class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('radius.nas.destroy', $nas) }}" method="POST" onsubmit="return confirm('Remove NAS client {{ $nas->shortname }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-500/10 text-slate-400 hover:text-rose-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="font-bold">No NAS Routers registered in PostgreSQL yet</div>
                                <div class="text-[11px] mt-0.5">Click "Add NAS Router" to register your MikroTik or access gateway.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($nasClients->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $nasClients->links() }}
            </div>
            @endif
        </div>
        @endif

        <!-- ========================================== -->
        <!-- TAB 3: AUTHENTICATION LOGS                -->
        <!-- ========================================== -->
        @if($tab === 'auth-logs')
        <div class="space-y-4">
            <form action="{{ route('radius.index') }}" method="GET" class="flex items-center gap-3">
                <input type="hidden" name="tab" value="auth-logs">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search auth attempts by subscriber username or Calling MAC..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <select name="status" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                    <option value="">All Attempts</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Access-Accept (Success)</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Access-Reject (Failed)</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700">Filter</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('radius.index', ['tab' => 'auth-logs']) }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                            <th class="py-3 px-4">Attempt Timestamp</th>
                            <th class="py-3 px-4">Subscriber Username</th>
                            <th class="py-3 px-4">Result / Response</th>
                            <th class="py-3 px-4">Calling MAC Address</th>
                            <th class="py-3 px-4">Called Station</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($authLogs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                {{ $log->authdate?->format('Y-m-d H:i:s') ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-slate-100">
                                {{ $log->username }}
                            </td>
                            <td class="py-3 px-4">
                                @if($log->is_success)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400">
                                        &check; {{ $log->reply }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20 dark:text-rose-400">
                                        &times; {{ $log->reply }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                {{ $log->callingstationid ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                {{ $log->calledstationid ?? 'N/A' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="font-bold">No authentication events found</div>
                                <div class="text-[11px] mt-0.5">Authentication attempts received by FreeRADIUS are logged automatically in table radpostauth.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($authLogs->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $authLogs->links() }}
            </div>
            @endif
        </div>
        @endif

        <!-- ========================================== -->
        <!-- TAB 4: ACCOUNTING HISTORY                  -->
        <!-- ========================================== -->
        @if($tab === 'accounting')
        <div class="space-y-4">
            <form action="{{ route('radius.index') }}" method="GET" class="flex items-center gap-3">
                <input type="hidden" name="tab" value="accounting">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search historical sessions by username, Framed IP, or MAC..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700">Filter</button>
                @if(request('search'))
                    <a href="{{ route('radius.index', ['tab' => 'accounting']) }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                            <th class="py-3 px-4">Subscriber</th>
                            <th class="py-3 px-4">Framed IP</th>
                            <th class="py-3 px-4">Session Duration</th>
                            <th class="py-3 px-4">Upload / Download</th>
                            <th class="py-3 px-4">Total Usage</th>
                            <th class="py-3 px-4">Terminated At</th>
                            <th class="py-3 px-4 text-right">Cause</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($historicalSessions as $h)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-slate-100">
                                {{ $h->username }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-300">
                                {{ $h->framedipaddress ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-slate-300">
                                {{ $h->formatted_session_time }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">
                                <span class="text-blue-500">&uarr; {{ $h->formatted_input_octets }}</span>
                                <span class="text-slate-300 dark:text-slate-700 mx-1">/</span>
                                <span class="text-emerald-500">&darr; {{ $h->formatted_output_octets }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-slate-100">
                                {{ $h->formatted_total_octets }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $h->acctstoptime?->format('M d, Y H:i') ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    {{ $h->acctterminatecause ?? 'Normal' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="font-bold">No completed accounting sessions</div>
                                <div class="text-[11px] mt-0.5">When subscribers disconnect or interim sessions close, full metrics appear here.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($historicalSessions->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $historicalSessions->links() }}
            </div>
            @endif
        </div>
        @endif

    </div>

</div>

<!-- Modal: Add NAS Router -->
<div id="modal-add-nas" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-lg w-full space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Register New NAS Router</h3>
            <button type="button" onclick="document.getElementById('modal-add-nas').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-base">&times;</button>
        </div>

        <form action="{{ route('radius.nas.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">NAS Identifier / Short Name *</label>
                <input type="text" name="shortname" placeholder="e.g. Core-Mikrotik-01" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">NAS IP Address / CIDR *</label>
                    <input type="text" name="nasname" placeholder="e.g. 192.168.88.1 or 0.0.0.0/0" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">NAS Vendor / Type *</label>
                    <select name="type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="mikrotik" selected>MikroTik RouterOS</option>
                        <option value="cisco">Cisco IOS</option>
                        <option value="huawei">Huawei BNG</option>
                        <option value="other">Generic / Other</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">RADIUS Shared Secret *</label>
                <input type="text" name="secret" placeholder="e.g. testing123 or strong_radius_secret" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-[10px] text-slate-400 mt-1 block">This exact secret must match the secret configured in MikroTik /radius menu.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description / Location</label>
                <input type="text" name="description" placeholder="e.g. Central NOC Gateway - PPPoE Server" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('modal-add-nas').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-500">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30">Save NAS Router</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit NAS Router -->
<div id="modal-edit-nas" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-lg w-full space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Edit NAS Router</h3>
            <button type="button" onclick="document.getElementById('modal-edit-nas').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-base">&times;</button>
        </div>

        <form id="form-edit-nas" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">NAS Identifier / Short Name *</label>
                <input type="text" id="edit-shortname" name="shortname" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">NAS IP Address / CIDR *</label>
                    <input type="text" id="edit-nasname" name="nasname" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">NAS Vendor / Type *</label>
                    <select id="edit-type" name="type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="mikrotik">MikroTik RouterOS</option>
                        <option value="cisco">Cisco IOS</option>
                        <option value="huawei">Huawei BNG</option>
                        <option value="other">Generic / Other</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">RADIUS Shared Secret *</label>
                <input type="text" id="edit-secret" name="secret" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description / Location</label>
                <input type="text" id="edit-description" name="description" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('modal-edit-nas').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-500">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30">Update NAS Router</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditNasModal(nas) {
    const form = document.getElementById('form-edit-nas');
    form.action = `/radius/nas/${nas.id}`;
    document.getElementById('edit-shortname').value = nas.shortname || '';
    document.getElementById('edit-nasname').value = nas.nasname || '';
    document.getElementById('edit-type').value = nas.type || 'mikrotik';
    document.getElementById('edit-secret').value = nas.secret || '';
    document.getElementById('edit-description').value = nas.description || '';
    document.getElementById('modal-edit-nas').classList.remove('hidden');
}
</script>
@endsection
