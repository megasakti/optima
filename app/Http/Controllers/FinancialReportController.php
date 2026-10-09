<?php

namespace App\Http\Controllers;

use App\Exports\BalanceSheetExport;
use App\Exports\CashFlowExport;
use App\Exports\IncomeStatementExport;
use App\Services\FinancialStatementService;
use App\Services\PdfFinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class FinancialReportController extends Controller
{
    /**
     * Laporan Laba Rugi (Income Statement) SAK EMKM.
     */
    public function incomeStatement(
        Request $request,
        FinancialStatementService $service,
        PdfFinancialReportService $pdfService
    ) {
        $company = Auth::user()->company;
        $companyId = $company->id;
        $companyName = $company->name ?? 'PT OPTIMA MITRA SERTIFIKASI';

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $data = $service->getIncomeStatement($companyId, $startDate, $endDate);

        if ($request->input('export') === 'excel') {
            return Excel::download(
                new IncomeStatementExport($data, $companyName),
                "Laba_Rugi_{$startDate}_sd_{$endDate}.xlsx"
            );
        }

        if ($request->input('export') === 'pdf') {
            return $pdfService->generateIncomeStatementPdf($data, $companyName);
        }

        return view('reports.financial.income_statement', compact('data', 'companyName'));
    }

    /**
     * Laporan Posisi Keuangan / Neraca (Balance Sheet).
     */
    public function balanceSheet(
        Request $request,
        FinancialStatementService $service,
        PdfFinancialReportService $pdfService
    ) {
        $company = Auth::user()->company;
        $companyId = $company->id;
        $companyName = $company->name ?? 'PT OPTIMA MITRA SERTIFIKASI';

        $asOfDate = $request->input('as_of_date', Carbon::now()->toDateString());

        $data = $service->getBalanceSheet($companyId, $asOfDate);

        if ($request->input('export') === 'excel') {
            return Excel::download(
                new BalanceSheetExport($data, $companyName),
                "Neraca_Posisi_Keuangan_{$asOfDate}.xlsx"
            );
        }

        if ($request->input('export') === 'pdf') {
            return $pdfService->generateBalanceSheetPdf($data, $companyName);
        }

        return view('reports.financial.balance_sheet', compact('data', 'companyName'));
    }

    /**
     * Laporan Arus Kas (Cash Flow Statement).
     */
    public function cashFlow(
        Request $request,
        FinancialStatementService $service,
        PdfFinancialReportService $pdfService
    ) {
        $company = Auth::user()->company;
        $companyId = $company->id;
        $companyName = $company->name ?? 'PT OPTIMA MITRA SERTIFIKASI';

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $data = $service->getCashFlowStatement($companyId, $startDate, $endDate);

        if ($request->input('export') === 'excel') {
            return Excel::download(
                new CashFlowExport($data, $companyName),
                "Arus_Kas_{$startDate}_sd_{$endDate}.xlsx"
            );
        }

        if ($request->input('export') === 'pdf') {
            return $pdfService->generateCashFlowPdf($data, $companyName);
        }

        return view('reports.financial.cash_flow', compact('data', 'companyName'));
    }
}
