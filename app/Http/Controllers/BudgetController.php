<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
use App\Models\TrainingBatch;
use App\Services\BudgetService;
use App\Services\PdfBudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BudgetController extends Controller
{
    /**
     * Daftar Rencana Anggaran Biaya (RAB) Pelatihan.
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = Budget::where('company_id', $companyId)
            ->with(['batch.program', 'createdBy', 'approvedBy'])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $budgets = $query->paginate(20)->withQueryString();

        return view('budgets.index', compact('budgets'));
    }

    /**
     * Form Susun Rencana Anggaran Biaya (RAB).
     */
    public function create(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $batchId = $request->input('batch_id');
        $selectedBatch = null;

        if ($batchId) {
            $selectedBatch = TrainingBatch::where('company_id', $companyId)->with('program')->findOrFail($batchId);
        }

        $batches = TrainingBatch::where('company_id', $companyId)
            ->whereDoesntHave('budget', fn($q) => $q->where('status', 'approved'))
            ->with('program')
            ->orderBy('batch_code', 'desc')
            ->get();

        $expenseAccounts = Account::where('company_id', $companyId)
            ->where('type', 'expense')
            ->orderBy('code')
            ->get();

        return view('budgets.create', compact('batches', 'selectedBatch', 'expenseAccounts'));
    }

    /**
     * Simpan / Perbarui RAB.
     */
    public function store(Request $request, BudgetService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'batch_id' => ['required', 'exists:training_batches,id'],
            'target_participants' => ['required', 'numeric', 'min:1'],
            'target_revenue_per_pax' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.account_id' => ['nullable', 'exists:accounts,id'],
            'items.*.category' => ['required', 'string'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $budget = $service->createOrUpdateBudget([
                'company_id' => $companyId,
                'batch_id' => (int) $request->batch_id,
                'target_participants' => (float) $request->target_participants,
                'target_revenue_per_pax' => (float) $request->target_revenue_per_pax,
                'notes' => $request->notes,
                'items' => $request->items,
            ]);

            return redirect()->route('budgets.show', $budget->id)
                ->with('success', "RAB {$budget->budget_number} berhasil disimpan sebagai draft. Silakan ajukan untuk persetujuan.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Detail Rencana Anggaran Biaya (RAB).
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $budget = Budget::where('company_id', $companyId)
            ->with(['batch.program', 'batch.lsp', 'batch.trainer', 'items.account', 'approvals.user', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('budgets.show', compact('budget'));
    }

    /**
     * Ajukan RAB ke Finance Manager.
     */
    public function submit(int $id, Request $request, BudgetService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $budget = Budget::where('company_id', $companyId)->findOrFail($id);

        try {
            $service->submitBudget($budget, $request->input('comment'));

            return back()->with('success', "RAB {$budget->budget_number} berhasil diajukan ke Finance Manager.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Setujui RAB oleh Finance Manager.
     */
    public function approve(int $id, Request $request, BudgetService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $budget = Budget::where('company_id', $companyId)->findOrFail($id);
        $user = Auth::user();

        try {
            $service->approveBudget($budget, $user, $request->input('comment'));

            return back()->with('success', "RAB {$budget->budget_number} resmi disetujui! Baseline anggaran batch telah terkunci.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cetak PDF Dokumen RAB.
     */
    public function pdf(int $id, PdfBudgetService $pdfService)
    {
        $companyId = Auth::user()->company_id;
        $budget = Budget::where('company_id', $companyId)->findOrFail($id);

        return $pdfService->generateBudgetPdf($budget);
    }
}
