@extends('layouts.app')

@section('title', 'Branches & Operational Subdivisions')
@section('page_title', 'Operational Branches')

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Branches & Service Offices</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage regional subdivisions, customer routing hubs, and operational branch locations.</p>
        </div>
        @can('branches.create')
        <a href="{{ route('branches.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Branch</span>
        </a>
        @endcan
    </div>

    <!-- Branches Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($branches as $branch)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                        @if($branch->status === 'active') bg-emerald-500/10 text-emerald-600 border border-emerald-500/20
                        @else bg-slate-100 dark:bg-slate-800 text-slate-500 @endif
                    ">
                        {{ ucfirst($branch->status) }}
                    </span>
                    <span class="font-mono text-xs font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/50 px-2 py-0.5 rounded">
                        {{ $branch->code ?? 'BR-' . $branch->id }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-extrabold text-lg flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-black text-slate-900 dark:text-slate-100 truncate">{{ $branch->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $branch->organization?->name ?? 'Default Organization' }}</p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl text-center">
                    <div>
                        <div class="text-base font-black text-indigo-600 dark:text-indigo-400">{{ $branch->customers_count }}</div>
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Subscribers</div>
                    </div>
                    <div>
                        <div class="text-base font-black text-slate-900 dark:text-slate-100">{{ $branch->users_count }}</div>
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Staff Users</div>
                    </div>
                </div>

                <div class="space-y-1 text-xs text-slate-500 dark:text-slate-400">
                    @if($branch->city || $branch->state)
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $branch->city }}, {{ $branch->state }}</span>
                    </div>
                    @endif
                    @if($branch->phone)
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $branch->phone }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                @can('branches.update')
                <a href="{{ route('branches.edit', $branch) }}" class="p-2 rounded-xl text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit Branch">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                @endcan

                @can('branches.delete')
                <form action="{{ route('branches.destroy', $branch) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete branch {{ $branch->name }}?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800" title="Delete Branch">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
                @endcan
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
