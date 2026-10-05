@extends('layouts.app')

@section('title', 'Create Security Role')
@section('page_title', 'Define Role & Permissions')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    search: '',
    selectAll() {
        document.querySelectorAll('input[type=checkbox][name=\'permissions[]\']').forEach(el => el.checked = true);
    },
    deselectAll() {
        document.querySelectorAll('input[type=checkbox][name=\'permissions[]\']').forEach(el => el.checked = false);
    },
    toggleGroup(groupClass) {
        const checkboxes = document.querySelectorAll('.' + groupClass);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    }
}">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">Create New Security Role</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Configure role name and assign granular permissions.</p>
        </div>
        <a href="{{ route('roles.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-semibold">
            &larr; Cancel
        </a>
    </div>

    <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Role Name Input -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Role Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. NOC Tier-2 Engineer" required class="w-full sm:w-1/2 px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <!-- Permission Matrix Controls Toolbar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Granular Permission Matrix</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Select exact permissions authorized for this role.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Search permissions..."
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                    <button type="button" @click="selectAll()" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-bold text-slate-700 dark:text-slate-300">
                        Select All
                    </button>
                    <button type="button" @click="deselectAll()" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-bold text-slate-700 dark:text-slate-300">
                        Deselect All
                    </button>
                </div>
            </div>

            <!-- Grouped Permission Sections -->
            <div class="space-y-6 pt-2">
                @foreach($groupedPermissions as $group => $perms)
                @php $groupSlug = 'group-' . strtolower(str_replace(' ', '-', $group)); @endphp
                <div class="border border-slate-100 dark:border-slate-800 rounded-2xl p-4 bg-slate-50/50 dark:bg-slate-800/30 space-y-3"
                     x-show="!search || '{{ strtolower($group) }}'.includes(search.toLowerCase()) || {{ json_encode($perms->pluck('name')->toArray()) }}.some(p => p.toLowerCase().includes(search.toLowerCase()))"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-xs uppercase tracking-wider text-slate-800 dark:text-slate-200">{{ $group }} Domain</span>
                        <button type="button" @click="toggleGroup('{{ $groupSlug }}')" class="text-[11px] font-bold text-brand-600 dark:text-brand-400 hover:underline">
                            Toggle Group
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                        @foreach($perms as $perm)
                        <label class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center gap-2.5 cursor-pointer hover:border-brand-500 transition-colors"
                               x-show="!search || '{{ $perm->name }}'.toLowerCase().includes(search.toLowerCase())"
                        >
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="w-4 h-4 rounded bg-slate-50 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500 {{ $groupSlug }}">
                            <span class="font-mono text-xs text-slate-700 dark:text-slate-300">{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('roles.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700 text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all">
                Save Role & Permissions
            </button>
        </div>
    </form>
</div>
@endsection
