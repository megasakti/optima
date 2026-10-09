<?php

namespace App\Http\Controllers;

use App\Models\TrainingBatch;
use App\Services\BatchProfitabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BatchProfitabilityController extends Controller
{
    /**
     * Dasbor Analisis Profitabilitas Seluruh Batch Pelatihan.
     */
    public function index(BatchProfitabilityService $service): View
    {
        $companyId = Auth::user()->company_id;

        $batches = TrainingBatch::where('company_id', $companyId)
            ->with(['program', 'budget', 'invoices', 'vendorBills'])
            ->orderBy('start_date', 'desc')
            ->get();

        $summaries = [];
        $totalTargetRevenue = 0;
        $totalActualRevenue = 0;
        $totalActualCost = 0;
        $totalGrossProfit = 0;

        foreach ($batches as $batch) {
            $summary = $service->getBatchFinancialSummary($batch);
            $summaries[] = $summary;

            $totalTargetRevenue += $summary['budget']['target_revenue'];
            $totalActualRevenue += $summary['actuals']['actual_revenue'];
            $totalActualCost += $summary['actuals']['actual_cost'];
            $totalGrossProfit += $summary['actuals']['actual_gross_profit'];
        }

        $overallMarginPct = ($totalActualRevenue > 0) ? ($totalGrossProfit / $totalActualRevenue) * 100 : 0.0;

        return view('reports.batch_profitability', compact(
            'summaries',
            'totalTargetRevenue',
            'totalActualRevenue',
            'totalActualCost',
            'totalGrossProfit',
            'overallMarginPct'
        ));
    }

    /**
     * Detail Analisis Keuangan & Biaya Per Batch.
     */
    public function show(int $id, BatchProfitabilityService $service): View
    {
        $companyId = Auth::user()->company_id;
        $batch = TrainingBatch::where('company_id', $companyId)->findOrFail($id);

        $summary = $service->getBatchFinancialSummary($batch);

        return view('reports.batch_profitability_detail', compact('summary'));
    }
}
