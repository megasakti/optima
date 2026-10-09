<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReceivableAgingService
{
    /**
     * Hitung laporan umur piutang (Accounts Receivable Aging Report).
     *
     * @return array{
     *     as_of_date: string,
     *     customers: Collection,
     *     totals: array{current: float, days_1_30: float, days_31_60: float, days_61_90: float, over_90: float, total: float}
     * }
     */
    public function getAgingReport(int $companyId, string $asOfDate): array
    {
        $asOf = Carbon::parse($asOfDate);

        // Ambil invoice yang belum lunas pada tanggal cut-off
        $invoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->where('invoice_date', '<=', $asOfDate)
            ->where('balance_due', '>', 0)
            ->with('customer')
            ->get();

        $grouped = [];

        $totals = [
            'current' => 0.0,
            'days_1_30' => 0.0,
            'days_31_60' => 0.0,
            'days_61_90' => 0.0,
            'over_90' => 0.0,
            'total' => 0.0,
        ];

        foreach ($invoices as $inv) {
            $custId = $inv->customer_id;
            if (!isset($grouped[$custId])) {
                $grouped[$custId] = [
                    'customer' => $inv->customer,
                    'current' => 0.0,
                    'days_1_30' => 0.0,
                    'days_31_60' => 0.0,
                    'days_61_90' => 0.0,
                    'over_90' => 0.0,
                    'total' => 0.0,
                ];
            }

            $balance = (float) $inv->balance_due;
            $dueDate = Carbon::parse($inv->due_date);

            if ($asOf->lte($dueDate)) {
                // Belum jatuh tempo (Current)
                $grouped[$custId]['current'] += $balance;
                $totals['current'] += $balance;
            } else {
                // Overdue
                $daysOverdue = $dueDate->diffInDays($asOf);

                if ($daysOverdue <= 30) {
                    $grouped[$custId]['days_1_30'] += $balance;
                    $totals['days_1_30'] += $balance;
                } elseif ($daysOverdue <= 60) {
                    $grouped[$custId]['days_31_60'] += $balance;
                    $totals['days_31_60'] += $balance;
                } elseif ($daysOverdue <= 90) {
                    $grouped[$custId]['days_61_90'] += $balance;
                    $totals['days_61_90'] += $balance;
                } else {
                    $grouped[$custId]['over_90'] += $balance;
                    $totals['over_90'] += $balance;
                }
            }

            $grouped[$custId]['total'] += $balance;
            $totals['total'] += $balance;
        }

        return [
            'as_of_date' => $asOfDate,
            'customers' => collect(array_values($grouped)),
            'totals' => $totals,
        ];
    }
}
