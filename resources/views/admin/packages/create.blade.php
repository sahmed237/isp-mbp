@extends('layouts.app')

@section('title', 'Create Internet Package')
@section('page_title', 'Create Service Package')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Create Internet Service Plan</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Define subscriber bandwidth, burst limits, and billing terms.</p>
        </div>
        <a href="{{ route('packages.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
            &larr; Cancel
        </a>
    </div>

    <form action="{{ route('packages.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Plan Basics -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">1. Plan Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Package Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Ultra 25 Mbps" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Package Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. PKG-ULTRA-25M" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Brief subscriber-facing marketing description" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- Bandwidth & Burst Limits -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">2. Bandwidth & Burst Limits (in Kbps)</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Download Speed (Kbps) *</label>
                    <input type="number" name="download_speed" value="{{ old('download_speed', 10240) }}" required step="128" min="128" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">10240 Kbps = 10 Mbps</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Upload Speed (Kbps) *</label>
                    <input type="number" name="upload_speed" value="{{ old('upload_speed', 5120) }}" required step="128" min="128" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">5120 Kbps = 5 Mbps</span>
                </div>
            </div>

            <!-- Optional Burst Parameters -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <span class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-3">Optional MikroTik Burst Settings</span>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Burst Download (Kbps)</label>
                        <input type="number" name="burst_download" value="{{ old('burst_download') }}" placeholder="e.g. 15360" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Burst Upload (Kbps)</label>
                        <input type="number" name="burst_upload" value="{{ old('burst_upload') }}" placeholder="e.g. 7680" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Burst Threshold (Kbps)</label>
                        <input type="number" name="burst_threshold" value="{{ old('burst_threshold') }}" placeholder="e.g. 8192" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Burst Time (seconds)</label>
                        <input type="number" name="burst_time" value="{{ old('burst_time', 20) }}" placeholder="e.g. 20" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing & Billing Cycle -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">3. Pricing & Provisioning Terms</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Monthly Price (₦) *</label>
                    <input type="number" name="price" value="{{ old('price') }}" step="0.01" required placeholder="25000.00" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Installation Fee (₦)</label>
                    <input type="number" name="installation_fee" value="{{ old('installation_fee', '0.00') }}" step="0.01" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Validity (Days) *</label>
                    <input type="number" name="validity_period" value="{{ old('validity_period', 30) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Billing Cycle *</label>
                    <select name="billing_cycle" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ old('billing_cycle') === 'quarterly' ? 'selected' : '' }}>Quarterly (3 Months)</option>
                        <option value="biannual" {{ old('billing_cycle') === 'biannual' ? 'selected' : '' }}>Bi-annual (6 Months)</option>
                        <option value="annual" {{ old('billing_cycle') === 'annual' ? 'selected' : '' }}>Annual (12 Months)</option>
                        <option value="custom" {{ old('billing_cycle') === 'custom' ? 'selected' : '' }}>Custom Validity</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Connection Type *</label>
                    <select name="connection_type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="all">Universal / All Technologies</option>
                        <option value="pppoe" selected>PPPoE</option>
                        <option value="fibre">Fibre (FTTH)</option>
                        <option value="hotspot">Hotspot</option>
                        <option value="ptp">Point-to-Point (PtP)</option>
                        <option value="wireless">Fixed Wireless</option>
                        <option value="dedicated">Dedicated Line</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status *</label>
                    <select name="status" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('packages.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                Save Package
            </button>
        </div>
    </form>
</div>
@endsection
