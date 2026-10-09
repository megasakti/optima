<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CustomerPayment;
use App\Models\JournalLine;
use App\Models\VendorPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialStatementService
{
    /**
     * Laporan Laba Rugi (Income Statement / Profit & Loss) berbasis SAK EMKM.
     *
     * @return array{
     *     company_id: int,
     *     start_date: string,
     *     end_date: string,
     *     revenues: Collection,
     *     total_revenue: float,
     *     cogs: Collection,
     *     total_cogs: float,
     *     gross_profit: float,
     *     gross_margin_percentage: float,
     *     operating_expenses: Collection,
     *     total_operating_expenses: float,
     *     operating_profit: float,
     *     operating_margin_percentage: float,
     *     other_revenues: Collection,
     *     total_other_revenue: float,
     *     other_expenses: Collection,
     *     total_other_expense: float,
     *     net_other: float,
     *     net_profit: float,
     *     net_margin_percentage: float
     * }
     */
    public function getIncomeStatement(int $companyId, string $startDate, string $endDate): array
    {
        // 1. Pendapatan Usaha (Revenue - Akun 4xx)
        $revenueAccounts = Account::where('company_id', $companyId)
            ->where('type', 'revenue')
            ->where('code', 'like', '4%')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $revenues = collect();
        $totalRevenue = 0.0;

        foreach ($revenueAccounts as $acc) {
            $sumDebit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('credit');

            $amount = $sumCredit - $sumDebit; // Saldo normal Kredit
            if ($sumDebit > 0 || $sumCredit > 0) {
                $revenues->push([
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $amount,
                ]);
                $totalRevenue += $amount;
            }
        }

        // 2. Beban Pokok Penjualan / Beban Langsung Pelatihan & LSP (COGS - Akun 5xx)
        $cogsAccounts = Account::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('code', 'like', '5%')
                  ->orWhere('category', 'cost_of_sales');
            })
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $cogs = collect();
        $totalCogs = 0.0;

        foreach ($cogsAccounts as $acc) {
            $sumDebit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('credit');

            $amount = $sumDebit - $sumCredit; // Saldo normal Debit
            if ($sumDebit > 0 || $sumCredit > 0) {
                $cogs->push([
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $amount,
                ]);
                $totalCogs += $amount;
            }
        }

        // Laba Kotor
        $grossProfit = $totalRevenue - $totalCogs;
        $grossMarginPct = ($totalRevenue > 0) ? ($grossProfit / $totalRevenue) * 100 : 0.0;

        // 3. Beban Operasional & Administrasi Umum (Opex - Akun 6xx)
        $opexAccounts = Account::where('company_id', $companyId)
            ->where('code', 'like', '6%')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $operatingExpenses = collect();
        $totalOpex = 0.0;

        foreach ($opexAccounts as $acc) {
            $sumDebit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('credit');

            $amount = $sumDebit - $sumCredit; // Saldo normal Debit
            if ($sumDebit > 0 || $sumCredit > 0) {
                $operatingExpenses->push([
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $amount,
                ]);
                $totalOpex += $amount;
            }
        }

        // Laba Operasional
        $operatingProfit = $grossProfit - $totalOpex;
        $operatingMarginPct = ($totalRevenue > 0) ? ($operatingProfit / $totalRevenue) * 100 : 0.0;

        // 4. Pendapatan & Beban Lain-Lain (Akun 7xx & 8xx)
        $otherRevAccounts = Account::where('company_id', $companyId)
            ->where('code', 'like', '7%')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $otherRevenues = collect();
        $totalOtherRevenue = 0.0;
        foreach ($otherRevAccounts as $acc) {
            $sumDebit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('credit');

            $amount = $sumCredit - $sumDebit;
            if ($sumDebit > 0 || $sumCredit > 0) {
                $otherRevenues->push([
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $amount,
                ]);
                $totalOtherRevenue += $amount;
            }
        }

        $otherExpAccounts = Account::where('company_id', $companyId)
            ->where('code', 'like', '8%')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $otherExpenses = collect();
        $totalOtherExpense = 0.0;
        foreach ($otherExpAccounts as $acc) {
            $sumDebit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })->sum('credit');

            $amount = $sumDebit - $sumCredit;
            if ($sumDebit > 0 || $sumCredit > 0) {
                $otherExpenses->push([
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $amount,
                ]);
                $totalOtherExpense += $amount;
            }
        }

        $netOther = $totalOtherRevenue - $totalOtherExpense;
        $netProfit = $operatingProfit + $netOther;
        $netMarginPct = ($totalRevenue > 0) ? ($netProfit / $totalRevenue) * 100 : 0.0;

        return [
            'company_id' => $companyId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenues' => $revenues,
            'total_revenue' => $totalRevenue,
            'cogs' => $cogs,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'gross_margin_percentage' => $grossMarginPct,
            'operating_expenses' => $operatingExpenses,
            'total_operating_expenses' => $totalOpex,
            'operating_profit' => $operatingProfit,
            'operating_margin_percentage' => $operatingMarginPct,
            'other_revenues' => $otherRevenues,
            'total_other_revenue' => $totalOtherRevenue,
            'other_expenses' => $otherExpenses,
            'total_other_expense' => $totalOtherExpense,
            'net_other' => $netOther,
            'net_profit' => $netProfit,
            'net_margin_percentage' => $netMarginPct,
        ];
    }

    /**
     * Laporan Posisi Keuangan / Neraca (Balance Sheet) per tanggal tertentu.
     *
     * @return array{
     *     company_id: int,
     *     as_of_date: string,
     *     current_assets: Collection,
     *     total_current_assets: float,
     *     non_current_assets: Collection,
     *     total_non_current_assets: float,
     *     total_assets: float,
     *     current_liabilities: Collection,
     *     total_current_liabilities: float,
     *     total_liabilities: float,
     *     equities: Collection,
     *     total_equity_nominal: float,
     *     current_period_earnings: float,
     *     total_equity: float,
     *     total_liabilities_and_equity: float,
     *     is_balanced: bool,
     *     discrepancy: float
     * }
     */
    public function getBalanceSheet(int $companyId, string $asOfDate): array
    {
        // 1. Aset Lancar (Akun 10x: Kas, Bank, Piutang Usaha, Uang Muka)
        $currentAssetAccounts = Account::where('company_id', $companyId)
            ->where('code', 'like', '10%')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $currentAssets = collect();
        $totalCurrentAssets = 0.0;

        foreach ($currentAssetAccounts as $acc) {
            $sumDebit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })->sum('credit');

            $balance = $sumDebit - $sumCredit;
            if ($sumDebit > 0 || $sumCredit > 0) {
                $currentAssets->push([
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $balance,
                ]);
                $totalCurrentAssets += $balance;
            }
        }

        // 2. Aset Tidak Lancar (Akun 11x: Peralatan Kantor & Akumulasi Penyusutan)
        $nonCurrentAssetAccounts = Account::where('company_id', $companyId)
            ->where('code', 'like', '11%')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $nonCurrentAssets = collect();
        $totalNonCurrentAssets = 0.0;

        foreach ($nonCurrentAssetAccounts as $acc) {
            $sumDebit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })->sum('credit');

            // Jika kontra aset (akumulasi penyusutan, saldo kredit mengurangi aset)
            $balance = ($acc->normal_balance === 'credit') ? ($sumCredit - $sumDebit) * -1 : ($sumDebit - $sumCredit);

            if ($sumDebit > 0 || $sumCredit > 0) {
                $nonCurrentAssets->push([
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $balance,
                ]);
                $totalNonCurrentAssets += $balance;
            }
        }

        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        // 3. Liabilitas Jangka Pendek (Akun 2xx: Utang Usaha, Utang LSP, Utang Pajak, dll.)
        $liabilityAccounts = Account::where('company_id', $companyId)
            ->where('code', 'like', '2%')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $currentLiabilities = collect();
        $totalLiabilities = 0.0;

        foreach ($liabilityAccounts as $acc) {
            $sumDebit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })->sum('credit');

            $balance = $sumCredit - $sumDebit; // Saldo normal Kredit
            if ($sumDebit > 0 || $sumCredit > 0) {
                $currentLiabilities->push([
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $balance,
                ]);
                $totalLiabilities += $balance;
            }
        }

        // 4. Ekuitas (Akun 3xx: Modal Disetor, Saldo Laba Ditahan)
        $equityAccounts = Account::where('company_id', $companyId)
            ->where('code', 'like', '3%')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $equities = collect();
        $totalEquityNominal = 0.0;

        foreach ($equityAccounts as $acc) {
            $sumDebit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })->sum('debit');

            $sumCredit = (float) JournalLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)
                      ->where('status', 'posted')
                      ->where('entry_date', '<=', $asOfDate);
                })->sum('credit');

            $balance = $sumCredit - $sumDebit; // Saldo normal Kredit
            if ($sumDebit > 0 || $sumCredit > 0) {
                $equities->push([
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $balance,
                ]);
                $totalEquityNominal += $balance;
            }
        }

        // 5. Laba Periode Berjalan (Current Period Earnings dari jurnal nominal yang belum ditutup)
        $totRevenueUpToDate = (float) JournalLine::whereHas('account', fn($aq) => $aq->where('company_id', $companyId)->where('type', 'revenue'))
            ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                $q->where('company_id', $companyId)
                  ->where('status', 'posted')
                  ->where('entry_date', '<=', $asOfDate);
            })
            ->select(DB::raw('SUM(credit) - SUM(debit) as net_rev'))
            ->value('net_rev') ?? 0.0;

        $totExpenseUpToDate = (float) JournalLine::whereHas('account', fn($aq) => $aq->where('company_id', $companyId)->where('type', 'expense'))
            ->whereHas('entry', function ($q) use ($companyId, $asOfDate) {
                $q->where('company_id', $companyId)
                  ->where('status', 'posted')
                  ->where('entry_date', '<=', $asOfDate);
            })
            ->select(DB::raw('SUM(debit) - SUM(credit) as net_exp'))
            ->value('net_exp') ?? 0.0;

        $currentPeriodEarnings = $totRevenueUpToDate - $totExpenseUpToDate;
        $totalEquity = $totalEquityNominal + $currentPeriodEarnings;

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $discrepancy = abs($totalAssets - $totalLiabilitiesAndEquity);
        $isBalanced = ($discrepancy < 0.01);

        return [
            'company_id' => $companyId,
            'as_of_date' => $asOfDate,
            'current_assets' => $currentAssets,
            'total_current_assets' => $totalCurrentAssets,
            'non_current_assets' => $nonCurrentAssets,
            'total_non_current_assets' => $totalNonCurrentAssets,
            'total_assets' => $totalAssets,
            'current_liabilities' => $currentLiabilities,
            'total_current_liabilities' => $totalLiabilities,
            'total_liabilities' => $totalLiabilities,
            'equities' => $equities,
            'total_equity_nominal' => $totalEquityNominal,
            'current_period_earnings' => $currentPeriodEarnings,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
            'is_balanced' => $isBalanced,
            'discrepancy' => $discrepancy,
        ];
    }

    /**
     * Laporan Arus Kas (Cash Flow Statement) SAK EMKM.
     *
     * @return array{
     *     company_id: int,
     *     start_date: string,
     *     end_date: string,
     *     beginning_cash: float,
     *     operating_receipts: float,
     *     operating_payments: float,
     *     operating_other_payments: float,
     *     net_operating_cash: float,
     *     investing_payments: float,
     *     net_investing_cash: float,
     *     financing_receipts: float,
     *     net_financing_cash: float,
     *     net_change_in_cash: float,
     *     ending_cash: float
     * }
     */
    public function getCashFlowStatement(int $companyId, string $startDate, string $endDate): array
    {
        // Identifikasi Akun Kas & Bank
        $cashBankAccounts = Account::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('is_cash_bank', true)
                  ->orWhere('code', 'like', '101%')
                  ->orWhere('code', 'like', '102%')
                  ->orWhere('code', 'like', '103%')
                  ->orWhere('code', 'like', '104%');
            })
            ->pluck('id');

        // 1. Saldo Kas Awal (Sebelum startDate)
        $begDebit = (float) JournalLine::whereIn('account_id', $cashBankAccounts)
            ->whereHas('entry', function ($q) use ($companyId, $startDate) {
                $q->where('company_id', $companyId)
                  ->where('status', 'posted')
                  ->where('entry_date', '<', $startDate);
            })->sum('debit');

        $begCredit = (float) JournalLine::whereIn('account_id', $cashBankAccounts)
            ->whereHas('entry', function ($q) use ($companyId, $startDate) {
                $q->where('company_id', $companyId)
                  ->where('status', 'posted')
                  ->where('entry_date', '<', $startDate);
            })->sum('credit');

        $beginningCash = $begDebit - $begCredit;

        // 2. Saldo Kas Akhir (s/d endDate)
        $endDebit = (float) JournalLine::whereIn('account_id', $cashBankAccounts)
            ->whereHas('entry', function ($q) use ($companyId, $endDate) {
                $q->where('company_id', $companyId)
                  ->where('status', 'posted')
                  ->where('entry_date', '<=', $endDate);
            })->sum('debit');

        $endCredit = (float) JournalLine::whereIn('account_id', $cashBankAccounts)
            ->whereHas('entry', function ($q) use ($companyId, $endDate) {
                $q->where('company_id', $companyId)
                  ->where('status', 'posted')
                  ->where('entry_date', '<=', $endDate);
            })->sum('credit');

        $endingCash = $endDebit - $endCredit;
        $netChangeInCash = $endingCash - $beginningCash;

        // 3. Arus Kas Aktivitas Operasi:
        // A. Penerimaan dari Pelanggan (Customer Payments)
        $operatingReceipts = (float) CustomerPayment::where('company_id', $companyId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // B. Pembayaran ke Vendor & LSP (Vendor Payments)
        $operatingPayments = (float) VendorPayment::where('company_id', $companyId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // C. Pembayaran Beban Operasional Langsung melalui Kas/Bank
        // (Seluruh mutasi kredit kas selain vendor payments)
        $totalCashOutflow = (float) JournalLine::whereIn('account_id', $cashBankAccounts)
            ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                $q->where('company_id', $companyId)
                  ->where('status', 'posted')
                  ->whereBetween('entry_date', [$startDate, $endDate]);
            })->sum('credit');

        $totalCashInflow = (float) JournalLine::whereIn('account_id', $cashBankAccounts)
            ->whereHas('entry', function ($q) use ($companyId, $startDate, $endDate) {
                $q->where('company_id', $companyId)
                  ->where('status', 'posted')
                  ->whereBetween('entry_date', [$startDate, $endDate]);
            })->sum('debit');

        // Selisih pengeluaran operasional kas umum
        $operatingOtherPayments = max(0.0, $totalCashOutflow - $operatingPayments);

        // Jika data transaksi belum ada di CustomerPayment/VendorPayment tetapi ada di Jurnal:
        if ($operatingReceipts === 0.0 && $totalCashInflow > 0.0) {
            $operatingReceipts = $totalCashInflow;
        }

        $netOperatingCash = $totalCashInflow - $totalCashOutflow;

        // 4. Aktivitas Investasi & Pendanaan
        $netInvestingCash = 0.0;
        $netFinancingCash = 0.0;

        return [
            'company_id' => $companyId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'beginning_cash' => $beginningCash,
            'operating_receipts' => $operatingReceipts,
            'operating_payments' => $operatingPayments,
            'operating_other_payments' => $operatingOtherPayments,
            'net_operating_cash' => $netOperatingCash,
            'investing_payments' => 0.0,
            'net_investing_cash' => $netInvestingCash,
            'financing_receipts' => 0.0,
            'net_financing_cash' => $netFinancingCash,
            'net_change_in_cash' => $netChangeInCash,
            'ending_cash' => $endingCash,
        ];
    }
}
