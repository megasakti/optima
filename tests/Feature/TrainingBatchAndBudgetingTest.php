<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lsp;
use App\Models\Trainer;
use App\Models\TrainingBatch;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\BatchProfitabilityService;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingBatchAndBudgetingTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected Company $company;

    protected User $financeManager;

    protected User $operationsCoordinator;

    protected TrainingProgram $program;

    protected Lsp $lsp;

    protected Trainer $trainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::where('code', 'OPTIMA-ID')->firstOrFail();
        $this->financeManager = User::where('email', 'finance.manager@optima.local')->firstOrFail();
        $this->operationsCoordinator = User::where('email', 'operations@optima.local')->firstOrFail();

        $this->program = TrainingProgram::where('company_id', $this->company->id)->firstOrFail();
        $this->lsp = Lsp::where('company_id', $this->company->id)->firstOrFail();
        $this->trainer = Trainer::where('company_id', $this->company->id)->firstOrFail();
    }

    public function test_user_can_view_batches_index(): void
    {
        $response = $this->actingAs($this->operationsCoordinator)
            ->get(route('batches.index'));

        $response->assertStatus(200);
        $response->assertSee('Angkatan / Batch Pelatihan');
        $response->assertSee('Buka Batch Baru');
    }

    public function test_user_can_create_new_training_batch(): void
    {
        $response = $this->actingAs($this->operationsCoordinator)
            ->post(route('batches.store'), [
                'program_id' => $this->program->id,
                'name' => 'Batch 25 - Uji Kompetensi BNSP Ahli K3',
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-12',
                'assessment_date' => '2026-11-12',
                'delivery_type' => 'offline_hotel',
                'venue_name' => 'Hotel Santika Premiere',
                'venue_city' => 'Jakarta Barat',
                'target_participants' => 20,
                'price_per_participant' => 4500000,
                'lsp_id' => $this->lsp->id,
                'trainer_id' => $this->trainer->id,
                'pic_user_id' => $this->operationsCoordinator->id,
                'notes' => 'Batch reguler publik K3 Umum dengan sertifikasi BNSP',
            ]);

        $this->assertDatabaseHas('training_batches', [
            'company_id' => $this->company->id,
            'program_id' => $this->program->id,
            'name' => 'Batch 25 - Uji Kompetensi BNSP Ahli K3',
            'target_participants' => 20,
            'price_per_participant' => 4500000.00,
            'status' => 'confirmed',
        ]);

        $batch = TrainingBatch::where('name', 'Batch 25 - Uji Kompetensi BNSP Ahli K3')->first();
        $this->assertNotNull($batch);
        $this->assertStringStartsWith('BTH-2026-11-', $batch->batch_code);

        $response->assertRedirect(route('batches.show', $batch->id));
    }

    public function test_user_can_view_batch_detail_with_financial_summary(): void
    {
        $batch = TrainingBatch::create([
            'company_id' => $this->company->id,
            'program_id' => $this->program->id,
            'batch_code' => 'BTH-2026-11-0001',
            'name' => 'Batch Sertifikasi BNSP Pelatihan',
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-17',
            'assessment_date' => '2026-11-17',
            'delivery_type' => 'offline_hotel',
            'target_participants' => 15,
            'price_per_participant' => 5000000,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->operationsCoordinator)
            ->get(route('batches.show', $batch->id));

        $response->assertStatus(200);
        $response->assertSee('BTH-2026-11-0001');
        $response->assertSee('Realisasi Pendapatan');
        $response->assertSee('Realisasi Biaya (HPP)');
        $response->assertSee('Laba Kotor Realisasi');
    }

    public function test_user_can_create_draft_budget_and_calculate_margin(): void
    {
        $batch = TrainingBatch::create([
            'company_id' => $this->company->id,
            'program_id' => $this->program->id,
            'batch_code' => 'BTH-2026-11-0002',
            'name' => 'Batch 26 HR Supervisor BNSP',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-22',
            'delivery_type' => 'offline_hotel',
            'target_participants' => 10,
            'price_per_participant' => 4000000,
            'status' => 'confirmed',
        ]);

        $acc504 = Account::where('company_id', $this->company->id)->where('code', '504')->firstOrFail();
        $acc505 = Account::where('company_id', $this->company->id)->where('code', '505')->firstOrFail();
        $acc515 = Account::where('company_id', $this->company->id)->where('code', '515')->firstOrFail();

        // Target: 10 pax @ 4,000,000 = 40,000,000
        // Cost:
        // LSP: 10 pax @ 1,500,000 = 15,000,000
        // Trainer: 2 hari @ 3,000,000 = 6,000,000
        // Venue: 10 pax @ 400,000 = 4,000,000
        // Total cost: 25,000,000
        // Projected Gross Margin: 40,000,000 - 25,000,000 = 15,000,000
        // Margin %: 15,000,000 / 40,000,000 * 100 = 37.5%

        $response = $this->actingAs($this->operationsCoordinator)
            ->post(route('budgets.store'), [
                'batch_id' => $batch->id,
                'target_participants' => 10,
                'target_revenue_per_pax' => 4000000,
                'notes' => 'RAB Batch 26 HR Supervisor',
                'items' => [
                    [
                        'category' => 'certification',
                        'description' => 'Uji Asesmen BNSP 10 Pax',
                        'account_id' => $acc504->id,
                        'quantity' => 10,
                        'unit' => 'Pax',
                        'unit_price' => 1500000,
                    ],
                    [
                        'category' => 'trainer',
                        'description' => 'Fee Instruktur Utama 2 Hari',
                        'account_id' => $acc505->id,
                        'quantity' => 2,
                        'unit' => 'Hari',
                        'unit_price' => 3000000,
                    ],
                    [
                        'category' => 'venue',
                        'description' => 'Paket Meeting Hotel 10 Pax',
                        'account_id' => $acc515->id,
                        'quantity' => 10,
                        'unit' => 'Pax',
                        'unit_price' => 4000000 / 10, // 400,000
                    ],
                ],
            ]);

        $this->assertDatabaseHas('budgets', [
            'company_id' => $this->company->id,
            'batch_id' => $batch->id,
            'target_participants' => 10,
            'target_revenue_per_pax' => 4000000.00,
            'total_revenue_budget' => 40000000.00,
            'total_cost_budget' => 25000000.00,
            'projected_gross_margin' => 15000000.00,
            'projected_margin_percentage' => 37.50,
            'status' => 'draft',
        ]);

        $budget = Budget::where('batch_id', $batch->id)->first();
        $this->assertNotNull($budget);
        $this->assertStringStartsWith('RAB-', $budget->budget_number);
        $this->assertCount(3, $budget->items);

        $response->assertRedirect(route('budgets.show', $budget->id));
    }

    public function test_user_can_submit_and_approve_budget(): void
    {
        $batch = TrainingBatch::create([
            'company_id' => $this->company->id,
            'program_id' => $this->program->id,
            'batch_code' => 'BTH-2026-11-0003',
            'name' => 'Batch 27 Pengawas K3 Migas',
            'start_date' => '2026-11-25',
            'end_date' => '2026-11-27',
            'delivery_type' => 'offline_hotel',
            'target_participants' => 15,
            'price_per_participant' => 5000000,
            'status' => 'confirmed',
        ]);

        /** @var BudgetService $service */
        $service = app(BudgetService::class);
        $budget = $service->createOrUpdateBudget([
            'company_id' => $this->company->id,
            'batch_id' => $batch->id,
            'target_participants' => 15,
            'target_revenue_per_pax' => 5000000,
            'items' => [
                [
                    'category' => 'certification',
                    'description' => 'Biaya Sertifikasi LSP',
                    'quantity' => 15,
                    'unit' => 'Pax',
                    'unit_price' => 2000000,
                ],
            ],
        ]);

        $this->assertEquals('draft', $budget->status);

        // 1. Submit by Operations
        $submitResponse = $this->actingAs($this->operationsCoordinator)
            ->post(route('budgets.submit', $budget->id), [
                'comment' => 'Mohon approval RAB Batch 27',
            ]);

        $submitResponse->assertRedirect();
        $budget->refresh();
        $this->assertEquals('submitted', $budget->status);

        // 2. Approve by Finance Manager
        $approveResponse = $this->actingAs($this->financeManager)
            ->post(route('budgets.approve', $budget->id), [
                'comment' => 'RAB disetujui, margin proyek sesuai ketentuan target.',
            ]);

        $approveResponse->assertRedirect();
        $budget->refresh();
        $this->assertEquals('approved', $budget->status);
        $this->assertEquals($this->financeManager->id, $budget->approved_by);
        $this->assertNotNull($budget->approved_at);

        // Check approval audit trail
        $this->assertDatabaseHas('budget_approvals', [
            'budget_id' => $budget->id,
            'user_id' => $this->financeManager->id,
            'action' => 'approved',
        ]);
    }

    public function test_batch_profitability_calculates_actual_vs_budget(): void
    {
        $batch = TrainingBatch::create([
            'company_id' => $this->company->id,
            'program_id' => $this->program->id,
            'batch_code' => 'BTH-2026-11-0004',
            'name' => 'Batch 28 Analisis Profit Margin Proyek',
            'start_date' => '2026-11-28',
            'end_date' => '2026-11-30',
            'delivery_type' => 'offline_hotel',
            'target_participants' => 10,
            'price_per_participant' => 5000000,
            'status' => 'confirmed',
        ]);

        /** @var BudgetService $service */
        $budgetService = app(BudgetService::class);
        $budget = $budgetService->createOrUpdateBudget([
            'company_id' => $this->company->id,
            'batch_id' => $batch->id,
            'target_participants' => 10,
            'target_revenue_per_pax' => 5000000, // Target Rev = 50,000,000
            'items' => [
                [
                    'category' => 'certification',
                    'description' => 'Target LSP Sertifikasi',
                    'quantity' => 10,
                    'unit' => 'Pax',
                    'unit_price' => 2000000, // Target Cost = 20,000,000
                ],
            ],
        ]);
        $budgetService->submitBudget($budget);
        $budgetService->approveBudget($budget, $this->financeManager);

        // 1. Create Realized Revenue: Invoice 55,000,000 (issued)
        $customer = Customer::where('company_id', $this->company->id)->firstOrFail();
        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'batch_id' => $batch->id,
            'invoice_number' => 'INV-2026-11-9999',
            'invoice_date' => '2026-11-28',
            'due_date' => '2026-12-10',
            'subtotal' => 55000000,
            'total_amount' => 55000000,
            'paid_amount' => 30000000,
            'balance_due' => 25000000,
            'status' => 'partial',
            'created_by' => $this->financeManager->id,
        ]);

        // 2. Create Realized Direct Cost: Vendor Bill 18,000,000 (approved)
        $vendor = Vendor::where('company_id', $this->company->id)->firstOrFail();
        $acc504 = Account::where('company_id', $this->company->id)->where('code', '504')->firstOrFail();
        $bill = VendorBill::create([
            'company_id' => $this->company->id,
            'vendor_id' => $vendor->id,
            'lsp_id' => $this->lsp->id,
            'batch_id' => $batch->id,
            'bill_number' => 'BIL-2026-11-9999',
            'bill_date' => '2026-11-29',
            'due_date' => '2026-12-15',
            'subtotal' => 18000000,
            'total_amount' => 18000000,
            'paid_amount' => 18000000,
            'balance_due' => 0,
            'status' => 'approved',
            'created_by' => $this->financeManager->id,
        ]);
        $bill->items()->create([
            'account_id' => $acc504->id,
            'description' => 'Biaya Sertifikasi Asesmen LSP 10 Peserta',
            'quantity' => 10,
            'unit_price' => 1800000,
            'total_price' => 18000000,
        ]);

        /** @var BatchProfitabilityService $profitService */
        $profitService = app(BatchProfitabilityService::class);
        $summary = $profitService->getBatchFinancialSummary($batch);

        // Budget Baseline
        $this->assertEquals(50000000.0, $summary['budget']['target_revenue']);
        $this->assertEquals(20000000.0, $summary['budget']['target_cost']);
        $this->assertEquals(30000000.0, $summary['budget']['projected_margin']);
        $this->assertEquals(60.0, $summary['budget']['projected_margin_pct']);

        // Actual Realization
        $this->assertEquals(55000000.0, $summary['actuals']['actual_revenue']);
        $this->assertEquals(18000000.0, $summary['actuals']['actual_cost']);
        $this->assertEquals(37000000.0, $summary['actuals']['actual_gross_profit']);
        $this->assertEquals(round((37000000 / 55000000) * 100, 2), round($summary['actuals']['actual_margin_pct'], 2));
        $this->assertEquals(30000000.0, $summary['actuals']['paid_revenue']);
        $this->assertEquals(25000000.0, $summary['actuals']['receivable_revenue']);

        // Variance
        $this->assertEquals(5000000.0, $summary['variance']['revenue_diff']); // +5M revenue
        $this->assertEquals(-2000000.0, $summary['variance']['cost_diff']); // -2M cost (saving)
        $this->assertFalse($summary['variance']['is_cost_overrun']);

        // Test Views
        $reportIndex = $this->actingAs($this->financeManager)
            ->get(route('reports.batch_profitability'));
        $reportIndex->assertStatus(200);
        $reportIndex->assertSee('BTH-2026-11-0004');

        $reportDetail = $this->actingAs($this->financeManager)
            ->get(route('reports.batch_profitability.detail', $batch->id));
        $reportDetail->assertStatus(200);
        $reportDetail->assertSee('Matriks Komparasi Anggaran vs Realisasi');
        $reportDetail->assertSee('Pengendalian Anggaran Berada Dalam Batas Aman');
    }

    public function test_budget_pdf_renders_successfully(): void
    {
        $batch = TrainingBatch::create([
            'company_id' => $this->company->id,
            'program_id' => $this->program->id,
            'batch_code' => 'BTH-2026-11-0005',
            'name' => 'Batch 29 Uji Kompetensi PDF',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-22',
            'delivery_type' => 'offline_hotel',
            'target_participants' => 12,
            'price_per_participant' => 4500000,
            'status' => 'confirmed',
        ]);

        /** @var BudgetService $service */
        $service = app(BudgetService::class);
        $budget = $service->createOrUpdateBudget([
            'company_id' => $this->company->id,
            'batch_id' => $batch->id,
            'target_participants' => 12,
            'target_revenue_per_pax' => 4500000,
            'items' => [
                [
                    'category' => 'certification',
                    'description' => 'Biaya Sertifikasi Uji Kompetensi',
                    'quantity' => 12,
                    'unit' => 'Pax',
                    'unit_price' => 1500000,
                ],
            ],
        ]);

        $response = $this->actingAs($this->financeManager)
            ->get(route('budgets.pdf', $budget->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
