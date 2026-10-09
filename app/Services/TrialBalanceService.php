<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Support\Collection;

class TrialBalanceService
{
    /**
     * Hitung Neraca Saldo (Trial Balance) per tanggal tertentu.
     *
     * @return array{
     *     as_of_date: string,
     *     rows: Collection,
     *     total_debit: float,
     *     total_credit: float,
     *     is_balanced: bool
     * }
     */
    public function generate(int $companyId, string $asOfDate): array
    {
        $accounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $rows = collect();
        $totalDebit = '0.00';
        $totalCredit = '0.00';

        foreach ($accounts as $account) {
            $sumDebit = (float) JournalLine::where('account_id', $account->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })
                ->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $account->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })
                ->sum('credit');

            $debitBalance = 0.0;
            $creditBalance = 0.0;

            if ($account->normal_balance === 'debit') {
                $net = $sumDebit - $sumCredit;
                if ($net >= 0) {
                    $debitBalance = $net;
                } else {
                    $creditBalance = abs($net);
                }
            } else {
                $net = $sumCredit - $sumDebit;
                if ($net >= 0) {
                    $creditBalance = $net;
                } else {
                    $debitBalance = abs($net);
                }
            }

            // Hanya tampilkan akun yang memiliki saldo atau mutasi
            if ($sumDebit > 0 || $sumCredit > 0) {
                $rows->push([
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'category' => $account->category,
                    'debit' => $debitBalance,
                    'credit' => $creditBalance,
                ]);

                $totalDebit = bcadd($totalDebit, number_format($debitBalance, 2, '.', ''), 2);
                $totalCredit = bcadd($totalCredit, number_format($creditBalance, 2, '.', ''), 2);
            }
        }

        $isBalanced = (bccomp($totalDebit, $totalCredit, 2) === 0);

        return [
            'as_of_date' => $asOfDate,
            'rows' => $rows,
            'total_debit' => (float) $totalDebit,
            'total_credit' => (float) $totalCredit,
            'is_balanced' => $isBalanced,
        ];
    }
}
