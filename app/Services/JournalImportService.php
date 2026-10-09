<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\TrainingBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JournalImportService
{
    public function __construct(
        protected JournalPostingService $postingService
    ) {}

    /**
     * Generate template file Excel (.xlsx) dengan 3 sheet lengkap:
     * 1. Template_Jurnal (berisi contoh transaksi riil yang seimbang)
     * 2. Daftar_Kode_Akun_COA (berisi seluruh 33 akun aktif)
     * 3. Panduan_Pengisian (instruksi aturan akuntansi dan format)
     */
    public function generateTemplate(int $companyId): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Optima Finance System')
            ->setTitle('Template Bulk Import Jurnal Umum');

        // ==========================================
        // SHEET 1: Template_Jurnal
        // ==========================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template_Jurnal');

        $headers = [
            'No Transaksi',
            'Tanggal (YYYY-MM-DD)',
            'Deskripsi Jurnal',
            'Kode Akun',
            'Nama Akun (Catatan)',
            'Debit',
            'Kredit',
            'Catatan / Memo',
            'Kode Batch Pelatihan (Opsional)',
        ];

        // Header Styling
        $sheet->fromArray($headers, null, 'A1');
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']], // Dark Navy
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Data Contoh Riil yang Seimbang
        $exampleRows = [
            // Jurnal 1: Penerimaan Kas Pembayaran Pelatihan BNSP Batch 01 (Seimbang Rp 15.000.000)
            ['TRX-001', '2026-03-10', 'Penerimaan Pembayaran Pelatihan BNSP Batch 01', '101', 'KAS', 15000000, 0, 'Pembayaran peserta tunai', ''],
            ['TRX-001', '2026-03-10', 'Penerimaan Pembayaran Pelatihan BNSP Batch 01', '401', 'PENDAPATAN JASA PELATIHAN', 0, 15000000, 'Pendapatan 10 peserta', ''],

            // Jurnal 2: Pembayaran Sewa Venue & Hotel Pelatihan (Seimbang Rp 5.000.000)
            ['TRX-002', '2026-03-12', 'Pembayaran Sewa Venue Uji Kompetensi Hotel', '515', 'BIAYA HOTEL', 5000000, 0, 'Sewa Ballroom Hotel Kartika', ''],
            ['TRX-002', '2026-03-12', 'Pembayaran Sewa Venue Uji Kompetensi Hotel', '102', 'KAS BANK BCA', 0, 5000000, 'Transfer Bank BCA', ''],

            // Jurnal 3: Pengakuan Tagihan Uji Asesmen LSP Mitra (Seimbang Rp 3.500.000)
            ['TRX-003', '2026-03-14', 'Pengakuan Biaya Uji Asesmen Mitra LSP', '504', 'BIAYA SERTIFIKASI', 3500000, 0, 'Fee asesmen 10 asesi', ''],
            ['TRX-003', '2026-03-14', 'Pengakuan Biaya Uji Asesmen Mitra LSP', '202', 'UTANG SERTIFIKASI', 0, 3500000, 'Tagihan LSP mitra', ''],
        ];

        $sheet->fromArray($exampleRows, null, 'A2');

        // Format angka Debit dan Kredit
        $sheet->getStyle('F2:G7')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A2:I7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B2:B7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:D7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Border data contoh
        $sheet->getStyle('A2:I7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // Auto width
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ==========================================
        // SHEET 2: Daftar_Kode_Akun_COA
        // ==========================================
        $coaSheet = $spreadsheet->createSheet();
        $coaSheet->setTitle('Daftar_Kode_Akun_COA');

        $coaHeaders = ['Kode Akun', 'Nama Akun', 'Klasifikasi', 'Kategori', 'Saldo Normal', 'Kas/Bank'];
        $coaSheet->fromArray($coaHeaders, null, 'A1');

        $coaHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']], // Green Emerald
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $coaSheet->getStyle('A1:F1')->applyFromArray($coaHeaderStyle);
        $coaSheet->getRowDimension(1)->setRowHeight(25);

        $accounts = Account::where('company_id', $companyId)->orderBy('code')->get();
        $coaRows = [];
        foreach ($accounts as $acc) {
            $coaRows[] = [
                $acc->code,
                $acc->name,
                strtoupper($acc->type),
                str_replace('_', ' ', strtoupper($acc->category)),
                strtoupper($acc->normal_balance),
                $acc->is_cash_bank ? 'YA' : 'TIDAK',
            ];
        }

        if (!empty($coaRows)) {
            $coaSheet->fromArray($coaRows, null, 'A2');
            $lastRow = count($coaRows) + 1;
            $coaSheet->getStyle("A2:F{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $coaSheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $coaSheet->getStyle("E2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'F') as $col) {
            $coaSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ==========================================
        // SHEET 3: Panduan_Pengisian
        // ==========================================
        $guideSheet = $spreadsheet->createSheet();
        $guideSheet->setTitle('Panduan_Pengisian');
        $guideSheet->setCellValue('A1', 'PETUNJUK PENGISIAN BULK IMPORT JURNAL UMUM OPTIMA FINANCE');
        $guideSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E3A8A');

        $guides = [
            ['1. Pengelompokan Jurnal:', 'Gunakan nilai "No Transaksi" yang sama untuk baris-baris transaksi yang merupakan satu kesatuan jurnal umum (misal TRX-001).'],
            ['2. Prinsip Double-Entry:', 'Total Debit HARUS SAMA DENGAN Total Kredit untuk setiap No Transaksi. Jurnal yang tidak seimbang akan otomatis ditolak.'],
            ['3. Minimal Baris:', 'Setiap No Transaksi wajib memiliki minimal 2 baris (minimal 1 baris debit dan 1 baris kredit).'],
            ['4. Kode Akun:', 'Wajib menggunakan Kode Akun yang terdaftar persis pada sheet "Daftar_Kode_Akun_COA".'],
            ['5. Format Tanggal:', 'Gunakan format tanggal YYYY-MM-DD (contoh: 2026-03-15) atau gunakan format tanggal bawaan Excel.'],
            ['6. Angka Nominal:', 'Isi kolom Debit dan Kredit hanya dengan angka tanpa simbol mata uang Rp atau titik pemisah ribuan (contoh: 15000000).'],
            ['7. Periode Akuntansi:', 'Sistem akan otomatis menyesuaikan transaksi dengan Periode Akuntansi aktif perusahaan.'],
        ];

        $guideSheet->fromArray($guides, null, 'A3');
        $guideSheet->getStyle('A3:A9')->getFont()->setBold(true);
        $guideSheet->getColumnDimension('A')->setWidth(30);
        $guideSheet->getColumnDimension('B')->setWidth(90);

        // Kembalikan active sheet ke Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Memproses impor file Excel (.xlsx / .xls)
     *
     * @return array{success: bool, journals_count: int, lines_count: int, total_amount: float, journal_numbers: array}
     * @throws InvalidArgumentException
     */
    public function import(UploadedFile $file, int $companyId, User $user): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls'])) {
            throw new InvalidArgumentException("Format berkas tidak valid. Harap unggah berkas berekstensi .xlsx atau .xls.");
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheet(0); // Ambil sheet pertama
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            throw new InvalidArgumentException("Berkas Excel kosong atau tidak memiliki data baris transaksi.");
        }

        // 1. Identifikasi Mapping Kolom Berdasarkan Header Baris 1
        $headerRow = array_map(fn($val) => strtolower(trim((string)$val)), $rows[1]);
        $colMap = $this->resolveColumnMapping($headerRow);

        // Ambil data akun perusahaan untuk lookup cepat
        $accounts = Account::where('company_id', $companyId)->get()->keyBy('code');

        // Ambil data batch pelatihan untuk lookup opsional
        $batches = TrainingBatch::where('company_id', $companyId)->get()->keyBy('batch_code');

        // 2. Parse & Kelompokkan Baris per No Transaksi
        $groupedJournals = [];
        $totalParsedLines = 0;

        for ($r = 2; $r <= count($rows); $r++) {
            $row = $rows[$r];

            // Cek apakah baris kosong
            $noTrx = trim((string)($row[$colMap['no_trx']] ?? ''));
            $dateRaw = trim((string)($row[$colMap['date']] ?? ''));
            $accountCode = trim((string)($row[$colMap['account_code']] ?? ''));
            $debitRaw = $row[$colMap['debit']] ?? 0;
            $creditRaw = $row[$colMap['credit']] ?? 0;

            if ($noTrx === '' && $accountCode === '' && empty($debitRaw) && empty($creditRaw)) {
                continue; // Lewati baris kosong
            }

            if ($noTrx === '') {
                throw new InvalidArgumentException("Baris {$r}: Kolom 'No Transaksi' tidak boleh kosong.");
            }

            if ($accountCode === '') {
                throw new InvalidArgumentException("Baris {$r} (Transaksi {$noTrx}): Kolom 'Kode Akun' tidak boleh kosong.");
            }

            if (!isset($accounts[$accountCode])) {
                throw new InvalidArgumentException("Baris {$r} (Transaksi {$noTrx}): Kode Akun '{$accountCode}' tidak ditemukan di Bagan Akun (COA). Periksa sheet Daftar_Kode_Akun_COA.");
            }

            // Normalisasi Tanggal
            $parsedDate = $this->parseDate($dateRaw, $r, $noTrx);

            // Normalisasi Angka Debit & Kredit
            $debit = $this->parseNumber($debitRaw);
            $credit = $this->parseNumber($creditRaw);

            if ($debit < 0 || $credit < 0) {
                throw new InvalidArgumentException("Baris {$r} (Transaksi {$noTrx}): Nilai Debit atau Kredit tidak boleh bernilai negatif.");
            }

            if ($debit == 0 && $credit == 0) {
                throw new InvalidArgumentException("Baris {$r} (Transaksi {$noTrx}): Baris harus memiliki nominal Debit atau Kredit (tidak boleh keduanya nol).");
            }

            $description = trim((string)($row[$colMap['description']] ?? ''));
            $memo = trim((string)($row[$colMap['memo']] ?? ''));
            $batchCode = trim((string)($row[$colMap['batch_code']] ?? ''));
            $batchId = isset($batches[$batchCode]) ? $batches[$batchCode]->id : null;

            if (!isset($groupedJournals[$noTrx])) {
                $groupedJournals[$noTrx] = [
                    'no_trx' => $noTrx,
                    'date' => $parsedDate,
                    'description' => $description ?: "Impor Jurnal {$noTrx}",
                    'batch_id' => $batchId,
                    'lines' => [],
                    'total_debit' => 0.0,
                    'total_credit' => 0.0,
                ];
            }

            $groupedJournals[$noTrx]['lines'][] = [
                'account_id' => $accounts[$accountCode]->id,
                'debit' => $debit,
                'credit' => $credit,
                'memo' => $memo ?: null,
            ];

            $groupedJournals[$noTrx]['total_debit'] += $debit;
            $groupedJournals[$noTrx]['total_credit'] += $credit;
            $totalParsedLines++;
        }

        if (empty($groupedJournals)) {
            throw new InvalidArgumentException("Tidak ada transaksi valid yang ditemukan di dalam berkas.");
        }

        // 3. Validasi Aturan Akuntansi (Keseimbangan & Minimal 2 Baris per Jurnal)
        foreach ($groupedJournals as $noTrx => $jData) {
            if (count($jData['lines']) < 2) {
                throw new InvalidArgumentException("Transaksi '{$noTrx}' hanya memiliki " . count($jData['lines']) . " baris. Jurnal akuntansi wajib memiliki minimal 2 baris (1 debit dan 1 kredit).");
            }

            $diff = abs($jData['total_debit'] - $jData['total_credit']);
            if ($diff > 0.01) {
                $fmtDebit = number_format($jData['total_debit'], 0, ',', '.');
                $fmtCredit = number_format($jData['total_credit'], 0, ',', '.');
                throw new InvalidArgumentException("Transaksi '{$noTrx}' TIDAK SEIMBANG! Total Debit (Rp {$fmtDebit}) tidak sama dengan Total Kredit (Rp {$fmtCredit}). Selisih: Rp " . number_format($diff, 2));
            }
        }

        // 4. Eksekusi Posting Atomik ke Database
        $createdJournals = [];
        $totalGrossAmount = 0.0;

        DB::transaction(function () use ($groupedJournals, $companyId, $user, &$createdJournals, &$totalGrossAmount) {
            foreach ($groupedJournals as $noTrx => $jData) {
                $journal = $this->postingService->createAndPost([
                    'company_id' => $companyId,
                    'entry_date' => $jData['date'],
                    'description' => $jData['description'],
                    'batch_id' => $jData['batch_id'],
                    'source_module' => 'general',
                    'lines' => $jData['lines'],
                ]);

                $createdJournals[] = $journal->entry_number;
                $totalGrossAmount += $jData['total_debit'];
            }

            AuditTrailService::log(
                'bulk_import',
                null,
                null,
                ['journals_count' => count($createdJournals), 'total_amount' => $totalGrossAmount],
                "Bulk import " . count($createdJournals) . " jurnal umum berhasil dilakukan oleh {$user->name}. Total mutasi: Rp " . number_format($totalGrossAmount, 0, ',', '.')
            );
        });

        return [
            'success' => true,
            'journals_count' => count($createdJournals),
            'lines_count' => $totalParsedLines,
            'total_amount' => $totalGrossAmount,
            'journal_numbers' => $createdJournals,
        ];
    }

    /**
     * Parsing dan Normalisasi Kolom Header Excel
     */
    protected function resolveColumnMapping(array $headers): array
    {
        $map = [
            'no_trx' => 'A',
            'date' => 'B',
            'description' => 'C',
            'account_code' => 'D',
            'account_name' => 'E',
            'debit' => 'F',
            'credit' => 'G',
            'memo' => 'H',
            'batch_code' => 'I',
        ];

        foreach ($headers as $colLetter => $headerText) {
            $h = strtolower($headerText);
            if (str_contains($h, 'no') && (str_contains($h, 'trx') || str_contains($h, 'transaksi') || str_contains($h, 'bukti'))) {
                $map['no_trx'] = $colLetter;
            } elseif (str_contains($h, 'tanggal') || str_contains($h, 'date')) {
                $map['date'] = $colLetter;
            } elseif (str_contains($h, 'deskripsi') || str_contains($h, 'keterangan')) {
                $map['description'] = $colLetter;
            } elseif (str_contains($h, 'kode') && str_contains($h, 'akun')) {
                $map['account_code'] = $colLetter;
            } elseif (str_contains($h, 'nama') && str_contains($h, 'akun')) {
                $map['account_name'] = $colLetter;
            } elseif ($h === 'debit') {
                $map['debit'] = $colLetter;
            } elseif ($h === 'kredit' || $h === 'credit') {
                $map['credit'] = $colLetter;
            } elseif (str_contains($h, 'memo') || str_contains($h, 'catatan')) {
                $map['memo'] = $colLetter;
            } elseif (str_contains($h, 'batch')) {
                $map['batch_code'] = $colLetter;
            }
        }

        return $map;
    }

    /**
     * Parsing Tanggal dari Nilai Sel Excel
     */
    protected function parseDate(mixed $dateVal, int $rowNum, string $noTrx): string
    {
        if (empty($dateVal)) {
            return Carbon::now()->toDateString();
        }

        // Jika berupa nilai serial number Excel
        if (is_numeric($dateVal)) {
            try {
                return ExcelDate::excelToDateTimeObject((float)$dateVal)->format('Y-m-d');
            } catch (\Exception $e) {
                // fall through
            }
        }

        // Jika berupa string format YYYY-MM-DD atau DD/MM/YYYY
        try {
            return Carbon::parse($dateVal)->toDateString();
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Baris {$rowNum} (Transaksi {$noTrx}): Format tanggal '{$dateVal}' tidak valid. Gunakan format YYYY-MM-DD.");
        }
    }

    /**
     * Normalisasi Angka
     */
    protected function parseNumber(mixed $val): float
    {
        if (is_numeric($val)) {
            return (float)$val;
        }

        if (is_string($val)) {
            // Bersihkan format titik, koma, spasi, simbol mata uang
            $clean = preg_replace('/[^\d.,-]/', '', $val);
            if (str_contains($clean, ',') && !str_contains($clean, '.')) {
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }
            return (float)$clean;
        }

        return 0.0;
    }
}
