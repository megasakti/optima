<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\JournalImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class JournalImportTest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;

    protected User $superAdmin;

    protected User $financeManager;

    protected User $accountant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::where('code', 'OPTIMA-ID')->firstOrFail();
        $this->superAdmin = User::where('email', 'admin@optima.local')->firstOrFail();
        $this->financeManager = User::where('email', 'finance.manager@optima.local')->firstOrFail();
        $this->accountant = User::where('email', 'accountant@optima.local')->firstOrFail();
    }

    public function test_user_can_download_journal_import_template(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('accounting.journals.template'));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-type'), 'spreadsheet') ||
            str_contains($response->headers->get('content-disposition'), 'Template_Bulk_Import_Jurnal_Optima.xlsx')
        );
    }

    public function test_user_can_bulk_import_valid_journals_from_excel(): void
    {
        /** @var JournalImportService $service */
        $service = app(JournalImportService::class);
        $spreadsheet = $service->generateTemplate($this->company->id);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_import_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile($tempPath, 'jurnal_valid.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $initialCount = JournalEntry::count();

        $response = $this->actingAs($this->accountant)
            ->post(route('accounting.journals.import'), [
                'file' => $uploadedFile,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Pastikan bertambah 3 transaksi jurnal baru di database
        $this->assertDatabaseCount('journal_entries', $initialCount + 3);

        // Verifikasi seluruh jurnal berstatus posted dan seimbang
        $journals = JournalEntry::all();
        foreach ($journals as $j) {
            $this->assertEquals('posted', $j->status);
            $this->assertEquals($j->total_debit, $j->total_credit);
        }

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }

    public function test_import_rejects_unbalanced_journals(): void
    {
        $initialCount = JournalEntry::count();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['No Transaksi', 'Tanggal', 'Deskripsi', 'Kode Akun', 'Nama Akun', 'Debit', 'Kredit'],
            ['TRX-ERR-01', '2026-03-01', 'Jurnal Tidak Seimbang', '101', 'KAS', 10000000, 0],
            ['TRX-ERR-01', '2026-03-01', 'Jurnal Tidak Seimbang', '401', 'PENDAPATAN', 0, 7500000], // Selisih 2.5 jt
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_unbalanced_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile($tempPath, 'jurnal_unbalanced.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->accountant)
            ->post(route('accounting.journals.import'), [
                'file' => $uploadedFile,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // Tidak boleh ada penambahan jurnal di database
        $this->assertDatabaseCount('journal_entries', $initialCount);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }

    public function test_import_rejects_unknown_account_code(): void
    {
        $initialCount = JournalEntry::count();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['No Transaksi', 'Tanggal', 'Deskripsi', 'Kode Akun', 'Nama Akun', 'Debit', 'Kredit'],
            ['TRX-ERR-02', '2026-03-01', 'Akun Tidak Ada', '999999', 'AKUN GAIB', 5000000, 0],
            ['TRX-ERR-02', '2026-03-01', 'Akun Tidak Ada', '101', 'KAS', 0, 5000000],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_unknown_acc_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile($tempPath, 'jurnal_unknown_acc.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->accountant)
            ->post(route('accounting.journals.import'), [
                'file' => $uploadedFile,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('journal_entries', $initialCount);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }
}
