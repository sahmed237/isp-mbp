@extends('layouts.app')

@section('title', 'Subscriptions')
@section('page_title', 'Subscriber Subscriptions')

@section('content')
<div class="space-y-6">

    <!-- Header & Stats Summary -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Broadband Subscriptions</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage subscriber plan cycles, automated expirations, validity periods, and RADIUS authentications.</p>
        </div>
        @can('customers.create')
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Customer & Subscription</span>
            </a>
        </div>
        @endcan
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('subscriptions.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Search Subscriptions</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Sub #, Customer, Account..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active (Authorized)</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending Payment</option>
                    <option value="suspended" {{ ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="expired" {{ ($filters['status'] ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Package Plan</label>
                <select name="package_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Packages</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" {{ ($filters['package_id'] ?? '') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white dark:bg-brand-600 dark:hover:bg-brand-500 font-bold text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('subscriptions.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-700 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Subscription</th>
                        <th class="px-5 py-3">Subscriber</th>
                        <th class="px-5 py-3">Package / Speed</th>
                        <th class="px-5 py-3">Validity & Countdown</th>
                        <th class="px-5 py-3">Price / Cycle</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($subscriptions as $sub)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('subscriptions.show', $sub) }}" class="font-mono font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                {{ $sub->subscription_number }}
                            </a>
                            <span class="block text-[10px] text-slate-400">{{ $sub->created_at->format('M d, Y') }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('customers.show', $sub->customer) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:underline">
                                {{ $sub->customer->full_name }}
                            </a>
                            <div class="flex items-center gap-2 text-[10px] text-slate-400 font-mono">
                                <span>{{ $sub->customer->account_number }}</span>
                                @if($sub->customer->radius_username)
                                <span>&bull;</span>
                                <span class="text-indigo-500 font-semibold">PPPoE: {{ $sub->customer->radius_username }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-800 dark:text-slate-200">{{ $sub->package->name }}</div>
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                &darr; {{ $sub->package->formatted_download_speed }} / &uarr; {{ $sub->package->formatted_upload_speed }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($sub->expires_at)
                                <div class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $sub->expires_at->format('M d, Y H:i') }}
                                </div>
                                @if($sub->isActive())
                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ $sub->days_remaining }} days remaining
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-rose-500">
                                        Expired {{ $sub->expires_at->diffForHumans() }}
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400 italic">Not yet started</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-slate-900 dark:text-slate-100">₦{{ number_format((float)$sub->price, 2) }}</span>
                            <span class="block text-[10px] text-slate-400 capitalize">{{ $sub->billing_cycle }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $sub->status_badge_class }}">
                                {{ $sub->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('subscriptions.show', $sub) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                @if($sub->status === 'pending')
                                <form action="{{ route('subscriptions.activate', $sub) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Activate subscription and enable RADIUS authentication?')" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40" title="Activate Now">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                @endif

                                @if($sub->status === 'active')
                                <form action="{{ route('subscriptions.suspend', $sub) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Suspend subscription? Subscriber will be immediately rejected on FreeRADIUS.')" class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40" title="Suspend Subscription">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                            No broadband subscriptions found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $subscriptions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
