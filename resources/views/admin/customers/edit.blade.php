@extends('layouts.app')

@section('title', "Edit {$customer->full_name}")
@section('page_title', "Edit Customer — {$customer->account_number}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Modify Subscriber Account</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Update contact, package, status, and installation details for {{ $customer->account_number }}.</p>
        </div>
        <a href="{{ route('customers.show', $customer) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
            &larr; Cancel
        </a>
    </div>

    <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1: Customer Identity -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">1. Subscriber Identity</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $customer->last_name) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Account Type *</label>
                    <select name="customer_type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="individual" {{ old('customer_type', $customer->customer_type) === 'individual' ? 'selected' : '' }}>Individual</option>
                        <option value="corporate" {{ old('customer_type', $customer->customer_type) === 'corporate' ? 'selected' : '' }}>Corporate / Enterprise</option>
                        <option value="government" {{ old('customer_type', $customer->customer_type) === 'government' ? 'selected' : '' }}>Government</option>
                        <option value="reseller" {{ old('customer_type', $customer->customer_type) === 'reseller' ? 'selected' : '' }}>Reseller</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Company / Organization Name (Optional)</label>
                <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <!-- Section 2: Contact Details -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">2. Contact & Site Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Primary Phone *</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alternate Phone</label>
                    <input type="text" name="alternate_phone" value="{{ old('alternate_phone', $customer->alternate_phone) }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Physical Installation Address</label>
                <textarea name="installation_address" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('installation_address', $customer->installation_address) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $customer->city) }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">State</label>
                    <input type="text" name="state" value="{{ old('state', $customer->state) }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">GPS Coordinates</label>
                    <input type="text" name="gps_coordinates" value="{{ old('gps_coordinates', $customer->gps_coordinates) }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- Section 3: Service Configuration -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">3. Service & Technology</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Connection Type *</label>
                    <select name="connection_type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach(['pppoe' => 'PPPoE Broadband', 'fibre' => 'Fibre (GPON / FTTH)', 'ptp' => 'Point-to-Point (PtP)', 'ptmp' => 'Wireless (PtMP)', 'wireless' => 'Fixed Wireless', 'dedicated' => 'Dedicated Line', 'other' => 'Other'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('connection_type', $customer->connection_type) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Branch</label>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Default HQ</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', $customer->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Internet Package</label>
                    <select name="current_package_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">No package assigned</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}" {{ old('current_package_id', $customer->current_package_id) == $pkg->id ? 'selected' : '' }}>
                                {{ $pkg->name }} ({{ $pkg->formatted_download_speed }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Account Status *</label>
                    <select name="status" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status', $customer->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="expired" {{ old('status', $customer->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="lead" {{ old('status', $customer->status) === 'lead' ? 'selected' : '' }}>Lead</option>
                        <option value="terminated" {{ old('status', $customer->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Administrative Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('notes', $customer->notes) }}</textarea>
            </div>
        </div>

        <!-- Section 4: Network Authentication & FreeRADIUS -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">4. PPPoE / Hotspot RADIUS Provisioning</h3>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-brand-500/10 text-brand-600 dark:text-brand-400">Auto-Provision to FreeRADIUS</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">RADIUS / PPPoE Username</label>
                    <input type="text" name="radius_username" value="{{ old('radius_username', $customer->radius_username) }}" placeholder="e.g. user_john or cust_1001" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Subscriber authentication username for MikroTik</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">RADIUS / PPPoE Password</label>
                    <input type="text" name="radius_password" value="{{ old('radius_password', $customer->radius_password) }}" placeholder="Password for PPPoE/Hotspot" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Saved to radcheck as Cleartext-Password</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Static Framed IP Address</label>
                    <input type="text" name="static_ip" value="{{ old('static_ip', $customer->static_ip) }}" placeholder="Optional (e.g. 192.168.88.50)" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Leave empty for dynamic pool IP</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('customers.show', $customer) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
