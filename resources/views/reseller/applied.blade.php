<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 font-sans antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Received — Reseller & Agent Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full flex flex-col justify-center items-center p-4 relative overflow-hidden bg-slate-950 text-slate-100">

    <!-- Ambient Glowing Background Circles -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full space-y-6 relative z-10 text-center">

        <!-- Success Icon -->
        <div class="w-16 h-16 rounded-3xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center mx-auto shadow-xl shadow-emerald-500/20">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>

        <div class="space-y-2">
            <h1 class="text-2xl font-black text-white tracking-tight">Application Submitted!</h1>
            <p class="text-xs text-slate-400">
                Your application to become an authorized voucher reseller has been received and logged in our system.
            </p>
        </div>

        <!-- Tracking Card -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-2xl backdrop-blur-xl text-left space-y-4">
            <div class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/60 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Reseller Reference:</span>
                    <span class="font-mono font-bold text-emerald-400">{{ session('reseller_code', 'RSL-XXXXXX') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Business Name:</span>
                    <span class="font-bold text-white">{{ session('business_name', 'Your Business') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Status:</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        Under Review (Pending Approval)
                    </span>
                </div>
            </div>

            <div class="space-y-2 text-xs text-slate-300">
                <h3 class="font-bold text-white flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    What happens next?
                </h3>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    1. Our compliance team verifies your business information and KYC identity documentation.<br>
                    2. Once approved, you can log in with your credentials to fund your prepaid wallet and generate batches of hotspot vouchers immediately.<br>
                    3. If additional documentation is required, our team will contact you.
                </p>
            </div>

            <div class="pt-2">
                <a href="{{ route('reseller.login') }}" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2">
                    <span>Return to Agent Login</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

    </div>

</body>
</html>
