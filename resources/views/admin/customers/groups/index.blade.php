@extends('layouts.app')

@section('title', 'Customer Groups & Segmentation')
@section('page_title', 'Customer Groups & Account Types')

@section('content')
<div class="space-y-6">

    <!-- Segment Breakdown Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($groups as $typeKey => $grp)
        <a href="{{ route('customers.groups.index', ['type' => $typeKey]) }}"
           class="bg-white dark:bg-slate-900 border rounded-3xl p-5 shadow-sm space-y-3 transition-all hover:scale-[1.02] {{ $selectedType === $typeKey ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-slate-200 dark:border-slate-800' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $grp['name'] }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $grp['badge'] }}">
                    {{ $grp['active'] }} Active
                </span>
            </div>
            <div class="text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                {{ number_format($grp['count']) }}
            </div>
            <div class="text-[11px] text-slate-400 line-clamp-2">
                {{ $grp['description'] }}
            </div>
        </a>
        @endforeach
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-2 overflow-x-auto text-xs font-bold">
                <a href="{{ route('customers.groups.index', ['type' => 'all']) }}"
                   class="px-4 py-2 rounded-xl transition-all {{ $selectedType === 'all' ? 'bg-brand-600 text-white' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    All Types
                </a>
                @foreach($groups as $key => $g)
                <a href="{{ route('customers.groups.index', ['type' => $key]) }}"
                   class="px-4 py-2 rounded-xl transition-all {{ $selectedType === $key ? 'bg-brand-600 text-white' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    {{ $g['name'] }} ({{ $g['count'] }})
                </a>
                @endforeach
            </div>

            <!-- Search Filter -->
            <form action="{{ route('customers.groups.index') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="type" value="{{ $selectedType }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subscriber..." class="px-3.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                        <th class="py-3 px-4">Account Number</th>
                        <th class="py-3 px-4">Subscriber Name</th>
                        <th class="py-3 px-4">Segment / Group</th>
                        <th class="py-3 px-4">Internet Package</th>
                        <th class="py-3 px-4">Technology</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($customers as $c)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">
                            {{ $c->account_number }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-slate-100">
                            {{ $c->full_name }}
                        </td>
                        <td class="py-3.5 px-4 capitalize">
                            <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold {{ $groups[$c->customer_type]['badge'] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $c->customer_type }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                            {{ $c->package?->name ?? 'No package' }}
                        </td>
                        <td class="py-3.5 px-4 uppercase font-mono text-[10px] text-slate-500">
                            {{ $c->connection_type }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $c->branch?->name ?? 'Headquarters' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $c->status_badge_class }}">
                                {{ $c->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('customers.show', $c) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-brand-50 hover:text-brand-600 text-slate-600 dark:text-slate-300 text-xs font-semibold transition-colors">
                                View Profile &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <div class="font-bold">No subscribers in this customer group</div>
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
