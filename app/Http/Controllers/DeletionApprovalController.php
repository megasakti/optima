<?php

namespace App\Http\Controllers;

use App\Models\DeletionRequest;
use App\Services\DeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DeletionApprovalController extends Controller
{
    /**
     * Daftar Permintaan Penghapusan Data (Approval Super Admin)
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = DeletionRequest::where('company_id', $companyId)
            ->with(['requester', 'approver'])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default prioritaskan pending
            $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('model_label', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate(20)->withQueryString();
        $pendingCount = DeletionRequest::where('company_id', $companyId)->where('status', 'pending')->count();

        return view('admin.deletion_requests.index', compact('requests', 'pendingCount'));
    }

    /**
     * Setujui Penghapusan Data (Approve & Delete)
     */
    public function approve(int $id, DeletionService $service): RedirectResponse
    {
        $deletionRequest = DeletionRequest::where('company_id', Auth::user()->company_id)->findOrFail($id);

        try {
            $result = $service->approve($deletionRequest, Auth::user());

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tolak Penghapusan Data (Reject)
     */
    public function reject(Request $request, int $id, DeletionService $service): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $deletionRequest = DeletionRequest::where('company_id', Auth::user()->company_id)->findOrFail($id);

        try {
            $result = $service->reject($deletionRequest, Auth::user(), $request->rejection_reason);

            return back()->with('info', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
