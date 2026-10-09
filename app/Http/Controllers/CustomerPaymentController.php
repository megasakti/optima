<?php

namespace App\Http\Controllers;

use App\Models\CashBankAccount;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Services\CustomerPaymentService;
use App\Services\PdfInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerPaymentController extends Controller
{
    /**
     * Daftar Penerimaan Pembayaran Pelanggan
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = CustomerPayment::where('company_id', $companyId)
            ->with(['customer', 'cashBankAccount', 'allocations.invoice'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->paginate(20)->withQueryString();
        $customers = Customer::where('company_id', $companyId)->orderBy('name')->get();

        return view('payments.index', compact('payments', 'customers'));
    }

    /**
     * Form Catat Penerimaan Pembayaran Pelanggan
     */
    public function create(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $cashBankAccounts = CashBankAccount::where('company_id', $companyId)->where('is_active', true)->get();

        $selectedCustomerId = $request->input('customer_id');
        $preselectedInvoiceId = $request->input('invoice_id');

        $outstandingInvoices = collect();
        if ($selectedCustomerId) {
            $outstandingInvoices = Invoice::where('company_id', $companyId)
                ->where('customer_id', $selectedCustomerId)
                ->whereIn('status', ['issued', 'partial', 'overdue'])
                ->where('balance_due', '>', 0)
                ->orderBy('due_date')
                ->get();
        }

        return view('payments.create', compact('customers', 'cashBankAccounts', 'selectedCustomerId', 'preselectedInvoiceId', 'outstandingInvoices'));
    }

    /**
     * Simpan Pembayaran Pelanggan
     */
    public function store(Request $request, CustomerPaymentService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'cash_bank_account_id' => ['required', 'exists:cash_bank_accounts,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:bank_transfer,cash,giro,credit_card'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'pph23_deducted' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.invoice_id' => ['required', 'exists:invoices,id'],
            'allocations.*.amount_applied' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $payment = $service->recordPayment([
                'company_id' => $companyId,
                'customer_id' => (int) $request->customer_id,
                'cash_bank_account_id' => (int) $request->cash_bank_account_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => (float) $request->amount,
                'pph23_deducted' => (float) $request->pph23_deducted,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'allocations' => $request->allocations,
            ]);

            return redirect()->route('payments.show', $payment->id)
                ->with('success', "Pembayaran {$payment->payment_number} berhasil dicatat dan jurnal kas/piutang telah diposting!");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Detail Kwitansi Pembayaran
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $payment = CustomerPayment::where('company_id', $companyId)
            ->with(['customer', 'cashBankAccount', 'allocations.invoice', 'journalEntry.lines.account', 'createdBy'])
            ->findOrFail($id);

        return view('payments.show', compact('payment'));
    }

    /**
     * Cetak PDF Kwitansi Resmi
     */
    public function receiptPdf(int $id, PdfInvoiceService $pdfService)
    {
        $companyId = Auth::user()->company_id;
        $payment = CustomerPayment::where('company_id', $companyId)->findOrFail($id);

        return $pdfService->generateReceiptPdf($payment);
    }
}
