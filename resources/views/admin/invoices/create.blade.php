@extends('layouts.app')

@section('title', 'Create Invoice')
@section('page_title', 'Generate New Invoice')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Create New Invoice</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Issue an itemized invoice for broadband service, router hardware, or field technician callout.</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
            &larr; Cancel
        </a>
    </div>

    <form action="{{ route('invoices.store') }}" method="POST" class="space-y-6" x-data="{
        items: [
            { description: 'Broadband Service Monthly Charge', quantity: 1, unit_price: 15000 }
        ],
        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        getTotal() {
            return this.items.reduce((sum, item) => sum + (Number(item.quantity) * Number(item.unit_price)), 0);
        }
    }">
        @csrf

        <!-- Subscriber & Date Section -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">1. Invoice Metadata</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Customer *</label>
                    <select name="customer_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Choose Subscriber --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->full_name }} ({{ $c->account_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Due Date *</label>
                    <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(7)->toDateString()) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Notes / Terms</label>
                <input type="text" name="notes" placeholder="e.g. Payment due within 7 days to prevent service suspension" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <!-- Line Items Section -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">2. Itemized Charges</h3>
                <button type="button" @click="addItem()" class="px-3 py-1.5 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400 hover:bg-brand-500/20 text-xs font-bold">
                    + Add Item Row
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="grid grid-cols-12 gap-3 items-center p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                        <div class="col-span-12 sm:col-span-6">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Description *</label>
                            <input type="text" :name="`items[${index}][description]`" x-model="item.description" required placeholder="e.g. 20 Mbps Fiber Plan or Dual-Band Router" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
                        </div>

                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quantity *</label>
                            <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 text-center font-mono">
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Unit Price (₦) *</label>
                            <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-mono text-right">
                        </div>

                        <div class="col-span-2 sm:col-span-1 text-center pt-4 sm:pt-0">
                            <button type="button" @click="removeItem(index)" class="text-rose-500 hover:text-rose-700 text-lg font-bold" title="Remove">&times;</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Total Preview -->
            <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <div class="text-right">
                    <span class="text-xs text-slate-400">Calculated Invoice Total:</span>
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-slate-100">
                        ₦<span x-text="getTotal().toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('invoices.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                Generate & Issue Invoice
            </button>
        </div>
    </form>
</div>
@endsection
