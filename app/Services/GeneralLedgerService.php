<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Support\Collection;

class GeneralLedgerService
{
    /**
     * Dapatkan rincian buku besar untuk akun tertentu pada rentang tanggal.
     *
     * @return array{
     *     account: Account,
     *     start_date: string,
     *     end_date: string,
     *     initial_balance: float,
     *     total_debit: float,
     *     total_credit: float,
     *     ending_balance: float,
     *     entries: Collection
     * }
     */
    public function getAccountLedger(
        Account $account,
        string $startDate,
        string $endDate,
        ?int $batchId = null
    ): array {
        // 1. Hitung Saldo Awal sebelum startDate
        $initialQuery = JournalLine::where('account_id', $account->id)
            ->whereHas('entry', function ($q) use ($account, $startDate, $batchId) {
                $q->where('company_id', $account->company_id)
                    ->where('status', 'posted')
                    ->where('entry_date', '<', $startDate);

                if ($batchId) {
                    $q->where('batch_id', $batchId);
                }
            });

        $initialDebits = (float) $initialQuery->sum('debit');
        $initialCredits = (float) $initialQuery->sum('credit');

        if ($account->normal_balance === 'debit') {
            $initialBalance = $initialDebits - $initialCredits;
        } else {
            $initialBalance = $initialCredits - $initialDebits;
        }

        // 2. Ambil Mutasi Transaksi dalam Periode [startDate, endDate]
        $linesQuery = JournalLine::where('account_id', $account->id)
            ->whereHas('entry', function ($q) use ($account, $startDate, $endDate, $batchId) {
                $q->where('company_id', $account->company_id)
                    ->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);

                if ($batchId) {
                    $q->where('batch_id', $batchId);
                }
            })
            ->with(['entry.batch'])
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.entry_date', 'asc')
            ->orderBy('journal_entries.id', 'asc')
            ->select('journal_lines.*');

        $lines = $linesQuery->get();

        // 3. Hitung Running Balance untuk Setiap Baris Transaksi
        $runningBalance = $initialBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $processedLines = $lines->map(function ($line) use (&$runningBalance, &$totalDebit, &$totalCredit, $account) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;

            $totalDebit += $debit;
            $totalCredit += $credit;

            if ($account->normal_balance === 'debit') {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
            }

            $line->running_balance = $runningBalance;

            return $line;
        });

        return [
            'account' => $account,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'initial_balance' => $initialBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'ending_balance' => $runningBalance,
            'entries' => $processedLines,
        ];
    }
}
