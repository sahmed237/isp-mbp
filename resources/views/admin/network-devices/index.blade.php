@extends('layouts.app')

@section('title', 'Network Infrastructure Devices')
@section('page_title', 'Network Devices & Routers')

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Network Infrastructure & Hardware</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage core routers, MikroTik devices, GPON OLTs, switches, base stations, and customer CPEs.</p>
        </div>
        @can('network.devices.create')
        <a href="{{ route('network-devices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Register Device</span>
        </a>
        @endcan
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('network-devices.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

            <div class="relative lg:col-span-2">
                <svg class="w-4 h-4 absolute left-3 top-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search name, IP, hostname, vendor..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
            </div>

            <div>
                <select name="device_type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Device Types</option>
                    <option value="mikrotik_router" {{ ($filters['device_type'] ?? '') === 'mikrotik_router' ? 'selected' : '' }}>MikroTik Router</option>
                    <option value="core_router" {{ ($filters['device_type'] ?? '') === 'core_router' ? 'selected' : '' }}>Core Router</option>
                    <option value="switch" {{ ($filters['device_type'] ?? '') === 'switch' ? 'selected' : '' }}>Access Switch</option>
                    <option value="olt" {{ ($filters['device_type'] ?? '') === 'olt' ? 'selected' : '' }}>GPON / EPON OLT</option>
                    <option value="access_point" {{ ($filters['device_type'] ?? '') === 'access_point' ? 'selected' : '' }}>Access Point (AP)</option>
                    <option value="tower" {{ ($filters['device_type'] ?? '') === 'tower' ? 'selected' : '' }}>Tower / Base Station</option>
                    <option value="cpe" {{ ($filters['device_type'] ?? '') === 'cpe' ? 'selected' : '' }}>Customer CPE</option>
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="online" {{ ($filters['status'] ?? '') === 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ ($filters['status'] ?? '') === 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="warning" {{ ($filters['status'] ?? '') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="maintenance" {{ ($filters['status'] ?? '') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 text-white font-semibold text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('network-devices.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-700 text-xs text-center font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Network Devices Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="py-3.5 px-4">Device Name</th>
                        <th class="py-3.5 px-4">Type</th>
                        <th class="py-3.5 px-4">IP Address / Hostname</th>
                        <th class="py-3.5 px-4">Vendor & Model</th>
                        <th class="py-3.5 px-4">Branch / Location</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($devices as $dev)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4">
                            <a href="{{ route('network-devices.show', $dev) }}" class="font-bold text-slate-800 dark:text-slate-100 hover:text-brand-600 dark:hover:text-brand-400 block font-mono text-xs">
                                {{ $dev->name }}
                            </a>
                            <span class="text-[10px] text-slate-400">{{ $dev->location ?? 'Site not specified' }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $dev->device_type_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono">
                            <div class="font-bold text-slate-800 dark:text-slate-200">{{ $dev->ip_address }}</div>
                            <div class="text-[10px] text-slate-400">{{ $dev->hostname ?? 'No DNS' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $dev->vendor ?? 'Generic' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $dev->model ?? 'Model N/A' }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                            {{ $dev->branch?->name ?? 'HQ Core Network' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize {{ $dev->status_badge_class }}">
                                {{ $dev->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @can('network.devices.test_connection')
                                <form action="{{ route('network-devices.test-connection', $dev) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/20 font-bold text-[10px] border border-emerald-500/20" title="Ping & Test API Reachability">
                                        Test Ping
                                    </button>
                                </form>
                                @endcan

                                @can('network.devices.view')
                                <a href="{{ route('network-devices.show', $dev) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800" title="View Device">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @endcan

                                @can('network.devices.update')
                                <a href="{{ route('network-devices.edit', $dev) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit Device">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan

                                @can('network.devices.delete')
                                <form action="{{ route('network-devices.destroy', $dev) }}" method="POST" onsubmit="return confirm('Delete network device {{ $dev->name }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Delete Device">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            No network devices found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
            {{ $devices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
