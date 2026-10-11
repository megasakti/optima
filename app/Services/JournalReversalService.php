<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class JournalReversalService
{
    /**
     * Membalik jurnal akuntansi posted (Storno / Reversing Entry).
     */
    public function reverse(JournalEntry $originalJournal, string $reason, ?string $reversalDate = null): JournalEntry
    {
        return DB::transaction(function () use ($originalJournal, $reason, $reversalDate) {
            // 1. Validasi Status Jurnal Asal
            if ($originalJournal->status !== 'posted') {
                throw new InvalidArgumentException(
                    "Hanya jurnal dengan status 'posted' yang dapat dibalik. Status jurnal saat ini: '{$originalJournal->status}'."
                );
            }

            if ($originalJournal->reversed_by_id !== null) {
                throw new InvalidArgumentException(
                    "Jurnal {$originalJournal->entry_number} telah dibalik sebelumnya oleh jurnal ID #{$originalJournal->reversed_by_id}."
                );
            }

            $date = $reversalDate ?? now()->toDateString();
            $companyId = $originalJournal->company_id;

            // 2. Validasi Periode Akuntansi Aktif untuk Tanggal Pembalikan
            $period = AccountingPeriod::where('company_id', $companyId)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->where('is_closed', false)
                ->first();

            if (! $period) {
                $period = AccountingPeriod::where('company_id', $companyId)
                    ->where('is_closed', false)
                    ->orderBy('start_date')
                    ->first();

                if (! $period) {
                    throw new InvalidArgumentException("Tidak ada periode akuntansi aktif untuk tanggal pembalikan {$date}.");
                }
            }

            // 3. Generate Nomor Jurnal Pembalik
            $reversalNumber = 'REV-'.$originalJournal->entry_number;

            // 4. Buat Header Jurnal Pembalik
            $user = Auth::user();

            $reversalJournal = JournalEntry::create([
                'company_id' => $companyId,
                'period_id' => $period->id,
                'batch_id' => $originalJournal->batch_id,
                'entry_number' => $reversalNumber,
                'entry_date' => $date,
                'reference_type' => JournalEntry::class,
                'reference_id' => $originalJournal->id,
                'source_module' => 'reversal',
                'description' => "Pembalik Jurnal {$originalJournal->entry_number}. Alasan: {$reason}",
                'total_debit' => $originalJournal->total_credit, // Dibalik
                'total_credit' => $originalJournal->total_debit, // Dibalik
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => $user?->id,
            ]);

            // 5. Salin dan Balik Semua Baris Jurnal (Debit <-> Kredit)
            $originalJournal->load('lines');
            foreach ($originalJournal->lines as $line) {
                JournalLine::create([
                    'journal_entry_id' => $reversalJournal->id,
                    'account_id' => $line->account_id,
                    'debit' => $line->credit,  // Posisi ditukar
                    'credit' => $line->debit,  // Posisi ditukar
                    'memo' => "Pembalik baris ID #{$line->id} ({$line->memo})",
                ]);
            }

            // 6. Kunci dan Perbarui Status Jurnal Asal
            $originalJournal->update([
                'status' => 'reversed',
                'reversed_by_id' => $reversalJournal->id,
            ]);

            // 7. Audit Trail
            AuditTrailService::log(
                'reverse_journal',
                $originalJournal,
                ['status' => 'posted'],
                ['status' => 'reversed', 'reversed_by_id' => $reversalJournal->id],
                "Pembalikan Jurnal {$originalJournal->entry_number} dengan jurnal baru {$reversalJournal->entry_number}. Alasan: {$reason}"
            );

            return $reversalJournal;
        });
    }
}
