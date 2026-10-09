<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\TrainingBatch;
use App\Services\InvoiceService;
use App\Services\PdfInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Daftar Faktur Invoice
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = Invoice::where('company_id', $companyId)
            ->with(['customer', 'batch', 'items'])
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('po_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20)->withQueryString();
        $customers = Customer::where('company_id', $companyId)->orderBy('name')->get();
        $batches = TrainingBatch::where('company_id', $companyId)->orderBy('batch_code')->get();

        $totalOutstanding = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->sum('balance_due');

        return view('invoices.index', compact('invoices', 'customers', 'batches', 'totalOutstanding'));
    }

    /**
     * Form Buat Faktur Invoice Baru
     */
    public function create(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $batches = TrainingBatch::where('company_id', $companyId)->with('program')->orderBy('batch_code')->get();
        $accounts = Account::where('company_id', $companyId)->where('type', 'revenue')->orderBy('code')->get();

        $selectedBatchId = $request->input('batch_id');

        return view('invoices.create', compact('customers', 'batches', 'accounts', 'selectedBatchId'));
    }

    /**
     * Simpan Invoice Baru (Draft)
     */
    public function store(Request $request, InvoiceService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'batch_id' => ['nullable', 'exists:training_batches,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'tax_ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_pph23_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'down_payment_applied' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.item_type' => ['required', 'string'],
        ]);

        try {
            $invoice = $service->createInvoice([
                'company_id' => $companyId,
                'customer_id' => (int) $request->customer_id,
                'batch_id' => $request->filled('batch_id') ? (int) $request->batch_id : null,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'po_number' => $request->po_number,
                'tax_ppn_rate' => (float) $request->tax_ppn_rate,
                'tax_pph23_rate' => (float) $request->tax_pph23_rate,
                'down_payment_applied' => (float) $request->down_payment_applied,
                'notes' => $request->notes,
                'items' => $request->items,
            ]);

            return redirect()->route('invoices.show', $invoice->id)
                ->with('success', "Draft Invoice {$invoice->invoice_number} berhasil dibuat.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Detail Invoice
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['customer', 'batch.program', 'items', 'journalEntry.lines.account', 'paymentAllocations.customerPayment.cashBankAccount', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Terbitkan Invoice (Issue & Post Jurnal)
     */
    public function issue(int $id, InvoiceService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);

        try {
            $service->issueInvoice($invoice);

            return back()->with('success', "Invoice {$invoice->invoice_number} berhasil diterbitkan dan jurnal piutang telah diposting!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cetak Dokumen Faktur Invoice PDF
     */
    public function pdf(int $id, PdfInvoiceService $pdfService)
    {
        $companyId = Auth::user()->company_id;
        $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);

        return $pdfService->generateInvoicePdf($invoice);
    }
}
