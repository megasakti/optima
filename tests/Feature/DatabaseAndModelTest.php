<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Budget;
use App\Models\CashBankAccount;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Lsp;
use App\Models\Trainer;
use App\Models\TrainingBatch;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseAndModelTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    public function test_company_and_users_exist_with_roles(): void
    {
        $company = Company::where('code', 'OPTIMA-ID')->first();
        $this->assertNotNull($company);
        $this->assertEquals('PT Optima Mitra Sertifikasi', $company->name);

        $admin = User::where('email', 'admin@optima.local')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('Super Admin'));

        $financeMgr = User::where('email', 'finance.manager@optima.local')->first();
        $this->assertNotNull($financeMgr);
        $this->assertTrue($financeMgr->hasRole('Finance Manager'));
    }

    public function test_chart_of_accounts_integrity(): void
    {
        // 101 Kas
        $kas = Account::where('code', '101')->first();
        $this->assertNotNull($kas);
        $this->assertEquals('asset', $kas->type);
        $this->assertEquals('debit', $kas->normal_balance);
        $this->assertTrue($kas->is_cash_bank);

        // 102 Kas Bank BCA
        $bca = Account::where('code', '102')->first();
        $this->assertNotNull($bca);
        $this->assertEquals('asset', $bca->type);

        // 202 Utang Sertifikasi (LSP)
        $utangSertifikasi = Account::where('code', '202')->first();
        $this->assertNotNull($utangSertifikasi);
        $this->assertEquals('liability', $utangSertifikasi->type);
        $this->assertEquals('credit', $utangSertifikasi->normal_balance);

        // 504 Biaya Sertifikasi
        $biayaSertifikasi = Account::where('code', '504')->first();
        $this->assertNotNull($biayaSertifikasi);
        $this->assertEquals('expense', $biayaSertifikasi->type);
        $this->assertEquals('direct_cost', $biayaSertifikasi->category);
    }

    public function test_operational_masters_and_batch_relationships(): void
    {
        $batch = TrainingBatch::where('batch_code', 'BTH-2026-03-001')->first();
        $this->assertNotNull($batch);
        $this->assertInstanceOf(TrainingProgram::class, $batch->program);
        $this->assertInstanceOf(Lsp::class, $batch->lsp);
        $this->assertInstanceOf(Trainer::class, $batch->trainer);
        $this->assertEquals('ongoing', $batch->status);
    }

    public function test_double_entry_journal_integrity(): void
    {
        $journals = JournalEntry::with('lines')->get();
        $this->assertGreaterThan(0, $journals->count());

        foreach ($journals as $journal) {
            $sumDebit = $journal->lines->sum('debit');
            $sumCredit = $journal->lines->sum('credit');

            $this->assertEquals(
                number_format((float) $journal->total_debit, 2, '.', ''),
                number_format((float) $sumDebit, 2, '.', ''),
                "Total debit journal {$journal->entry_number} mismatch"
            );

            $this->assertEquals(
                number_format((float) $journal->total_credit, 2, '.', ''),
                number_format((float) $sumCredit, 2, '.', ''),
                "Total credit journal {$journal->entry_number} mismatch"
            );

            $this->assertTrue(
                $journal->isBalanced(),
                "Journal {$journal->entry_number} is not balanced: Debit={$sumDebit}, Credit={$sumCredit}"
            );
        }
    }

    public function test_invoice_and_customer_payment_flow(): void
    {
        $invoice = Invoice::with(['items', 'paymentAllocations', 'journalEntry'])->first();
        $this->assertNotNull($invoice);
        $this->assertGreaterThan(0, $invoice->items->count());
        $this->assertEquals(52000000.00, (float) $invoice->total_amount);
        $this->assertEquals(26000000.00, (float) $invoice->paid_amount);
        $this->assertEquals(26000000.00, (float) $invoice->balance_due);
        $this->assertNotNull($invoice->journalEntry);

        $payment = CustomerPayment::with(['allocations.invoice', 'cashBankAccount'])->first();
        $this->assertNotNull($payment);
        $this->assertEquals(26000000.00, (float) $payment->amount);
        $this->assertEquals($invoice->id, $payment->allocations->first()->invoice_id);
    }

    public function test_vendor_bill_and_lsp_payable(): void
    {
        $bill = VendorBill::with(['items', 'lsp', 'batch', 'journalEntry'])->first();
        $this->assertNotNull($bill);
        $this->assertEquals('BILL-2026-03-0001', $bill->bill_number);
        $this->assertEquals(21600000.00, (float) $bill->total_amount);
        $this->assertNotNull($bill->lsp);
        $this->assertNotNull($bill->batch);
        $this->assertNotNull($bill->journalEntry);
    }

    public function test_budget_rab_and_cost_items(): void
    {
        $budget = Budget::with(['items', 'batch'])->first();
        $this->assertNotNull($budget);
        $this->assertEquals('RAB-2026-03-001', $budget->budget_number);
        $this->assertEquals(78000000.00, (float) $budget->total_revenue_budget);
        $this->assertEquals(45500000.00, (float) $budget->total_cost_budget);
        $this->assertEquals(4, $budget->items->count());
    }
}
