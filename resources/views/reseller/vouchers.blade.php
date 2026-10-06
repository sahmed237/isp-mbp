@extends('reseller.layout')

@section('title', 'Generate Vouchers & Batches')

@section('content')
<div class="space-y-6" x-data="{
    packages: {{ json_encode($packages->keyBy('id')->map(fn($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'price' => (float) $p->price,
        'speed' => $p->formatted_download_speed,
        'validity' => $p->validity_period ? ($p->validity_period . ' ' . $p->validity_unit) : 'Unlimited',
    ])) }},
    selectedPackageId: '{{ $packages->first()?->id ?? '' }}',
    quantity: 10,
    prefix: 'RS',
    walletBalance: {{ (float) $reseller->balance }},
    paymentMethod: 'wallet',

    get selectedPackage() {
        return this.packages[this.selectedPackageId] || null;
    },
    get unitPrice() {
        return this.selectedPackage ? this.selectedPackage.price : 0;
    },
    get totalCost() {
        return this.unitPrice * this.quantity;
    },
    get hasSufficientWallet() {
        return this.walletBalance >= this.totalCost;
    }
}">

    <!-- Success Notice for Just Generated Batch -->
    @if(session('just_created_batch'))
    <div class="bg-gradient-to-r from-emerald-900/60 to-slate-900 border border-emerald-500/30 rounded-3xl p-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <h4 class="text-sm font-extrabold text-white">Batch Ready for Printing!</h4>
                <p class="text-xs text-slate-300">
                    Batch <strong class="font-mono text-emerald-400">{{ session('just_created_batch') }}</strong> has been created with secure pins and barcodes.
                </p>
            </div>
        </div>
        <a href="{{ route('reseller.vouchers.print', session('just_created_batch')) }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs shadow-lg transition-all flex items-center justify-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print Perforated Cards &rarr;</span>
        </a>
    </div>
    @endif

    <!-- Batch Generator Form -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Order Hotspot Voucher Batch</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Generate a new pack of high-resolution printable cards ready for retail sale.</p>
            </div>
            <div class="flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl px-3 py-1.5 self-start sm:self-auto">
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold">Your Wallet Balance:</span>
                <span class="text-xs font-mono font-extrabold text-emerald-700 dark:text-emerald-300">₦{{ number_format((float) $reseller->balance, 2) }}</span>
            </div>
        </div>

        <form action="{{ route('reseller.vouchers.buy') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- 1. Select Package -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">1. Internet Plan / Package *</label>
                    <select name="package_id" x-model="selectedPackageId" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                        @foreach($packages as $package)
                        <option value="{{ $package->id }}">
                            {{ $package->name }} (₦{{ number_format((float) $package->price, 2) }}) &bull; {{ $package->formatted_download_speed }}
                        </option>
                        @endforeach
                    </select>

                    <div x-show="selectedPackage" class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs space-y-1">
                        <div class="flex justify-between text-slate-500">
                            <span>Speed Profile:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedPackage?.speed"></span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Card Retail Value:</span>
                            <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="'₦' + selectedPackage?.price.toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                        </div>
                    </div>
                </div>

                <!-- 2. Quantity & Prefix -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">2. Cards Quantity *</label>
                    <div class="grid grid-cols-4 gap-2 mb-2">
                        <button type="button" @click="quantity = 5" :class="quantity === 5 ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="py-1.5 text-xs font-bold font-mono rounded-lg transition-colors">5 pcs</button>
                        <button type="button" @click="quantity = 10" :class="quantity === 10 ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="py-1.5 text-xs font-bold font-mono rounded-lg transition-colors">10 pcs</button>
                        <button type="button" @click="quantity = 25" :class="quantity === 25 ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="py-1.5 text-xs font-bold font-mono rounded-lg transition-colors">25 pcs</button>
                        <button type="button" @click="quantity = 50" :class="quantity === 50 ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="py-1.5 text-xs font-bold font-mono rounded-lg transition-colors">50 pcs</button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input type="number" name="quantity" x-model.number="quantity" min="1" max="100" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <input type="text" name="prefix" x-model="prefix" placeholder="Prefix (e.g. RS)" maxlength="5" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono uppercase text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-400">Max 100 cards per single batch order.</span>
                </div>

                <!-- 3. Payment Method & Summary -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">3. Payment Option *</label>

                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-emerald-500 transition-colors">
                            <input type="radio" name="payment_method" value="wallet" x-model="paymentMethod" class="text-emerald-600 focus:ring-emerald-500">
                            <div class="flex items-center justify-between w-full text-xs">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block">Prepaid Wallet</span>
                                    <span class="text-[10px] text-slate-400">Instant generation</span>
                                </div>
                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">₦{{ number_format((float) $reseller->balance, 2) }}</span>
                            </div>
                        </label>

                        @if($paystackEnabled)
                        <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-emerald-500 transition-colors">
                            <input type="radio" name="payment_method" value="paystack" x-model="paymentMethod" class="text-emerald-600 focus:ring-emerald-500">
                            <div class="flex items-center justify-between w-full text-xs">
                                <span class="font-bold text-slate-900 dark:text-white">Paystack Checkout</span>
                                <span class="text-[10px] text-slate-400">Cards, Transfer</span>
                            </div>
                        </label>
                        @endif

                        @if($monnifyEnabled)
                        <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-emerald-500 transition-colors">
                            <input type="radio" name="payment_method" value="monnify" x-model="paymentMethod" class="text-emerald-600 focus:ring-emerald-500">
                            <div class="flex items-center justify-between w-full text-xs">
                                <span class="font-bold text-slate-900 dark:text-white">Monnify Gateway</span>
                                <span class="text-[10px] text-slate-400">Virtual Bank Account</span>
                            </div>
                        </label>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Cost Calculation & Submission -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="text-xs text-slate-500">
                        Total Order Cost:
                        <span class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400 block" x-text="'₦' + totalCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                    </div>

                    <div x-show="paymentMethod === 'wallet' && !hasSufficientWallet" x-cloak class="text-xs text-rose-500 font-semibold bg-rose-50 dark:bg-rose-950/40 px-3 py-1.5 rounded-xl border border-rose-200 dark:border-rose-900">
                        Insufficient balance &bull;
                        <button type="button" @click="fundWalletModal = true" class="underline font-bold text-rose-600 dark:text-rose-400 hover:text-rose-700">Top Up Wallet</button>
                    </div>
                </div>

                <button type="submit" :disabled="paymentMethod === 'wallet' && !hasSufficientWallet" class="px-8 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Generate & Activate Batch Pack</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Batch Inventory Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Batch Inventory & Print Center</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">All voucher batches generated under your reseller code.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">{{ $batches->total() }} batches total</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Batch ID</th>
                        <th class="py-3 px-4">Internet Package</th>
                        <th class="py-3 px-4 text-center">Total</th>
                        <th class="py-3 px-4 text-center">Unsold / Active</th>
                        <th class="py-3 px-4 text-center">Used / Consumed</th>
                        <th class="py-3 px-4">Created Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                            {{ $batch->batch_id }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $batch->package?->name ?? 'Hotspot Plan' }}</span>
                            <span class="block text-[11px] text-slate-400 font-mono">₦{{ number_format((float) ($batch->package?->price ?? 0), 2) }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $batch->total_count }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                                {{ $batch->active_count }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400">
                                {{ $batch->used_count }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                            {{ \Carbon\Carbon::parse($batch->created_at)->format('d M Y, h:i A') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('reseller.vouchers.print', $batch->batch_id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Print Cards</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                            No voucher batches found. Place an order above to generate your first batch!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            {{ $batches->links() }}
        </div>
    </div>

</div>
@endsection
