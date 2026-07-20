<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if (Gate::denies('view-audit-logs')) {
            abort(403, 'No tiene permiso para ver la bitácora del sistema.');
        }

        $query = AuditLog::with('user:id,name');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(30)->withQueryString();

        return Inertia::render('AuditLogs/Index', [
            'logs'      => $logs,
            'filters'   => $request->only(['user_id', 'action', 'model_type', 'date_from', 'date_to']),
            'users'     => \App\Models\User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

}
