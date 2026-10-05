@extends('portal.layout')

@section('title', 'Router & Security Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100">Broadband Router & Account Settings</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">View your router PPPoE authentication details and manage your portal password.</p>
    </div>

    <!-- PPPoE Dial-in Credentials Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Broadband Router / ONT Credentials</h3>
                <p class="text-xs text-slate-500">Enter these credentials in your home Wi-Fi router WAN PPPoE configuration.</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                PPPoE Settings
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <span class="text-[10px] text-slate-400 uppercase font-bold block mb-1">PPPoE Dial-in Username</span>
                <span class="font-mono font-bold text-sm text-slate-900 dark:text-slate-100">{{ $customer->radius_username ?? 'Not set' }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700" x-data="{ show: false }">
                <span class="text-[10px] text-slate-400 uppercase font-bold block mb-1">PPPoE Password</span>
                <div class="flex items-center justify-between">
                    <span class="font-mono font-bold text-sm text-slate-900 dark:text-slate-100" x-text="show ? '{{ $customer->radius_password ?? 'Not set' }}' : '••••••••'"></span>
                    <button type="button" @click="show = !show" class="text-xs text-brand-600 dark:text-brand-400 font-semibold hover:underline" x-text="show ? 'Hide' : 'Show'"></button>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <span class="text-[10px] text-slate-400 uppercase font-bold block mb-1">Assigned Framed IP</span>
                <span class="font-mono font-bold text-sm text-brand-600 dark:text-brand-400">{{ $customer->static_ip ?? 'Dynamic DHCP Pool' }}</span>
            </div>
        </div>

        <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl text-[11px] text-slate-500">
            <strong>Need assistance setting up your router?</strong> Contact customer support at support@isp.ng or call our helpline with your Account ID <strong>{{ $customer->account_number }}</strong>.
        </div>
    </div>

    <!-- Security & Password Update -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Change Portal Password</h3>

        <form action="{{ route('portal.profile.password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Current Password *</label>
                <input type="password" name="current_password" required placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">New Password *</label>
                    <input type="password" name="password" minlength="6" required placeholder="Min 6 characters" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" minlength="6" required placeholder="Re-enter new password" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30">
                    Update Security Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
