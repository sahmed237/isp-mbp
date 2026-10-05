@extends('layouts.app')

@section('title', "Audit Log #{$auditLog->id}")
@section('page_title', "Audit Log Details #{$auditLog->id}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Navigation Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('audit-logs.index') }}" class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Audit Record #{{ $auditLog->id }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                        @if($auditLog->action === 'created') bg-emerald-500/10 text-emerald-600 border border-emerald-500/20
                        @elseif($auditLog->action === 'updated') bg-blue-500/10 text-blue-600 border border-blue-500/20
                        @elseif($auditLog->action === 'deleted') bg-rose-500/10 text-rose-600 border border-rose-500/20
                        @elseif($auditLog->action === 'login') bg-indigo-500/10 text-indigo-600 border border-indigo-500/20
                        @elseif($auditLog->action === 'failed_login') bg-amber-500/10 text-amber-600 border border-amber-500/20
                        @else bg-slate-100 dark:bg-slate-800 text-slate-600 @endif
                    ">
                        {{ str_replace('_', ' ', $auditLog->action) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Recorded on {{ $auditLog->created_at->format('l, F j, Y — H:i:s T') }}</p>
            </div>
        </div>
    </div>

    <!-- Metadata Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Event Metadata</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-slate-400 font-semibold block mb-1">Actor / Staff Member</span>
                @if($auditLog->user)
                    <a href="{{ route('users.show', $auditLog->user) }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline">
                        {{ $auditLog->user->name }} ({{ $auditLog->user->email }})
                    </a>
                @else
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $auditLog->user_name ?? 'System Process' }}</span>
                @endif
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-slate-400 font-semibold block mb-1">Target Resource</span>
                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">
                    {{ $auditLog->auditable_type ?? 'N/A' }}
                    @if($auditLog->auditable_id)
                        #{{ $auditLog->auditable_id }}
                    @endif
                </span>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-slate-400 font-semibold block mb-1">Client IP Address</span>
                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $auditLog->ip_address ?? 'Unknown' }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-slate-400 font-semibold block mb-1">User Agent</span>
                <span class="font-mono text-[11px] text-slate-600 dark:text-slate-400 break-all">{{ $auditLog->user_agent ?? 'Unknown' }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 sm:col-span-2">
                <span class="text-slate-400 font-semibold block mb-1">Event Description</span>
                <span class="font-medium text-slate-800 dark:text-slate-200 text-sm">{{ $auditLog->description }}</span>
            </div>
        </div>
    </div>

    <!-- Payload Diffs (Old vs New) -->
    @if(!empty($auditLog->old_values) || !empty($auditLog->new_values))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Old Values -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <h4 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Previous State (Before)</h4>
            </div>
            @if(!empty($auditLog->old_values))
                <pre class="p-4 rounded-2xl bg-slate-950 text-emerald-400 font-mono text-xs overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <p class="text-xs text-slate-400 italic">No previous state recorded (Initial creation).</p>
            @endif
        </div>

        <!-- New Values -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">New State (After)</h4>
            </div>
            @if(!empty($auditLog->new_values))
                <pre class="p-4 rounded-2xl bg-slate-950 text-emerald-400 font-mono text-xs overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <p class="text-xs text-slate-400 italic">No resulting state (Resource deleted).</p>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection
