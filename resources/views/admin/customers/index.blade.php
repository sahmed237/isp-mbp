@extends('layouts.app')

@section('title', 'Customers Directory')
@section('page_title', 'Broadband Customers')

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Customer Accounts Directory</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage individual, corporate, and reseller broadband subscribers across operational branches.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('customers.create')
            <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Add Customer</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('customers.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

            <!-- Search input -->
            <div class="relative lg:col-span-2">
                <svg class="w-4 h-4 absolute left-3 top-3 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search name, account, phone, email..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
            </div>

            <!-- Status filter -->
            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="expired" {{ ($filters['status'] ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="lead" {{ ($filters['status'] ?? '') === 'lead' ? 'selected' : '' }}>Lead</option>
                    <option value="terminated" {{ ($filters['status'] ?? '') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>

            <!-- Connection Type filter -->
            <div>
                <select name="connection_type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Technologies</option>
                    <option value="fibre" {{ ($filters['connection_type'] ?? '') === 'fibre' ? 'selected' : '' }}>Fibre (FTTH)</option>
                    <option value="pppoe" {{ ($filters['connection_type'] ?? '') === 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                    <option value="hotspot" {{ ($filters['connection_type'] ?? '') === 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                    <option value="ptp" {{ ($filters['connection_type'] ?? '') === 'ptp' ? 'selected' : '' }}>Point-to-Point (PtP)</option>
                    <option value="ptmp" {{ ($filters['connection_type'] ?? '') === 'ptmp' ? 'selected' : '' }}>Wireless (PtMP)</option>
                    <option value="dedicated" {{ ($filters['connection_type'] ?? '') === 'dedicated' ? 'selected' : '' }}>Dedicated Leased Line</option>
                </select>
            </div>

            <!-- Submit / Reset Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 text-white font-semibold text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('customers.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-700 text-xs text-center font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Customers Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="py-3.5 px-4">Account ID</th>
                        <th class="py-3.5 px-4">Subscriber Name</th>
                        <th class="py-3.5 px-4">Phone / Email</th>
                        <th class="py-3.5 px-4">Current Package</th>
                        <th class="py-3.5 px-4">Technology</th>
                        <th class="py-3.5 px-4">Branch</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">
                            <a href="{{ route('customers.show', $customer) }}" class="hover:underline">
                                {{ $customer->account_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <a href="{{ route('customers.show', $customer) }}" class="font-bold text-slate-800 dark:text-slate-100 hover:text-brand-600 dark:hover:text-brand-400 block">
                                {{ $customer->full_name }}
                            </a>
                            <span class="text-[10px] text-slate-400 capitalize">{{ $customer->customer_type }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <div>{{ $customer->phone }}</div>
                            <div class="text-[11px] text-slate-400">{{ $customer->email ?? 'No email' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($customer->package)
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $customer->package->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $customer->package->formatted_download_speed }} &bull; ₦{{ number_format((float)$customer->package->price, 2) }}</div>
                            @else
                                <span class="text-slate-400 italic">None assigned</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $customer->connection_type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                            {{ $customer->branch?->name ?? 'Default HQ' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize {{ $customer->status_badge_class }}">
                                {{ $customer->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @can('customers.view')
                                <a href="{{ route('customers.show', $customer) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800" title="View Profile">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @endcan

                                @can('customers.update')
                                <a href="{{ route('customers.edit', $customer) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit Customer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan

                                @can('customers.delete')
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete customer {{ $customer->full_name }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Delete Customer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">No customers found</p>
                                <p class="text-xs text-slate-400">Try adjusting your search criteria or add a new customer.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
