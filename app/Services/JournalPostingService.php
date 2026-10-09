<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class JournalPostingService
{
    /**
     * Buat dan posting jurnal double-entry secara atomik.
     *
     * @param array{
     *     company_id: int,
     *     entry_date: string,
     *     description: string,
     *     batch_id?: int|null,
     *     source_module?: string,
     *     reference?: Model|null,
     *     entry_number?: string|null,
     *     lines: array<array{account_id: int, debit: float|string, credit: float|string, memo?: string|null}>
     * } $data
     * @return JournalEntry
     * @throws InvalidArgumentException
     */
    public function createAndPost(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $companyId = $data['company_id'];
            $entryDate = $data['entry_date'];

            // 1. Validasi Periode Akuntansi Aktif
            $period = AccountingPeriod::where('company_id', $companyId)
                ->where('start_date', '<=', $entryDate)
                ->where('end_date', '>=', $entryDate)
                ->where('is_closed', false)
                ->first();

            if (!$period) {
                // Fallback cari periode terbuka yang mencakup tanggal
                $period = AccountingPeriod::where('company_id', $companyId)
                    ->where('is_closed', false)
                    ->orderBy('start_date')
                    ->first();

                if (!$period) {
                    $parsedDate = \Carbon\Carbon::parse($entryDate);
                    $period = AccountingPeriod::create([
                        'company_id' => $companyId,
                        'name' => 'Periode ' . $parsedDate->translatedFormat('F Y'),
                        'start_date' => $parsedDate->copy()->startOfMonth()->toDateString(),
                        'end_date' => $parsedDate->copy()->endOfMonth()->toDateString(),
                        'is_closed' => false,
                    ]);
                }
            }

            // 2. Validasi Minimal 2 Baris Jurnal
            $lines = $data['lines'] ?? [];
            if (count($lines) < 2) {
                throw new InvalidArgumentException("Jurnal akuntansi wajib memiliki minimal 2 baris transaksi (debit dan kredit).");
            }

            // 3. Hitung dan Validasi Keseimbangan Debit vs Kredit
            $totalDebit = '0.00';
            $totalCredit = '0.00';

            foreach ($lines as $line) {
                $debit = number_format((float) ($line['debit'] ?? 0), 2, '.', '');
                $credit = number_format((float) ($line['credit'] ?? 0), 2, '.', '');

                $totalDebit = bcadd($totalDebit, $debit, 2);
                $totalCredit = bcadd($totalCredit, $credit, 2);
            }

            if (bccomp($totalDebit, $totalCredit, 2) !== 0) {
                throw new InvalidArgumentException(
                    "Jurnal tidak seimbang (Unbalanced)! Total Debit: Rp " . number_format((float) $totalDebit, 2) .
                    " tidak sama dengan Total Kredit: Rp " . number_format((float) $totalCredit, 2)
                );
            }

            if (bccomp($totalDebit, '0.00', 2) === 0) {
                throw new InvalidArgumentException("Nilai transaksi jurnal tidak boleh nol.");
            }

            // 4. Generate Nomor Jurnal Unik jika belum disediakan
            $entryNumber = $data['entry_number'] ?? $this->generateEntryNumber($companyId, $entryDate);

            // 5. Buat Header Journal Entry
            $reference = $data['reference'] ?? null;
            $user = Auth::user();

            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'period_id' => $period->id,
                'batch_id' => $data['batch_id'] ?? null,
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->getKey() : null,
                'source_module' => $data['source_module'] ?? 'general',
                'description' => $data['description'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => $user?->id,
            ]);

            // 6. Buat Detail Journal Lines
            foreach ($lines as $line) {
                JournalLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $line['account_id'],
                    'debit' => number_format((float) ($line['debit'] ?? 0), 2, '.', ''),
                    'credit' => number_format((float) ($line['credit'] ?? 0), 2, '.', ''),
                    'memo' => $line['memo'] ?? null,
                ]);
            }

            // 7. Catat Audit Log
            AuditTrailService::log(
                'post_journal',
                $journal,
                null,
                [
                    'entry_number' => $journal->entry_number,
                    'total' => $totalDebit,
                    'source_module' => $journal->source_module
                ],
                "Posting Jurnal {$journal->entry_number}: {$journal->description}"
            );

            return $journal;
        });
    }

    /**
     * Generate format nomor jurnal unik JV-YYYY-MM-XXXX
     */
    protected function generateEntryNumber(int $companyId, string $date): string
    {
        $yearMonth = date('Y-m', strtotime($date));
        $prefix = "JV-" . date('Y-m', strtotime($date)) . "-";

        $lastNumber = JournalEntry::where('company_id', $companyId)
            ->where('entry_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('entry_number');

        if ($lastNumber) {
            $lastSeq = (int) substr($lastNumber, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
