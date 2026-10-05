<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $query = CustomerDocument::with(['customer', 'uploadedBy'])->latest();

        if ($request->filled('type')) {
            $query->where('document_type', $request->query('type'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('file_name', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'ilike', "%{$search}%")
                         ->orWhere('last_name', 'ilike', "%{$search}%")
                         ->orWhere('account_number', 'ilike', "%{$search}%");
                  });
            });
        }

        $documents = $query->paginate(20)->withQueryString();

        return view('admin.customers.documents.index', compact('documents'));
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'document_type' => ['required', 'string', 'in:national_id,utility_bill,caf_form,installation_signoff,sla_contract,other'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getClientMimeType();

        $path = $file->store("documents/{$customer->organization_id}/{$customer->id}", 'local');

        $doc = CustomerDocument::create([
            'organization_id' => $customer->organization_id,
            'customer_id' => $customer->id,
            'uploaded_by' => Auth::id(),
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'file_path' => $path,
            'file_name' => $originalName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::record(
            action: 'created',
            description: "Uploaded document '{$doc->title}' ({$doc->file_name}) for customer {$customer->full_name}.",
            model: $doc,
            newValues: $doc->only(['title', 'document_type', 'file_name', 'file_size'])
        );

        return redirect()->route('customers.show', [$customer, 'tab' => 'documents'])
            ->with('success', "Document '{$doc->title}' uploaded successfully.");
    }

    public function download(CustomerDocument $document): StreamedResponse
    {
        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found in storage.');
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function destroy(CustomerDocument $document): RedirectResponse
    {
        $customer = $document->customer;

        AuditLog::record(
            action: 'deleted',
            description: "Deleted document '{$document->title}' for customer {$customer->full_name}.",
            model: $document,
            oldValues: $document->only(['title', 'file_name'])
        );

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
