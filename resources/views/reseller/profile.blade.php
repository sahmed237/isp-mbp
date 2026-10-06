@extends('reseller.layout')

@section('title', 'Store Profile & Security')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">Store Profile & KYC Details</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">View your verified agent credentials and update your security settings.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                Status: {{ $reseller->status }}
            </span>
        </div>

        <!-- Read-only KYC Overview Card -->
        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-6 border border-slate-200 dark:border-slate-700/60 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Verified Identification</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block">Reseller Code:</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $reseller->reseller_code }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">ID Document Type:</span>
                    <span class="font-bold text-slate-900 dark:text-white uppercase">{{ str_replace('_', ' ', $reseller->id_type ?? 'N/A') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">ID / Registration Number:</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $reseller->id_number ?? 'N/A' }}</span>
                </div>
            </div>

            @if($reseller->id_card_path)
            <div class="pt-2 flex items-center gap-3">
                <a href="{{ asset('storage/' . $reseller->id_card_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-emerald-500 transition-colors">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>View Uploaded KYC Document</span>
                </a>
            </div>
            @endif
        </div>

        <!-- Editable Form -->
        <form action="{{ route('reseller.profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Business Name</label>
                    <input type="text" value="{{ $reseller->business_name }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Person</label>
                    <input type="text" value="{{ $reseller->contact_person }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-500 cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Primary Phone</label>
                    <input type="text" value="{{ $reseller->phone }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-500 cursor-not-allowed font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alternate Phone (WhatsApp)</label>
                    <input type="tel" name="alternate_phone" value="{{ old('alternate_phone', $reseller->alternate_phone) }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Shop / Retail Store Address *</label>
                <textarea name="shop_address" rows="2" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('shop_address', $reseller->shop_address) }}</textarea>
            </div>

            <!-- Change Password -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Security & Password</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Current Password</label>
                        <input type="password" name="current_password" placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">New Password</label>
                        <input type="password" name="new_password" placeholder="Minimum 8 characters" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" placeholder="Re-type new password" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
