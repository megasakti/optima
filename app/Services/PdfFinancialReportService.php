<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfFinancialReportService
{
    /**
     * Cetak PDF Laporan Laba Rugi.
     */
    public function generateIncomeStatementPdf(array $data, string $companyName = 'PT OPTIMA MITRA SERTIFIKASI')
    {
        $pdf = Pdf::loadView('reports.financial.pdf.income_statement', compact('data', 'companyName'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Laporan_Laba_Rugi_{$data['start_date']}_sd_{$data['end_date']}.pdf");
    }

    /**
     * Cetak PDF Laporan Posisi Keuangan (Neraca).
     */
    public function generateBalanceSheetPdf(array $data, string $companyName = 'PT OPTIMA MITRA SERTIFIKASI')
    {
        $pdf = Pdf::loadView('reports.financial.pdf.balance_sheet', compact('data', 'companyName'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Laporan_Posisi_Keuangan_Neraca_{$data['as_of_date']}.pdf");
    }

    /**
     * Cetak PDF Laporan Arus Kas.
     */
    public function generateCashFlowPdf(array $data, string $companyName = 'PT OPTIMA MITRA SERTIFIKASI')
    {
        $pdf = Pdf::loadView('reports.financial.pdf.cash_flow', compact('data', 'companyName'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Laporan_Arus_Kas_{$data['start_date']}_sd_{$data['end_date']}.pdf");
    }
}
