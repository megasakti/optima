<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CashBankAccount;
use App\Models\Company;
use App\Models\Lsp;
use App\Models\TrainingBatch;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\VendorBillService;
use App\Services\VendorPaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountsPayableAndTreasuryTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $financeManager;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->financeManager = User::where('email', 'finance.manager@optima.local')->firstOrFail();
        $this->company = Company::where('code', 'OPTIMA-ID')->firstOrFail();
    }

    public function test_user_can_view_bills_index(): void
    {
        $response = $this->actingAs($this->financeManager)->get(route('bills.index'));

        $response->assertStatus(200);
        $response->assertViewIs('bills.index');
        $response->assertSee('Tagihan LSP');
    }

    public function test_user_can_create_draft_bill_with_items(): void
    {
        $lsp = Lsp::where('company_id', $this->company->id)->firstOrFail();
        $batch = TrainingBatch::where('company_id', $this->company->id)->firstOrFail();
        $accExpense = Account::where('company_id', $this->company->id)->where('code', '504')->firstOrFail();

        $payload = [
            'payee_type' => 'lsp',
            'lsp_id' => $lsp->id,
            'batch_id' => $batch->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'vendor_invoice_number' => 'INV-LSP-TEST-001',
            'tax_amount' => 0.00,
            'notes' => 'Tagihan asesmen 10 asesi batch 1',
            'items' => [
                [
                    'account_id' => $accExpense->id,
                    'description' => 'Biaya Asesmen Uji Sertifikasi K3',
                    'quantity' => 10,
                    'unit_price' => 1500000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->financeManager)->post(route('bills.store'), $payload);

        $response->assertRedirect();

        $bill = VendorBill::where('company_id', $this->company->id)
            ->where('vendor_invoice_number', 'INV-LSP-TEST-001')
            ->firstOrFail();

        $this->assertEquals('draft', $bill->status);
        $this->assertEquals(15000000.00, (float) $bill->subtotal);
        $this->assertEquals(15000000.00, (float) $bill->total_amount);
        $this->assertEquals(15000000.00, (float) $bill->balance_due);
        $this->assertEquals($lsp->id, $bill->lsp_id);
        $this->assertCount(1, $bill->items);
    }

    public function test_user_can_approve_bill_and_it_creates_posted_journal_entry(): void
    {
        $lsp = Lsp::where('company_id', $this->company->id)->firstOrFail();
        $batch = TrainingBatch::where('company_id', $this->company->id)->firstOrFail();
        $accExpense = Account::where('company_id', $this->company->id)->where('code', '504')->firstOrFail();

        $service = app(VendorBillService::class);
        $bill = $service->createBill([
            'company_id' => $this->company->id,
            'lsp_id' => $lsp->id,
            'batch_id' => $batch->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'account_id' => $accExpense->id,
                    'description' => 'Biaya Asesmen BNSP',
                    'quantity' => 5,
                    'unit_price' => 1000000.00,
                ],
            ],
        ]);

        $this->assertEquals('draft', $bill->status);
        $this->assertNull($bill->journal_entry_id);

        $response = $this->actingAs($this->financeManager)->post(route('bills.approve', $bill->id));

        $response->assertRedirect();
        $bill->refresh();

        $this->assertEquals('approved', $bill->status);
        $this->assertNotNull($bill->journal_entry_id);

        $journal = $bill->journalEntry;
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals(5000000.00, (float) $journal->total_debit);
        $this->assertEquals(5000000.00, (float) $journal->total_credit);

        // Verification of double entry accounts
        // Dr. 504 Biaya Sertifikasi = 5.000.000
        // Cr. 202 Utang Sertifikasi = 5.000.000
        $accLiability = Account::where('company_id', $this->company->id)->where('code', '202')->firstOrFail();

        $expLine = $journal->lines->where('account_id', $accExpense->id)->first();
        $liabLine = $journal->lines->where('account_id', $accLiability->id)->first();

        $this->assertNotNull($expLine);
        $this->assertEquals(5000000.00, (float) $expLine->debit);

        $this->assertNotNull($liabLine);
        $this->assertEquals(5000000.00, (float) $liabLine->credit);
    }

    public function test_user_can_record_vendor_payment_with_allocation_and_it_posts_journal_and_updates_bank(): void
    {
        $lsp = Lsp::where('company_id', $this->company->id)->firstOrFail();
        $accExpense = Account::where('company_id', $this->company->id)->where('code', '504')->firstOrFail();
        $cashBank = CashBankAccount::where('company_id', $this->company->id)->where('account_type', 'bank')->firstOrFail();
        $initialBalance = (float) $cashBank->current_balance;

        // 1. Create and approve bill for Rp 8.000.000
        $billService = app(VendorBillService::class);
        $bill = $billService->createBill([
            'company_id' => $this->company->id,
            'lsp_id' => $lsp->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'account_id' => $accExpense->id,
                    'description' => 'Asesmen 4 Peserta',
                    'quantity' => 4,
                    'unit_price' => 2000000.00,
                ],
            ],
        ]);
        $billService->approveBill($bill);
        $bill->refresh();

        $this->assertEquals(8000000.00, (float) $bill->balance_due);

        // 2. Pay bill via Bank BCA
        $payload = [
            'payee_type' => 'lsp',
            'payee_id' => $lsp->id,
            'cash_bank_account_id' => $cashBank->id,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'amount' => 8000000.00,
            'reference_number' => 'TRF-OUT-8819',
            'notes' => 'Pelunasan tagihan LSP',
            'allocations' => [
                [
                    'vendor_bill_id' => $bill->id,
                    'amount_applied' => 8000000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->financeManager)->post(route('vendor-payments.store'), $payload);

        $response->assertRedirect();

        // 3. Verify Bill is PAID
        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertEquals(8000000.00, (float) $bill->paid_amount);
        $this->assertEquals(0.00, (float) $bill->balance_due);

        // 4. Verify Bank Balance Decreased
        $cashBank->refresh();
        $this->assertEquals($initialBalance - 8000000.00, (float) $cashBank->current_balance);

        // 5. Verify Journal Double-Entry
        // Dr. 202 Utang Sertifikasi = 8.000.000
        // Cr. 102 Bank BCA = 8.000.000
        $accBank = Account::findOrFail($cashBank->account_id);
        $accLiability = Account::where('company_id', $this->company->id)->where('code', '202')->firstOrFail();

        $payment = $bill->paymentAllocations->first()->vendorPayment;
        $journal = $payment->journalEntry;

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals(8000000.00, (float) $journal->total_debit);
        $this->assertEquals(8000000.00, (float) $journal->total_credit);

        $liabLine = $journal->lines->where('account_id', $accLiability->id)->first();
        $bankLine = $journal->lines->where('account_id', $accBank->id)->first();

        $this->assertEquals(8000000.00, (float) $liabLine->debit);
        $this->assertEquals(8000000.00, (float) $bankLine->credit);
    }

    public function test_treasury_transfer_between_cash_bank_accounts(): void
    {
        $bankBca = CashBankAccount::where('company_id', $this->company->id)->where('name', 'like', '%BCA%')->firstOrFail();
        $pettyCash = CashBankAccount::where('company_id', $this->company->id)->where('name', 'like', '%Kas%')->firstOrFail();

        $initialBca = (float) $bankBca->current_balance;
        $initialPetty = (float) $pettyCash->current_balance;
        $transferAmt = 3000000.00;

        $payload = [
            'from_account_id' => $bankBca->id,
            'to_account_id' => $pettyCash->id,
            'amount' => $transferAmt,
            'transfer_date' => now()->toDateString(),
            'reference_number' => 'TRF-INTERNAL-01',
            'description' => 'Pengisian Kas Kecil dari Bank BCA',
        ];

        $response = $this->actingAs($this->financeManager)->post(route('cash-bank.transfer.store'), $payload);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('cash-bank.index'));

        $bankBca->refresh();
        $pettyCash->refresh();

        $this->assertEquals($initialBca - $transferAmt, (float) $bankBca->current_balance);
        $this->assertEquals($initialPetty + $transferAmt, (float) $pettyCash->current_balance);
    }

    public function test_bank_reconciliation_toggle(): void
    {
        $cashBank = CashBankAccount::where('company_id', $this->company->id)->firstOrFail();

        $tx = \App\Models\BankTransaction::create([
            'cash_bank_account_id' => $cashBank->id,
            'transaction_date' => now()->toDateString(),
            'transaction_type' => 'deposit',
            'amount' => 1000000.00,
            'description' => 'Setoran Awal Test',
            'is_reconciled' => false,
        ]);

        $this->assertFalse($tx->is_reconciled);

        $response = $this->actingAs($this->financeManager)->post(route('cash-bank.reconcile', $tx->id));

        $response->assertRedirect();
        $tx->refresh();

        $this->assertTrue($tx->is_reconciled);
        $this->assertNotNull($tx->reconciled_at);
        $this->assertEquals($this->financeManager->id, $tx->reconciled_by);
    }

    public function test_payable_aging_report_and_voucher_pdf(): void
    {
        // 1. AP Aging report view
        $response = $this->actingAs($this->financeManager)->get(route('reports.aging_payable'));
        $response->assertStatus(200);
        $response->assertViewIs('reports.aging_payable');
        $response->assertSee('Laporan Analisis Umur Hutang');

        // 2. Voucher PDF view
        $lsp = Lsp::where('company_id', $this->company->id)->firstOrFail();
        $accExpense = Account::where('company_id', $this->company->id)->where('code', '504')->firstOrFail();
        $cashBank = CashBankAccount::where('company_id', $this->company->id)->firstOrFail();

        $billService = app(VendorBillService::class);
        $bill = $billService->createBill([
            'company_id' => $this->company->id,
            'lsp_id' => $lsp->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'account_id' => $accExpense->id,
                    'description' => 'Asesmen BNSP',
                    'quantity' => 1,
                    'unit_price' => 1000000.00,
                ],
            ],
        ]);
        $billService->approveBill($bill);

        $paymentService = app(VendorPaymentService::class);
        $payment = $paymentService->recordPayment([
            'company_id' => $this->company->id,
            'lsp_id' => $lsp->id,
            'cash_bank_account_id' => $cashBank->id,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'amount' => 1000000.00,
            'allocations' => [
                [
                    'vendor_bill_id' => $bill->id,
                    'amount_applied' => 1000000.00,
                ],
            ],
        ]);

        $pdfResponse = $this->actingAs($this->financeManager)->get(route('vendor-payments.voucher-pdf', $payment->id));
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }
}
