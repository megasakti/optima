<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Lsp;
use App\Models\Trainer;
use App\Models\TrainingBatch;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\VendorBillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VendorBillController extends Controller
{
    /**
     * Daftar Tagihan Hutang Vendor, LSP, dan Instruktur.
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = VendorBill::where('company_id', $companyId)
            ->with(['lsp', 'vendor', 'trainer', 'batch', 'items'])
            ->orderBy('bill_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('lsp_id')) {
            $query->where('lsp_id', $request->lsp_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhere('vendor_invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('lsp', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('vendor', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('trainer', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $bills = $query->paginate(20)->withQueryString();

        $lsps = Lsp::where('company_id', $companyId)->orderBy('name')->get();
        $vendors = Vendor::where('company_id', $companyId)->orderBy('name')->get();
        $trainers = Trainer::where('company_id', $companyId)->orderBy('name')->get();
        $batches = TrainingBatch::where('company_id', $companyId)->orderBy('batch_code')->get();

        $totalOutstanding = VendorBill::where('company_id', $companyId)
            ->whereIn('status', ['approved', 'partial'])
            ->sum('balance_due');

        return view('bills.index', compact('bills', 'lsps', 'vendors', 'trainers', 'batches', 'totalOutstanding'));
    }

    /**
     * Form Input Tagihan Baru (Draft).
     */
    public function create(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $lsps = Lsp::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $trainers = Trainer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $batches = TrainingBatch::where('company_id', $companyId)->with('program')->orderBy('batch_code')->get();

        // Akun Beban (Expense)
        $expenseAccounts = Account::where('company_id', $companyId)
            ->where('type', 'expense')
            ->orderBy('code')
            ->get();

        $selectedBatchId = $request->input('batch_id');

        return view('bills.create', compact('lsps', 'vendors', 'trainers', 'batches', 'expenseAccounts', 'selectedBatchId'));
    }

    /**
     * Simpan Tagihan Baru.
     */
    public function store(Request $request, VendorBillService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'payee_type' => ['required', 'in:lsp,vendor,trainer'],
            'lsp_id' => ['nullable', 'required_if:payee_type,lsp', 'exists:lsps,id'],
            'vendor_id' => ['nullable', 'required_if:payee_type,vendor', 'exists:vendors,id'],
            'trainer_id' => ['nullable', 'required_if:payee_type,trainer', 'exists:trainers,id'],
            'batch_id' => ['nullable', 'exists:training_batches,id'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:bill_date'],
            'vendor_invoice_number' => ['nullable', 'string', 'max:100'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.account_id' => ['required', 'exists:accounts,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $bill = $service->createBill([
                'company_id' => $companyId,
                'lsp_id' => ($request->payee_type === 'lsp') ? (int) $request->lsp_id : null,
                'vendor_id' => ($request->payee_type === 'vendor') ? (int) $request->vendor_id : null,
                'trainer_id' => ($request->payee_type === 'trainer') ? (int) $request->trainer_id : null,
                'batch_id' => $request->filled('batch_id') ? (int) $request->batch_id : null,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'vendor_invoice_number' => $request->vendor_invoice_number,
                'tax_amount' => (float) $request->tax_amount,
                'notes' => $request->notes,
                'items' => $request->items,
            ]);

            return redirect()->route('bills.show', $bill->id)
                ->with('success', "Tagihan {$bill->bill_number} berhasil dibuat sebagai draft.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Detail Tagihan.
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $bill = VendorBill::where('company_id', $companyId)
            ->with([
                'lsp',
                'vendor',
                'trainer',
                'batch.program',
                'items.account',
                'journalEntry.lines.account',
                'paymentAllocations.vendorPayment.cashBankAccount',
                'createdBy',
                'approvedBy'
            ])
            ->findOrFail($id);

        return view('bills.show', compact('bill'));
    }

    /**
     * Setujui Tagihan & Posting Jurnal Beban/Utang.
     */
    public function approve(int $id, VendorBillService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $bill = VendorBill::where('company_id', $companyId)->findOrFail($id);

        try {
            $service->approveBill($bill);

            return back()->with('success', "Tagihan {$bill->bill_number} berhasil disetujui dan jurnal pengakuan beban & utang telah diposting!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
