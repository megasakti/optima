<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lsp;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Models\TrainingProgram;
use App\Services\AuditTrailService;
use App\Services\DeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    /**
     * Tampilkan Daftar Peserta Pelatihan & Filter
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = Participant::where('company_id', $companyId)
            ->with(['customer', 'lsp', 'trainingProgram'])
            ->latest('training_date');

        // Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('training_title', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter Kategori (Public, IHT, Webinar)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter Relasi Perusahaan / Klien
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter Status Pembayaran
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter Status Sertifikat
        if ($request->filled('certificate_status')) {
            $query->where('certificate_status', $request->certificate_status);
        }

        // Filter Sumber (Web, IG, BOD)
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Filter LSP Mitra BNSP
        if ($request->filled('lsp_id')) {
            $query->where('lsp_id', $request->lsp_id);
        }

        // Filter Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('training_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('training_date', '<=', $request->date_to);
        }

        // Ringkasan Statistik KPI
        $stats = [
            'total_participants' => Participant::where('company_id', $companyId)->count(),
            'total_revenue' => Participant::where('company_id', $companyId)->sum('price'),
            'paid_count' => Participant::where('company_id', $companyId)->where('payment_status', 'paid')->count(),
            'unpaid_count' => Participant::where('company_id', $companyId)->where('payment_status', 'unpaid')->count(),
            'certified_count' => Participant::where('company_id', $companyId)->whereIn('certificate_status', ['passed', 'issued'])->count(),
        ];

        $participants = $query->paginate(15)->withQueryString();

        // Data Master untuk Filter Dropdown
        $customers = Customer::where('company_id', $companyId)->orderBy('name')->get(['id', 'name', 'code']);
        $lsps = Lsp::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('operations.participants.index', compact('participants', 'stats', 'customers', 'lsps'));
    }

    /**
     * Form Tambah Peserta Baru
     */
    public function create(): View
    {
        $companyId = Auth::user()->company_id;

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $lsps = Lsp::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $programs = TrainingProgram::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $batches = TrainingBatch::where('company_id', $companyId)->latest('start_date')->take(20)->get();

        return view('operations.participants.create', compact('customers', 'lsps', 'programs', 'batches'));
    }

    /**
     * Simpan Data Peserta Baru
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'category' => ['required', 'in:public,iht,webinar'],
            'name' => ['required', 'string', 'max:150'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'training_title' => ['required', 'string', 'max:150'],
            'training_program_id' => ['nullable', 'exists:training_programs,id'],
            'training_batch_id' => ['nullable', 'exists:training_batches,id'],
            'training_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0'],
            'source' => ['required', 'in:web,ig,bod,other'],
            'lsp_id' => ['nullable', 'exists:lsps,id'],
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
            'certificate_status' => ['required', 'in:process,passed,failed,issued'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['company_id'] = $companyId;

        // Jika memilih customer terdaftar, simpan atau sinkronkan company_name
        if (! empty($validated['customer_id'])) {
            $customer = Customer::where('company_id', $companyId)->find($validated['customer_id']);
            if ($customer) {
                $validated['company_name'] = $customer->name;
            }
        }

        $participant = Participant::create($validated);

        AuditTrailService::log(
            'create',
            $participant,
            null,
            $participant->toArray(),
            "Mendaftarkan peserta pelatihan: {$participant->name} ({$participant->training_title})"
        );

        return redirect()->route('participants.show', $participant->id)
            ->with('success', "Data peserta \"{$participant->name}\" berhasil disimpan.");
    }

    /**
     * Tampilkan Detail Peserta
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $participant = Participant::where('company_id', $companyId)
            ->with(['customer', 'lsp', 'trainingProgram', 'trainingBatch'])
            ->findOrFail($id);

        return view('operations.participants.show', compact('participant'));
    }

    /**
     * Form Edit Peserta
     */
    public function edit(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $participant = Participant::where('company_id', $companyId)->findOrFail($id);
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $lsps = Lsp::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $programs = TrainingProgram::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $batches = TrainingBatch::where('company_id', $companyId)->latest('start_date')->take(20)->get();

        return view('operations.participants.edit', compact('participant', 'customers', 'lsps', 'programs', 'batches'));
    }

    /**
     * Update Data Peserta
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $participant = Participant::where('company_id', $companyId)->findOrFail($id);
        $oldData = $participant->toArray();

        $validated = $request->validate([
            'category' => ['required', 'in:public,iht,webinar'],
            'name' => ['required', 'string', 'max:150'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'training_title' => ['required', 'string', 'max:150'],
            'training_program_id' => ['nullable', 'exists:training_programs,id'],
            'training_batch_id' => ['nullable', 'exists:training_batches,id'],
            'training_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0'],
            'source' => ['required', 'in:web,ig,bod,other'],
            'lsp_id' => ['nullable', 'exists:lsps,id'],
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
            'certificate_status' => ['required', 'in:process,passed,failed,issued'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($validated['customer_id'])) {
            $customer = Customer::where('company_id', $companyId)->find($validated['customer_id']);
            if ($customer) {
                $validated['company_name'] = $customer->name;
            }
        }

        $participant->update($validated);

        AuditTrailService::log(
            'update',
            $participant,
            $oldData,
            $participant->toArray(),
            "Memperbarui data peserta pelatihan: {$participant->name}"
        );

        return redirect()->route('participants.show', $participant->id)
            ->with('success', "Data peserta \"{$participant->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus Data Peserta
     */
    public function destroy(Request $request, int $id, DeletionService $deletionService): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $participant = Participant::where('company_id', $companyId)->findOrFail($id);

        $result = $deletionService->requestOrDelete(
            Auth::user(),
            $participant,
            "Peserta Pelatihan: {$participant->name} ({$participant->training_title})",
            $request->input('reason')
        );

        if ($result['status'] === 'deleted') {
            return redirect()->route('participants.index')->with('success', $result['message']);
        }

        return back()->with('info', $result['message']);
    }
}
