@extends('layouts.app')

@section('title', 'Onboard Voucher Reseller')
@section('page_title', 'Onboard Reseller')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Onboard Hotspot Voucher Reseller</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Register an authorized retail merchant or voucher distribution agent.</p>
        </div>
        <a href="{{ route('resellers.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
            &larr; Cancel
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-xs text-rose-500 space-y-1">
        <div class="font-bold">Please check the errors below:</div>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('resellers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. Store Identity -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">1. Business & Store Identity</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Business Trade Name *</label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}" placeholder="e.g. Apex Cybercafe & Hub" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Person (Full Name) *</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Usman Bello" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Primary Phone *</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="08012345678" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alternate Phone</label>
                    <input type="tel" name="alternate_phone" value="{{ old('alternate_phone') }}" placeholder="Optional" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="agent@shop.ng" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Business Category *</label>
                    <select name="business_type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="cybercafe" {{ old('business_type') === 'cybercafe' ? 'selected' : '' }}>Cybercafe / Business Center</option>
                        <option value="pos_agent" {{ old('business_type') === 'pos_agent' ? 'selected' : '' }}>POS & Mobile Money Agent</option>
                        <option value="phone_accessories" {{ old('business_type') === 'phone_accessories' ? 'selected' : '' }}>Phone & Computer Accessories Store</option>
                        <option value="campus_kiosk" {{ old('business_type') === 'campus_kiosk' ? 'selected' : '' }}>Campus / Student Hostel Kiosk</option>
                        <option value="hotel" {{ old('business_type') === 'hotel' ? 'selected' : '' }}>Hotel / Guest House</option>
                        <option value="retail_agent" {{ old('business_type', 'retail_agent') === 'retail_agent' ? 'selected' : '' }}>Supermarket / Retail Shop</option>
                        <option value="other" {{ old('business_type') === 'other' ? 'selected' : '' }}>Other Independent Merchant</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Operational Branch</label>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- All / Main Branch --</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

@php
$nigerianStates = [
    'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno',
    'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT - Abuja', 'Gombe',
    'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos',
    'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto',
    'Taraba', 'Yobe', 'Zamfara'
];
@endphp
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Shop / Store Physical Address *</label>
                <input type="text" name="shop_address" value="{{ old('shop_address') }}" placeholder="e.g. Suite 12, Garki Mall, Area 11" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">State (Nigeria)</label>
                    <select name="state" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Select State --</option>
                        @foreach($nigerianStates as $stateOption)
                            <option value="{{ $stateOption }}" {{ old('state') === $stateOption ? 'selected' : '' }}>{{ $stateOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">City / Town</label>
                    <input type="text" name="city" value="{{ old('city') }}" placeholder="e.g. Ikeja, Wuse 2, Kano City" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        <!-- 2. KYC Documentation -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">2. KYC & Identity Verification (Optional for Direct Onboarding)</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Identity Document Type</label>
                    <select name="id_type" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- None / Pending --</option>
                        <option value="nin" {{ old('id_type') === 'nin' ? 'selected' : '' }}>National Identity Number (NIN)</option>
                        <option value="drivers_license" {{ old('id_type') === 'drivers_license' ? 'selected' : '' }}>Federal Driver's License</option>
                        <option value="voters_card" {{ old('id_type') === 'voters_card' ? 'selected' : '' }}>Permanent Voter's Card (PVC)</option>
                        <option value="passport" {{ old('id_type') === 'passport' ? 'selected' : '' }}>International Passport</option>
                        <option value="cac_registration" {{ old('id_type') === 'cac_registration' ? 'selected' : '' }}>CAC Certificate</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Identity / CAC Number</label>
                    <input type="text" name="id_number" value="{{ old('id_number') }}" placeholder="e.g. 12345678901" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Upload ID Document File</label>
                <input type="file" name="id_document" accept=".pdf,.png,.jpg,.jpeg" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 cursor-pointer">
            </div>
        </div>

        <!-- 3. Wallet & Security -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">3. Initial Wallet Balance & Portal Password</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-emerald-700 dark:text-emerald-400 mb-1">Initial Prepaid Wallet Balance (₦)</label>
                    <input type="number" step="0.01" min="0" name="initial_balance" value="{{ old('initial_balance', '0.00') }}" placeholder="0.00" class="w-full px-3.5 py-2.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-700 rounded-xl text-xs font-mono font-bold text-emerald-800 dark:text-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1">Starting credit for the agent to purchase voucher batches immediately.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Portal Password *</label>
                    <input type="password" name="password" value="12345678" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[11px] text-slate-400 mt-1">Default temporary password: <strong>12345678</strong> (Agent can change on login).</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Internal Admin Notes</label>
                <textarea name="notes" rows="2" placeholder="e.g. Onboarded by field sales manager for the university student village..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('resellers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                Onboard Reseller & Activate &rarr;
            </button>
        </div>
    </form>

</div>
@endsection
