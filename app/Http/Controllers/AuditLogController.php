<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AuditLog::class);

        $query = AuditLog::with('user')->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ilike', "%{$search}%")
                  ->orWhere('user_name', 'ilike', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $auditLogs = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();
        $filters = $request->only(['action', 'user_id', 'search']);

        return view('admin.audit-logs.index', compact('auditLogs', 'users', 'filters'));
    }

    public function show(AuditLog $auditLog): View
    {
        Gate::authorize('view', $auditLog);

        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
