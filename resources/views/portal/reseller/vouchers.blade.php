@extends('portal.layout')

@section('content')
<div x-data="{
    walletModal: false,
    topupAmount: 5000,
    topupGateway: '{{ $paystackActive ? 'paystack' : ($monnifyActive ? 'monnify' : '') }}',
    selectedPackagePrice: {{ $packages->first()?->price ?? 0 }},
    quantity: 10,
    paymentMethod: '{{ $customer->hasSufficientBalance(($packages->first()?->price ?? 0) * 10) ? 'wallet' : ($paystackActive ? 'paystack' : ($monnifyActive ? 'monnify' : 'wallet')) }}',
    customPrefix: 'RS',
    walletBalance: {{ (float) $customer->balance }},
    getTotalCost() {
        return (parseFloat(this.selectedPackagePrice) || 0) * (parseInt(this.quantity) || 0);
    },
    hasEnoughWallet() {
        return this.walletBalance >= this.getTotalCost();
    }
}" class="space-y-6">

    <!-- Header & Wallet Status Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gradient-to-r from-slate-900 to-indigo-950 p-6 sm:p-8 rounded-3xl border border-slate-800 text-white shadow-xl relative overflow-hidden">
        <div class="space-y-1 relative z-10">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    ★ Certified Reseller Agent
                </span>
                <span class="text-xs text-slate-400">• {{ $customer->account_number }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Reseller Hotspot Hub</h1>
            <p class="text-xs text-slate-400">Generate bulk voucher batches, print physical card sheets, and manage your prepaid sales balance.</p>
        </div>

        <!-- Wallet Card -->
        <div class="bg-slate-900/90 border border-slate-700/80 rounded-2xl p-4 sm:p-5 flex items-center gap-5 shrink-0 relative z-10 shadow-lg">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Prepaid Wallet Balance</span>
                <div class="text-2xl font-black font-mono text-emerald-400">₦{{ number_format((float)$customer->balance, 2) }}</div>
            </div>
            <button
                type="button"
                @click="walletModal = true"
                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition"
            >
                + Top Up
            </button>
        </div>
    </div>

    <!-- Batch Generator Form -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Generate New Voucher Batch
                </h2>
                <p class="text-xs text-slate-400">Order a batch of vouchers for printing or direct resale to walk-in subscribers.</p>
            </div>
        </div>

        <form action="{{ route('portal.reseller.vouchers.buy') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- 1. Select Package -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">1. Select Hotspot Package *</label>
                    <select
                        name="package_id"
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500 font-semibold"
                        @change="selectedPackagePrice = $event.target.options[$event.target.selectedIndex].dataset.price"
                    >
                        @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" data-price="{{ $pkg->price }}">
                            {{ $pkg->name }} — ₦{{ number_format((float)$pkg->price, 2) }} ({{ $pkg->validity_period }}d)
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Select Batch Quantity -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">2. Batch Quantity *</label>
                    <div class="flex items-center gap-2 mb-2">
                        <button type="button" @click="quantity = 5" :class="quantity == 5 ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">5</button>
                        <button type="button" @click="quantity = 10" :class="quantity == 10 ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">10</button>
                        <button type="button" @click="quantity = 20" :class="quantity == 20 ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">20</button>
                        <button type="button" @click="quantity = 50" :class="quantity == 50 ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">50</button>
                        <button type="button" @click="quantity = 100" :class="quantity == 100 ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">100</button>
                    </div>
                    <input
                        type="number"
                        name="quantity"
                        x-model="quantity"
                        min="1"
                        max="500"
                        required
                        class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                </div>

                <!-- 3. Custom Batch Prefix -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">3. Voucher Prefix (Optional)</label>
                    <input
                        type="text"
                        name="prefix"
                        x-model="customPrefix"
                        maxlength="6"
                        placeholder="e.g. SHOP, CAFE"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-mono uppercase focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                    <p class="text-[10px] text-slate-400 mt-1">Prepends voucher codes (e.g. <span class="font-mono text-brand-600 dark:text-brand-400" x-text="(customPrefix || 'RS') + '-XXXX-1234'"></span>).</p>
                </div>

            </div>

            <!-- Payment Method Choice -->
            <div class="space-y-3 pt-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">4. Payment Method *</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <!-- Option A: Prepaid Wallet -->
                    <label
                        class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition-all"
                        :class="paymentMethod === 'wallet' ? 'bg-emerald-500/10 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                    >
                        <input type="radio" name="payment_method" value="wallet" x-model="paymentMethod" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-xs text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0" :class="paymentMethod === 'wallet' ? 'border-emerald-500 bg-emerald-500' : 'border-slate-400'">
                                    <span x-show="paymentMethod === 'wallet'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                </span>
                                Reseller Wallet (Instant)
                            </span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" :class="hasEnoughWallet() ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600'">
                                <span x-text="hasEnoughWallet() ? 'Sufficient Balance' : 'Low Balance'"></span>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Deducts directly from your ₦{{ number_format((float)$customer->balance, 2) }} balance. Instant generation!</p>
                    </label>

                    @if($paystackActive)
                    <!-- Option B: Paystack -->
                    <label
                        class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition-all"
                        :class="paymentMethod === 'paystack' ? 'bg-brand-500/10 border-brand-500 ring-2 ring-brand-500/20' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                    >
                        <input type="radio" name="payment_method" value="paystack" x-model="paymentMethod" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-xs text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0" :class="paymentMethod === 'paystack' ? 'border-brand-500 bg-brand-500' : 'border-slate-400'">
                                    <span x-show="paymentMethod === 'paystack'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                </span>
                                Paystack Online Gateway
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Pay directly with Debit Card, USSD, or Bank Transfer.</p>
                    </label>
                    @endif

                    @if($monnifyActive)
                    <!-- Option C: Monnify -->
                    <label
                        class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition-all"
                        :class="paymentMethod === 'monnify' ? 'bg-brand-500/10 border-brand-500 ring-2 ring-brand-500/20' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                    >
                        <input type="radio" name="payment_method" value="monnify" x-model="paymentMethod" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-xs text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0" :class="paymentMethod === 'monnify' ? 'border-brand-500 bg-brand-500' : 'border-slate-400'">
                                    <span x-show="paymentMethod === 'monnify'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                </span>
                                Monnify Gateway
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Instant dedicated account transfer or card checkout.</p>
                    </label>
                    @endif

                </div>
            </div>

            <!-- Calculation Summary & Action Button -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <span class="text-xs text-slate-400">Total Batch Investment:</span>
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white flex items-baseline gap-1">
                        <span>₦</span>
                        <span x-text="getTotalCost().toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                        <span class="text-xs font-normal text-slate-400 font-sans" x-text="'(' + quantity + ' vouchers)'"></span>
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-extrabold text-xs text-white shadow-lg transition-all flex items-center justify-center gap-2"
                    :class="(paymentMethod === 'wallet' && !hasEnoughWallet()) ? 'bg-slate-400 cursor-not-allowed opacity-60' : 'bg-brand-600 hover:bg-brand-500 shadow-brand-500/20'"
                    :disabled="paymentMethod === 'wallet' && !hasEnoughWallet()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span x-text="paymentMethod === 'wallet' ? 'Generate & Deduct from Wallet' : 'Proceed to Gateway Payment'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Reseller Batches Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Your Generated Batches</h3>
                <p class="text-xs text-slate-400">All voucher batches linked to your reseller account.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $batches->total() }} Total Batches</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Batch ID</th>
                        <th class="py-3 px-4">Total Cards</th>
                        <th class="py-3 px-4">Inventory Status</th>
                        <th class="py-3 px-4">Total Value</th>
                        <th class="py-3 px-4">Created Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($batches as $b)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                            {{ $b->batch_id }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                            {{ $b->total_vouchers }} vouchers
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    {{ $b->unused_count }} Unused
                                </span>
                                @if($b->used_count > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/10 text-slate-500 border border-slate-500/20">
                                    {{ $b->used_count }} Active/Used
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            ₦{{ number_format((float)$b->total_value, 2) }}
                        </td>
                        <td class="py-3 px-4 text-slate-500">
                            {{ \Carbon\Carbon::parse($b->created_at)->format('d M Y, H:i') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a
                                href="{{ route('portal.reseller.vouchers.print', $b->batch_id) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-50 dark:bg-brand-950/40 text-brand-600 dark:text-brand-400 hover:bg-brand-600 hover:text-white font-bold text-xs transition border border-brand-500/20"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Print Cards Sheet</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            No batches generated yet. Select a package above to create your first voucher batch!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            {{ $batches->links() }}
        </div>
        @endif
    </div>

    <!-- Top Up Wallet Modal -->
    <div
        x-show="walletModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
        @keydown.escape.window="walletModal = false"
    >
        <div
            @click.away="walletModal = false"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h4 class="font-extrabold text-slate-900 dark:text-white text-base">Top Up Reseller Wallet</h4>
                <button @click="walletModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('portal.reseller.wallet.topup') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Top-Up Amount (₦) *</label>
                    <div class="flex items-center gap-2 mb-2">
                        <button type="button" @click="topupAmount = 2000" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">₦2,000</button>
                        <button type="button" @click="topupAmount = 5000" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">₦5,000</button>
                        <button type="button" @click="topupAmount = 10000" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">₦10,000</button>
                        <button type="button" @click="topupAmount = 25000" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200">₦25,000</button>
                    </div>
                    <input
                        type="number"
                        name="amount"
                        x-model="topupAmount"
                        min="500"
                        step="100"
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Select Payment Gateway *</label>
                    <div class="grid grid-cols-2 gap-3">
                        @if($paystackActive)
                        <label class="flex items-center p-3 rounded-xl border cursor-pointer" :class="topupGateway === 'paystack' ? 'bg-emerald-500/10 border-emerald-500' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" name="payment_method" value="paystack" x-model="topupGateway" class="sr-only">
                            <span class="text-xs font-bold text-slate-900 dark:text-white">Paystack</span>
                        </label>
                        @endif

                        @if($monnifyActive)
                        <label class="flex items-center p-3 rounded-xl border cursor-pointer" :class="topupGateway === 'monnify' ? 'bg-emerald-500/10 border-emerald-500' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" name="payment_method" value="monnify" x-model="topupGateway" class="sr-only">
                            <span class="text-xs font-bold text-slate-900 dark:text-white">Monnify</span>
                        </label>
                        @endif
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button
                        type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2"
                    >
                        <span>Proceed to Pay ₦<span x-text="parseFloat(topupAmount || 0).toLocaleString()"></span></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
