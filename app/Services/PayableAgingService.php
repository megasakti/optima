<?php

namespace App\Services;

use App\Models\VendorBill;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayableAgingService
{
    /**
     * Hitung laporan analisis umur hutang (Accounts Payable Aging Report).
     *
     * @return array{
     *     as_of_date: string,
     *     payees: Collection,
     *     totals: array{current: float, days_1_30: float, days_31_60: float, days_61_90: float, over_90: float, total: float}
     * }
     */
    public function getAgingReport(int $companyId, string $asOfDate): array
    {
        $asOf = Carbon::parse($asOfDate);

        $bills = VendorBill::where('company_id', $companyId)
            ->whereIn('status', ['approved', 'partial'])
            ->where('bill_date', '<=', $asOfDate)
            ->where('balance_due', '>', 0)
            ->with(['lsp', 'vendor', 'trainer'])
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

        foreach ($bills as $bill) {
            // Tentukan identitas payee
            if ($bill->lsp_id) {
                $payeeKey = 'lsp_'.$bill->lsp_id;
                $payeeName = $bill->lsp->name;
                $payeeType = 'Lembaga Sertifikasi (LSP)';
            } elseif ($bill->vendor_id) {
                $payeeKey = 'vendor_'.$bill->vendor_id;
                $payeeName = $bill->vendor->name;
                $payeeType = 'Vendor '.ucfirst($bill->vendor->category ?? 'Umum');
            } elseif ($bill->trainer_id) {
                $payeeKey = 'trainer_'.$bill->trainer_id;
                $payeeName = $bill->trainer->name;
                $payeeType = 'Instruktur / Asesor';
            } else {
                $payeeKey = 'other_'.$bill->id;
                $payeeName = 'Vendor Lainnya';
                $payeeType = 'Lainnya';
            }

            if (! isset($grouped[$payeeKey])) {
                $grouped[$payeeKey] = [
                    'name' => $payeeName,
                    'type' => $payeeType,
                    'current' => 0.0,
                    'days_1_30' => 0.0,
                    'days_31_60' => 0.0,
                    'days_61_90' => 0.0,
                    'over_90' => 0.0,
                    'total' => 0.0,
                ];
            }

            $balance = (float) $bill->balance_due;
            $dueDate = Carbon::parse($bill->due_date);

            if ($asOf->lte($dueDate)) {
                // Belum jatuh tempo
                $grouped[$payeeKey]['current'] += $balance;
                $totals['current'] += $balance;
            } else {
                // Lewat jatuh tempo
                $daysOverdue = $dueDate->diffInDays($asOf);

                if ($daysOverdue <= 30) {
                    $grouped[$payeeKey]['days_1_30'] += $balance;
                    $totals['days_1_30'] += $balance;
                } elseif ($daysOverdue <= 60) {
                    $grouped[$payeeKey]['days_31_60'] += $balance;
                    $totals['days_31_60'] += $balance;
                } elseif ($daysOverdue <= 90) {
                    $grouped[$payeeKey]['days_61_90'] += $balance;
                    $totals['days_61_90'] += $balance;
                } else {
                    $grouped[$payeeKey]['over_90'] += $balance;
                    $totals['over_90'] += $balance;
                }
            }

            $grouped[$payeeKey]['total'] += $balance;
            $totals['total'] += $balance;
        }

        return [
            'as_of_date' => $asOfDate,
            'payees' => collect(array_values($grouped)),
            'totals' => $totals,
        ];
    }
}
