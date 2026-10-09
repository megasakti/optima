<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PeriodClosingService
{
    /**
     * Eksekusi proses Tutup Buku Periode Akuntansi (Closing Entries).
     */
    public function closePeriod(AccountingPeriod $period, string $closingDate): JournalEntry
    {
        return DB::transaction(function () use ($period, $closingDate) {
            if ($period->is_closed) {
                throw new InvalidArgumentException("Periode {$period->name} sudah ditutup sebelumnya.");
            }

            $companyId = $period->company_id;

            // 1. Dapatkan Akun Ikhtisar Laba Rugi (303) dan Saldo Laba (302)
            $incomeSummaryAccount = Account::where('company_id', $companyId)->where('code', '303')->first();
            $retainedEarningsAccount = Account::where('company_id', $companyId)->where('code', '302')->first();

            if (!$incomeSummaryAccount || !$retainedEarningsAccount) {
                throw new InvalidArgumentException(
                    "Akun 303 (Ikhtisar Laba Rugi) atau 302 (Saldo Laba) belum terdaftar di Chart of Accounts."
                );
            }

            // 2. Hitung Saldo Nominal Pendapatan dalam Periode Ini
            $revenueAccounts = Account::where('company_id', $companyId)
                ->where('type', 'revenue')
                ->where('is_active', true)
                ->get();

            $closingLines = [];
            $totalRevenue = 0.0;

            foreach ($revenueAccounts as $revAcc) {
                $credits = (float) JournalLine::where('account_id', $revAcc->id)
                    ->whereHas('entry', function ($q) use ($companyId, $period) {
                        $q->where('company_id', $companyId)
                          ->where('period_id', $period->id)
                          ->where('status', 'posted')
                          ->where('source_module', '!=', 'closing');
                    })
                    ->sum('credit');

                $debits = (float) JournalLine::where('account_id', $revAcc->id)
                    ->whereHas('entry', function ($q) use ($companyId, $period) {
                        $q->where('company_id', $companyId)
                          ->where('period_id', $period->id)
                          ->where('status', 'posted')
                          ->where('source_module', '!=', 'closing');
                    })
                    ->sum('debit');

                $netRevenue = $credits - $debits;
                if ($netRevenue > 0) {
                    $totalRevenue += $netRevenue;
                    // Debit Pendapatan untuk menutupnya
                    $closingLines[] = [
                        'account_id' => $revAcc->id,
                        'debit' => $netRevenue,
                        'credit' => 0.00,
                        'memo' => "Penutupan Akun {$revAcc->code} {$revAcc->name}",
                    ];
                }
            }

            // 3. Hitung Saldo Nominal Beban / Biaya dalam Periode Ini
            $expenseAccounts = Account::where('company_id', $companyId)
                ->where('type', 'expense')
                ->where('is_active', true)
                ->get();

            $totalExpense = 0.0;

            foreach ($expenseAccounts as $expAcc) {
                $debits = (float) JournalLine::where('account_id', $expAcc->id)
                    ->whereHas('entry', function ($q) use ($companyId, $period) {
                        $q->where('company_id', $companyId)
                          ->where('period_id', $period->id)
                          ->where('status', 'posted')
                          ->where('source_module', '!=', 'closing');
                    })
                    ->sum('debit');

                $credits = (float) JournalLine::where('account_id', $expAcc->id)
                    ->whereHas('entry', function ($q) use ($companyId, $period) {
                        $q->where('company_id', $companyId)
                          ->where('period_id', $period->id)
                          ->where('status', 'posted')
                          ->where('source_module', '!=', 'closing');
                    })
                    ->sum('credit');

                $netExpense = $debits - $credits;
                if ($netExpense > 0) {
                    $totalExpense += $netExpense;
                    // Kredit Beban untuk menutupnya
                    $closingLines[] = [
                        'account_id' => $expAcc->id,
                        'debit' => 0.00,
                        'credit' => $netExpense,
                        'memo' => "Penutupan Akun {$expAcc->code} {$expAcc->name}",
                    ];
                }
            }

            // 4. Hitung Laba/Rugi Bersih (Net Profit/Loss)
            $netProfit = $totalRevenue - $totalExpense;

            if ($netProfit >= 0) {
                // Laba Bersih: Kredit Saldo Laba (302)
                $closingLines[] = [
                    'account_id' => $retainedEarningsAccount->id,
                    'debit' => 0.00,
                    'credit' => $netProfit,
                    'memo' => "Pemindahan Laba Bersih ke Saldo Laba Periode {$period->name}",
                ];
            } else {
                // Rugi Bersih: Debit Saldo Laba (302)
                $closingLines[] = [
                    'account_id' => $retainedEarningsAccount->id,
                    'debit' => abs($netProfit),
                    'credit' => 0.00,
                    'memo' => "Pemindahan Rugi Bersih ke Saldo Laba Periode {$period->name}",
                ];
            }

            // 5. Posting Jurnal Penutup
            $postingService = new JournalPostingService();
            $closingJournal = $postingService->createAndPost([
                'company_id' => $companyId,
                'entry_date' => $closingDate,
                'entry_number' => "CLS-" . date('Ymd', strtotime($closingDate)) . "-{$period->id}",
                'source_module' => 'closing',
                'description' => "Jurnal Penutup Akhir Periode {$period->name} (Laba Bersih: Rp " . number_format($netProfit, 2) . ")",
                'lines' => $closingLines,
            ]);

            // 6. Kunci Periode Akuntansi
            $user = Auth::user();
            $period->update([
                'is_closed' => true,
                'closed_at' => now(),
                'closed_by' => $user?->id,
            ]);

            // 7. Audit Log
            AuditTrailService::log(
                'close_period',
                $period,
                ['is_closed' => false],
                ['is_closed' => true, 'closing_journal_id' => $closingJournal->id, 'net_profit' => $netProfit],
                "Tutup Buku Periode {$period->name} berhasil. Jurnal Penutup: {$closingJournal->entry_number}"
            );

            return $closingJournal;
        });
    }
}
