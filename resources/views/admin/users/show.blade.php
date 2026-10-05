@extends('layouts.app')

@section('title', "Staff: {$user->name}")
@section('page_title', "Staff Profile — {$user->name}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Profile Header Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-brand-500/20 flex-shrink-0">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">{{ $user->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize
                        @if($user->status === 'active') bg-emerald-500/10 text-emerald-600
                        @elseif($user->status === 'suspended') bg-amber-500/10 text-amber-600
                        @else bg-slate-100 text-slate-600 @endif
                    ">
                        {{ $user->status }}
                    </span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ $user->email }}</span>
                    @if($user->phone)
                        <span>&bull; {{ $user->phone }}</span>
                    @endif
                    <span>&bull; {{ $user->organization?->name ?? 'System Master' }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('users.update')
            <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all">
                Edit Staff User
            </a>
            @endcan

            <a href="{{ route('users.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
                &larr; Back
            </a>
        </div>
    </div>

    <!-- Assigned Roles & Security Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Assigned Operational Roles</h3>
            <div class="space-y-2">
                @forelse($user->roles as $role)
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $role->name }}</span>
                    <span class="text-[10px] font-mono text-slate-400">{{ $role->permissions->count() }} permissions</span>
                </div>
                @empty
                <p class="text-xs text-slate-400">No roles assigned.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Session & Authentication Info</h3>
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400">Last Login:</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400">Last Login IP:</span>
                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ $user->last_login_ip ?? 'N/A' }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-slate-400">Account Created:</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $user->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Audit Activity By This User -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Recent Activity Logs</h3>
        <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
            @forelse($user->auditLogs as $log)
            <div class="py-3 flex items-start justify-between">
                <div>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $log->description }}</span>
                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ $log->ip_address }}</div>
                </div>
                <span class="text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <p class="py-4 text-center text-slate-400 text-xs">No audit events generated by this user.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
