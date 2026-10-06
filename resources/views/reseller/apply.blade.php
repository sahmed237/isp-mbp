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

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 mb-1">Physical Store / Shop Address *</label>
                        <input type="text" name="shop_address" value="{{ old('shop_address') }}" placeholder="e.g. Shop 4B, Central Plaza, Ahmadu Bello Way" required class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">City & State *</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="city" value="{{ old('city') }}" placeholder="City" required class="w-full px-3 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <input type="text" name="state" value="{{ old('state') }}" placeholder="State" required class="w-full px-3 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
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
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs flex items-center justify-center">3</span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Account Password & Security</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Create Portal Password *</label>
                        <input type="password" name="password" required minlength="8" placeholder="Minimum 8 characters" class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="Re-type password" class="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
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
