@extends('layouts.app')

@section('title', 'Security Audit Logs')
@section('page_title', 'Immutable Audit Trail')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Security Audit Logs</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Append-only immutable record of all administrative state mutations and staff actions.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Immutable Ledger Active
            </span>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search description, user or IP..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <select name="action" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Actions</option>
                    <option value="login" {{ ($filters['action'] ?? '') === 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ ($filters['action'] ?? '') === 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="failed_login" {{ ($filters['action'] ?? '') === 'failed_login' ? 'selected' : '' }}>Failed Login</option>
                    <option value="created" {{ ($filters['action'] ?? '') === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ ($filters['action'] ?? '') === 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ ($filters['action'] ?? '') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>

            <div>
                <select name="user_id" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 text-xs font-bold rounded-xl transition-colors">
                    Filter Logs
                </button>
                @if(!empty(array_filter($filters ?? [])))
                    <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-700 text-xs font-medium rounded-xl">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-6">Timestamp</th>
                        <th class="py-3.5 px-6">User / Actor</th>
                        <th class="py-3.5 px-6">Action</th>
                        <th class="py-3.5 px-6">Target Resource</th>
                        <th class="py-3.5 px-6">Description</th>
                        <th class="py-3.5 px-6">IP Address</th>
                        <th class="py-3.5 px-6 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($auditLogs as $log)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="py-3.5 px-6 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                            {{ $log->created_at->format('M d, Y H:i:s') }}
                        </td>
                        <td class="py-3.5 px-6 whitespace-nowrap">
                            @if($log->user)
                                <a href="{{ route('users.show', $log->user) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:text-brand-600">
                                    {{ $log->user_name ?? $log->user->name }}
                                </a>
                            @else
                                <span class="font-bold text-slate-500">{{ $log->user_name ?? 'System' }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                @if($log->action === 'created') bg-emerald-500/10 text-emerald-600 border border-emerald-500/20
                                @elseif($log->action === 'updated') bg-blue-500/10 text-blue-600 border border-blue-500/20
                                @elseif($log->action === 'deleted') bg-rose-500/10 text-rose-600 border border-rose-500/20
                                @elseif($log->action === 'login') bg-indigo-500/10 text-indigo-600 border border-indigo-500/20
                                @elseif($log->action === 'failed_login') bg-amber-500/10 text-amber-600 border border-amber-500/20
                                @else bg-slate-100 dark:bg-slate-800 text-slate-600 @endif
                            ">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 whitespace-nowrap text-slate-600 dark:text-slate-300 font-mono text-[11px]">
                            @if($log->auditable_type)
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-slate-700 dark:text-slate-200 max-w-xs truncate" title="{{ $log->description }}">
                            {{ $log->description }}
                        </td>
                        <td class="py-3.5 px-6 whitespace-nowrap text-slate-400 font-mono text-[11px]">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                        <td class="py-3.5 px-6 whitespace-nowrap text-right">
                            <a href="{{ route('audit-logs.show', $log) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors inline-block" title="View Payload & Diff">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            No audit log records found matching the specified criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $auditLogs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
