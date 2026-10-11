<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\FinancialStatementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportsAndAuditTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected Company $company;

    protected User $superAdmin;

    protected User $financeManager;

    protected User $operationsCoordinator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::where('code', 'OPTIMA-ID')->firstOrFail();
        $this->superAdmin = User::where('email', 'admin@optima.local')->firstOrFail();
        $this->financeManager = User::where('email', 'finance.manager@optima.local')->firstOrFail();
        $this->operationsCoordinator = User::where('email', 'operations@optima.local')->firstOrFail();
    }

    public function test_user_can_view_income_statement(): void
    {
        $response = $this->actingAs($this->financeManager)
            ->get(route('reports.income_statement', [
                'start_date' => Carbon::now()->startOfMonth()->toDateString(),
                'end_date' => Carbon::now()->endOfMonth()->toDateString(),
            ]));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN LABA RUGI');
        $response->assertSee('Pendapatan Usaha');
        $response->assertSee('Beban Pokok Pelatihan');
        $response->assertSee('LABA KOTOR');
        $response->assertSee('Beban Operasional');
        $response->assertSee('LABA BERSIH');
    }

    public function test_user_can_download_income_statement_excel_and_pdf(): void
    {
        // 1. Export Excel
        $excelResponse = $this->actingAs($this->financeManager)
            ->get(route('reports.income_statement', ['export' => 'excel']));

        $excelResponse->assertStatus(200);
        $this->assertTrue(
            str_contains($excelResponse->headers->get('content-type'), 'spreadsheet') ||
            str_contains($excelResponse->headers->get('content-disposition'), '.xlsx')
        );

        // 2. Export PDF
        $pdfResponse = $this->actingAs($this->financeManager)
            ->get(route('reports.income_statement', ['export' => 'pdf']));

        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_can_view_balance_sheet_and_verifies_balance_equilibrium(): void
    {
        $today = Carbon::now()->toDateString();

        $response = $this->actingAs($this->financeManager)
            ->get(route('reports.balance_sheet', ['as_of_date' => $today]));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN POSISI KEUANGAN (NERACA)');
        $response->assertSee('ASET (AKTIVA)');
        $response->assertSee('Aset Lancar');
        $response->assertSee('TOTAL ASET');
        $response->assertSee('LIABILITAS');
        $response->assertSee('EKUITAS');

        // Check service calculations and balance equilibrium
        /** @var FinancialStatementService $service */
        $service = app(FinancialStatementService::class);
        $sheet = $service->getBalanceSheet($this->company->id, $today);

        $this->assertArrayHasKey('total_assets', $sheet);
        $this->assertArrayHasKey('total_liabilities', $sheet);
        $this->assertArrayHasKey('total_equity', $sheet);
        $this->assertArrayHasKey('total_liabilities_and_equity', $sheet);
        $this->assertTrue($sheet['is_balanced'], "Balance sheet must be balanced: Assets={$sheet['total_assets']}, Pasiva={$sheet['total_liabilities_and_equity']}");
    }

    public function test_user_can_download_balance_sheet_excel_and_pdf(): void
    {
        // 1. Export Excel
        $excelResponse = $this->actingAs($this->financeManager)
            ->get(route('reports.balance_sheet', ['export' => 'excel']));

        $excelResponse->assertStatus(200);
        $this->assertTrue(
            str_contains($excelResponse->headers->get('content-type'), 'spreadsheet') ||
            str_contains($excelResponse->headers->get('content-disposition'), '.xlsx')
        );

        // 2. Export PDF
        $pdfResponse = $this->actingAs($this->financeManager)
            ->get(route('reports.balance_sheet', ['export' => 'pdf']));

        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_can_view_cash_flow_statement(): void
    {
        $response = $this->actingAs($this->financeManager)
            ->get(route('reports.cash_flow', [
                'start_date' => Carbon::now()->startOfMonth()->toDateString(),
                'end_date' => Carbon::now()->endOfMonth()->toDateString(),
            ]));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN ARUS KAS');
        $response->assertSee('Arus Kas dari Aktivitas Operasi');
        $response->assertSee('Arus Kas dari Aktivitas Investasi');
        $response->assertSee('SALDO KAS');
        $response->assertSee('AKHIR PERIODE');
    }

    public function test_user_can_download_cash_flow_excel_and_pdf(): void
    {
        // 1. Export Excel
        $excelResponse = $this->actingAs($this->financeManager)
            ->get(route('reports.cash_flow', ['export' => 'excel']));

        $excelResponse->assertStatus(200);
        $this->assertTrue(
            str_contains($excelResponse->headers->get('content-type'), 'spreadsheet') ||
            str_contains($excelResponse->headers->get('content-disposition'), '.xlsx')
        );

        // 2. Export PDF
        $pdfResponse = $this->actingAs($this->financeManager)
            ->get(route('reports.cash_flow', ['export' => 'pdf']));

        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_super_admin_can_view_audit_trail_logs(): void
    {
        // Authenticate as Super Admin first
        $this->actingAs($this->superAdmin);

        // Create an audit entry via AuditTrailService
        AuditTrailService::log(
            'update',
            $this->financeManager,
            ['phone' => '081200000000'],
            ['phone' => '081299999999'],
            'Pengujian pembaruan profil user pada audit trail'
        );

        $response = $this->get(route('admin.audit_logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Trail');
        $response->assertSee('Pengujian pembaruan profil user pada audit trail');

        $latestLog = AuditLog::where('description', 'Pengujian pembaruan profil user pada audit trail')->firstOrFail();

        $detailResponse = $this->actingAs($this->superAdmin)
            ->get(route('admin.audit_logs.show', $latestLog->id));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee("Rincian Log Audit #{$latestLog->id}");
        $detailResponse->assertSee('081200000000');
        $detailResponse->assertSee('081299999999');
    }

    public function test_unauthorized_user_cannot_access_audit_logs(): void
    {
        $response = $this->actingAs($this->operationsCoordinator)
            ->get(route('admin.audit_logs.index'));

        $response->assertStatus(403);
    }
}
