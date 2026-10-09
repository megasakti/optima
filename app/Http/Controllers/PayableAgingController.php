<?php

namespace App\Http\Controllers;

use App\Services\PayableAgingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PayableAgingController extends Controller
{
    /**
     * Laporan Analisis Umur Hutang LSP & Vendor (AP Aging Report).
     */
    public function index(Request $request, PayableAgingService $service): View
    {
        $companyId = Auth::user()->company_id;
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));

        $report = $service->getAgingReport($companyId, $asOfDate);

        return view('reports.aging_payable', compact('report', 'asOfDate'));
    }
}
