@extends('layouts.app')

@section('title', 'Analisis Detail Profitabilitas ' . $summary['batch']->batch_code)

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('reports.batch_profitability') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold text-dark mb-0">Analisis Keuangan & Biaya: {{ $summary['batch']->batch_code }}</h4>
            </div>
            <p class="text-secondary small mb-0">
                Program: {{ $summary['batch']->program->name }} | 
                Jadwal: {{ $summary['batch']->start_date->format('d/m/Y') }} s/d {{ $summary['batch']->end_date->format('d/m/Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('batches.show', $summary['batch']->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-mortarboard me-1"></i> Data Operasional Batch
            </a>
            @if ($summary['batch']->budget)
                <a href="{{ route('budgets.show', $summary['batch']->budget->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Dokumen RAB
                </a>
            @endif
        </div>
    </div>

    <!-- Alert Status Varian -->
    @if ($summary['variance']['is_cost_overrun'])
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <h6 class="fw-bold mb-1">Peringatan: Realisasi Biaya Melampaui Baseline Anggaran (Cost Overrun)!</h6>
                <p class="small mb-0">Total biaya aktual sebesar Rp {{ number_format($summary['actuals']['actual_cost'], 0, ',', '.') }} telah melampaui RAB sebesar Rp {{ number_format(abs($summary['variance']['cost_diff']), 0, ',', '.') }}. Lakukan audit pos tagihan vendor.</p>
            </div>
        </div>
    @elseif (!$summary['budget']['has_budget'])
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
                <h6 class="fw-bold mb-1">RAB Belum Disetujui (Baseline Belum Aktif)</h6>
                <p class="small mb-0">Perhitungan varian anggaran belum dapat dianalisis optimal karena belum ada RAB yang disetujui untuk batch ini.</p>
            </div>
        </div>
    @else
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-shield-check fs-4 me-3"></i>
            <div>
                <h6 class="fw-bold mb-1">Pengendalian Anggaran Berada Dalam Batas Aman</h6>
                <p class="small mb-0">Realisasi pengeluaran operasional terkendali dengan margin laba kotor sebesar {{ number_format($summary['actuals']['actual_margin_pct'], 1) }}%.</p>
            </div>
        </div>
    @endif

    <!-- Comparative Table Card -->
    <div class="card card-optima shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="bi bi-sliders me-2"></i>Matriks Komparasi Anggaran vs Realisasi Aktual
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 28%;">Metrik Keuangan</th>
                        <th style="width: 20%;" class="text-end">Anggaran (RAB)</th>
                        <th style="width: 20%;" class="text-end">Realisasi Aktual</th>
                        <th style="width: 18%;" class="text-end">Variansi (Selisih)</th>
                        <th style="width: 14%;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Revenue -->
                    <tr>
                        <td class="fw-semibold text-dark">
                            <i class="bi bi-receipt text-primary me-2"></i>Pendapatan / Omset (Invoices)
                        </td>
                        <td class="text-end text-muted">
                            Rp {{ number_format($summary['budget']['target_revenue'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold text-primary">
                            Rp {{ number_format($summary['actuals']['actual_revenue'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-semibold {{ $summary['variance']['revenue_diff'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $summary['variance']['revenue_diff'] >= 0 ? '+' : '' }} Rp {{ number_format($summary['variance']['revenue_diff'], 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if ($summary['variance']['revenue_diff'] >= 0)
                                <span class="badge bg-success-subtle text-success">Target Tercapai</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Di Bawah Target</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Direct Cost (HPP) -->
                    <tr>
                        <td class="fw-semibold text-dark">
                            <i class="bi bi-wallet2 text-danger me-2"></i>Biaya Langsung Operasional (HPP)
                        </td>
                        <td class="text-end text-muted">
                            Rp {{ number_format($summary['budget']['target_cost'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold text-danger">
                            Rp {{ number_format($summary['actuals']['actual_cost'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-semibold {{ $summary['variance']['cost_diff'] <= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $summary['variance']['cost_diff'] > 0 ? '+' : '' }} Rp {{ number_format($summary['variance']['cost_diff'], 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if ($summary['variance']['is_cost_overrun'])
                                <span class="badge bg-danger-subtle text-danger">Over Budget</span>
                            @else
                                <span class="badge bg-success-subtle text-success">Efisien</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Gross Profit -->
                    <tr class="table-light">
                        <td class="fw-bold text-dark">
                            <i class="bi bi-graph-up text-success me-2"></i>Laba Kotor (Gross Profit)
                        </td>
                        <td class="text-end fw-bold text-muted">
                            Rp {{ number_format($summary['budget']['projected_margin'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold fs-6 {{ $summary['actuals']['actual_gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($summary['actuals']['actual_gross_profit'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold {{ $summary['variance']['profit_diff'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $summary['variance']['profit_diff'] >= 0 ? '+' : '' }} Rp {{ number_format($summary['variance']['profit_diff'], 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if ($summary['actuals']['actual_gross_profit'] >= $summary['budget']['projected_margin'])
                                <span class="badge bg-success">Melampaui Target</span>
                            @else
                                <span class="badge bg-warning text-dark">Di Bawah Proyeksi</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Profit Margin (%) -->
                    <tr>
                        <td class="fw-semibold text-dark">
                            <i class="bi bi-percent text-info me-2"></i>Gross Profit Margin (%)
                        </td>
                        <td class="text-end text-muted">
                            {{ number_format($summary['budget']['projected_margin_pct'], 1) }}%
                        </td>
                        <td class="text-end fw-bold fs-6 text-dark">
                            {{ number_format($summary['actuals']['actual_margin_pct'], 1) }}%
                        </td>
                        <td class="text-end fw-semibold">
                            {{ number_format($summary['actuals']['actual_margin_pct'] - $summary['budget']['projected_margin_pct'], 1) }}%
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $summary['actuals']['actual_margin_pct'] >= 30 ? 'bg-success' : ($summary['actuals']['actual_margin_pct'] >= 15 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                {{ $summary['actuals']['actual_margin_pct'] >= 30 ? 'Prima' : 'Cukup' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cost Breakdown & Cash Realization Row -->
    <div class="row g-4">
        <!-- Breakdown of Direct Costs -->
        <div class="col-12 col-lg-7">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-pie-chart me-2"></i>Rincian Komponen Biaya Aktual (HPP)
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <canvas id="costBreakdownChart" style="max-height: 220px;"></canvas>
                        </div>
                        <div class="col-12 col-md-6">
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="bi bi-circle-fill text-primary me-2 small"></i>Sertifikasi LSP (504):</span>
                                    <span class="fw-bold">Rp {{ number_format($summary['actuals']['cost_breakdown']['lsp_certification'], 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="bi bi-circle-fill text-info me-2 small"></i>Honor Trainer (505):</span>
                                    <span class="fw-bold">Rp {{ number_format($summary['actuals']['cost_breakdown']['trainer_fee'], 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="bi bi-circle-fill text-warning me-2 small"></i>Hotel / Venue (515):</span>
                                    <span class="fw-bold">Rp {{ number_format($summary['actuals']['cost_breakdown']['hotel_venue'], 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="bi bi-circle-fill text-secondary me-2 small"></i>Konsumsi/Katering (514):</span>
                                    <span class="fw-bold">Rp {{ number_format($summary['actuals']['cost_breakdown']['catering'], 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="bi bi-circle-fill text-success me-2 small"></i>Kit & Modul (503):</span>
                                    <span class="fw-bold">Rp {{ number_format($summary['actuals']['cost_breakdown']['materials_kit'], 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="bi bi-circle-fill text-muted me-2 small"></i>Operasional Lainnya:</span>
                                    <span class="fw-bold">Rp {{ number_format($summary['actuals']['cost_breakdown']['other'], 0, ',', '.') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Inflow vs Piutang -->
        <div class="col-12 col-lg-5">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-cash-coin me-2"></i>Status Arus Kas Masuk (Penagihan)
                    </h6>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="p-3 bg-light rounded border mb-3">
                            <span class="text-muted small">Total Tagihan Diterbitkan:</span>
                            <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($summary['actuals']['actual_revenue'], 0, ',', '.') }}</h5>
                        </div>

                        <div class="p-3 bg-success-subtle rounded border border-success mb-3">
                            <span class="text-success small fw-semibold">Kas Diterima (Sudah Tertagih):</span>
                            <h5 class="fw-bold text-success mb-0">Rp {{ number_format($summary['actuals']['paid_revenue'], 0, ',', '.') }}</h5>
                        </div>

                        <div class="p-3 bg-danger-subtle rounded border border-danger mb-3">
                            <span class="text-danger small fw-semibold">Sisa Piutang Tertahan (Outstanding AR):</span>
                            <h5 class="fw-bold text-danger mb-0">Rp {{ number_format($summary['actuals']['receivable_revenue'], 0, ',', '.') }}</h5>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Pantau jadwal jatuh tempo pada modul Piutang Usaha untuk memastikan arus kas batch tetap positif.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('costBreakdownChart').getContext('2d');
    
    const costData = [
        {{ $summary['actuals']['cost_breakdown']['lsp_certification'] }},
        {{ $summary['actuals']['cost_breakdown']['trainer_fee'] }},
        {{ $summary['actuals']['cost_breakdown']['hotel_venue'] }},
        {{ $summary['actuals']['cost_breakdown']['catering'] }},
        {{ $summary['actuals']['cost_breakdown']['materials_kit'] }},
        {{ $summary['actuals']['cost_breakdown']['other'] }}
    ];

    const hasData = costData.some(v => v > 0);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['LSP Sertifikasi', 'Honor Trainer', 'Hotel/Venue', 'Konsumsi', 'Kit & Modul', 'Lainnya'],
            datasets: [{
                data: hasData ? costData : [1],
                backgroundColor: hasData ? [
                    '#3b82f6',
                    '#06b6d4',
                    '#f59e0b',
                    '#64748b',
                    '#10b981',
                    '#94a3b8'
                ] : ['#e2e8f0'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (!hasData) return 'Belum ada realisasi biaya';
                            return context.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
