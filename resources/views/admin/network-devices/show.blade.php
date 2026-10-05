@extends('layouts.app')

@section('title', "Device: {$device->name}")
@section('page_title', "Network Device — {$device->name}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Device Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize {{ $device->status_badge_class }}">
                    {{ $device->status }}
                </span>
                <span class="px-2 py-0.5 rounded font-mono text-xs font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    {{ $device->device_type_label }}
                </span>
            </div>
            <h2 class="text-2xl font-black font-mono text-slate-900 dark:text-slate-100 tracking-tight mt-1">{{ $device->name }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $device->vendor }} {{ $device->model }} &bull; Located at: {{ $device->location ?? 'Site not specified' }}</p>
        </div>

        <div class="flex items-center gap-2">
            @can('network.devices.test_connection')
            <form action="{{ route('network-devices.test-connection', $device) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Test Ping</span>
                </button>
            </form>
            @endcan

            @can('network.devices.update')
            <a href="{{ route('network-devices.edit', $device) }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors">
                Edit
            </a>
            @endcan

            <a href="{{ route('network-devices.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
                &larr; Back
            </a>
        </div>
    </div>

    <!-- Device Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- IP & Network Addressing -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Network Addressing</h3>
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400">Management IPv4:</span>
                    <span class="font-mono font-bold text-base text-brand-600 dark:text-brand-400">{{ $device->ip_address }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400">Hostname / FQDN:</span>
                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ $device->hostname ?? 'None' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400">MAC Address:</span>
                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ $device->mac_address ?? 'Not recorded' }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-slate-400">Operational Branch:</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $device->branch?->name ?? 'HQ Datacenter' }}</span>
                </div>
            </div>
        </div>

        <!-- Management Ports & API Engine -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">API & Service Ports</h3>
            <div class="grid grid-cols-3 gap-3 text-center text-xs">
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">RouterOS API</span>
                    <span class="font-mono font-extrabold text-sm text-slate-800 dark:text-slate-200">{{ $device->api_port }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">SSH</span>
                    <span class="font-mono font-extrabold text-sm text-slate-800 dark:text-slate-200">{{ $device->ssh_port }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">WebUI</span>
                    <span class="font-mono font-extrabold text-sm text-slate-800 dark:text-slate-200">{{ $device->web_port }}</span>
                </div>
            </div>

            <div class="pt-2 text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">API Username:</span>
                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ $device->username ?? 'admin' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Last Seen / Polled:</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}</span>
                </div>
            </div>
        </div>

    </div>

    @if($device->notes)
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-2 text-xs">
        <h3 class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Configuration & Interface Notes</h3>
        <p class="text-slate-700 dark:text-slate-300 leading-relaxed font-mono bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl">{{ $device->notes }}</p>
    </div>
    @endif

</div>
@endsection
