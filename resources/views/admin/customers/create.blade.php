@extends('layouts.app')

@section('title', 'Add New Customer')
@section('page_title', 'Register Subscriber')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Add New Subscriber</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Enroll a new broadband customer account into the ISP directory.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
            &larr; Cancel
        </a>
    </div>

    <form action="{{ route('customers.store') }}" method="POST" class="space-y-6"
        x-data="{
            packages: {{ json_encode($packages->keyBy('id')->map(fn($p) => [
                'id' => (string) $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'installation_fee' => (float) $p->installation_fee,
                'connection_type' => strtolower((string) $p->connection_type),
                'speed' => $p->formatted_download_speed,
                'label' => $p->name . ' (' . $p->formatted_download_speed . ') — ₦' . number_format((float)$p->price, 2),
            ])) }},
            selectedConnectionType: '{{ old('connection_type', 'pppoe') }}',
            selectedPackageId: '{{ old('current_package_id', '') }}',
            packageFee: '{{ old('package_fee', '') }}',
            installationFee: '{{ old('installation_fee', '') }}',
            generateInvoice: true,
            markPaid: false,
            isLoadingPackages: false,
            filteredPackages: [],

            filterPackages() {
                this.isLoadingPackages = true;
                const type = this.selectedConnectionType;

                setTimeout(() => {
                    this.filteredPackages = Object.values(this.packages).filter(pkg => {
                        if (pkg.connection_type === 'all' || pkg.connection_type === 'universal') return true;
                        if (pkg.connection_type === type) return true;
                        if ((type === 'wireless' || type === 'ptmp') && (pkg.connection_type === 'wireless' || pkg.connection_type === 'ptmp')) return true;
                        return false;
                    });

                    // If currently selected package is no longer in filtered list, reset it
                    if (this.selectedPackageId && !this.filteredPackages.some(p => p.id == this.selectedPackageId)) {
                        this.selectedPackageId = '';
                        this.packageFee = '';
                        this.installationFee = '';
                    }

                    this.isLoadingPackages = false;
                }, 300);
            },

            onPackageChange() {
                if (this.selectedPackageId && this.packages[this.selectedPackageId]) {
                    const pkg = this.packages[this.selectedPackageId];
                    this.packageFee = pkg.price;
                    this.installationFee = pkg.installation_fee;
                } else {
                    this.packageFee = '';
                    this.installationFee = '';
                }
            },

            calculateTotal() {
                const p = parseFloat(this.packageFee) || 0;
                const i = parseFloat(this.installationFee) || 0;
                return (p + i).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }"
        x-init="
            filterPackages();
            if (selectedPackageId && !packageFee && packages[selectedPackageId]) {
                onPackageChange();
            }
        "
    >
        @csrf

        <!-- Section 1: Customer Identity -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">1. Subscriber Identity</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Account Type *</label>
                    <select name="customer_type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="individual" {{ old('customer_type') === 'individual' ? 'selected' : '' }}>Individual</option>
                        <option value="corporate" {{ old('customer_type') === 'corporate' ? 'selected' : '' }}>Corporate / Enterprise</option>
                        <option value="government" {{ old('customer_type') === 'government' ? 'selected' : '' }}>Government</option>
                        <option value="reseller" {{ old('customer_type') === 'reseller' ? 'selected' : '' }}>Reseller</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Company / Organization Name (Optional)</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Required for corporate accounts" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <!-- Section 2: Contact & Installation Details -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">2. Contact & Site Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Primary Phone *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="+234 803 000 0000" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alternate Phone</label>
                    <input type="text" name="alternate_phone" value="{{ old('alternate_phone') }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="subscriber@example.ng" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Physical Installation Address</label>
                <textarea name="installation_address" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('installation_address') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', 'Abuja') }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">State</label>
                    <input type="text" name="state" value="{{ old('state', 'FCT') }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">GPS Coordinates</label>
                    <input type="text" name="gps_coordinates" value="{{ old('gps_coordinates') }}" placeholder="9.0765, 7.3986" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- Section 3: Package & Technology Assignment -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">3. Service & Network Technology</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Connection Type *</label>
                    <select name="connection_type" 
                            x-model="selectedConnectionType" 
                            @change="filterPackages()" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all">
                        <option value="pppoe" {{ old('connection_type', 'pppoe') === 'pppoe' ? 'selected' : '' }}>PPPoE Broadband</option>
                        <option value="fibre" {{ old('connection_type') === 'fibre' ? 'selected' : '' }}>Fibre (GPON / FTTH)</option>
                        <option value="ptp" {{ old('connection_type') === 'ptp' ? 'selected' : '' }}>Point-to-Point (PtP)</option>
                        <option value="ptmp" {{ old('connection_type') === 'ptmp' ? 'selected' : '' }}>Point-to-Multipoint (PtMP)</option>
                        <option value="wireless" {{ old('connection_type') === 'wireless' ? 'selected' : '' }}>Fixed Wireless</option>
                        <option value="dedicated" {{ old('connection_type') === 'dedicated' ? 'selected' : '' }}>Dedicated Leased Line</option>
                        <option value="other" {{ old('connection_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Operational Branch *</label>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Default Organization HQ</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Internet Package</label>
                        <!-- Dynamic Loading Pill Animation -->
                        <span x-show="isLoadingPackages" 
                              x-transition:enter="transition ease-out duration-150" 
                              x-transition:enter-start="opacity-0 scale-95" 
                              x-transition:enter-end="opacity-100 scale-100" 
                              class="flex items-center gap-1.5 text-[10px] font-semibold text-brand-600 dark:text-brand-400 bg-brand-500/10 px-2 py-0.5 rounded-full">
                            <svg class="animate-spin w-3 h-3 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Loading plans...</span>
                        </span>
                    </div>

                    <div class="relative">
                        <select name="current_package_id" 
                                x-model="selectedPackageId" 
                                @change="onPackageChange()" 
                                :disabled="isLoadingPackages"
                                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500 disabled:opacity-60 transition-all">
                            <option value="">No initial package</option>
                            <template x-for="pkg in filteredPackages" :key="pkg.id">
                                <option :value="pkg.id" x-text="pkg.label" :selected="pkg.id == selectedPackageId"></option>
                            </template>
                        </select>

                        <!-- Subtle Loading Overlay on the selector -->
                        <div x-show="isLoadingPackages" 
                             x-transition:enter="transition ease-out duration-150" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100" 
                             class="absolute inset-0 bg-slate-50/95 dark:bg-slate-800/95 backdrop-blur-xs rounded-xl flex items-center px-3.5 gap-2 border border-brand-500/40 text-brand-600 dark:text-brand-400 text-xs font-semibold">
                            <svg class="animate-spin w-3.5 h-3.5 text-brand-600 dark:text-brand-400 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span class="text-[11px] truncate animate-pulse">Filtering <span class="uppercase font-bold" x-text="selectedConnectionType"></span> packages...</span>
                        </div>
                    </div>

                    <!-- Filter summary or empty notice -->
                    <div x-show="!isLoadingPackages && filteredPackages.length > 0" class="mt-1 text-[10px] text-slate-400 flex items-center justify-between">
                        <span><strong class="text-slate-600 dark:text-slate-300" x-text="filteredPackages.length"></strong> plan(s) for <span class="font-bold uppercase text-brand-600 dark:text-brand-400" x-text="selectedConnectionType"></span></span>
                        <span x-show="selectedPackageId" class="text-emerald-500 font-semibold">✓ Plan attached</span>
                    </div>

                    <div x-show="!isLoadingPackages && filteredPackages.length === 0" class="mt-1.5 p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[10px] text-amber-600 dark:text-amber-400 flex items-center justify-between">
                        <span>No plans for <strong class="uppercase" x-text="selectedConnectionType"></strong></span>
                        <a href="{{ route('packages.create') }}" target="_blank" class="font-bold underline hover:text-amber-500">Create Plan &rarr;</a>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Account Status *</label>
                    <select name="status" required class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-medium focus:outline-none cursor-default">
                        <option value="lead" selected>Lead / Pending Payment</option>
                    </select>
                    <span class="text-[10px] text-amber-500 font-semibold mt-1 flex items-center gap-1">
                        🔒 PPPoE dial-in locked until initial invoice is paid.
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Administrative Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional internal comments regarding installation, optical power, or billing terms" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Section 4: Network Authentication & FreeRADIUS -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">4. PPPoE RADIUS Provisioning</h3>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-brand-500/10 text-brand-600 dark:text-brand-400">Auto-Provision to FreeRADIUS</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">RADIUS / PPPoE Username</label>
                    <input type="text" name="radius_username" value="{{ old('radius_username') }}" placeholder="e.g. user_john or cust_1001" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Subscriber authentication username for MikroTik</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">RADIUS / PPPoE Password</label>
                    <input type="text" name="radius_password" value="{{ old('radius_password') }}" placeholder="Password for PPPoE" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Saved to radcheck as Cleartext-Password</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Static Framed IP Address</label>
                    <input type="text" name="static_ip" value="{{ old('static_ip') }}" placeholder="Optional (e.g. 192.168.88.50)" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Leave empty for dynamic pool IP</span>
                </div>
            </div>
        </div>

        <!-- Section 5: Initial Subscription & Invoicing Workflow -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">5. Initial Subscription & Invoicing Flow</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Automated lifecycle onboarding: invoice generation, pricing breakdown, and payment clearance.</p>
                </div>
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Automated Lifecycle</span>
            </div>

            <div class="space-y-4 pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="generate_invoice" value="1" x-model="generateInvoice" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                    <div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Automatically Generate Initial Invoice & Subscription Record</span>
                        <p class="text-[11px] text-slate-400">Creates itemized initial invoice including package fee and custom installation fee.</p>
                    </div>
                </label>

                <div x-show="generateInvoice" class="pl-7 pt-2 border-l-2 border-brand-500/30 space-y-4">
                    <!-- Package Fee & Manual Installation Fee Fields -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Invoice Items & Charges Breakdown</span>
                            <span class="text-[11px] text-brand-600 dark:text-brand-400 font-semibold" x-show="selectedPackageId">
                                Package: <span x-text="packages[selectedPackageId]?.name"></span>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Package Subscription Fee (₦)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-bold">₦</span>
                                    <input type="number" step="0.01" min="0" name="package_fee" x-model="packageFee" placeholder="0.00" class="w-full pl-8 pr-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Defaults to package price; can be adjusted manually</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Manual Installation Fee (₦)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-bold">₦</span>
                                    <input type="number" step="0.01" min="0" name="installation_fee" x-model="installationFee" placeholder="0.00" class="w-full pl-8 pr-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Manual installation/setup fee to charge on this customer's initial invoice</span>
                            </div>
                        </div>

                        <!-- Live Total Invoice Calculation Box -->
                        <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Estimated Initial Invoice Total:</span>
                            <span class="font-extrabold text-sm text-brand-600 dark:text-brand-400 font-mono">
                                ₦<span x-text="calculateTotal()"></span>
                            </span>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer pt-1">
                        <input type="checkbox" name="mark_paid_immediately" value="1" x-model="markPaid" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Mark Initial Invoice as Paid Immediately</span>
                            <p class="text-[11px] text-slate-400">Check this if the subscriber has already paid at onboarding. The subscription is activated immediately and enabled on RADIUS for PPPoE.</p>
                        </div>
                    </label>

                    <div x-show="markPaid" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="cash">Cash Payment</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">POS / Debit Card</option>
                                <option value="paystack">Paystack</option>
                                <option value="moniepoint">Moniepoint</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-[11px] text-emerald-700 dark:text-emerald-400">
                                <span class="font-bold">Immediate Activation:</span> Customer status will be set to <strong>Active</strong> with full PPPoE bandwidth speed authorization.
                            </div>
                        </div>
                    </div>

                    <div x-show="!markPaid" class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-700 dark:text-amber-400">
                        <span class="font-bold">Pending Payment:</span> An unpaid invoice will be issued. Subscriber will be placed in <strong>Lead/Pending</strong> status and blocked from PPPoE dial-in until payment is made in cash, by bank transfer, or online via the subscriber self-service portal.
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 6: Subscriber Self-Service Portal Access -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">6. Customer Self-Service Portal Credentials</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Enables subscriber to log in at <code>/portal</code> to view subscriptions, usage, download invoices, and pay online.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Portal Username / Login Identifier</label>
                    <input type="text" name="portal_username" value="{{ old('portal_username') }}" placeholder="Defaults to email or account number" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Subscriber can also log in using their email or Account ID</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Portal Security Password</label>
                    <input type="text" name="portal_password" value="{{ old('portal_password', '123456') }}" placeholder="Defaults to 123456" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Default initial password is 123456</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                Save & Enroll Customer
            </button>
        </div>
    </form>
</div>
@endsection
