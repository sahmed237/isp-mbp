@extends('layouts.app')

@section('title', 'System Settings')
@section('page_title', 'Platform Settings')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">System Configuration & Integrations</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                    Super Admin Only
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Configure global ISP parameters, SMTP mail gateway, payment gateways, and network alert settings.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <!-- Sidebar Navigation Tabs -->
        <div class="md:col-span-1 space-y-1.5">
            @php
                $tabs = [
                    'general' => [
                        'label' => 'General & Billing',
                        'desc' => 'ISP identity, currency & defaults',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
                    ],
                    'email' => [
                        'label' => 'Email & SMTP',
                        'desc' => 'Mail server, ports & test sender',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'
                    ],
                    'payments' => [
                        'label' => 'Payment Gateways',
                        'desc' => 'Paystack, Monnify & Zainpay',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>'
                    ],
                    'notifications' => [
                        'label' => 'Alerts & SMS',
                        'desc' => 'SMS gateway & trigger events',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'
                    ],
                ];
            @endphp

            @foreach($tabs as $tabKey => $tab)
                <a href="{{ route('settings.index', ['group' => $tabKey]) }}"
                   class="flex items-center gap-3 p-3.5 rounded-2xl transition-all {{ $group === $tabKey ? 'bg-brand-600 text-white shadow-md shadow-brand-600/20' : 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                    <div class="p-2 rounded-xl {{ $group === $tabKey ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {!! $tab['icon'] !!}
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold">{{ $tab['label'] }}</div>
                        <div class="text-[10px] {{ $group === $tabKey ? 'text-white/80' : 'text-slate-400' }}">{{ $tab['desc'] }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Main Form Area -->
        <div class="md:col-span-3 space-y-6">

            @if($group === 'payments')
                @php
                    $gateways = ['paystack', 'monnify', 'zainpay'];
                @endphp

                @foreach($gateways as $gw)
                    @php
                        $gwSettings = $settings->filter(fn($s) => str_starts_with($s->key, $gw))
                            ->sortBy(function ($s) {
                                if (str_ends_with($s->key, '_active')) return 1;
                                if (str_ends_with($s->key, '_live')) return 2;
                                return 3;
                            });
                        $isActive = $gwSettings->where('key', $gw . '_active')->first()?->value == '1';
                        $isLive = $gwSettings->where('key', $gw . '_mode_live')->first()?->value == '1';
                    @endphp

                    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($gw, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100">{{ ucfirst($gw) }} Gateway</h3>
                                    <p class="text-xs text-slate-400">Online subscriber collections via card, bank transfer & USSD</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                                    {{ $isActive ? 'Enabled' : 'Disabled' }}
                                </span>
                                @if($isActive)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $isLive ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }}">
                                        {{ $isLive ? 'Live Mode' : 'Test Mode' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="group" value="payments">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($gwSettings as $setting)
                                    @if($setting->type === 'boolean' || str_ends_with($setting->key, '_active') || str_ends_with($setting->key, '_live'))
                                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                                            <div>
                                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                                    {{ str_contains($setting->key, 'active') ? 'Enable ' . ucfirst($gw) : 'Production (Live) Mode' }}
                                                </span>
                                                <p class="text-[10px] text-slate-400">
                                                    {{ str_contains($setting->key, 'active') ? 'Allow subscribers to pay via this gateway' : 'Toggle between sandbox test keys and live processing' }}
                                                </p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="{{ $setting->key }}" value="1" {{ $setting->value == '1' ? 'checked' : '' }} class="sr-only peer">
                                                <div class="w-10 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                                            </label>
                                        </div>
                                    @else
                                        <div class="{{ str_contains($setting->key, 'key') || str_contains($setting->key, 'token') ? 'sm:col-span-2' : '' }}">
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                {{ ucwords(str_replace(['_', $gw], [' ', ''], $setting->key)) }}
                                            </label>
                                            <input type="{{ str_contains($setting->key, 'secret') ? 'password' : 'text' }}"
                                                   name="{{ $setting->key }}"
                                                   value="{{ $setting->value }}"
                                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 font-bold text-xs shadow-sm transition-all">
                                    Save {{ ucfirst($gw) }} Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach

            @elseif($group === 'email')
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100">SMTP Mail Server Settings</h3>
                        <p class="text-xs text-slate-400">Configure outbound email delivery for customer invoices, payment receipts, password resets, and network announcements.</p>
                    </div>

                    <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="group" value="email">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($settings as $setting)
                                <div class="{{ in_array($setting->key, ['mail_host', 'mail_username', 'mail_password', 'mail_from_address']) ? 'sm:col-span-1' : '' }}">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    </label>
                                    <input type="{{ $setting->key === 'mail_password' ? 'password' : 'text' }}"
                                           name="{{ $setting->key }}"
                                           value="{{ $setting->value }}"
                                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                                Save SMTP Configuration
                            </button>
                        </div>
                    </form>

                    <!-- Interactive Diagnostic Mail Sender -->
                    <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4" x-data="{
                        recipient: '{{ Auth::user()->email }}',
                        loading: false,
                        resultMsg: '',
                        isSuccess: false,
                        sendTest() {
                            if (!this.recipient) return;
                            this.loading = true;
                            this.resultMsg = '';

                            fetch('{{ route('settings.test-mail') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ email: this.recipient })
                            })
                            .then(res => res.json())
                            .then(data => {
                                this.loading = false;
                                this.isSuccess = data.success;
                                this.resultMsg = data.message;
                            })
                            .catch(err => {
                                this.loading = false;
                                this.isSuccess = false;
                                this.resultMsg = 'Network or server error while testing mail delivery.';
                            });
                        }
                    }">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">SMTP Diagnostics & Connection Test</h4>
                                <p class="text-[11px] text-slate-400">Send an instant test email to verify DNS, TLS handshake, and mailbox credentials.</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="email" x-model="recipient" placeholder="recipient@example.com" class="flex-1 px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <button type="button" @click="sendTest()" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-bold text-xs hover:bg-slate-800 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                                <span x-show="!loading">Send Diagnostic Test</span>
                                <span x-show="loading" class="flex items-center gap-2">
                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Testing Connection...
                                </span>
                            </button>
                        </div>

                        <div x-show="resultMsg" :class="isSuccess ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 border-rose-500/20 text-rose-600 dark:text-rose-400'" class="p-3.5 rounded-xl border text-xs font-semibold">
                            <span x-text="resultMsg"></span>
                        </div>
                    </div>
                </div>

            @else
                <!-- General / Notifications Settings -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100">{{ ucfirst($group) }} Configuration</h3>
                        <p class="text-xs text-slate-400">Configure global parameters and behavioral options for the {{ $group }} module.</p>
                    </div>

                    <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="group" value="{{ $group }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($settings as $setting)
                                @if($setting->type === 'boolean')
                                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700 sm:col-span-2">
                                        <div>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                                {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                            </span>
                                            <p class="text-[10px] text-slate-400">Enable or disable automated system behavior for this event</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="{{ $setting->key }}" value="1" {{ $setting->value == '1' ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-10 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                                        </label>
                                    </div>
                                @else
                                    <div class="{{ in_array($setting->key, ['address', 'company_legal_name', 'sms_api_key']) ? 'sm:col-span-2' : '' }}">
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </label>
                                        <input type="{{ $setting->type === 'integer' || $setting->type === 'float' ? 'number' : 'text' }}"
                                               name="{{ $setting->key }}"
                                               value="{{ $setting->value }}"
                                               step="{{ $setting->type === 'float' ? '0.01' : '1' }}"
                                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                                Save {{ ucfirst($group) }} Settings
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
