<?php

namespace App\Http\Controllers;

use App\Models\Lsp;
use App\Services\AuditTrailService;
use App\Services\DeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LspController extends Controller
{
    /**
     * Tampilkan Daftar Mitra LSP BNSP
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = Lsp::where('company_id', $companyId)
            ->withCount(['trainingPrograms', 'trainingBatches'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('bnsp_license_number', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        $lsps = $query->paginate(15)->withQueryString();

        return view('operations.lsps.index', compact('lsps'));
    }

    /**
     * Form Tambah LSP Baru
     */
    public function create(): View
    {
        return view('operations.lsps.create');
    }

    /**
     * Simpan Data LSP Baru
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:lsps,code,NULL,id,company_id,'.$companyId],
            'name' => ['required', 'string', 'max:150'],
            'bnsp_license_number' => ['nullable', 'string', 'max:100'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'pic_email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['company_id'] = $companyId;
        $validated['is_active'] = true;

        $lsp = Lsp::create($validated);

        AuditTrailService::log(
            'create',
            $lsp,
            null,
            $lsp->toArray(),
            "Menambahkan mitra LSP baru: {$lsp->name} ({$lsp->code})"
        );

        return redirect()->route('lsps.show', $lsp->id)
            ->with('success', "Data mitra LSP \"{$lsp->name}\" berhasil ditambahkan.");
    }

    /**
     * Tampilkan Detail LSP
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $lsp = Lsp::where('company_id', $companyId)
            ->with(['trainingPrograms', 'trainingBatches' => function ($q) {
                $q->orderBy('start_date', 'desc')->take(10);
            }])
            ->findOrFail($id);

        return view('operations.lsps.show', compact('lsp'));
    }

    /**
     * Form Edit LSP
     */
    public function edit(int $id): View
    {
        $companyId = Auth::user()->company_id;
        $lsp = Lsp::where('company_id', $companyId)->findOrFail($id);

        return view('operations.lsps.edit', compact('lsp'));
    }

    /**
     * Update Data LSP
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $lsp = Lsp::where('company_id', $companyId)->findOrFail($id);
        $oldData = $lsp->toArray();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:lsps,code,'.$id.',id,company_id,'.$companyId],
            'name' => ['required', 'string', 'max:150'],
            'bnsp_license_number' => ['nullable', 'string', 'max:100'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'pic_email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $lsp->update($validated);

        AuditTrailService::log(
            'update',
            $lsp,
            $oldData,
            $lsp->toArray(),
            "Memperbarui data mitra LSP: {$lsp->name}"
        );

        return redirect()->route('lsps.show', $lsp->id)
            ->with('success', "Data mitra LSP \"{$lsp->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus Data LSP (Approval Super Admin)
     */
    public function destroy(Request $request, int $id, DeletionService $deletionService): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $lsp = Lsp::where('company_id', $companyId)->findOrFail($id);

        if ($lsp->trainingBatches()->exists() || $lsp->vendorBills()->exists()) {
            return back()->with('error', "Mitra LSP \"{$lsp->name}\" tidak dapat dihapus karena sudah memiliki histori batch pelatihan atau tagihan keuangan. Non-aktifkan status LSP jika tidak lagi bermitra.");
        }

        $result = $deletionService->requestOrDelete(
            Auth::user(),
            $lsp,
            "Mitra LSP: {$lsp->name} ({$lsp->code})",
            $request->input('reason')
        );

        if ($result['status'] === 'deleted') {
            return redirect()->route('lsps.index')->with('success', $result['message']);
        }

        return back()->with('info', $result['message']);
    }
}
