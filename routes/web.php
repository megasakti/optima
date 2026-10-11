<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BatchProfitabilityController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CashBankController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeletionApprovalController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LspController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PayableAgingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceivableAgingController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\TrainingBatchController;
use App\Http\Controllers\TrainingProgramController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorBillController;
use App\Http\Controllers\VendorPaymentController;
use Illuminate\Support\Facades\Route;

// Redirect Root
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Accounting & General Ledger Routes (Permission Protected: view-ledger)
    Route::middleware('permission:view-ledger')->prefix('accounting')->name('accounting.')->group(function () {
        // Chart of Accounts
        Route::get('/accounts', [AccountingController::class, 'accounts'])->name('accounts.index');
        Route::post('/accounts', [AccountingController::class, 'storeAccount'])->name('accounts.store');

        // General Journals
        Route::get('/journals', [AccountingController::class, 'journals'])->name('journals.index');
        Route::get('/journals/create', [AccountingController::class, 'createJournal'])->name('journals.create');
        Route::post('/journals', [AccountingController::class, 'storeJournal'])->name('journals.store');
        Route::get('/journals/template', [AccountingController::class, 'downloadJournalTemplate'])->name('journals.template');
        Route::post('/journals/import', [AccountingController::class, 'importJournals'])->name('journals.import');
        Route::get('/journals/{id}', [AccountingController::class, 'showJournal'])->name('journals.show');
        Route::post('/journals/{id}/reverse', [AccountingController::class, 'reverseJournal'])->name('journals.reverse');

        // General Ledger (Buku Besar)
        Route::get('/ledger', [AccountingController::class, 'ledger'])->name('ledger.index');

        // Trial Balance (Neraca Saldo)
        Route::get('/trial-balance', [AccountingController::class, 'trialBalance'])->name('trial_balance.index');

        // Accounting Periods & Tutup Buku
        Route::get('/periods', [AccountingController::class, 'periods'])->name('periods.index');
        Route::post('/periods', [AccountingController::class, 'storePeriod'])->name('periods.store');
        Route::post('/periods/{id}/close', [AccountingController::class, 'closePeriod'])->name('periods.close');

        // Test Endpoint
        Route::get('/test-ledger', function () {
            return response()->json(['status' => 'authorized', 'module' => 'General Ledger']);
        })->name('test-ledger');
    });

    // Invoices & Accounts Receivable (Tahap 9)
    Route::middleware('permission:view-invoices')->prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create')->middleware('permission:create-invoices');
        Route::post('/', [InvoiceController::class, 'store'])->name('store')->middleware('permission:create-invoices');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/{id}/issue', [InvoiceController::class, 'issue'])->name('issue')->middleware('permission:approve-invoices');
        Route::get('/{id}/pdf', [InvoiceController::class, 'pdf'])->name('pdf');
    });

    // Customer Payments / Pelunasan Piutang (Tahap 9)
    Route::middleware('permission:view-customer-payments')->prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [CustomerPaymentController::class, 'index'])->name('index');
        Route::get('/create', [CustomerPaymentController::class, 'create'])->name('create')->middleware('permission:record-customer-payments');
        Route::post('/', [CustomerPaymentController::class, 'store'])->name('store')->middleware('permission:record-customer-payments');
        Route::get('/{id}', [CustomerPaymentController::class, 'show'])->name('show');
        Route::get('/{id}/receipt-pdf', [CustomerPaymentController::class, 'receiptPdf'])->name('receipt-pdf');
    });

    // Financial Reports: AR Aging, AP Aging, Batch Profitability, & Laporan Keuangan SAK EMKM (Tahap 12)
    Route::middleware('permission:view-financial-reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/aging-receivable', [ReceivableAgingController::class, 'index'])->name('aging_receivable');
        Route::get('/aging-payable', [PayableAgingController::class, 'index'])->name('aging_payable');
        Route::get('/batch-profitability', [BatchProfitabilityController::class, 'index'])->name('batch_profitability');
        Route::get('/batch-profitability/{id}', [BatchProfitabilityController::class, 'show'])->name('batch_profitability.detail');

        // Laporan Keuangan SAK EMKM Terpadu
        Route::get('/income-statement', [FinancialReportController::class, 'incomeStatement'])->name('income_statement');
        Route::get('/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('balance_sheet');
        Route::get('/cash-flow', [FinancialReportController::class, 'cashFlow'])->name('cash_flow');
    });

    // Vendor & LSP Bills / Accounts Payable (Tahap 10)
    Route::middleware('permission:view-bills')->prefix('bills')->name('bills.')->group(function () {
        Route::get('/', [VendorBillController::class, 'index'])->name('index');
        Route::get('/create', [VendorBillController::class, 'create'])->name('create')->middleware('permission:create-bills');
        Route::post('/', [VendorBillController::class, 'store'])->name('store')->middleware('permission:create-bills');
        Route::get('/{id}', [VendorBillController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [VendorBillController::class, 'approve'])->name('approve')->middleware('permission:approve-bills');
    });

    // Vendor Payments (Tahap 10)
    Route::middleware('permission:view-vendor-payments')->prefix('vendor-payments')->name('vendor-payments.')->group(function () {
        Route::get('/', [VendorPaymentController::class, 'index'])->name('index');
        Route::get('/create', [VendorPaymentController::class, 'create'])->name('create')->middleware('permission:record-vendor-payments');
        Route::post('/', [VendorPaymentController::class, 'store'])->name('store')->middleware('permission:record-vendor-payments');
        Route::get('/{id}', [VendorPaymentController::class, 'show'])->name('show');
        Route::get('/{id}/voucher-pdf', [VendorPaymentController::class, 'voucherPdf'])->name('voucher-pdf');
    });

    // Cash & Bank (Treasury & Reconcile) (Tahap 10)
    Route::middleware('permission:view-cash-bank')->prefix('cash-bank')->name('cash-bank.')->group(function () {
        Route::get('/', [CashBankController::class, 'index'])->name('index');
        Route::get('/transfer', [CashBankController::class, 'createTransfer'])->name('transfer')->middleware('permission:manage-cash-bank');
        Route::post('/transfer', [CashBankController::class, 'storeTransfer'])->name('transfer.store')->middleware('permission:manage-cash-bank');
        Route::post('/transactions/{id}/reconcile', [CashBankController::class, 'toggleReconcile'])->name('reconcile')->middleware('permission:reconcile-bank');
    });

    // Training Batches (Tahap 11)
    Route::middleware('permission:view-batches')->prefix('training')->name('batches.')->group(function () {
        Route::get('/batches', [TrainingBatchController::class, 'index'])->name('index');
        Route::get('/batches/create', [TrainingBatchController::class, 'create'])->name('create')->middleware('permission:create-batches');
        Route::post('/batches', [TrainingBatchController::class, 'store'])->name('store')->middleware('permission:create-batches');
        Route::get('/batches/{id}', [TrainingBatchController::class, 'show'])->name('show');
        Route::get('/test-batches', function () {
            return response()->json(['status' => 'authorized', 'module' => 'Training Batches']);
        })->name('test-batches');
    });

    // Rencana Anggaran Biaya (RAB / Budgeting) (Tahap 11)
    Route::middleware('permission:view-budgets')->prefix('budgets')->name('budgets.')->group(function () {
        Route::get('/', [BudgetController::class, 'index'])->name('index');
        Route::get('/create', [BudgetController::class, 'create'])->name('create')->middleware('permission:create-budgets');
        Route::post('/', [BudgetController::class, 'store'])->name('store')->middleware('permission:create-budgets');
        Route::get('/{id}', [BudgetController::class, 'show'])->name('show');
        Route::post('/{id}/submit', [BudgetController::class, 'submit'])->name('submit')->middleware('permission:create-budgets');
        Route::post('/{id}/approve', [BudgetController::class, 'approve'])->name('approve')->middleware('permission:approve-budgets');
        Route::get('/{id}/pdf', [BudgetController::class, 'pdf'])->name('pdf');
    });

    // Training Programs / Skema Pelatihan
    Route::middleware('permission:view-programs')->resource('programs', TrainingProgramController::class);

    // Lembaga Sertifikasi Profesi (LSP)
    Route::middleware('permission:view-lsps')->resource('lsps', LspController::class);

    // Instruktur Pelatihan (Trainers)
    Route::middleware('permission:view-trainers')->resource('trainers', TrainerController::class);

    // Data Peserta Pelatihan (Berelasi Klien/Perusahaan)
    Route::middleware('permission:view-participants')->resource('participants', ParticipantController::class);

    // Manajemen Pengguna & PIC (Super Admin)
    Route::middleware('role:Super Admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);

        // Deletion Approvals (Persetujuan Hapus Data oleh Super Admin)
        Route::get('/deletion-requests', [DeletionApprovalController::class, 'index'])->name('deletion_requests.index');
        Route::post('/deletion-requests/{id}/approve', [DeletionApprovalController::class, 'approve'])->name('deletion_requests.approve');
        Route::post('/deletion-requests/{id}/reject', [DeletionApprovalController::class, 'reject'])->name('deletion_requests.reject');

        Route::get('/test-superadmin', function () {
            return response()->json(['status' => 'authorized', 'role' => 'Super Admin']);
        })->name('test-superadmin');
    });

    // Audit Trail & Activity Logs Viewer (Tahap 12)
    Route::middleware('permission:view-audit-logs')->prefix('admin/audit-logs')->name('admin.audit_logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{id}', [AuditLogController::class, 'show'])->name('show');
    });
});
