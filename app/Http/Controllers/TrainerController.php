<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Services\AuditTrailService;
use App\Services\DeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TrainerController extends Controller
{
    /**
     * Tampilkan Daftar Instruktur / Trainer
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = Trainer::where('company_id', $companyId)
            ->withCount(['trainingBatches', 'vendorBills'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $trainers = $query->paginate(15)->withQueryString();

        return view('operations.trainers.index', compact('trainers'));
    }

    /**
     * Form Tambah Instruktur Baru
     */
    public function create(): View
    {
        return view('operations.trainers.create');
    }

    /**
     * Simpan Instruktur Baru
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:trainers,code,NULL,id,company_id,' . $companyId],
            'name' => ['required', 'string', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'id_card_number' => ['nullable', 'string', 'max:30'],
            'default_daily_rate' => ['required', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['company_id'] = $companyId;
        $validated['is_active'] = true;

        $trainer = Trainer::create($validated);

        AuditTrailService::log(
            'create',
            $trainer,
            null,
            $trainer->toArray(),
            "Menambahkan instruktur baru: {$trainer->name} ({$trainer->code})"
        );

        return redirect()->route('trainers.show', $trainer->id)
            ->with('success', "Instruktur \"{$trainer->name}\" berhasil ditambahkan.");
    }

    /**
     * Tampilkan Detail Instruktur
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $trainer = Trainer::where('company_id', $companyId)
            ->with(['trainingBatches' => function ($q) {
                $q->orderBy('start_date', 'desc')->take(10);
            }, 'vendorBills' => function ($q) {
                $q->orderBy('bill_date', 'desc')->take(10);
            }])
            ->findOrFail($id);

        return view('operations.trainers.show', compact('trainer'));
    }

    /**
     * Form Edit Instruktur
     */
    public function edit(int $id): View
    {
        $companyId = Auth::user()->company_id;
        $trainer = Trainer::where('company_id', $companyId)->findOrFail($id);

        return view('operations.trainers.edit', compact('trainer'));
    }

    /**
     * Update Data Instruktur
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $trainer = Trainer::where('company_id', $companyId)->findOrFail($id);
        $oldData = $trainer->toArray();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:trainers,code,' . $id . ',id,company_id,' . $companyId],
            'name' => ['required', 'string', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'id_card_number' => ['nullable', 'string', 'max:30'],
            'default_daily_rate' => ['required', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $trainer->update($validated);

        AuditTrailService::log(
            'update',
            $trainer,
            $oldData,
            $trainer->toArray(),
            "Memperbarui data instruktur: {$trainer->name}"
        );

        return redirect()->route('trainers.show', $trainer->id)
            ->with('success', "Data instruktur \"{$trainer->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus Data Instruktur (Approval Super Admin)
     */
    public function destroy(Request $request, int $id, DeletionService $deletionService): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $trainer = Trainer::where('company_id', $companyId)->findOrFail($id);

        if ($trainer->trainingBatches()->exists() || $trainer->vendorBills()->exists()) {
            return back()->with('error', "Instruktur \"{$trainer->name}\" tidak dapat dihapus karena sudah memiliki histori pelatihan atau honorarium. Non-aktifkan status instruktur jika tidak aktif.");
        }

        $result = $deletionService->requestOrDelete(
            Auth::user(),
            $trainer,
            "Instruktur: {$trainer->name} ({$trainer->code})",
            $request->input('reason')
        );

        if ($result['status'] === 'deleted') {
            return redirect()->route('trainers.index')->with('success', $result['message']);
        }

        return back()->with('info', $result['message']);
    }
}
