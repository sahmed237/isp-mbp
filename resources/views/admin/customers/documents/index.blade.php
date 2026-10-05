@extends('layouts.app')

@section('title', 'Customer Documents & KYC Repository')
@section('page_title', 'Customer Documents & KYC Records')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
    </div>
    @endif

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <form action="{{ route('customers.documents.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search document title, filename, or subscriber..." class="px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500">

                <select name="type" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
                    <option value="">All Document Types</option>
                    <option value="national_id" {{ request('type') === 'national_id' ? 'selected' : '' }}>National ID / Passport</option>
                    <option value="utility_bill" {{ request('type') === 'utility_bill' ? 'selected' : '' }}>Utility Bill / Address Proof</option>
                    <option value="caf_form" {{ request('type') === 'caf_form' ? 'selected' : '' }}>CAF Application Form</option>
                    <option value="installation_signoff" {{ request('type') === 'installation_signoff' ? 'selected' : '' }}>Installation Sign-Off</option>
                    <option value="sla_contract" {{ request('type') === 'sla_contract' ? 'selected' : '' }}>SLA Contract</option>
                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other Documents</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700">Filter</button>
                @if(request()->anyFilled(['search', 'type']))
                    <a href="{{ route('customers.documents.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                        <th class="py-3 px-4">Document Title</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Subscriber</th>
                        <th class="py-3 px-4">File Details</th>
                        <th class="py-3 px-4">Uploaded By</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($documents as $doc)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-slate-100">
                            {{ $doc->title }}
                            @if($doc->notes)
                                <span class="block text-[11px] text-slate-400 font-normal truncate max-w-xs">{{ $doc->notes }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $doc->document_type_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($doc->customer)
                            <a href="{{ route('customers.show', $doc->customer) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:text-brand-600 block">
                                {{ $doc->customer->full_name }}
                            </a>
                            <span class="text-[10px] font-mono text-brand-600 dark:text-brand-400">{{ $doc->customer->account_number }}</span>
                            @else
                            <span class="text-slate-400">N/A</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                            <div>{{ $doc->file_name }}</div>
                            <span class="text-slate-400 text-[10px]">{{ $doc->formatted_file_size }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $doc->uploadedBy?->name ?? 'System' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $doc->created_at->format('M d, Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('customers.documents.download', $doc) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-brand-50 hover:bg-brand-100 dark:bg-brand-950/40 dark:hover:bg-brand-900/50 text-brand-600 dark:text-brand-400 text-xs font-bold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Download</span>
                                </a>
                                <form action="{{ route('customers.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-lg hover:bg-rose-500/10 text-slate-400 hover:text-rose-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <div class="font-bold">No subscriber documents found</div>
                            <div class="text-[11px] mt-0.5">Documents uploaded from customer profiles appear in this organization repository.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            {{ $documents->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
