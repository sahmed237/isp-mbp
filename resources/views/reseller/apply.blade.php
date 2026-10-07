<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 font-sans antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apply as Authorized Voucher Reseller & Agent</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } [x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full flex flex-col justify-center items-center p-4 sm:p-6 lg:p-12 relative overflow-x-hidden bg-slate-950 text-slate-100">

    <!-- Ambient Glowing Background Circles -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-3xl w-full space-y-6 relative z-10 py-8">

        <!-- Top Header & Breadcrumb -->
        <div class="flex items-center justify-between">
            <a href="{{ route('reseller.login') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span>Back to Agent Login</span>
            </a>
            <span class="text-[11px] font-mono uppercase tracking-wider text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                Agent Onboarding
            </span>
        </div>

        <div class="space-y-2">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Become an Authorized Voucher Reseller</h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Sell high-speed Wi-Fi hotspot vouchers in your retail shop, cybercafe, campus kiosk, or POS center. Earn attractive retail commissions and generate instant batches.
            </p>
        </div>

        @if($errors->any())
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-xs text-rose-400 space-y-1">
            <div class="font-bold">Please check the errors below:</div>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Application Card -->
        <form action="{{ route('reseller.apply.submit') }}" method="POST" enctype="multipart/form-data" class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl space-y-8">
            @csrf

            <!-- Section 1: Business Details -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs flex items-center justify-center">1</span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Business & Store Information</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Business / Store Trade Name *</label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}" placeholder="e.g. QuickNet Cybercafe & POS Hub" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Contact Person (Full Name) *</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Ibrahim Abubakar" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Primary Phone Number *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="08012345678" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Alternate Phone (WhatsApp)</label>
                        <input type="tel" name="alternate_phone" value="{{ old('alternate_phone') }}" placeholder="Optional" class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="agent@shop.ng" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Business Category *</label>
                    <select name="business_type" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="cybercafe" {{ old('business_type') === 'cybercafe' ? 'selected' : '' }}>Cybercafe / Business Center</option>
                        <option value="pos_agent" {{ old('business_type') === 'pos_agent' ? 'selected' : '' }}>POS & Mobile Money Agent</option>
                        <option value="phone_accessories" {{ old('business_type') === 'phone_accessories' ? 'selected' : '' }}>Phone & Computer Accessories Store</option>
                        <option value="campus_kiosk" {{ old('business_type') === 'campus_kiosk' ? 'selected' : '' }}>Campus / Student Hostel Kiosk</option>
                        <option value="hotel" {{ old('business_type') === 'hotel' ? 'selected' : '' }}>Hotel / Guest House</option>
                        <option value="retail_agent" {{ old('business_type', 'retail_agent') === 'retail_agent' ? 'selected' : '' }}>Supermarket / Retail Shop</option>
                        <option value="other" {{ old('business_type') === 'other' ? 'selected' : '' }}>Other Independent Merchant</option>
                    </select>
                </div>

@php
$nigerianStates = $nigerianStates ?? [
    'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno',
    'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT - Abuja', 'Gombe',
    'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos',
    'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto',
    'Taraba', 'Yobe', 'Zamfara'
];
@endphp
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Physical Store / Shop Address *</label>
                    <input type="text" name="shop_address" value="{{ old('shop_address') }}" placeholder="e.g. Shop 4B, Central Plaza, Ahmadu Bello Way" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">State (Nigeria) *</label>
                        <select name="state" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Select State --</option>
                            @foreach($nigerianStates as $stateOption)
                                <option value="{{ $stateOption }}" {{ old('state') === $stateOption ? 'selected' : '' }}>{{ $stateOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">City / Town *</label>
                        <input type="text" name="city" value="{{ old('city') }}" placeholder="e.g. Ikeja, Wuse 2, Kano City" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Section 2: KYC Identity Verification -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs flex items-center justify-center">2</span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-400">KYC Compliance & Identity Document</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Identity Document Type *</label>
                        <select name="id_type" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="nin" {{ old('id_type') === 'nin' ? 'selected' : '' }}>National Identity Number (NIN Slip)</option>
                            <option value="drivers_license" {{ old('id_type') === 'drivers_license' ? 'selected' : '' }}>Federal Driver's License</option>
                            <option value="voters_card" {{ old('id_type') === 'voters_card' ? 'selected' : '' }}>Permanent Voter's Card (PVC)</option>
                            <option value="passport" {{ old('id_type') === 'passport' ? 'selected' : '' }}>International Passport</option>
                            <option value="cac_registration" {{ old('id_type') === 'cac_registration' ? 'selected' : '' }}>CAC Business Certificate (BN / RC)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Identification Number *</label>
                        <input type="text" name="id_number" value="{{ old('id_number') }}" placeholder="e.g. NIN or License Number" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Upload ID Document (PDF, PNG, JPG — Max 5MB) *</label>
                    <div class="border-2 border-dashed border-slate-700 hover:border-emerald-500 rounded-2xl p-4 bg-slate-800/40 text-center transition-colors">
                        <input type="file" name="id_document" required accept=".pdf,.png,.jpg,.jpeg" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 cursor-pointer">
                        <p class="text-[11px] text-slate-500 mt-2">Clear photo or scanned copy of your government-issued ID card or CAC certificate.</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Portal Security & Access -->
            <div x-data="{
                password: '{{ old('password', '') }}',
                password_confirmation: '{{ old('password_confirmation', '') }}',
                showPassword: false,
                showConfirm: false,
                get lengthValid() { return this.password.length >= 8; },
                get uppercaseValid() { return /[A-Z]/.test(this.password); },
                get lowercaseValid() { return /[a-z]/.test(this.password); },
                get numberOrSymbolValid() { return /[0-9\W_]/.test(this.password); },
                get matchValid() { return this.password.length > 0 && this.password === this.password_confirmation; },
                get score() {
                    let s = 0;
                    if (this.lengthValid) s++;
                    if (this.uppercaseValid) s++;
                    if (this.lowercaseValid) s++;
                    if (this.numberOrSymbolValid) s++;
                    return s;
                },
                get strengthLabel() {
                    if (this.password.length === 0) return 'Not entered';
                    if (this.score <= 1) return 'Weak';
                    if (this.score === 2) return 'Fair';
                    if (this.score === 3) return 'Good';
                    return 'Strong';
                },
                get strengthColor() {
                    if (this.score <= 1) return 'bg-rose-500';
                    if (this.score === 2) return 'bg-amber-500';
                    if (this.score === 3) return 'bg-sky-500';
                    return 'bg-emerald-500';
                },
                get strengthTextColor() {
                    if (this.score <= 1) return 'text-rose-400';
                    if (this.score === 2) return 'text-amber-400';
                    if (this.score === 3) return 'text-sky-400';
                    return 'text-emerald-400';
                },
                get strengthPercent() {
                    if (this.password.length === 0) return 0;
                    return (this.score / 4) * 100;
                }
            }" class="space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs flex items-center justify-center">3</span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Account Password & Security</h2>
                </div>

                <!-- Password Policy Notice Card -->
                <div class="p-4 bg-slate-800/60 border border-slate-700/80 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-200">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Password Security Policy & Checklist</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] font-mono">
                            <span class="text-slate-400">Strength:</span>
                            <span :class="strengthTextColor" class="font-bold" x-text="strengthLabel">Not entered</span>
                        </div>
                    </div>

                    <!-- Live Strength Meter Bar -->
                    <div class="w-full h-1.5 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full transition-all duration-300 rounded-full"
                             :class="strengthColor"
                             :style="`width: ${strengthPercent}%`"></div>
                    </div>

                    <!-- Policy rules list with live indicators -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1 text-[11px]">
                        <div class="flex items-center gap-2 transition-colors" :class="lengthValid ? 'text-emerald-400 font-medium' : 'text-slate-400'">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] shrink-0"
                                  :class="lengthValid ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-500'">
                                <svg x-show="lengthValid" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!lengthValid">&bull;</span>
                            </span>
                            <span>At least 8 characters long</span>
                        </div>

                        <div class="flex items-center gap-2 transition-colors" :class="uppercaseValid ? 'text-emerald-400 font-medium' : 'text-slate-400'">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] shrink-0"
                                  :class="uppercaseValid ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-500'">
                                <svg x-show="uppercaseValid" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!uppercaseValid">&bull;</span>
                            </span>
                            <span>At least one uppercase letter (A-Z)</span>
                        </div>

                        <div class="flex items-center gap-2 transition-colors" :class="lowercaseValid ? 'text-emerald-400 font-medium' : 'text-slate-400'">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] shrink-0"
                                  :class="lowercaseValid ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-500'">
                                <svg x-show="lowercaseValid" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!lowercaseValid">&bull;</span>
                            </span>
                            <span>At least one lowercase letter (a-z)</span>
                        </div>

                        <div class="flex items-center gap-2 transition-colors" :class="numberOrSymbolValid ? 'text-emerald-400 font-medium' : 'text-slate-400'">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] shrink-0"
                                  :class="numberOrSymbolValid ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-500'">
                                <svg x-show="numberOrSymbolValid" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!numberOrSymbolValid">&bull;</span>
                            </span>
                            <span>At least one number or symbol (0-9, @, #, etc.)</span>
                        </div>

                        <div class="flex items-center gap-2 sm:col-span-2 transition-colors" :class="matchValid ? 'text-emerald-400 font-medium' : (password_confirmation.length > 0 ? 'text-rose-400' : 'text-slate-400')">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] shrink-0"
                                  :class="matchValid ? 'bg-emerald-500/20 text-emerald-400' : (password_confirmation.length > 0 ? 'bg-rose-500/20 text-rose-400' : 'bg-slate-700 text-slate-500')">
                                <svg x-show="matchValid" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!matchValid">&bull;</span>
                            </span>
                            <span x-text="matchValid ? 'Passwords match correctly' : (password_confirmation.length > 0 ? 'Passwords do not match yet' : 'Password confirmation must match')"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Create Portal Password *</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'"
                                   name="password"
                                   x-model="password"
                                   required
                                   minlength="8"
                                   placeholder="Minimum 8 characters"
                                   class="w-full px-3.5 py-2.5 pr-10 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Confirm Password *</label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'"
                                   name="password_confirmation"
                                   x-model="password_confirmation"
                                   required
                                   minlength="8"
                                   placeholder="Re-type password"
                                   class="w-full px-3.5 py-2.5 pr-10 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <button type="button"
                                    @click="showConfirm = !showConfirm"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none">
                                <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showConfirm" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Additional Information / Proposed Coverage Area (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Tell us about your customer footfall, target hotspot location, or estimated monthly sales volume..." class="w-full px-3.5 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[11px] text-slate-400">
                    By submitting this application, you confirm all provided KYC information is genuine. Applications are verified within 24 hours.
                </p>
                <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all shrink-0">
                    Submit Agent Application &rarr;
                </button>
            </div>
        </form>

    </div>

</body>
</html>
