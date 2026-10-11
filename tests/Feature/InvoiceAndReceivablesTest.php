<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CashBankAccount;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\TrainingBatch;
use App\Models\User;
use App\Services\CustomerPaymentService;
use App\Services\InvoiceService;
use App\Services\ReceivableAgingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceAndReceivablesTest extends TestCase
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

    public function test_user_can_view_invoice_index(): void
    {
        $response = $this->actingAs($this->financeManager)->get(route('invoices.index'));

        $response->assertStatus(200);
        $response->assertViewIs('invoices.index');
        $response->assertSee('Faktur Invoice');
    }

    public function test_user_can_create_draft_invoice_with_items_and_tax_calculation(): void
    {
        $customer = Customer::where('company_id', $this->company->id)->firstOrFail();
        $batch = TrainingBatch::where('company_id', $this->company->id)->firstOrFail();

        $payload = [
            'customer_id' => $customer->id,
            'batch_id' => $batch->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'po_number' => 'PO-TEST-001',
            'tax_ppn_rate' => 11.00,
            'tax_pph23_rate' => 2.00,
            'down_payment_applied' => 5000000.00,
            'notes' => 'Tagihan termin 2 pelatihan K3',
            'items' => [
                [
                    'item_type' => 'training',
                    'description' => 'Pelatihan Ahli K3 Umum Batch 1',
                    'quantity' => 10,
                    'unit_price' => 2000000.00,
                    'discount' => 0.00,
                ],
                [
                    'item_type' => 'certification',
                    'description' => 'Uji Sertifikasi BNSP K3',
                    'quantity' => 10,
                    'unit_price' => 1000000.00,
                    'discount' => 0.00,
                ],
            ],
        ];

        // Subtotal = (10 * 2.000.000) + (10 * 1.000.000) = 30.000.000
        // PPN 11% = 3.300.000
        // DP = 5.000.000
        // Total = 30.000.000 + 3.300.000 - 5.000.000 = 28.300.000
        // PPh 23 (2%) = 600.000

        $response = $this->actingAs($this->financeManager)->post(route('invoices.store'), $payload);

        $response->assertRedirect();

        $invoice = Invoice::where('customer_id', $customer->id)->where('po_number', 'PO-TEST-001')->firstOrFail();

        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals(30000000.00, (float) $invoice->subtotal);
        $this->assertEquals(3300000.00, (float) $invoice->tax_ppn_amount);
        $this->assertEquals(600000.00, (float) $invoice->tax_pph23_amount);
        $this->assertEquals(5000000.00, (float) $invoice->down_payment_applied);
        $this->assertEquals(28300000.00, (float) $invoice->total_amount);
        $this->assertEquals(28300000.00, (float) $invoice->balance_due);
        $this->assertCount(2, $invoice->items);
    }

    public function test_user_can_issue_invoice_and_it_creates_posted_journal_entry(): void
    {
        $customer = Customer::where('company_id', $this->company->id)->firstOrFail();
        $batch = TrainingBatch::where('company_id', $this->company->id)->firstOrFail();

        $service = app(InvoiceService::class);
        $invoice = $service->createInvoice([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'batch_id' => $batch->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'tax_ppn_rate' => 11.00,
            'tax_pph23_rate' => 2.00,
            'down_payment_applied' => 2000000.00,
            'items' => [
                [
                    'item_type' => 'training',
                    'description' => 'Pelatihan Internal Audit',
                    'quantity' => 5,
                    'unit_price' => 2000000.00, // 10.000.000
                ],
            ],
        ]);

        // Subtotal = 10.000.000, PPN = 1.100.000, DP = 2.000.000 => Total = 9.100.000
        $this->assertEquals('draft', $invoice->status);
        $this->assertNull($invoice->journal_entry_id);

        $response = $this->actingAs($this->financeManager)->post(route('invoices.issue', $invoice->id));

        $response->assertRedirect();
        $invoice->refresh();

        $this->assertEquals('issued', $invoice->status);
        $this->assertNotNull($invoice->journal_entry_id);

        $journal = $invoice->journalEntry;
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals(11100000.00, (float) $journal->total_debit);
        $this->assertEquals(11100000.00, (float) $journal->total_credit);

        // Verification of double entry accounts
        // Dr. 103 Piutang Usaha = 9.100.000
        // Dr. 205 DP = 2.000.000
        // Cr. 401 Pendapatan = 10.000.000
        // Cr. 204 Utang PPN = 1.100.000
        $accAR = Account::where('company_id', $this->company->id)->where('code', '103')->firstOrFail();
        $accDP = Account::where('company_id', $this->company->id)->where('code', '205')->firstOrFail();
        $accRev = Account::where('company_id', $this->company->id)->where('code', '401')->firstOrFail();
        $accPPN = Account::where('company_id', $this->company->id)->where('code', '204')->firstOrFail();

        $arLine = $journal->lines->where('account_id', $accAR->id)->first();
        $dpLine = $journal->lines->where('account_id', $accDP->id)->first();
        $revLine = $journal->lines->where('account_id', $accRev->id)->first();
        $ppnLine = $journal->lines->where('account_id', $accPPN->id)->first();

        $this->assertNotNull($arLine);
        $this->assertEquals(9100000.00, (float) $arLine->debit);

        $this->assertNotNull($dpLine);
        $this->assertEquals(2000000.00, (float) $dpLine->debit);

        $this->assertNotNull($revLine);
        $this->assertEquals(10000000.00, (float) $revLine->credit);

        $this->assertNotNull($ppnLine);
        $this->assertEquals(1100000.00, (float) $ppnLine->credit);
    }

    public function test_user_can_record_customer_payment_with_allocation_and_it_posts_journal_and_updates_bank(): void
    {
        $customer = Customer::where('company_id', $this->company->id)->firstOrFail();
        $cashBank = CashBankAccount::where('company_id', $this->company->id)->firstOrFail();
        $initialBankBalance = (float) $cashBank->current_balance;

        // 1. Create and issue invoice for Rp 10.000.000
        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->createInvoice([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'tax_ppn_rate' => 0.00,
            'items' => [
                [
                    'item_type' => 'training',
                    'description' => 'Inhouse Training Leadership',
                    'quantity' => 1,
                    'unit_price' => 10000000.00,
                ],
            ],
        ]);
        $invoiceService->issueInvoice($invoice);
        $invoice->refresh();

        $this->assertEquals(10000000.00, (float) $invoice->balance_due);

        // 2. Record Customer Payment:
        // Cash Received: Rp 9.800.000
        // PPh 23: Rp 200.000
        // Total Credited: Rp 10.000.000 (Lunas!)
        $payload = [
            'customer_id' => $customer->id,
            'cash_bank_account_id' => $cashBank->id,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'amount' => 9800000.00,
            'pph23_deducted' => 200000.00,
            'reference_number' => 'TRF-TEST-8899',
            'notes' => 'Pelunasan invoice penuh',
            'allocations' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount_applied' => 10000000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->financeManager)->post(route('payments.store'), $payload);

        $response->assertRedirect();

        // 3. Verify Invoice is now PAID
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(10000000.00, (float) $invoice->paid_amount);
        $this->assertEquals(0.00, (float) $invoice->balance_due);

        // 4. Verify Bank Balance updated
        $cashBank->refresh();
        $this->assertEquals($initialBankBalance + 9800000.00, (float) $cashBank->current_balance);

        // 5. Verify Journal Double-Entry
        $accBank = Account::findOrFail($cashBank->account_id);
        $accTax23 = Account::where('company_id', $this->company->id)->where('code', '106')->firstOrFail();
        $accAR = Account::where('company_id', $this->company->id)->where('code', '103')->firstOrFail();

        $payment = $invoice->paymentAllocations->first()->customerPayment;
        $journal = $payment->journalEntry;

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals(10000000.00, (float) $journal->total_debit);
        $this->assertEquals(10000000.00, (float) $journal->total_credit);

        // Dr. Bank = 9.800.000
        // Dr. PPh 23 = 200.000
        // Cr. AR = 10.000.000
        $bankLine = $journal->lines->where('account_id', $accBank->id)->first();
        $taxLine = $journal->lines->where('account_id', $accTax23->id)->first();
        $arLine = $journal->lines->where('account_id', $accAR->id)->first();

        $this->assertEquals(9800000.00, (float) $bankLine->debit);
        $this->assertEquals(200000.00, (float) $taxLine->debit);
        $this->assertEquals(10000000.00, (float) $arLine->credit);
    }

    public function test_aging_receivable_report_calculates_correct_buckets(): void
    {
        $customer = Customer::where('company_id', $this->company->id)->firstOrFail();
        $invoiceService = app(InvoiceService::class);
        $asOfDate = Carbon::parse('2026-03-31');

        // Invoice 1: Current (due April 10, 2026) -> Rp 5.000.000
        $invCurrent = $invoiceService->createInvoice([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_date' => '2026-03-10',
            'due_date' => '2026-04-10',
            'tax_ppn_rate' => 0,
            'items' => [['item_type' => 'training', 'description' => 'Test 1', 'quantity' => 1, 'unit_price' => 5000000.00]],
        ]);
        $invoiceService->issueInvoice($invCurrent);

        // Invoice 2: 1-30 days overdue (due March 15, 2026; 16 days overdue) -> Rp 3.000.000
        $invOverdue1 = $invoiceService->createInvoice([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_date' => '2026-02-15',
            'due_date' => '2026-03-15',
            'tax_ppn_rate' => 0,
            'items' => [['item_type' => 'training', 'description' => 'Test 2', 'quantity' => 1, 'unit_price' => 3000000.00]],
        ]);
        $invoiceService->issueInvoice($invOverdue1);

        // Invoice 3: 31-60 days overdue (due Jan 20, 2026; 70 days? Let's check Feb 1 -> 58 days overdue) -> Rp 2.000.000
        $invOverdue2 = $invoiceService->createInvoice([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_date' => '2026-01-01',
            'due_date' => '2026-02-01',
            'tax_ppn_rate' => 0,
            'items' => [['item_type' => 'training', 'description' => 'Test 3', 'quantity' => 1, 'unit_price' => 2000000.00]],
        ]);
        $invoiceService->issueInvoice($invOverdue2);

        $agingService = app(ReceivableAgingService::class);
        $report = $agingService->getAgingReport($this->company->id, '2026-03-31');

        $this->assertGreaterThanOrEqual(5000000.00, $report['totals']['current']);
        $this->assertGreaterThanOrEqual(3000000.00, $report['totals']['days_1_30']);
        $this->assertGreaterThanOrEqual(2000000.00, $report['totals']['days_31_60']);

        // View test
        $response = $this->actingAs($this->financeManager)->get(route('reports.aging_receivable', ['as_of_date' => '2026-03-31']));
        $response->assertStatus(200);
        $response->assertViewIs('reports.aging_receivable');
    }

    public function test_invoice_pdf_and_receipt_pdf_render_successfully(): void
    {
        $customer = Customer::where('company_id', $this->company->id)->firstOrFail();
        $cashBank = CashBankAccount::where('company_id', $this->company->id)->firstOrFail();
        $invoiceService = app(InvoiceService::class);

        $invoice = $invoiceService->createInvoice([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'tax_ppn_rate' => 11.00,
            'items' => [
                [
                    'item_type' => 'training',
                    'description' => 'Training Ahli K3',
                    'quantity' => 2,
                    'unit_price' => 5000000.00,
                ],
            ],
        ]);
        $invoiceService->issueInvoice($invoice);

        // Test Invoice PDF endpoint
        $invPdfResponse = $this->actingAs($this->financeManager)->get(route('invoices.pdf', $invoice->id));
        $invPdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $invPdfResponse->headers->get('content-type'));

        // Test Customer Payment Receipt PDF endpoint
        $paymentService = app(CustomerPaymentService::class);
        $payment = $paymentService->recordPayment([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'cash_bank_account_id' => $cashBank->id,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'amount' => (float) $invoice->total_amount,
            'allocations' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount_applied' => (float) $invoice->total_amount,
                ],
            ],
        ]);

        $receiptPdfResponse = $this->actingAs($this->financeManager)->get(route('payments.receipt-pdf', $payment->id));
        $receiptPdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $receiptPdfResponse->headers->get('content-type'));
    }
}
