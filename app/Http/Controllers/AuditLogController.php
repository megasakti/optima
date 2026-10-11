<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Tampilkan Jejak Audit & Log Aktivitas Sistem (Audit Trail).
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = AuditLog::where('company_id', $companyId)
            ->with('user')
            ->orderBy('id', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();
        $users = User::where('company_id', $companyId)->orderBy('name')->get();

        $actions = AuditLog::where('company_id', $companyId)
            ->select('action')
            ->distinct()
            ->pluck('action');

        return view('admin.audit_logs.index', compact('logs', 'users', 'actions'));
    }

    /**
     * Tampilkan Rincian Detail Log Audit (Perubahan Nilai Lama vs Nilai Baru).
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $log = AuditLog::where('company_id', $companyId)
            ->with('user')
            ->findOrFail($id);

        return view('admin.audit_logs.show', compact('log'));
    }
}
