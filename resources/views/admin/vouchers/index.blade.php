@extends('layouts.app')

@section('title', 'Hotspot Vouchers')
@section('page_title', 'MikroTik Hotspot Vouchers')

@section('content')
<div class="space-y-6" x-data="{ generateModalOpen: false }">

    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Hotspot Voucher Cards</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">FreeRADIUS AAA</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Generate time-limited & speed-capped Wi-Fi voucher PINs for MikroTik captive portals and guest networks.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(!empty($filters['batch_id']))
            <a href="{{ route('vouchers.print_batch', $filters['batch_id']) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Current Batch</span>
            </a>
            @endif

            @can('radius.users.create')
            <button @click="generateModalOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Generate Voucher Batch</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('vouchers.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Search Voucher</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Voucher Code or Username..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="unused" {{ ($filters['status'] ?? '') === 'unused' ? 'selected' : '' }}>Unused (Fresh)</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active (In Use)</option>
                    <option value="used" {{ ($filters['status'] ?? '') === 'used' ? 'selected' : '' }}>Used / Completed</option>
                    <option value="expired" {{ ($filters['status'] ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Batch ID</label>
                <select name="batch_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b }}" {{ ($filters['batch_id'] ?? '') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white dark:bg-brand-600 dark:hover:bg-brand-500 font-bold text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('vouchers.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-700 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Vouchers Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Voucher Code</th>
                        <th class="px-5 py-3">RADIUS Username</th>
                        <th class="px-5 py-3">PIN / Password</th>
                        <th class="px-5 py-3">Plan / Speed</th>
                        <th class="px-5 py-3">Duration</th>
                        <th class="px-5 py-3">Price</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Batch ID</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($vouchers as $v)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 font-mono font-bold text-brand-600 dark:text-brand-400">
                            {{ $v->code }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-800 dark:text-slate-200">
                            {{ $v->username }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-black tracking-widest text-emerald-600 dark:text-emerald-400">
                            {{ $v->password }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $v->package->name }}</span>
                            <span class="block text-[10px] text-slate-400 font-mono">{{ $v->package->formatted_download_speed }} / {{ $v->package->formatted_upload_speed }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-bold text-slate-700 dark:text-slate-300">
                            {{ $v->duration_formatted }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-900 dark:text-slate-100">
                            ₦{{ number_format((float)$v->price, 2) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $v->status_badge_class }}">
                                {{ $v->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[11px] text-slate-400">
                            @if($v->batch_id)
                            <a href="{{ route('vouchers.print_batch', $v->batch_id) }}" target="_blank" class="hover:underline hover:text-brand-500" title="Print this whole batch">
                                {{ $v->batch_id }}
                            </a>
                            @else
                            Individual
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <form action="{{ route('vouchers.destroy', $v) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete voucher {{ $v->code }}? It will be immediately deleted from FreeRADIUS.')" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg transition-colors" title="Delete Voucher">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-8 text-center text-slate-400">
                            No hotspot vouchers found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $vouchers->links() }}
        </div>
        @endif
    </div>

    @can('radius.users.create')
    <!-- Generate Batch Modal -->
    <div x-show="generateModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div @click.away="generateModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-lg w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Generate Hotspot Vouchers (Batch)</h3>
                <button type="button" @click="generateModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('vouchers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Hotspot Package *</label>
                    <select name="package_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}">
                                {{ $pkg->name }} ({{ $pkg->formatted_download_speed }}) — ₦{{ number_format((float)$pkg->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Quantity to Generate *</label>
                        <select name="quantity" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="5">5 Vouchers</option>
                            <option value="10" selected>10 Vouchers</option>
                            <option value="25">25 Vouchers</option>
                            <option value="50">50 Vouchers</option>
                            <option value="100">100 Vouchers</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Code Prefix</label>
                        <input type="text" name="prefix" value="HS" maxlength="6" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs uppercase font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Access Duration Value *</label>
                        <input type="number" name="duration_value" value="1" min="1" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Duration Unit *</label>
                        <select name="duration_unit" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="hours">Hours</option>
                            <option value="days" selected>Days</option>
                            <option value="minutes">Minutes</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-[11px] text-emerald-700 dark:text-emerald-400">
                    <strong>RADIUS Synced:</strong> Each generated voucher is instantly recorded in PostgreSQL <code>radcheck</code> and <code>radreply</code> with MikroTik bandwidth limits and session timeouts.
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="generateModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-500">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30">Generate & Provision</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

</div>
@endsection
