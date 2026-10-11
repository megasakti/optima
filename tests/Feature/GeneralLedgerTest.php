<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\User;
use App\Services\GeneralLedgerService;
use App\Services\JournalPostingService;
use App\Services\JournalReversalService;
use App\Services\PeriodClosingService;
use App\Services\TrialBalanceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class GeneralLedgerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $financeManager;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->financeManager = User::where('email', 'finance.manager@optima.local')->first();
        $this->company = Company::where('code', 'OPTIMA-ID')->first();
    }

    public function test_chart_of_accounts_page_rendered_successfully(): void
    {
        $response = $this->actingAs($this->financeManager)->get('/accounting/accounts');

        $response->assertStatus(200);
        $response->assertSee('Bagan Akun (Chart of Accounts)');
        $response->assertSee('KAS');
        $response->assertSee('KAS BANK BCA');
        $response->assertSee('UTANG SERTIFIKASI');
    }

    public function test_journals_index_page_rendered_successfully(): void
    {
        $response = $this->actingAs($this->financeManager)->get('/accounting/journals');

        $response->assertStatus(200);
        $response->assertSee('Jurnal Umum & Transaksi Keuangan', false);
        $response->assertSee('Total Transaksi');
        $response->assertSee('100% Balanced');
        $response->assertSee('No. Jurnal');
        $response->assertSee('Bulk Import');
    }

    public function test_user_can_create_new_account_in_coa(): void
    {
        $response = $this->actingAs($this->financeManager)->post('/accounting/accounts', [
            'code' => '516',
            'name' => 'BIAYA ASURANSI PESERTA',
            'type' => 'expense',
            'normal_balance' => 'debit',
            'category' => 'direct_cost',
            'is_cash_bank' => 0,
            'description' => 'Asuransi keselamatan peserta pelatihan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accounts', [
            'company_id' => $this->company->id,
            'code' => '516',
            'name' => 'BIAYA ASURANSI PESERTA',
        ]);
    }

    public function test_journal_posting_service_validates_double_entry_balance(): void
    {
        $service = new JournalPostingService;

        $accKas = Account::where('company_id', $this->company->id)->where('code', '101')->first();
        $accRev = Account::where('company_id', $this->company->id)->where('code', '401')->first();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Jurnal tidak seimbang (Unbalanced)');

        // Unbalanced: Debit 1.000.000 vs Kredit 500.000
        $service->createAndPost([
            'company_id' => $this->company->id,
            'entry_date' => '2026-03-20',
            'description' => 'Jurnal Unbalanced Test',
            'lines' => [
                ['account_id' => $accKas->id, 'debit' => 1000000.00, 'credit' => 0.00],
                ['account_id' => $accRev->id, 'debit' => 0.00, 'credit' => 500000.00],
            ],
        ]);
    }

    public function test_journal_posting_service_creates_and_posts_balanced_journal(): void
    {
        $service = new JournalPostingService;

        $accKas = Account::where('company_id', $this->company->id)->where('code', '101')->first();
        $accRev = Account::where('company_id', $this->company->id)->where('code', '401')->first();

        $journal = $service->createAndPost([
            'company_id' => $this->company->id,
            'entry_date' => '2026-03-20',
            'description' => 'Penerimaan Kas Pelatihan Uji Coba',
            'source_module' => 'general',
            'lines' => [
                ['account_id' => $accKas->id, 'debit' => 5000000.00, 'credit' => 0.00, 'memo' => 'Debit Kas'],
                ['account_id' => $accRev->id, 'debit' => 0.00, 'credit' => 5000000.00, 'memo' => 'Kredit Pendapatan'],
            ],
        ]);

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals('5000000.00', $journal->total_debit);
        $this->assertEquals('5000000.00', $journal->total_credit);
        $this->assertTrue($journal->isBalanced());

        $this->assertDatabaseHas('journal_entries', [
            'id' => $journal->id,
            'status' => 'posted',
        ]);

        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => $accKas->id,
            'debit' => 5000000.00,
        ]);
    }

    public function test_journal_reversal_creates_opposite_entries_and_marks_original_reversed(): void
    {
        $postingService = new JournalPostingService;
        $reversalService = new JournalReversalService;

        $accKas = Account::where('company_id', $this->company->id)->where('code', '101')->first();
        $accHotel = Account::where('company_id', $this->company->id)->where('code', '515')->first();

        // 1. Post original journal
        $original = $postingService->createAndPost([
            'company_id' => $this->company->id,
            'entry_date' => '2026-03-21',
            'description' => 'Pembayaran Hotel Akomodasi',
            'lines' => [
                ['account_id' => $accHotel->id, 'debit' => 3000000.00, 'credit' => 0.00],
                ['account_id' => $accKas->id, 'debit' => 0.00, 'credit' => 3000000.00],
            ],
        ]);

        $this->assertEquals('posted', $original->status);

        // 2. Reverse journal
        $reversal = $reversalService->reverse($original, 'Pembatalan transaksi hotel karena salah jumlah');

        $this->assertNotNull($reversal);
        $this->assertEquals('reversal', $reversal->source_module);
        $this->assertEquals('3000000.00', $reversal->total_debit);
        $this->assertEquals('3000000.00', $reversal->total_credit);

        // Verify original journal marked reversed
        $original->refresh();
        $this->assertEquals('reversed', $original->status);
        $this->assertEquals($reversal->id, $original->reversed_by_id);

        // Verify reversed lines position swapped
        $reversalLines = $reversal->lines()->get();
        $hotelLine = $reversalLines->where('account_id', $accHotel->id)->first();
        $kasLine = $reversalLines->where('account_id', $accKas->id)->first();

        $this->assertEquals(3000000.00, (float) $hotelLine->credit); // original was debit
        $this->assertEquals(0.00, (float) $hotelLine->debit);
        $this->assertEquals(3000000.00, (float) $kasLine->debit); // original was credit
        $this->assertEquals(0.00, (float) $kasLine->credit);
    }

    public function test_reversed_journal_cannot_be_reversed_again(): void
    {
        $postingService = new JournalPostingService;
        $reversalService = new JournalReversalService;

        $accKas = Account::where('company_id', $this->company->id)->where('code', '101')->first();
        $accRev = Account::where('company_id', $this->company->id)->where('code', '401')->first();

        $original = $postingService->createAndPost([
            'company_id' => $this->company->id,
            'entry_date' => '2026-03-22',
            'description' => 'Test Double Reversal',
            'lines' => [
                ['account_id' => $accKas->id, 'debit' => 1000000.00, 'credit' => 0.00],
                ['account_id' => $accRev->id, 'debit' => 0.00, 'credit' => 1000000.00],
            ],
        ]);

        $reversalService->reverse($original, 'Pembalikan 1');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Hanya jurnal dengan status 'posted' yang dapat dibalik");

        $reversalService->reverse($original, 'Pembalikan 2 yang tidak diizinkan');
    }

    public function test_general_ledger_service_calculates_correct_movements(): void
    {
        $service = new GeneralLedgerService;
        $accKas = Account::where('company_id', $this->company->id)->where('code', '101')->first();

        $ledger = $service->getAccountLedger($accKas, '2026-01-01', '2026-12-31');

        $this->assertNotNull($ledger);
        $this->assertEquals($accKas->id, $ledger['account']->id);
        $this->assertIsFloat($ledger['initial_balance']);
        $this->assertIsFloat($ledger['total_debit']);
        $this->assertIsFloat($ledger['total_credit']);
        $this->assertIsFloat($ledger['ending_balance']);
    }

    public function test_trial_balance_service_produces_balanced_report(): void
    {
        $service = new TrialBalanceService;

        $tb = $service->generate($this->company->id, '2026-12-31');

        $this->assertNotNull($tb);
        $this->assertGreaterThan(0, $tb['rows']->count());
        $this->assertTrue($tb['is_balanced'], "Neraca Saldo tidak seimbang: Debit={$tb['total_debit']}, Credit={$tb['total_credit']}");
    }

    public function test_period_closing_service_generates_closing_entries_and_locks_period(): void
    {
        $closingService = new PeriodClosingService;

        // Cari periode yang terbuka
        $period = AccountingPeriod::where('company_id', $this->company->id)->where('is_closed', false)->first();
        $this->assertNotNull($period);

        $closingJournal = $closingService->closePeriod($period, '2026-12-31');

        $this->assertNotNull($closingJournal);
        $this->assertEquals('closing', $closingJournal->source_module);
        $this->assertTrue($closingJournal->isBalanced());

        $period->refresh();
        $this->assertTrue($period->is_closed);
        $this->assertNotNull($period->closed_at);
    }
}
