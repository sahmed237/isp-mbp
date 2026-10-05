@extends('portal.layout')

@section('title', 'Hotspot Vouchers')

@section('content')
<div class="space-y-6" x-data="{ buyModalOpen: false, selectedPkg: null }">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100">Hotspot Wi-Fi Vouchers</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Buy high-speed guest Wi-Fi passes for extra devices, mobile phones, or visitors.</p>
        </div>
    </div>

    <!-- Available Hotspot Packages Grid -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Available Hotspot Passes</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($hotspotPackages as $pkg)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between space-y-4 hover:border-brand-500/50 transition-all">
                <div class="space-y-2">
                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-brand-500/10 text-brand-600 dark:text-brand-400">
                        {{ $pkg->validity_period }} Days Pass
                    </span>
                    <h4 class="font-extrabold text-base text-slate-900 dark:text-slate-100">{{ $pkg->name }}</h4>
                    <p class="text-xs text-slate-400 line-clamp-2">{{ $pkg->description }}</p>

                    <div class="pt-2 text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                        Speed: &darr; {{ $pkg->formatted_download_speed }} / &uarr; {{ $pkg->formatted_upload_speed }}
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-lg font-black font-mono text-slate-900 dark:text-slate-100">₦{{ number_format((float)$pkg->price, 0) }}</span>
                    <button
                        @click="selectedPkg = { id: {{ $pkg->id }}, name: '{{ addslashes($pkg->name) }}', price: '{{ number_format((float)$pkg->price, 2) }}' }; buyModalOpen = true;"
                        class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-sm transition-all"
                    >
                        Buy Voucher
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- My Purchased Vouchers Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden space-y-3 p-6">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">My Purchased Wi-Fi Vouchers</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-y border-slate-200 dark:border-slate-800 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Voucher Code</th>
                        <th class="py-3 px-4">Plan</th>
                        <th class="py-3 px-4">Login Username</th>
                        <th class="py-3 px-4">PIN / Password</th>
                        <th class="py-3 px-4">Duration</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Purchased</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($vouchers as $v)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                        <td class="py-3.5 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">
                            {{ $v->code }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200">
                            {{ $v->package->name }}
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-slate-100">
                            {{ $v->username }}
                        </td>
                        <td class="py-3.5 px-4 font-mono font-black text-sm text-emerald-600 dark:text-emerald-400 tracking-wider">
                            {{ $v->password }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                            {{ $v->duration_formatted }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $v->status_badge_class }}">
                                {{ $v->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-400">
                            {{ $v->created_at->format('M d, Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">You have not purchased any hotspot vouchers yet. Select a pass above to buy.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Buy Voucher Modal -->
    <div x-show="buyModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div @click.away="buyModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Buy Hotspot Wi-Fi Voucher</h3>
                <button type="button" @click="buyModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('portal.hotspot.buy') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="package_id" :value="selectedPkg ? selectedPkg.id : ''">

                <div class="p-4 rounded-2xl bg-brand-500/10 border border-brand-500/20 text-center">
                    <span class="text-xs text-brand-600 dark:text-brand-400 block font-semibold" x-text="selectedPkg ? selectedPkg.name : ''"></span>
                    <span class="text-3xl font-black font-mono text-slate-900 dark:text-slate-100">₦<span x-text="selectedPkg ? selectedPkg.price : '0.00'"></span></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Payment Method</label>
                    <select name="payment_method" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="card">Debit Card Checkout</option>
                        <option value="bank_transfer">Direct Bank Transfer</option>
                        <option value="paystack">Paystack</option>
                    </select>
                </div>

                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-[11px] text-emerald-700 dark:text-emerald-400">
                    &check; Random username & PIN generated immediately and authorized in FreeRADIUS AAA.
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="buyModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-500">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30">Confirm Purchase</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
