<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class CashFlowExport implements FromView, ShouldAutoSize, WithTitle
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
        return view('reports.financial.excel.cash_flow', [
            'data' => $this->data,
            'companyName' => $this->companyName,
        ]);
    }

    public function title(): string
    {
        return 'Laporan Arus Kas';
    }
}
