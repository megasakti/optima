<?php

namespace App\Http\Controllers;

use App\Services\ReceivableAgingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReceivableAgingController extends Controller
{
    /**
     * Tampilkan Laporan Analisis Umur Piutang (AR Aging Report)
     */
    public function index(Request $request, ReceivableAgingService $service): View
    {
        $companyId = Auth::user()->company_id;
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));

        $report = $service->getAgingReport($companyId, $asOfDate);

        return view('reports.aging_receivable', compact('report', 'asOfDate'));
    }
}
