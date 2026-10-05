<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Invoice {{ $invoice->invoice_number }} — {{ $companyInfo['name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen py-6 sm:py-12 px-4 sm:px-6">

    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Notification Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-xs font-semibold flex items-start gap-3 shadow-sm">
                <svg class="w-5 h-5 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <strong class="block font-bold">Payment Verified</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-700 dark:text-rose-300 text-xs font-semibold flex items-start gap-3 shadow-sm">
                <svg class="w-5 h-5 text-rose-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <strong class="block font-bold">Transaction Notice</strong>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-700 dark:text-blue-300 text-xs font-semibold flex items-start gap-3 shadow-sm">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <strong class="block font-bold">Information</strong>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
        @endif

        <!-- Main Invoice & Payment Container -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-10 shadow-xl space-y-8">

            <!-- ISP Header & Invoice Number -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black shadow-md shadow-indigo-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight leading-tight">{{ $companyInfo['name'] }}</h1>
                            <span class="text-[11px] text-slate-400 font-medium">Internet Service Provider</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 space-y-0.5 pl-0.5">
                        <p>{{ $companyInfo['address'] }}</p>
                        <p>Phone: <a href="tel:{{ $companyInfo['phone'] }}" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">{{ $companyInfo['phone'] }}</a></p>
                        <p>Email: <a href="mailto:{{ $companyInfo['email'] }}" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">{{ $companyInfo['email'] }}</a></p>
                    </div>
                </div>

                <div class="sm:text-right space-y-1 bg-slate-50 dark:bg-slate-800/60 p-4 sm:p-0 sm:bg-transparent rounded-2xl">
                    <span class="text-[10px] uppercase font-extrabold tracking-widest text-slate-400 block">TAX INVOICE</span>
                    <span class="text-xl font-black font-mono text-indigo-600 dark:text-indigo-400 block">{{ $invoice->invoice_number }}</span>
                    <div class="text-xs text-slate-500 dark:text-slate-400 space-y-0.5 mt-2">
                        <p>Issue Date: <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->issue_date->format('M d, Y') }}</strong></p>
                        <p>Due Date: <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->due_date->format('M d, Y') }}</strong></p>
                        <div class="pt-1">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold capitalize {{ $invoice->status_badge_class }}">
                                {{ $invoice->status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bill To & Service Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-slate-100 dark:border-slate-800 text-xs">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1 tracking-wider">Subscriber Details</span>
                    <h3 class="font-extrabold text-sm text-slate-900 dark:text-slate-100">{{ $invoice->customer->full_name }}</h3>
                    <div class="text-slate-500 dark:text-slate-400 space-y-0.5 mt-1">
                        <p class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">Account: {{ $invoice->customer->account_number }}</p>
                        <p>{{ $invoice->customer->installation_address ?? 'Physical installation address' }}</p>
                        <p>{{ $invoice->customer->city }}, {{ $invoice->customer->state }}</p>
                        <p>Phone: {{ $invoice->customer->phone }}</p>
                    </div>
                </div>

                <div class="sm:text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1 tracking-wider">Plan & Subscription</span>
                    @if($invoice->subscription)
                        <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $invoice->subscription->package->name }}</p>
                        <p class="text-slate-500 dark:text-slate-400 font-mono">Sub ID: {{ $invoice->subscription->subscription_number }}</p>
                        <p class="text-slate-500 dark:text-slate-400">Billing Cycle: {{ ucfirst($invoice->subscription->billing_cycle) }}</p>
                    @else
                        <p class="text-slate-700 dark:text-slate-300 font-bold">Broadband Services & Equipment</p>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-y border-slate-200 dark:border-slate-800 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">Description</th>
                            <th class="py-2.5 px-3 text-center">Qty</th>
                            <th class="py-2.5 px-3 text-right">Unit Price</th>
                            <th class="py-2.5 px-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ $item->description }}</td>
                            <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $item->quantity }}</td>
                            <td class="py-3 px-3 text-right font-mono text-slate-500">₦{{ number_format((float)$item->unit_price, 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 dark:text-slate-100">₦{{ number_format((float)$item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals & Balance -->
            <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <div class="w-full sm:w-72 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span>Subtotal:</span>
                        <span class="font-mono">₦{{ number_format((float)$invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-extrabold text-slate-900 dark:text-slate-100 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span>Total Invoiced:</span>
                        <span class="font-mono">₦{{ number_format((float)$invoice->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-emerald-600 dark:text-emerald-400">
                        <span>Amount Settled:</span>
                        <span class="font-mono">₦{{ number_format((float)$invoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-black text-base text-rose-600 dark:text-rose-400 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span>Balance Due:</span>
                        <span class="font-mono">₦{{ number_format((float)$invoice->balance_due, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Action Box -->
            @if(!$invoice->isPaid())
            <div class="rounded-3xl bg-gradient-to-br from-indigo-50 to-slate-50 dark:from-slate-800 dark:to-slate-800/60 border border-indigo-100 dark:border-slate-700 p-6 sm:p-8 space-y-6"
                 x-data="{
                     method: '{{ $paystackActive ? 'paystack' : ($monnifyActive ? 'monnify' : '') }}',
                     email: {{ Js::from(old('customer_email', $invoice->customer->email ?? '')) }},
                     phone: {{ Js::from(old('customer_phone', $invoice->customer->phone ?? '')) }},
                     isValidEmail() {
                         if (!this.email) return false;
                         const trimmed = this.email.toString().trim();
                         return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(trimmed);
                     },
                     isValidPhone() {
                         if (!this.phone) return false;
                         const trimmed = this.phone.toString().trim();
                         if (!/^\+?[0-9\s\-()]{10,20}$/.test(trimmed)) {
                             return false;
                         }
                         const digits = trimmed.replace(/\D/g, '');
                         return digits.length >= 10 && digits.length <= 15;
                     },
                     isFormValid() {
                         return Boolean(this.method) && this.isValidEmail() && this.isValidPhone();
                     }
                 }">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs uppercase font-extrabold tracking-wider text-indigo-600 dark:text-indigo-400 block mb-1">Instant Online Settlement</span>
                        <h2 class="text-xl font-black text-slate-900 dark:text-slate-100">Pay Outstanding Balance Now</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Choose your preferred online gateway. Instant confirmation & subscription activation.</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-[11px] text-slate-400 block">Total Due</span>
                        <span class="text-2xl sm:text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400">₦{{ number_format((float)$invoice->balance_due, 2) }}</span>
                    </div>
                </div>

                @if($paystackActive || $monnifyActive)
                <form action="{{ route('public.invoices.checkout', $invoice->uuid) }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Channel Options -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if($paystackActive)
                        <label @click="method = 'paystack'" :class="method === 'paystack' ? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-white dark:bg-slate-900' : 'border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-900/60'" class="flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition-all">
                            <input type="radio" name="payment_method" value="paystack" x-model="method" class="sr-only">
                            <div class="w-8 h-8 rounded-xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                                PS
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 dark:text-slate-100">Paystack</span>
                                <span class="block text-[10px] text-slate-400">Card, Transfer & USSD</span>
                            </div>
                        </label>
                        @endif

                        @if($monnifyActive)
                        <label @click="method = 'monnify'" :class="method === 'monnify' ? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-white dark:bg-slate-900' : 'border-slate-200 dark:border-slate-700 bg-white/60 dark:bg-slate-900/60'" class="flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition-all">
                            <input type="radio" name="payment_method" value="monnify" x-model="method" class="sr-only">
                            <div class="w-8 h-8 rounded-xl bg-blue-600/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                                MN
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 dark:text-slate-100">Monnify</span>
                                <span class="block text-[10px] text-slate-400">Card, USSD & Online Checkout</span>
                            </div>
                        </label>
                        @endif
                    </div>

                    <!-- Customer Contact for Receipt (Strictly Required for payment) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                    Receipt Email Address <span class="text-rose-500 font-extrabold">*</span>
                                </label>
                                <template x-if="isValidEmail()">
                                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1" x-cloak>
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Valid Email
                                    </span>
                                </template>
                                <template x-if="email && email.toString().trim().length > 0 && !isValidEmail()">
                                    <span class="text-[10px] text-rose-500 font-semibold" x-cloak>
                                        Invalid format
                                    </span>
                                </template>
                            </div>
                            <input type="email"
                                   name="customer_email"
                                   x-model="email"
                                   required
                                   placeholder="e.g. subscriber@example.com"
                                   :class="{
                                       'border-rose-400 focus:ring-rose-500 bg-rose-50/30': email && email.toString().trim().length > 0 && !isValidEmail(),
                                       'border-emerald-400 focus:ring-emerald-500 bg-emerald-50/10': isValidEmail(),
                                       'border-slate-200 dark:border-slate-700 focus:ring-indigo-500': !email || email.toString().trim().length === 0
                                   }"
                                   class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 transition-colors">
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[10px] text-slate-400" x-show="!email || email.toString().trim().length === 0">
                                    Required for transaction confirmation & invoice receipt.
                                </span>
                                <span class="text-[10px] text-rose-500 font-medium" x-show="email && email.toString().trim().length > 0 && !isValidEmail()" x-cloak>
                                    Please enter a valid email address (e.g. name@domain.com).
                                </span>
                            </div>
                            @error('customer_email')
                                <span class="text-[10px] text-rose-500 font-semibold block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                    Receipt SMS Phone <span class="text-rose-500 font-extrabold">*</span>
                                </label>
                                <template x-if="isValidPhone()">
                                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1" x-cloak>
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Valid Phone
                                    </span>
                                </template>
                                <template x-if="phone && phone.toString().trim().length > 0 && !isValidPhone()">
                                    <span class="text-[10px] text-rose-500 font-semibold" x-cloak>
                                        Min 10 digits
                                    </span>
                                </template>
                            </div>
                            <input type="tel"
                                   name="customer_phone"
                                   x-model="phone"
                                   required
                                   placeholder="e.g. 08012345678 or +234..."
                                   :class="{
                                       'border-rose-400 focus:ring-rose-500 bg-rose-50/30': phone && phone.toString().trim().length > 0 && !isValidPhone(),
                                       'border-emerald-400 focus:ring-emerald-500 bg-emerald-50/10': isValidPhone(),
                                       'border-slate-200 dark:border-slate-700 focus:ring-indigo-500': !phone || phone.toString().trim().length === 0
                                   }"
                                   class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 transition-colors">
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[10px] text-slate-400" x-show="!phone || phone.toString().trim().length === 0">
                                    Required for SMS alert and account identification.
                                </span>
                                <span class="text-[10px] text-rose-500 font-medium" x-show="phone && phone.toString().trim().length > 0 && !isValidPhone()" x-cloak>
                                    Must contain 10-15 digits (e.g. 08012345678 or +234...).
                                </span>
                            </div>
                            @error('customer_phone')
                                <span class="text-[10px] text-rose-500 font-semibold block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-3 border-t border-slate-200/60 dark:border-slate-700/60">
                        <div class="space-y-1 w-full sm:w-auto">
                            <span class="text-[11px] text-slate-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                256-bit SSL encrypted secure checkout
                            </span>
                            <template x-if="!isFormValid()">
                                <p class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Provide both a valid <strong>email</strong> &amp; <strong>phone number</strong> to enable payment.</span>
                                </p>
                            </template>
                            <template x-if="isFormValid()">
                                <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Ready to pay! Contact details verified.</span>
                                </p>
                            </template>
                        </div>

                        <button type="submit"
                                disabled
                                :disabled="!isFormValid()"
                                :class="isFormValid() ? 'bg-emerald-600 hover:bg-emerald-500 cursor-pointer shadow-lg shadow-emerald-600/30 text-white ring-2 ring-emerald-500/50' : 'bg-slate-300 dark:bg-slate-800 text-slate-400 dark:text-slate-500 opacity-60 cursor-not-allowed pointer-events-none shadow-none'"
                                class="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-black text-xs transition-all flex items-center justify-center gap-2">
                            <span>Proceed to Pay ₦{{ number_format((float)$invoice->balance_due, 2) }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
                @else
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-300 text-xs">
                    Online automated payment channels are currently undergoing scheduled maintenance. Please contact support.
                </div>
                @endif
            </div>
            @else
            <!-- Paid Confirmation Box -->
            <div class="rounded-3xl bg-emerald-500/10 border border-emerald-500/20 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-emerald-800 dark:text-emerald-300">Invoice Settled in Full</h3>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">
                            Payment cleared on {{ $invoice->paid_at?->format('M d, Y \a\t H:i') ?? 'record' }}. Broadband access is fully active.
                        </p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-xs border border-slate-200 dark:border-slate-700 shadow-sm hover:bg-slate-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Download Official Receipt</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- QR Code Section & Payment Advice -->
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="p-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">
                        {!! $qrCodeSvg !!}
                    </div>
                    <div class="text-xs space-y-1">
                        <span class="font-extrabold text-slate-800 dark:text-slate-200 block text-xs">Scan & Pay Mobile Link</span>
                        <p class="text-slate-400 text-[11px] max-w-xs">
                            Scan this QR code with any smartphone camera to open and settle this invoice anywhere instantly.
                        </p>
                        <a href="{{ $publicUrl }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-mono font-bold hover:underline block break-all">
                            {{ $publicUrl }}
                        </a>
                    </div>
                </div>

                <div class="text-center sm:text-right">
                    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print Invoice / PDF</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-400 space-y-1">
            <p>&copy; {{ date('Y') }} {{ $companyInfo['name'] }}. All rights reserved.</p>
            <p>For billing inquiries, contact <a href="mailto:{{ $companyInfo['email'] }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">{{ $companyInfo['email'] }}</a> or call <a href="tel:{{ $companyInfo['phone'] }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">{{ $companyInfo['phone'] }}</a>.</p>
        </div>

    </div>

</body>
</html>
