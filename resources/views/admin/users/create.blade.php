@extends('layouts.app')

@section('title', 'Create Staff Account')
@section('page_title', 'Add Staff Member')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Create Staff User</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Add an administrator, manager, engineer, or support agent with role-based access.</p>
        </div>
        <a href="{{ route('users.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
            &larr; Cancel
        </a>
    </div>

    <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Identity & Credentials -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">1. Staff Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Aminu Kano" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="aminu@isp-mbp.local" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+234 803 000 0000" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Account Status *</label>
                    <select name="status" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Assigned Branch</label>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">All Operational Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Initial Password *</label>
                <input type="password" name="password" required placeholder="Minimum 8 characters" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <!-- Role Assignment Checkboxes -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">2. Assign Operational Roles</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Select one or more functional roles to determine backend permissions.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                @foreach($roles as $role)
                <label class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-start gap-3 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="w-4 h-4 mt-0.5 rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500">
                    <div>
                        <div class="font-bold text-xs text-slate-900 dark:text-slate-100">{{ $role->name }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            @if($role->name === 'Super Administrator') Complete unrestricted control across all tenants.
                            @elseif($role->name === 'Organization Administrator') Complete access across the organization.
                            @elseif($role->name === 'Operations Manager') Subscribers, packages, and network oversight.
                            @elseif($role->name === 'Billing Manager') Invoices, payments, and financial reconciliation.
                            @elseif($role->name === 'Network Administrator') Routers, MikroTik, RADIUS, and network devices.
                            @elseif($role->name === 'Customer Support') Customer assistance, tickets, and subscriptions.
                            @elseif($role->name === 'Accountant') Financial reporting, payments, and invoice view.
                            @else View-only access across permitted resources. @endif
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                Save Staff User
            </button>
        </div>
    </form>
</div>
@endsection
