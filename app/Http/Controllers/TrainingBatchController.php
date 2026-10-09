<?php

namespace App\Http\Controllers;

use App\Models\Lsp;
use App\Models\Trainer;
use App\Models\TrainingBatch;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Services\BatchProfitabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TrainingBatchController extends Controller
{
    /**
     * Daftar Batch Pelatihan & Sertifikasi BNSP.
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = TrainingBatch::where('company_id', $companyId)
            ->with(['program', 'lsp', 'trainer', 'budget'])
            ->orderBy('start_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('program', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $batches = $query->paginate(20)->withQueryString();

        return view('training.batches.index', compact('batches'));
    }

    /**
     * Form Buka Angkatan / Batch Baru.
     */
    public function create(): View
    {
        $companyId = Auth::user()->company_id;

        $programs = TrainingProgram::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $lsps = Lsp::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $trainers = Trainer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $users = User::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        return view('training.batches.create', compact('programs', 'lsps', 'trainers', 'users'));
    }

    /**
     * Simpan Batch Baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'program_id' => ['required', 'exists:training_programs,id'],
            'name' => ['required', 'string', 'max:200'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'assessment_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'delivery_type' => ['required', 'in:offline_hotel,offline_inhouse,online,hybrid'],
            'venue_name' => ['nullable', 'string', 'max:150'],
            'venue_city' => ['nullable', 'string', 'max:100'],
            'target_participants' => ['required', 'integer', 'min:1'],
            'price_per_participant' => ['required', 'numeric', 'min:0'],
            'lsp_id' => ['nullable', 'exists:lsps,id'],
            'trainer_id' => ['nullable', 'exists:trainers,id'],
            'pic_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $batchCode = $this->generateBatchCode($companyId, $request->start_date);

        $batch = TrainingBatch::create([
            'company_id' => $companyId,
            'program_id' => (int) $request->program_id,
            'lsp_id' => $request->filled('lsp_id') ? (int) $request->lsp_id : null,
            'trainer_id' => $request->filled('trainer_id') ? (int) $request->trainer_id : null,
            'pic_user_id' => $request->filled('pic_user_id') ? (int) $request->pic_user_id : Auth::id(),
            'batch_code' => $batchCode,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'assessment_date' => $request->assessment_date,
            'delivery_type' => $request->delivery_type,
            'venue_name' => $request->venue_name,
            'venue_city' => $request->venue_city,
            'target_participants' => (int) $request->target_participants,
            'price_per_participant' => (float) $request->price_per_participant,
            'status' => 'confirmed',
            'notes' => $request->notes,
        ]);

        return redirect()->route('batches.show', $batch->id)
            ->with('success', "Batch Pelatihan {$batch->batch_code} berhasil didaftarkan. Anda dapat menyusun RAB sekarang.");
    }

    /**
     * Detail Batch Pelatihan.
     */
    public function show(int $id, BatchProfitabilityService $profitService): View
    {
        $companyId = Auth::user()->company_id;

        $batch = TrainingBatch::where('company_id', $companyId)
            ->with(['program', 'lsp', 'trainer', 'pic', 'budget.items', 'invoices.customer', 'vendorBills.vendor', 'vendorBills.lsp'])
            ->findOrFail($id);

        $financials = $profitService->getBatchFinancialSummary($batch);

        return view('training.batches.show', compact('batch', 'financials'));
    }

    /**
     * Generate Batch Code BTH-YYYY-MM-XXXX
     */
    protected function generateBatchCode(int $companyId, string $date): string
    {
        $yearMonth = date('Y-m', strtotime($date));
        $prefix = "BTH-" . $yearMonth . "-";

        $lastNumber = TrainingBatch::where('company_id', $companyId)
            ->where('batch_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('batch_code');

        if ($lastNumber) {
            $lastSeq = (int) substr($lastNumber, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
