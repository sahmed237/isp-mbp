@extends('layouts.app')

@section('title', "Subscription: {$subscription->subscription_number}")
@section('page_title', "Subscription {$subscription->subscription_number}")

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ renewModalOpen: false }">

    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white flex items-center justify-center font-black shadow-lg shadow-brand-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">{{ $subscription->subscription_number }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize {{ $subscription->status_badge_class }}">
                        {{ $subscription->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-400">Created on {{ $subscription->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('subscriptions.update')
            <!-- Renew Button -->
            <button @click="renewModalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Renew Subscription</span>
            </button>

            @if($subscription->status === 'pending')
            <form action="{{ route('subscriptions.activate', $subscription) }}" method="POST" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Activate subscription and grant network authorization?')" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs">
                    Activate
                </button>
            </form>
            @endif

            @if($subscription->status === 'active')
            <form action="{{ route('subscriptions.suspend', $subscription) }}" method="POST" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Suspend subscription? PPPoE dial-in will be rejected immediately.')" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs">
                    Suspend
                </button>
            </form>
            @endif
            @endcan

            <a href="{{ route('subscriptions.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
                &larr; Back
            </a>
        </div>
    </div>

    <!-- 3-Column Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Card 1: Validity & Countdown -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Validity Period</h3>

            <div class="text-center py-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800">
                @if($subscription->isActive())
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $subscription->days_remaining }}</span>
                    <span class="block text-xs uppercase font-bold text-slate-400">Days Remaining</span>
                @elseif($subscription->isExpired())
                    <span class="text-2xl font-black text-rose-500">EXPIRED</span>
                    <span class="block text-xs uppercase font-bold text-slate-400">Subscription Inactive</span>
                @else
                    <span class="text-2xl font-black text-amber-500 uppercase">{{ $subscription->status }}</span>
                    <span class="block text-xs uppercase font-bold text-slate-400">Awaiting Activation</span>
                @endif
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Start Date:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $subscription->starts_at?->format('M d, Y H:i') ?? 'Not started' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Expiry Date:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $subscription->expires_at?->format('M d, Y H:i') ?? 'Not defined' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Auto Renew:</span>
                    <span class="font-bold {{ $subscription->auto_renew ? 'text-emerald-500' : 'text-slate-400' }}">{{ $subscription->auto_renew ? 'Enabled' : 'Disabled' }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Package Specifications -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Service Plan</h3>

            <div>
                <h4 class="font-extrabold text-base text-slate-900 dark:text-slate-100">{{ $subscription->package->name }}</h4>
                <p class="text-xs text-slate-400 line-clamp-2 mt-0.5">{{ $subscription->package->description }}</p>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Download Speed</span>
                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">&darr; {{ $subscription->package->formatted_download_speed }}</span>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Upload Speed</span>
                    <span class="font-mono font-bold text-blue-600 dark:text-blue-400">&uarr; {{ $subscription->package->formatted_upload_speed }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                <span class="text-slate-400">Recurring Price:</span>
                <span class="font-mono font-extrabold text-sm text-slate-900 dark:text-slate-100">₦{{ number_format((float)$subscription->price, 2) }} / {{ $subscription->billing_cycle }}</span>
            </div>
        </div>

        <!-- Card 3: Subscriber Details -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Subscriber</h3>
                <a href="{{ route('customers.show', $subscription->customer) }}" class="text-xs text-brand-600 dark:text-brand-400 font-bold hover:underline">View Profile &rarr;</a>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center font-bold text-sm">
                    {{ substr($subscription->customer->first_name, 0, 1) }}{{ substr($subscription->customer->last_name, 0, 1) }}
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ $subscription->customer->full_name }}</h4>
                    <span class="font-mono text-xs text-slate-400">Account: {{ $subscription->customer->account_number }}</span>
                </div>
            </div>

            <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300 pt-1">
                <div><span class="text-slate-400">Phone:</span> {{ $subscription->customer->phone }}</div>
                <div><span class="text-slate-400">Email:</span> {{ $subscription->customer->email ?? 'N/A' }}</div>
                @if($subscription->customer->radius_username)
                <div class="font-mono text-[11px] bg-slate-50 dark:bg-slate-800 p-2 rounded-lg">
                    <span class="text-slate-400">RADIUS User:</span> <strong class="text-brand-600 dark:text-brand-400">{{ $subscription->customer->radius_username }}</strong>
                </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Related Invoices & Payments -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Billing History for this Subscription</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-4 py-2.5">Invoice #</th>
                        <th class="px-4 py-2.5">Date Issued</th>
                        <th class="px-4 py-2.5">Due Date</th>
                        <th class="px-4 py-2.5">Amount</th>
                        <th class="px-4 py-2.5">Paid</th>
                        <th class="px-4 py-2.5">Status</th>
                        <th class="px-4 py-2.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($subscription->invoices as $inv)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-3 font-mono font-bold text-brand-600 dark:text-brand-400">
                            <a href="{{ route('invoices.show', $inv) }}" class="hover:underline">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $inv->issue_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $inv->due_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-slate-100">₦{{ number_format((float)$inv->total_amount, 2) }}</td>
                        <td class="px-4 py-3 font-bold text-emerald-600">₦{{ number_format((float)$inv->paid_amount, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $inv->status_badge_class }}">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('invoices.show', $inv) }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-bold">View Invoice &rarr;</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-400">No invoices generated for this subscription yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @can('subscriptions.update')
    <!-- Renewal Modal -->
    <div x-show="renewModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div @click.away="renewModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-lg w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Renew Subscription</h3>
                <button type="button" @click="renewModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('subscriptions.renew', $subscription) }}" method="POST" class="space-y-4" x-data="{ markPaid: true }">
                @csrf
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 space-y-1 text-xs">
                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $subscription->package->name }}</div>
                    <div class="text-slate-400">Validity extension: <strong>+{{ $subscription->package->validity_period }} days</strong></div>
                    <div class="font-mono text-sm font-bold text-brand-600 dark:text-brand-400">Amount: ₦{{ number_format((float)$subscription->package->price, 2) }}</div>
                </div>

                <label class="flex items-center gap-3 cursor-pointer text-xs">
                    <input type="checkbox" name="mark_paid" value="1" x-model="markPaid" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <span class="font-bold text-slate-800 dark:text-slate-200">Record Payment & Extend Immediately</span>
                        <p class="text-[11px] text-slate-400">Immediately updates validity, activates subscriber, and updates FreeRADIUS.</p>
                    </div>
                </label>

                <div x-show="markPaid">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">POS / Card</option>
                        <option value="paystack">Paystack</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="renewModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-500">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30">Confirm Renewal</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

</div>
@endsection
