<?php

namespace App\Services;

use App\Models\Budget;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfBudgetService
{
    /**
     * Render PDF Rencana Anggaran Biaya (RAB) Pelatihan.
     */
    public function generateBudgetPdf(Budget $budget)
    {
        $budget->load([
            'batch.program',
            'batch.lsp',
            'batch.trainer',
            'company',
            'items.account',
            'createdBy',
            'approvedBy',
            'approvals.user',
        ]);

        $pdf = Pdf::loadView('budgets.pdf', compact('budget'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("RAB_{$budget->budget_number}_{$budget->batch->batch_code}.pdf");
    }
}
