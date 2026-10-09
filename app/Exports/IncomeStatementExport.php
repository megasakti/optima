<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class IncomeStatementExport implements FromView, ShouldAutoSize, WithTitle
{
    protected array $data;
    protected string $companyName;

    public function __construct(array $data, string $companyName = 'PT OPTIMA MITRA SERTIFIKASI')
    {
        $this->data = $data;
        $this->companyName = $companyName;
    }

    public function view(): View
    {
        return view('reports.financial.excel.income_statement', [
            'data' => $this->data,
            'companyName' => $this->companyName,
        ]);
    }

    public function title(): string
    {
        return 'Laba Rugi SAK EMKM';
    }
}
