<div
    x-show="searchModalOpen"
    x-cloak
    @keydown.escape.window="searchModalOpen = false"
    @keydown.window.prevent.cmd.k="searchModalOpen = true"
    @keydown.window.prevent.ctrl.k="searchModalOpen = true"
    class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20"
>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="searchModalOpen = false"></div>

    <!-- Modal Box -->
    <div class="relative mx-auto max-w-xl rounded-2xl bg-white dark:bg-slate-900 shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden transform transition-all">
        <form action="{{ route('customers.index') }}" method="GET" class="relative">
            <svg class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input
                type="text"
                name="search"
                class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-0 sm:text-sm outline-none"
                placeholder="Search customers, account numbers, IP addresses, packages..."
                x-ref="searchInput"
            >
        </form>

        <div class="border-t border-slate-100 dark:border-slate-800 px-4 py-3 text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span>Navigate: <kbd class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-300 dark:border-slate-700">↑</kbd> <kbd class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-300 dark:border-slate-700">↓</kbd></span>
                <span>Select: <kbd class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-300 dark:border-slate-700">↵</kbd></span>
            </div>
            <button @click="searchModalOpen = false" class="hover:text-slate-700 dark:hover:text-slate-200">ESC to close</button>
        </div>
    </div>
</div>
