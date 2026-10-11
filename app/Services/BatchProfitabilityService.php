<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\TrainingBatch;
use App\Models\VendorBill;

class BatchProfitabilityService
{
    /**
     * Hitung perbandingan anggaran vs realisasi dan profitabilitas batch pelatihan.
     *
     * @return array{
     *     batch: TrainingBatch,
     *     budget: array{
     *         has_budget: bool,
     *         target_revenue: float,
     *         target_cost: float,
     *         projected_margin: float,
     *         projected_margin_pct: float,
     *     },
     *     actuals: array{
     *         actual_revenue: float,
     *         actual_cost: float,
     *         actual_gross_profit: float,
     *         actual_margin_pct: float,
     *         paid_revenue: float,
     *         receivable_revenue: float,
     *         cost_breakdown: array<string, float>,
     *     },
     *     variance: array{
     *         revenue_diff: float,
     *         cost_diff: float,
     *         profit_diff: float,
     *         is_cost_overrun: bool,
     *     }
     * }
     */
    public function getBatchFinancialSummary(TrainingBatch $batch): array
    {
        $batch->load(['program', 'lsp', 'trainer', 'budget.items.account']);

        $budgetModel = $batch->budget;
        $hasBudget = ($budgetModel !== null && $budgetModel->status === 'approved');

        $targetRevenue = (float) ($budgetModel?->total_revenue_budget ?? 0);
        $targetCost = (float) ($budgetModel?->total_cost_budget ?? 0);
        $projectedMargin = (float) ($budgetModel?->projected_gross_margin ?? 0);
        $projectedMarginPct = (float) ($budgetModel?->projected_margin_percentage ?? 0);

        // 1. Realisasi Pendapatan (Invoices Terbit)
        $invoices = Invoice::where('batch_id', $batch->id)
            ->whereIn('status', ['issued', 'partial', 'paid'])
            ->get();

        $actualRevenue = (float) $invoices->sum('total_amount');
        $paidRevenue = (float) $invoices->sum('paid_amount');
        $receivableRevenue = (float) $invoices->sum('balance_due');

        // 2. Realisasi Biaya Langsung (Vendor Bills Disetujui)
        $bills = VendorBill::where('batch_id', $batch->id)
            ->whereIn('status', ['approved', 'partial', 'paid'])
            ->with('items.account')
            ->get();

        $actualCost = 0.0;
        $costBreakdown = [
            'lsp_certification' => 0.0,
            'trainer_fee' => 0.0,
            'hotel_venue' => 0.0,
            'catering' => 0.0,
            'materials_kit' => 0.0,
            'other' => 0.0,
        ];

        foreach ($bills as $bill) {
            $actualCost += (float) $bill->total_amount;

            foreach ($bill->items as $item) {
                $code = $item->account?->code;
                $lineTotal = (float) $item->total_price;

                if ($code === '504' || $bill->lsp_id) {
                    $costBreakdown['lsp_certification'] += $lineTotal;
                } elseif ($code === '505' || $bill->trainer_id) {
                    $costBreakdown['trainer_fee'] += $lineTotal;
                } elseif ($code === '515') {
                    $costBreakdown['hotel_venue'] += $lineTotal;
                } elseif ($code === '514') {
                    $costBreakdown['catering'] += $lineTotal;
                } elseif ($code === '503') {
                    $costBreakdown['materials_kit'] += $lineTotal;
                } else {
                    $costBreakdown['other'] += $lineTotal;
                }
            }
        }

        // 3. Profit Margin Realisasi
        $actualGrossProfit = $actualRevenue - $actualCost;
        $actualMarginPct = ($actualRevenue > 0) ? ($actualGrossProfit / $actualRevenue) * 100 : 0.0;

        // 4. Analisis Variansi
        $revenueDiff = $actualRevenue - $targetRevenue;
        $costDiff = $actualCost - $targetCost;
        $profitDiff = $actualGrossProfit - $projectedMargin;
        $isCostOverrun = ($targetCost > 0 && $actualCost > $targetCost);

        return [
            'batch' => $batch,
            'budget' => [
                'has_budget' => $hasBudget,
                'target_revenue' => $targetRevenue,
                'target_cost' => $targetCost,
                'projected_margin' => $projectedMargin,
                'projected_margin_pct' => $projectedMarginPct,
            ],
            'actuals' => [
                'actual_revenue' => $actualRevenue,
                'actual_cost' => $actualCost,
                'actual_gross_profit' => $actualGrossProfit,
                'actual_margin_pct' => $actualMarginPct,
                'paid_revenue' => $paidRevenue,
                'receivable_revenue' => $receivableRevenue,
                'cost_breakdown' => $costBreakdown,
            ],
            'variance' => [
                'revenue_diff' => $revenueDiff,
                'cost_diff' => $costDiff,
                'profit_diff' => $profitDiff,
                'is_cost_overrun' => $isCostOverrun,
            ],
        ];
    }
}
