@extends('layouts.app')

@section('title', "Customer: {$customer->full_name}")
@section('page_title', "Subscriber Profile — {$customer->account_number}")

@section('content')
<div class="space-y-6">

    <!-- Customer Header Profile Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-brand-500/20 flex-shrink-0">
                    {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                </div>
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ $customer->full_name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize {{ $customer->status_badge_class }}">
                            {{ $customer->status }}
                        </span>
                        <span class="px-2 py-0.5 rounded font-mono text-xs font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            {{ $customer->connection_type }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                        <span class="font-mono font-bold text-brand-600 dark:text-brand-400">ID: {{ $customer->account_number }}</span>
                        <span>&bull;</span>
                        <span>{{ $customer->phone }}</span>
                        <span>&bull;</span>
                        <span>{{ $customer->email ?? 'No email on record' }}</span>
                        <span>&bull;</span>
                        <span>Branch: {{ $customer->branch?->name ?? 'Headquarters' }}</span>
                    </div>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex items-center gap-3">
                @can('customers.update')
                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Profile</span>
                </a>
                @endcan

                <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-medium">
                    &larr; Back
                </a>
            </div>
        </div>

        <!-- Tab Navigation Bar -->
        <div class="flex items-center gap-2 border-t border-slate-100 dark:border-slate-800 mt-6 pt-4 overflow-x-auto text-xs font-bold scrollbar-none">
            @php
                $tabs = [
                    'overview' => 'Overview',
                    'contact' => 'Contact & Address',
                    'services' => 'Services & Bandwidth',
                    'subscriptions' => 'Subscriptions',
                    'invoices' => 'Invoices',
                    'payments' => 'Payments',
                    'network' => 'Network & CPE',
                    'radius' => 'RADIUS Sessions',
                    'tickets' => 'Support Tickets',
                    'documents' => 'Documents & KYC',
                    'activity' => 'Audit Activity',
                ];
            @endphp

            @foreach($tabs as $key => $label)
                <a
                    href="{{ route('customers.show', [$customer, 'tab' => $key]) }}"
                    class="px-3.5 py-2 rounded-xl whitespace-nowrap transition-all {{ $activeTab === $key ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="space-y-6">

        @if($activeTab === 'overview')
        <!-- Tab: OVERVIEW -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Primary Customer Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Account Summary</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block mb-0.5">Account Number</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $customer->account_number }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Account Type</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 capitalize">{{ $customer->customer_type }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Primary Phone</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $customer->phone }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Email</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $customer->email ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Technology</span>
                            <span class="font-mono uppercase font-bold text-brand-600 dark:text-brand-400">{{ $customer->connection_type }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Enrolled Since</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $customer->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    @if($customer->notes)
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <span class="text-slate-400 font-semibold block mb-1">Administrative Notes:</span>
                        <p class="text-slate-600 dark:text-slate-300 italic bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl">{{ $customer->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Installation Address Section -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Installation Site</h3>
                    <div class="text-xs space-y-2">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-slate-800 dark:text-slate-200">{{ $customer->installation_address ?? 'No physical address specified' }}</span>
                        </div>
                        <div class="flex items-center gap-4 text-slate-500 pl-6">
                            <span>City: <strong class="text-slate-700 dark:text-slate-300">{{ $customer->city ?? 'N/A' }}</strong></span>
                            <span>State: <strong class="text-slate-700 dark:text-slate-300">{{ $customer->state ?? 'N/A' }}</strong></span>
                            <span>GPS: <strong class="font-mono text-slate-700 dark:text-slate-300">{{ $customer->gps_coordinates ?? 'N/A' }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Active Internet Package & Financial Balance -->
            <div class="space-y-6">

                <!-- Internet Package Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Current Plan</h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Broadband</span>
                    </div>

                    @if($customer->package)
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-brand-50 to-indigo-50/50 dark:from-slate-800/80 dark:to-slate-800 border border-brand-200/60 dark:border-slate-700 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-extrabold text-slate-900 dark:text-slate-100">{{ $customer->package->name }}</span>
                            <span class="text-sm font-black text-brand-600 dark:text-brand-400">₦{{ number_format((float)$customer->package->price, 2) }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-brand-200/40 dark:border-slate-700">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Download</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $customer->package->formatted_download_speed }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Upload</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $customer->package->formatted_upload_speed }}</span>
                            </div>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1">
                            Cycle: <strong class="capitalize">{{ $customer->package->billing_cycle }}</strong> &bull; Validity: <strong>{{ $customer->package->validity_period }} Days</strong>
                        </div>
                    </div>
                    @else
                    <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/50 text-center text-xs text-slate-400">
                        No internet package currently assigned.
                    </div>
                    @endif
                </div>

                <!-- Financial Balance Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Account Balance</h3>
                    <div class="text-2xl font-black {{ $customer->balance < 0 ? 'text-rose-500' : 'text-slate-800 dark:text-slate-100' }}">
                        ₦{{ number_format((float)$customer->balance, 2) }}
                    </div>
                    <div class="text-xs text-slate-400">
                        @if($customer->balance < 0)
                            <span class="text-rose-500 font-semibold">&bull; Overdue balance pending payment reconciliation.</span>
                        @else
                            <span class="text-emerald-500 font-semibold">&check; Account is in good standing.</span>
                        @endif
                    </div>
                </div>

            </div>

        </div>

        @elseif($activeTab === 'contact')
        <!-- Tab: CONTACT & ADDRESS -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Contact & Physical Installation Site</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Primary Contact Information</h4>
                    <p><strong>Full Name:</strong> {{ $customer->full_name }}</p>
                    <p><strong>Primary Phone:</strong> {{ $customer->phone }}</p>
                    <p><strong>Alternate Phone:</strong> {{ $customer->alternate_phone ?? 'None' }}</p>
                    <p><strong>Email Address:</strong> {{ $customer->email ?? 'None' }}</p>
                </div>
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Physical Site Location</h4>
                    <p><strong>Address:</strong> {{ $customer->installation_address ?? 'Not specified' }}</p>
                    <p><strong>City / State:</strong> {{ $customer->city ?? 'N/A' }}, {{ $customer->state ?? 'N/A' }}</p>
                    <p><strong>GPS Coordinates:</strong> <span class="font-mono">{{ $customer->gps_coordinates ?? 'N/A' }}</span></p>
                </div>
            </div>
        </div>

        @elseif($activeTab === 'services')
        <!-- Tab: SERVICES & BANDWIDTH -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Broadband Service Configuration</h3>
            @if($customer->package)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Package Name</span>
                    <span class="text-sm font-extrabold text-slate-800 dark:text-slate-200">{{ $customer->package->name }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Download / Upload</span>
                    <span class="text-sm font-extrabold text-slate-800 dark:text-slate-200">{{ $customer->package->formatted_download_speed }} / {{ $customer->package->formatted_upload_speed }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">MikroTik Rate-Limit</span>
                    <span class="font-mono text-sm font-bold text-brand-600 dark:text-brand-400">{{ $customer->package->toMikrotikRateLimitString() }}</span>
                </div>
            </div>
            @else
            <p class="text-xs text-slate-400">No package assigned.</p>
            @endif
        </div>

        @elseif($activeTab === 'radius')
        <!-- Tab: RADIUS SESSIONS & AAA -->
        <div class="space-y-6">
            <!-- RADIUS Account Provisioning Summary -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">RADIUS Provisioning Configuration</h3>
                    @if($customer->radius_username)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400">
                            &check; Provisioned to PostgreSQL radcheck
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20 dark:text-amber-400">
                            Not Provisioned
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">RADIUS Username</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $customer->radius_username ?? 'None set' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Framed IP Assignment</span>
                        <span class="font-mono font-bold text-brand-600 dark:text-brand-400 text-sm">{{ $customer->static_ip ?? 'Dynamic Pool' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">MikroTik Rate-Limit</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $customer->package?->toMikrotikRateLimitString() ?? 'None' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">RADIUS Policy Status</span>
                        <span class="font-bold capitalize {{ $customer->status === 'active' ? 'text-emerald-500' : 'text-rose-500' }}">
                            {{ $customer->status === 'active' ? 'Auth Allowed' : 'Auth Rejected (Suspended)' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Active Online Session -->
            @php $activeSession = $customer->activeRadiusSession; @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Live Active Session</h3>
                    @if($activeSession)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online Now
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-400">
                            Offline
                        </span>
                    @endif
                </div>

                @if($activeSession)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Assigned IP</span>
                        <span class="font-mono font-bold text-brand-600 dark:text-brand-400 text-sm">{{ $activeSession->framedipaddress }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">NAS Router IP</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $activeSession->nasipaddress }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Current Uptime</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $activeSession->formatted_session_time }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Traffic Consumed</span>
                        <span class="font-bold text-emerald-500">&darr; {{ $activeSession->formatted_output_octets }}</span>
                        <span class="text-slate-400 mx-1">/</span>
                        <span class="font-bold text-blue-500">&uarr; {{ $activeSession->formatted_input_octets }}</span>
                    </div>
                </div>
                @else
                <p class="text-xs text-slate-400 italic">No active session currently recorded in radacct for {{ $customer->radius_username ?? 'this subscriber' }}.</p>
                @endif
            </div>

            <!-- Historical Sessions & Recent Auth Logs -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Session History -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Recent Completed Sessions (radacct)</h4>
                    @php $pastSessions = $customer->radiusSessions()->whereNotNull('acctstoptime')->latest('acctstoptime')->limit(5)->get(); @endphp
                    @if($pastSessions->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 uppercase font-bold">
                                    <th class="py-2">Stop Time</th>
                                    <th class="py-2">Duration</th>
                                    <th class="py-2">Total Data</th>
                                    <th class="py-2">Cause</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($pastSessions as $ps)
                                <tr>
                                    <td class="py-2 text-slate-500">{{ $ps->acctstoptime?->format('M d, H:i') }}</td>
                                    <td class="py-2 font-medium text-slate-800 dark:text-slate-200">{{ $ps->formatted_session_time }}</td>
                                    <td class="py-2 font-bold text-slate-900 dark:text-slate-100">{{ $ps->formatted_total_octets }}</td>
                                    <td class="py-2 text-[10px] font-mono text-slate-400">{{ $ps->acctterminatecause ?? 'Normal' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-xs text-slate-400 italic">No historical session records found.</p>
                    @endif
                </div>

                <!-- Auth Logs -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Recent Login Attempts (radpostauth)</h4>
                    @php $recentAuths = $customer->radiusAuthLogs()->limit(5)->get(); @endphp
                    @if($recentAuths->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 uppercase font-bold">
                                    <th class="py-2">Timestamp</th>
                                    <th class="py-2">Response</th>
                                    <th class="py-2">Calling MAC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($recentAuths as $ra)
                                <tr>
                                    <td class="py-2 text-slate-500 text-[11px] font-mono">{{ $ra->authdate?->format('M d, H:i:s') }}</td>
                                    <td class="py-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $ra->is_success ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                                            {{ $ra->reply }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-[11px] font-mono text-slate-400">{{ $ra->callingstationid ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-xs text-slate-400 italic">No authentication attempts logged yet.</p>
                    @endif
                </div>
            </div>
        </div>

        @elseif($activeTab === 'documents')
        <!-- Tab: DOCUMENTS & KYC -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">KYC & Customer Attachments</h3>
                        <p class="text-xs text-slate-400">National IDs, installation forms, proofs of residence, and contracts.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modal-upload-doc').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Upload Document</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                                <th class="py-3 px-4">Document Title</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">File Name & Size</th>
                                <th class="py-3 px-4">Uploaded By</th>
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($customer->documents as $doc)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-slate-100">
                                    {{ $doc->title }}
                                    @if($doc->notes)
                                        <span class="block text-[11px] text-slate-400 font-normal truncate max-w-xs">{{ $doc->notes }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        {{ $doc->document_type_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                                    <div>{{ $doc->file_name }}</div>
                                    <span class="text-slate-400 text-[10px]">{{ $doc->formatted_file_size }}</span>
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $doc->uploadedBy?->name ?? 'System' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $doc->created_at->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('customers.documents.download', $doc) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-600 dark:bg-brand-950/40 text-xs font-bold transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            <span>Download</span>
                                        </a>
                                        <form action="{{ route('customers.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded-lg hover:bg-rose-500/10 text-slate-400 hover:text-rose-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <div class="font-bold">No documents attached to this customer</div>
                                    <div class="text-[11px] mt-0.5">Click "Upload Document" to attach IDs, utility bills, or installation forms.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Upload Customer Document -->
        <div id="modal-upload-doc" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-lg w-full space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Upload KYC / Customer Document</h3>
                    <button type="button" onclick="document.getElementById('modal-upload-doc').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-base">&times;</button>
                </div>

                <form action="{{ route('customers.documents.store', $customer) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Document Title *</label>
                        <input type="text" name="title" placeholder="e.g. National ID Card or SLA Agreement" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Document Classification *</label>
                        <select name="document_type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="national_id">National ID / International Passport / NIN</option>
                            <option value="utility_bill">Proof of Address / Utility Bill</option>
                            <option value="caf_form">Customer Application Form (CAF)</option>
                            <option value="installation_signoff">Installation & CPE Sign-Off Sheet</option>
                            <option value="sla_contract">Corporate SLA Contract / Agreement</option>
                            <option value="other" selected>Other KYC Attachment</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select File (PDF, Images, DOCX — Max 10MB) *</label>
                        <input type="file" name="file" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 dark:file:bg-slate-800 dark:file:text-slate-300">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Internal Notes</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes regarding verification or expiry" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" onclick="document.getElementById('modal-upload-doc').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-500">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30">Upload Document</button>
                    </div>
                </form>
            </div>
        </div>

        @elseif($activeTab === 'subscriptions')
        <!-- Tab: SUBSCRIPTIONS -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Broadband Subscriptions</h3>
                <a href="{{ route('subscriptions.index', ['customer_id' => $customer->id]) }}" class="text-xs font-bold text-brand-600 hover:underline">
                    View in Module &rarr;
                </a>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-3 px-4">Subscription #</th>
                            <th class="py-3 px-4">Package</th>
                            <th class="py-3 px-4">Valid From</th>
                            <th class="py-3 px-4">Expires At</th>
                            <th class="py-3 px-4">Price</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($customer->subscriptions as $sub)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">
                                <a href="{{ route('subscriptions.show', $sub) }}" class="hover:underline">{{ $sub->subscription_number }}</a>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">
                                {{ $sub->package->name }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $sub->starts_at?->format('M d, Y') ?? 'Pending' }}</td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $sub->expires_at?->format('M d, Y') ?? 'Pending' }}
                                @if($sub->isActive())
                                <span class="block text-[10px] font-bold text-emerald-600">{{ $sub->days_remaining }} days left</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-slate-100">₦{{ number_format((float)$sub->price, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $sub->status_badge_class }}">
                                    {{ $sub->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('subscriptions.show', $sub) }}" class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-semibold">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No subscriptions found for this subscriber.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @elseif($activeTab === 'invoices')
        <!-- Tab: INVOICES -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Subscriber Invoices</h3>
                @can('invoices.create')
                <a href="{{ route('invoices.create') }}" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-sm">
                    + Generate Invoice
                </a>
                @endcan
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Issue Date</th>
                            <th class="py-3 px-4">Due Date</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Paid</th>
                            <th class="py-3 px-4">Balance</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($customer->invoices as $inv)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">
                                <a href="{{ route('invoices.show', $inv) }}" class="hover:underline">{{ $inv->invoice_number }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $inv->issue_date->format('M d, Y') }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $inv->due_date->format('M d, Y') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-slate-100 font-mono">₦{{ number_format((float)$inv->total_amount, 2) }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-600 font-mono">₦{{ number_format((float)$inv->paid_amount, 2) }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200 font-mono">₦{{ number_format((float)$inv->balance_due, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $inv->status_badge_class }}">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('invoices.show', $inv) }}" class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-semibold">
                                    View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">No invoices on record for this customer.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @elseif($activeTab === 'payments')
        <!-- Tab: PAYMENTS -->
        <div class="space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Payment History & Receipts</h3>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-3 px-4">Payment #</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Method</th>
                            <th class="py-3 px-4">Reference</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4 text-right">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($customer->payments as $pmt)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">
                                <a href="{{ route('payments.show', $pmt) }}" class="hover:underline">{{ $pmt->payment_number }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $pmt->paid_at->format('M d, Y H:i') }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-400">{{ $pmt->invoice?->invoice_number ?? 'Direct' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold capitalize {{ $pmt->method_badge_class }}">
                                    {{ str_replace('_', ' ', $pmt->payment_method) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $pmt->reference ?? '—' }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">₦{{ number_format((float)$pmt->amount, 2) }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('payments.print', $pmt) }}" target="_blank" class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-semibold">
                                    Receipt
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No payment records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @else
        <!-- Placeholder for tickets / network -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 capitalize">{{ $tabs[$activeTab] ?? $activeTab }} Module</h4>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                This tab is scheduled for future network topology and ticket integration phases.
            </p>
        </div>
        @endif

    </div>

</div>
@endsection
