<?php

namespace App\Http\Controllers;

use App\Models\CashBankAccount;
use App\Models\Lsp;
use App\Models\Trainer;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use App\Services\PdfVendorBillService;
use App\Services\VendorPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VendorPaymentController extends Controller
{
    /**
     * Daftar Bukti Pembayaran Hutang Vendor & LSP.
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $query = VendorPayment::where('company_id', $companyId)
            ->with(['lsp', 'vendor', 'trainer', 'cashBankAccount', 'allocations.vendorBill'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('lsp', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('vendor', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('trainer', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('vendor_payments.index', compact('payments'));
    }

    /**
     * Form Catat Pembayaran Tagihan Vendor/LSP.
     */
    public function create(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        $lsps = Lsp::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $trainers = Trainer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $cashBankAccounts = CashBankAccount::where('company_id', $companyId)->where('is_active', true)->get();

        $selectedPayeeType = $request->input('payee_type', 'lsp');
        $selectedPayeeId = $request->input('payee_id');
        $preselectedBillId = $request->input('bill_id');

        $outstandingBills = collect();
        if ($selectedPayeeId) {
            $billsQuery = VendorBill::where('company_id', $companyId)
                ->whereIn('status', ['approved', 'partial'])
                ->where('balance_due', '>', 0);

            if ($selectedPayeeType === 'lsp') {
                $billsQuery->where('lsp_id', $selectedPayeeId);
            } elseif ($selectedPayeeType === 'vendor') {
                $billsQuery->where('vendor_id', $selectedPayeeId);
            } elseif ($selectedPayeeType === 'trainer') {
                $billsQuery->where('trainer_id', $selectedPayeeId);
            }

            $outstandingBills = $billsQuery->orderBy('due_date')->get();
        }

        return view('vendor_payments.create', compact(
            'lsps',
            'vendors',
            'trainers',
            'cashBankAccounts',
            'selectedPayeeType',
            'selectedPayeeId',
            'preselectedBillId',
            'outstandingBills'
        ));
    }

    /**
     * Simpan Pembayaran Tagihan & Potong Saldo Kas/Bank.
     */
    public function store(Request $request, VendorPaymentService $service): RedirectResponse
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'payee_type' => ['required', 'in:lsp,vendor,trainer'],
            'payee_id' => ['required', 'integer'],
            'cash_bank_account_id' => ['required', 'exists:cash_bank_accounts,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:bank_transfer,cash,giro'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.vendor_bill_id' => ['required', 'exists:vendor_bills,id'],
            'allocations.*.amount_applied' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $payment = $service->recordPayment([
                'company_id' => $companyId,
                'lsp_id' => ($request->payee_type === 'lsp') ? (int) $request->payee_id : null,
                'vendor_id' => ($request->payee_type === 'vendor') ? (int) $request->payee_id : null,
                'trainer_id' => ($request->payee_type === 'trainer') ? (int) $request->payee_id : null,
                'cash_bank_account_id' => (int) $request->cash_bank_account_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => (float) $request->amount,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'allocations' => $request->allocations,
            ]);

            return redirect()->route('vendor-payments.show', $payment->id)
                ->with('success', "Voucher Pembayaran {$payment->payment_number} berhasil dicatat dan jurnal pengeluaran kas telah diposting!");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Detail Voucher Pembayaran.
     */
    public function show(int $id): View
    {
        $companyId = Auth::user()->company_id;

        $payment = VendorPayment::where('company_id', $companyId)
            ->with([
                'lsp',
                'vendor',
                'trainer',
                'cashBankAccount',
                'allocations.vendorBill',
                'journalEntry.lines.account',
                'createdBy',
            ])
            ->findOrFail($id);

        return view('vendor_payments.show', compact('payment'));
    }

    /**
     * Cetak PDF Payment Voucher Resmi.
     */
    public function voucherPdf(int $id, PdfVendorBillService $pdfService)
    {
        $companyId = Auth::user()->company_id;
        $payment = VendorPayment::where('company_id', $companyId)->findOrFail($id);

        return $pdfService->generateVoucherPdf($payment);
    }
}
