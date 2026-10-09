<?php

namespace App\Http\Controllers;

use App\Models\Lsp;
use App\Models\TrainingProgram;
use App\Services\AuditTrailService;
use App\Services\DeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TrainingProgramController extends Controller
{
    /**
     * Tampilkan Daftar Skema / Program Pelatihan
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = TrainingProgram::where('company_id', $companyId)
            ->with(['lsp'])
            ->withCount('batches')
            ->orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('lsp_id')) {
            $query->where('lsp_id', $request->lsp_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('skkni_scheme_name', 'like', "%{$search}%");
            });
        }

        $programs = $query->paginate(15)->withQueryString();
        $lsps = Lsp::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        return view('operations.programs.index', compact('programs', 'lsps'));
    }

    /**
     * Form Tambah Program Baru
     */
    public function create(): View
    {
        $companyId = Auth::user()->company_id;
        $lsps = Lsp::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        return view('operations.programs.create', compact('lsps'));
    }

    /**
     * Simpan Program Baru
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:training_programs,code,NULL,id,company_id,' . $companyId],
            'name' => ['required', 'string', 'max:150'],
            'lsp_id' => ['nullable', 'exists:lsps,id'],
            'skkni_scheme_name' => ['nullable', 'string', 'max:200'],
            'category' => ['required', 'in:certification,non_certification,consulting,inhouse'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:90'],
            'default_price_per_participant' => ['required', 'numeric', 'min:0'],
            'default_lsp_cost_per_participant' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['company_id'] = $companyId;
        $validated['is_active'] = true;

        $program = TrainingProgram::create($validated);

        AuditTrailService::log(
            'create',
            $program,
            null,
            $program->toArray(),
            "Menambahkan skema pelatihan baru: {$program->name} ({$program->code})"
        );

        return redirect()->route('programs.show', $program->id)
            ->with('success', "Program/Skema pelatihan \"{$program->name}\" berhasil ditambahkan.");
    }

    /**
     * Tampilkan Detail Program Pelatihan
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $program = TrainingProgram::where('company_id', $companyId)
            ->with(['lsp', 'batches' => function ($q) {
                $q->orderBy('start_date', 'desc')->take(10);
            }])
            ->findOrFail($id);

        return view('operations.programs.show', compact('program'));
    }

    /**
     * Form Edit Program Pelatihan
     */
    public function edit(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $program = TrainingProgram::where('company_id', $companyId)->findOrFail($id);
        $lsps = Lsp::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        return view('operations.programs.edit', compact('program', 'lsps'));
    }

    /**
     * Update Program Pelatihan
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $program = TrainingProgram::where('company_id', $companyId)->findOrFail($id);
        $oldData = $program->toArray();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:training_programs,code,' . $id . ',id,company_id,' . $companyId],
            'name' => ['required', 'string', 'max:150'],
            'lsp_id' => ['nullable', 'exists:lsps,id'],
            'skkni_scheme_name' => ['nullable', 'string', 'max:200'],
            'category' => ['required', 'in:certification,non_certification,consulting,inhouse'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:90'],
            'default_price_per_participant' => ['required', 'numeric', 'min:0'],
            'default_lsp_cost_per_participant' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $program->update($validated);

        AuditTrailService::log(
            'update',
            $program,
            $oldData,
            $program->toArray(),
            "Memperbarui data skema pelatihan: {$program->name}"
        );

        return redirect()->route('programs.show', $program->id)
            ->with('success', "Data program pelatihan \"{$program->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus Program Pelatihan (Approval Super Admin)
     */
    public function destroy(Request $request, int $id, DeletionService $deletionService): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $program = TrainingProgram::where('company_id', $companyId)->findOrFail($id);

        // Jika program masih terikat dengan batch pelatihan, tolak penghapusan langsung
        if ($program->batches()->exists()) {
            return back()->with('error', "Program \"{$program->name}\" tidak dapat dihapus karena sudah memiliki batch pelatihan terkait. Non-aktifkan program jika tidak lagi digunakan.");
        }

        $result = $deletionService->requestOrDelete(
            Auth::user(),
            $program,
            "Skema Pelatihan: {$program->name} ({$program->code})",
            $request->input('reason')
        );

        if ($result['status'] === 'deleted') {
            return redirect()->route('programs.index')->with('success', $result['message']);
        }

        return back()->with('info', $result['message']);
    }
}
