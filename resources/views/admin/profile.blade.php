@extends('layouts.app')

@section('title', 'My Profile & Security')
@section('page_title', 'Staff Account Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">User Profile & Credentials</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage your staff information, contact details, and account password.</p>
        </div>
    </div>

    <!-- User Information Summary -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row items-center gap-6">
        <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-brand-500/30 flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="space-y-1 text-center sm:text-left flex-1">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">{{ $user->name }}</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-500/20">
                    {{ $user->roles->first()?->name ?? 'Staff User' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }} • {{ $user->phone ?? 'No phone added' }}</p>
            <div class="text-xs text-slate-400 pt-1">
                Organization: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $user->organization?->name ?? 'Global Platform' }}</span>
                @if($user->branch)
                    • Branch: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $user->branch->name }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Profile Edit Form -->
    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Account Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address (Read-only)</label>
                    <input type="email" value="{{ $user->email }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-500 cursor-not-allowed">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+234 800 000 0000" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- Security & Password -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Change Password</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Leave blank if you do not want to alter your password.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Current Password</label>
                    <input type="password" name="current_password" placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">New Password</label>
                    <input type="password" name="new_password" placeholder="Min. 8 characters" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" placeholder="Confirm password" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-extrabold shadow-md shadow-brand-600/30">
                Update Profile Settings
            </button>
        </div>
    </form>

</div>
@endsection
