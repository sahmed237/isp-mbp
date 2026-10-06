<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Hotspot WiFi Access — {{ $companyInfo['company_name'] ?? 'ISP Hotspot' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-full flex flex-col font-sans selection:bg-brand-500 selection:text-white">

    <!-- Top Banner -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-extrabold tracking-tight text-white flex items-center gap-2">
                        {{ $companyInfo['company_name'] ?? 'ISP High-Speed WiFi' }}
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Hotspot Active
                        </span>
                    </h1>
                    <p class="text-[11px] text-slate-400">Instant Self-Service WiFi Voucher Portal</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('public.hotspot.lookup') }}" class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition">
                    Retrieve Voucher
                </a>
                <a href="{{ route('portal.login') }}" class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-slate-700 text-slate-400 hover:text-white transition hidden sm:inline-block">
                    Subscriber Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container with Alpine State -->
    <main x-data="{
        selectedPackage: null,
        modalOpen: false,
        phone: '{{ old('customer_phone') }}',
        email: '{{ old('customer_email') }}',
        gateway: '{{ old('payment_method', $paystackActive ? 'paystack' : ($monnifyActive ? 'monnify' : '')) }}',
        openCheckout(pkg) {
            this.selectedPackage = pkg;
            this.modalOpen = true;
        },
        isValid() {
            const cleanPhone = this.phone.replace(/\D/g, '');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return cleanPhone.length >= 10 && cleanPhone.length <= 15 && emailRegex.test(this.email) && this.gateway;
        }
    }" class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-8 sm:py-12">

        <!-- Hero Section -->
        <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-14 space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-300 text-xs font-semibold">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Ultra-Fast Broadband • Low Latency • Instant Activation
            </div>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                Connect in Seconds. <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 via-indigo-300 to-sky-400">Choose Your Plan.</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                Purchase your internet voucher online with Debit Card or Bank Transfer. Your access credentials are generated immediately on screen and ready to browse.
            </p>
        </div>

        <!-- Flash Messages -->
        @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('info'))
        <div class="mb-6 p-4 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('info') }}</span>
        </div>
        @endif

        <!-- Packages Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($packages as $package)
            <div class="relative group bg-slate-900/80 hover:bg-slate-900 border border-slate-800 hover:border-brand-500/50 rounded-3xl p-6 transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-brand-500/10 flex flex-col justify-between">
                <div>
                    <!-- Package Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-400 bg-brand-500/10 px-2 py-0.5 rounded-md border border-brand-500/20">
                                {{ ucfirst($package->connection_type) }} Access
                            </span>
                            <h3 class="text-lg font-bold text-white mt-1.5">{{ $package->name }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 group-hover:text-brand-400 group-hover:border-brand-500/30 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="mb-6 pb-6 border-b border-slate-800">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs text-slate-400">₦</span>
                            <span class="text-3xl font-extrabold text-white tracking-tight font-mono">{{ number_format((float)$package->price, 2) }}</span>
                        </div>
                        <span class="text-[11px] text-slate-400">Valid for {{ $package->validity_period }} {{ $package->validity_period === 1 ? 'day' : 'days' }}</span>
                    </div>

                    <!-- Specs List -->
                    <ul class="space-y-2.5 text-xs text-slate-300 mb-6">
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Speed: <strong>{{ $package->download_speed }} Mbps</strong> Down / <strong>{{ $package->upload_speed }} Mbps</strong> Up</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Data Quota: <strong>Unlimited Browsing</strong></span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Devices: 1 Device per Active PIN</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Instant SMS & On-Screen Delivery</span>
                        </li>
                    </ul>
                </div>

                <!-- Buy Action Button -->
                <button
                    type="button"
                    @click="openCheckout({
                        id: {{ $package->id }},
                        name: '{{ addslashes($package->name) }}',
                        price: '{{ number_format((float)$package->price, 2) }}',
                        validity: '{{ $package->validity_period }} days'
                    })"
                    class="w-full py-3 px-4 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg shadow-brand-600/30 transition-all flex items-center justify-center gap-2 group-hover:scale-[1.02]"
                >
                    <span>Buy Access Voucher</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-slate-500">
                <p>No active hotspot plans currently available. Please check back shortly.</p>
            </div>
            @endforelse
        </div>

        <!-- How It Works Strip -->
        <div class="mt-16 bg-slate-900/60 border border-slate-800/80 rounded-3xl p-8">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-400 text-center mb-6">3 Simple Steps to Get Connected</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-2xl bg-brand-500/10 text-brand-400 border border-brand-500/20 font-black text-sm mx-auto flex items-center justify-center">1</div>
                    <div class="font-bold text-white text-xs sm:text-sm">Choose & Pay Online</div>
                    <p class="text-[11px] text-slate-400">Select your preferred internet package and settle seamlessly via card or bank transfer.</p>
                </div>
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-2xl bg-brand-500/10 text-brand-400 border border-brand-500/20 font-black text-sm mx-auto flex items-center justify-center">2</div>
                    <div class="font-bold text-white text-xs sm:text-sm">Receive Voucher PIN</div>
                    <p class="text-[11px] text-slate-400">Your unique voucher code and login PIN appear on-screen immediately.</p>
                </div>
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-2xl bg-brand-500/10 text-brand-400 border border-brand-500/20 font-black text-sm mx-auto flex items-center justify-center">3</div>
                    <div class="font-bold text-white text-xs sm:text-sm">Connect & Browse</div>
                    <p class="text-[11px] text-slate-400">Connect to the WiFi network, enter your PIN on the login page, and enjoy blazing speed.</p>
                </div>
            </div>
        </div>

        <!-- Checkout Modal -->
        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
            @keydown.escape.window="modalOpen = false"
        >
            <div
                @click.away="modalOpen = false"
                class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5 text-slate-100"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div>
                        <h4 class="font-extrabold text-white text-base">Instant WiFi Checkout</h4>
                        <p class="text-[11px] text-slate-400" x-text="selectedPackage ? selectedPackage.name + ' (₦' + selectedPackage.price + ')' : ''"></p>
                    </div>
                    <button @click="modalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('public.hotspot.checkout') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="package_id" :value="selectedPackage ? selectedPackage.id : ''">

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">
                            Phone Number <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="tel"
                            name="customer_phone"
                            x-model="phone"
                            required
                            placeholder="e.g. 08031234567 or +234..."
                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                        <p class="text-[10px] text-slate-500 mt-1">Used to identify your transaction and retrieve your voucher anytime.</p>
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">
                            Email Address <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="email"
                            name="customer_email"
                            x-model="email"
                            required
                            placeholder="your.email@domain.com"
                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                        <p class="text-[10px] text-slate-500 mt-1">Your payment receipt and voucher copy will be sent here.</p>
                    </div>

                    <!-- Payment Gateway Selector -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">Select Payment Gateway *</label>
                        <div class="grid grid-cols-2 gap-3">
                            @if($paystackActive)
                            <label class="relative flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="gateway === 'paystack' ? 'bg-brand-500/10 border-brand-500 text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'">
                                <input type="radio" name="payment_method" value="paystack" x-model="gateway" class="sr-only">
                                <div class="flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0" :class="gateway === 'paystack' ? 'border-brand-500 bg-brand-500' : 'border-slate-600'">
                                        <span x-show="gateway === 'paystack'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-white">Paystack</div>
                                        <div class="text-[10px] text-slate-400">Card / USSD / Bank</div>
                                    </div>
                                </div>
                            </label>
                            @endif

                            @if($monnifyActive)
                            <label class="relative flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="gateway === 'monnify' ? 'bg-brand-500/10 border-brand-500 text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'">
                                <input type="radio" name="payment_method" value="monnify" x-model="gateway" class="sr-only">
                                <div class="flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0" :class="gateway === 'monnify' ? 'border-brand-500 bg-brand-500' : 'border-slate-600'">
                                        <span x-show="gateway === 'monnify'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-white">Monnify</div>
                                        <div class="text-[10px] text-slate-400">Transfer / Card</div>
                                    </div>
                                </div>
                            </label>
                            @endif
                        </div>
                    </div>

                    <!-- Summary & Proceed Button -->
                    <div class="pt-3 border-t border-slate-800 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">Total Due:</span>
                            <span class="text-base font-extrabold font-mono text-white" x-text="selectedPackage ? '₦' + selectedPackage.price : ''"></span>
                        </div>

                        <button
                            type="submit"
                            :disabled="!isValid()"
                            class="w-full py-3 px-4 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2"
                            :class="isValid() ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 cursor-pointer' : 'bg-slate-800 text-slate-500 cursor-not-allowed'"
                        >
                            <span>Proceed to Secure Payment</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-900 bg-slate-950 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} {{ $companyInfo['company_name'] ?? 'ISP-MBP' }}. Powered by High-Performance FreeRADIUS & MikroTik Engine.</p>
    </footer>

</body>
</html>
