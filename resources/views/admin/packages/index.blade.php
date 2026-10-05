@extends('layouts.app')

@section('title', 'Internet Packages')
@section('page_title', 'Internet Service Plans')

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Internet Packages & Bandwidth Plans</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Configure broadband speed tiers, burst configurations, pricing, and billing cycles.</p>
        </div>
        @can('packages.create')
        <a href="{{ route('packages.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Create Package</span>
        </a>
        @endcan
    </div>

    <!-- Packages Grid / Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($packages as $pkg)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between relative overflow-hidden group">
            @if($pkg->is_featured)
                <div class="absolute top-0 right-0 bg-brand-600 text-white text-[10px] font-extrabold uppercase px-3 py-1 rounded-bl-xl tracking-wider shadow-sm">
                    Popular
                </div>
            @endif

            <div class="space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-mono uppercase text-slate-400 font-bold block">{{ $pkg->code ?? 'PKG-' . $pkg->id }}</span>
                        <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors">{{ $pkg->name }}</h3>
                    </div>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 min-h-[32px]">
                    {{ $pkg->description ?? 'High-speed broadband internet connectivity.' }}
                </p>

                <!-- Speed Metrics Banner -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 grid grid-cols-2 gap-3 text-center">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Download</span>
                        <span class="text-base font-extrabold text-brand-600 dark:text-brand-400">{{ $pkg->formatted_download_speed }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Upload</span>
                        <span class="text-base font-extrabold text-indigo-600 dark:text-indigo-400">{{ $pkg->formatted_upload_speed }}</span>
                    </div>
                </div>

                <!-- Rate Limit & Burst Attributes -->
                <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">RouterOS Rate-Limit:</span>
                        <span class="font-mono font-bold text-slate-700 dark:text-slate-200">{{ $pkg->toMikrotikRateLimitString() }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">Technology:</span>
                        <span class="font-bold uppercase text-slate-700 dark:text-slate-200">{{ $pkg->connection_type }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">Validity Cycle:</span>
                        <span>{{ $pkg->validity_period }} Days ({{ ucfirst($pkg->billing_cycle) }})</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">Subscribers:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $pkg->customers_count }} active</span>
                    </div>
                </div>
            </div>

            <!-- Pricing & Actions Footer -->
            <div class="pt-5 mt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-400">Price / Mo</span>
                    <div class="text-xl font-black text-slate-900 dark:text-slate-100">
                        ₦{{ number_format((float)$pkg->price, 2) }}
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    @can('packages.view')
                    <a href="{{ route('packages.show', $pkg) }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800" title="View Details">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    @endcan

                    @can('packages.update')
                    <a href="{{ route('packages.edit', $pkg) }}" class="p-2 rounded-xl text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit Plan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    @endcan

                    @can('packages.delete')
                    <form action="{{ route('packages.destroy', $pkg) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete package {{ $pkg->name }}?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Delete Plan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-400">
            <p class="text-sm font-semibold">No internet packages defined yet.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
