<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashBankAccount;
use App\Models\Invoice;
use App\Models\TrainingBatch;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Utama Eksekutif Keuangan
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // 1. Saldo Kas & Bank
        $cashBankAccounts = CashBankAccount::where('company_id', $companyId)->where('is_active', true)->get();
        $totalCashBank = $cashBankAccounts->sum('current_balance');

        // 2. Piutang Usaha (AR)
        $totalReceivables = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->sum('balance_due');

        // 3. Utang Usaha & Sertifikasi LSP (AP)
        $totalPayables = VendorBill::where('company_id', $companyId)
            ->whereIn('status', ['approved', 'partial'])
            ->sum('balance_due');

        // 4. Batch Pelatihan Aktif
        $activeBatchesCount = TrainingBatch::where('company_id', $companyId)
            ->whereIn('status', ['confirmed', 'ongoing'])
            ->count();

        // 5. Daftar Batch Pelatihan Terbaru
        $recentBatches = TrainingBatch::where('company_id', $companyId)
            ->with(['program', 'lsp', 'trainer'])
            ->latest('start_date')
            ->take(5)
            ->get();

        // 6. Tagihan / Invoice Pending
        $pendingInvoices = Invoice::where('company_id', $companyId)
            ->with('customer')
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->latest('invoice_date')
            ->take(5)
            ->get();

        // 7. Tagihan LSP / Vendor Pending
        $pendingBills = VendorBill::where('company_id', $companyId)
            ->with(['lsp', 'vendor'])
            ->whereIn('status', ['approved', 'partial'])
            ->latest('bill_date')
            ->take(5)
            ->get();

        // 8. Jejak Audit Terbaru
        $recentAuditLogs = AuditLog::where('company_id', $companyId)
            ->with('user')
            ->latest('created_at')
            ->take(6)
            ->get();

        return view('dashboard.index', compact(
            'cashBankAccounts',
            'totalCashBank',
            'totalReceivables',
            'totalPayables',
            'activeBatchesCount',
            'recentBatches',
            'pendingInvoices',
            'pendingBills',
            'recentAuditLogs'
        ));
    }
}
