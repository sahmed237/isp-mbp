@extends('layouts.app')

@section('title', 'Customer Locations & Installation Sites')
@section('page_title', 'Customer Locations & Coverage Map')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Physical Sites</div>
            <div class="text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                {{ number_format($totalSites) }}
            </div>
            <div class="text-[11px] text-slate-400">Subscribers with recorded addresses</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Distinct Cities / Towns</div>
            <div class="text-3xl font-black text-brand-600 dark:text-brand-400 tracking-tight">
                {{ number_format($citiesCount) }}
            </div>
            <div class="text-[11px] text-slate-400">Regional coverage footprint</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Geo-Tagged (GPS) Sites</div>
            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                {{ number_format($withGpsCount) }}
            </div>
            <div class="text-[11px] text-slate-400">Pinpointed installation coordinates</div>
        </div>
    </div>

    <!-- Data Table & Filter Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <form action="{{ route('customers.locations.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search address, city, or name..." class="px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">

                <select name="branch_id" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>

                <select name="has_gps" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
                    <option value="">All Sites</option>
                    <option value="yes" {{ request('has_gps') === 'yes' ? 'selected' : '' }}>GPS Pinpointed</option>
                    <option value="no" {{ request('has_gps') === 'no' ? 'selected' : '' }}>Missing GPS</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700">Filter</button>
                @if(request()->anyFilled(['search', 'branch_id', 'has_gps']))
                    <a href="{{ route('customers.locations.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                        <th class="py-3 px-4">Subscriber</th>
                        <th class="py-3 px-4">Installation Physical Address</th>
                        <th class="py-3 px-4">City / State</th>
                        <th class="py-3 px-4">GPS Coordinates</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4 text-right">Map & Navigation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($customers as $c)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="py-3.5 px-4">
                            <a href="{{ route('customers.show', $c) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:text-brand-600 block">
                                {{ $c->full_name }}
                            </a>
                            <span class="text-[10px] font-mono text-brand-600 dark:text-brand-400">{{ $c->account_number }}</span>
                        </td>
                        <td class="py-3.5 px-4 max-w-xs text-slate-700 dark:text-slate-300">
                            {{ $c->installation_address ?? 'Address not specified' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                            {{ $c->city ?? 'N/A' }}, {{ $c->state ?? 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono">
                            @if($c->gps_coordinates)
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $c->gps_coordinates }}</span>
                            @else
                                <span class="text-slate-400 text-[10px] italic">No GPS</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $c->branch?->name ?? 'Headquarters' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($c->gps_coordinates)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($c->gps_coordinates) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/20 text-xs font-bold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Google Maps &nearr;</span>
                            </a>
                            @else
                            <a href="{{ route('customers.edit', $c) }}" class="text-slate-400 hover:text-brand-600 text-xs font-semibold">
                                + Add GPS
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="font-bold">No customer installation locations found</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
