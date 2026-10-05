@extends('layouts.app')

@section('title', $info['title'])
@section('page_title', $info['title'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Roadmap Banner Card -->
    <div class="bg-gradient-to-br from-slate-900 to-indigo-950 border border-slate-800 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="space-y-4 relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 border border-brand-500/30 text-xs font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-brand-400 animate-pulse"></span>
                {{ $info['phase'] }}
            </div>

            <h2 class="text-2xl sm:text-3xl font-black tracking-tight">{{ $info['title'] }}</h2>
            <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">{{ $info['desc'] }}</p>

            <div class="pt-4 flex flex-wrap items-center gap-3">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-white text-slate-900 hover:bg-slate-100 font-bold text-xs shadow-md">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Architectural Foundation & Planned Specifications -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">Planned Architecture Capabilities</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Specifications planned and designed for subsequent implementation phases.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            @foreach($info['features'] as $feature)
            <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/60">
                <div class="w-8 h-8 rounded-xl bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $feature }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
