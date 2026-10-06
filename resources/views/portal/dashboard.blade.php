@extends('portal.layout')

@section('title', 'Subscriber Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Welcome Greeting & Account Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-brand-500/20">
                {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100">Welcome, {{ $customer->full_name }}</h1>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    <span class="font-mono font-bold text-brand-600 dark:text-brand-400">Account: {{ $customer->account_number }}</span>
                    <span>&bull;</span>
                    <span class="font-semibold capitalize text-slate-700 dark:text-slate-300">{{ $customer->customer_type }} Plan</span>
                    <span>&bull;</span>
                    <span>Branch: {{ $customer->branch?->name ?? 'HQ Network' }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold capitalize {{ $customer->status_badge_class }}">
                Service {{ $customer->status }}
            </span>
        </div>
    </div>

    <!-- Reseller Agent Quick Hub -->
    @if($customer->isReseller())
    <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/5 to-transparent border border-emerald-500/30 rounded-3xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-500/30">Reseller Privileges Active</span>
                </div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 mt-1">Hotspot Voucher Reseller Hub</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Prepaid Wallet Balance: <strong class="text-emerald-600 dark:text-emerald-400 font-mono text-sm font-bold">₦{{ number_format((float)$customer->balance, 2) }}</strong>. Buy bulk batches and print perforated card sheets.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('portal.reseller.vouchers') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition-all">
                Open Reseller Hub &rarr;
            </a>
        </div>
    </div>
    @endif

    <!-- Outstanding Invoices Alert Banner -->
    @if($unpaidInvoices->isNotEmpty())
    <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/30 rounded-3xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">You have {{ $unpaidInvoices->count() }} Unpaid Invoice(s)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Total balance due: <strong class="text-amber-600 dark:text-amber-400 font-mono text-sm font-bold">₦{{ number_format((float)$unpaidInvoices->sum('balance_due'), 2) }}</strong>. Pay online to keep your broadband service active.
                </p>
            </div>
        </div>
        <a href="{{ route('portal.invoices.show', $unpaidInvoices->first()) }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/30 transition-all">
            Pay Invoice Now &rarr;
        </a>
    </div>
    @endif

    <!-- 2 Main Cards: Active Subscription & Network Session -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Card 1: Broadband Subscription Status -->
        @php $sub = $customer->activeSubscription ?? $customer->latestSubscription; @endphp
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase font-bold tracking-wider text-slate-400">Current Plan & Subscription</span>
                    @if($sub && $sub->isActive())
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400">
                            Active &bull; {{ $sub->days_remaining }} days left
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">
                            Inactive / Expired
                        </span>
                    @endif
                </div>

                @if($sub)
                <div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $sub->package->name }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $sub->package->description }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Download Speed</span>
                        <span class="font-mono text-base font-extrabold text-emerald-600 dark:text-emerald-400">&darr; {{ $sub->package->formatted_download_speed }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Upload Speed</span>
                        <span class="font-mono text-base font-extrabold text-blue-600 dark:text-blue-400">&uarr; {{ $sub->package->formatted_upload_speed }}</span>
                    </div>
                </div>

                <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Expires On:</span>
                        <strong class="text-slate-800 dark:text-slate-200">{{ $sub->expires_at?->format('F j, Y \a\t g:i A') ?? 'Pending Payment' }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Renewal Price:</span>
                        <strong class="font-mono text-slate-800 dark:text-slate-200">₦{{ number_format((float)$sub->price, 2) }} / {{ $sub->billing_cycle }}</strong>
                    </div>
                </div>
                @else
                <div class="py-6 text-center text-slate-400 text-xs">
                    No active subscription assigned yet. Please contact support or check your invoices.
                </div>
                @endif
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('portal.invoices') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                    View Billing History &rarr;
                </a>
                <a href="{{ route('portal.hotspot') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition-colors">
                    Buy Hotspot Pass
                </a>
            </div>
        </div>

        <!-- Card 2: Live Network Session Status -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase font-bold tracking-wider text-slate-400">Live Network Connection (RADIUS)</span>
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
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Assigned IP</span>
                        <span class="font-mono text-sm font-bold text-brand-600 dark:text-brand-400">{{ $activeSession->framedipaddress }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Session Uptime</span>
                        <span class="font-mono text-sm font-bold text-slate-800 dark:text-slate-200">{{ $activeSession->formatted_session_time }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Data Downloaded</span>
                        <span class="font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ $activeSession->formatted_output_octets }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Data Uploaded</span>
                        <span class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">{{ $activeSession->formatted_input_octets }}</span>
                    </div>
                </div>
                @else
                <div class="py-4 text-xs text-slate-400 space-y-2">
                    <p>Your router / CPE is currently offline or not authenticated on the network.</p>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                        <p>PPPoE Username: <strong>{{ $customer->radius_username ?? 'Not assigned' }}</strong></p>
                    </div>
                </div>
                @endif
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('portal.profile') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                    View Router Dial-in Settings &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- Recent Invoices & Payments Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Invoices -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Recent Invoices</h3>
                <a href="{{ route('portal.invoices') }}" class="text-xs text-brand-600 dark:text-brand-400 font-bold hover:underline">View All &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($customer->invoices()->take(4)->get() as $inv)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div>
                        <a href="{{ route('portal.invoices.show', $inv) }}" class="font-mono font-bold text-slate-900 dark:text-slate-100 hover:text-brand-600">
                            {{ $inv->invoice_number }}
                        </a>
                        <span class="block text-[11px] text-slate-400">Due: {{ $inv->due_date->format('M d, Y') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-bold text-slate-900 dark:text-slate-100">₦{{ number_format((float)$inv->total_amount, 2) }}</span>
                        <div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $inv->status_badge_class }}">
                                {{ $inv->status }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-4 text-center text-slate-400 text-xs">No invoices on file.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Recent Payment Receipts</h3>
                <a href="{{ route('portal.payments') }}" class="text-xs text-brand-600 dark:text-brand-400 font-bold hover:underline">View All &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentPayments as $pmt)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-mono font-bold text-slate-900 dark:text-slate-100">{{ $pmt->payment_number }}</span>
                        <span class="block text-[11px] text-slate-400">{{ $pmt->paid_at->format('M d, Y H:i') }} &bull; {{ ucfirst($pmt->payment_method) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">₦{{ number_format((float)$pmt->amount, 2) }}</span>
                        <span class="block text-[10px] font-bold text-emerald-500">&check; Cleared</span>
                    </div>
                </div>
                @empty
                <div class="py-4 text-center text-slate-400 text-xs">No payment records on file yet.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
