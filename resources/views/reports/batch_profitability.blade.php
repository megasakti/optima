@extends('layouts.app')

@section('title', 'Laporan Analisis Profitabilitas Proyek & Batch Pelatihan')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-graph-up-arrow text-primary me-2"></i>Analisis Profitabilitas & Margin Proyek Pelatihan
            </h4>
            <p class="text-secondary small mb-0">Evaluasi komparasi Anggaran (RAB) vs Realisasi Finansial aktual (Faktur Invoices vs Tagihan Vendor/LSP) per angkatan.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('batches.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-mortarboard me-1"></i> Daftar Batch
            </a>
            <a href="{{ route('budgets.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-pie-chart me-1"></i> Rencana Anggaran (RAB)
            </a>
        </div>
    </div>

    <!-- Aggregate KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Actual Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Realisasi Omset (Faktur)</span>
                    <h4 class="fw-bold text-primary mt-2 mb-1">Rp {{ number_format($totalActualRevenue, 0, ',', '.') }}</h4>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Target RAB:</span>
                        <span>Rp {{ number_format($totalTargetRevenue, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actual Cost -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Realisasi HPP (Vendor & LSP)</span>
                    <h4 class="fw-bold text-danger mt-2 mb-1">Rp {{ number_format($totalActualCost, 0, ',', '.') }}</h4>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Efisiensi HPP:</span>
                        <span>{{ $totalActualRevenue > 0 ? number_format(($totalActualCost / $totalActualRevenue) * 100, 1) : 0 }}% dari omset</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Realized Gross Profit -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Total Laba Kotor (Gross Profit)</span>
                    <h4 class="fw-bold text-success mt-2 mb-1">Rp {{ number_format($totalGrossProfit, 0, ',', '.') }}</h4>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Portofolio Batch:</span>
                        <span>{{ count($summaries) }} Angkatan Terdaftar</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Margin -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Rata-rata Profit Margin</span>
                    <h4 class="fw-bold mt-2 mb-1 {{ $overallMarginPct >= 30 ? 'text-success' : ($overallMarginPct >= 15 ? 'text-warning' : 'text-danger') }}">
                        {{ number_format($overallMarginPct, 1) }}%
                    </h4>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Target Standar:</span>
                        <span class="badge bg-success-subtle text-success">&ge; 30.0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Card -->
    <div class="card card-optima shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="bi bi-bar-chart-line me-2"></i>Komparasi Pendapatan vs Beban per Batch Pelatihan
            </h6>
        </div>
        <div class="card-body p-3">
            <canvas id="profitComparisonChart" style="max-height: 280px;"></canvas>
        </div>
    </div>

    <!-- Batch Profitability Table -->
    <div class="card card-optima shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="bi bi-table me-2"></i>Tabel Rekapitulasi Realisasi & Margin Laba Seluruh Batch
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode Batch & Program</th>
                        <th class="text-end">Target Omset (RAB)</th>
                        <th class="text-end">Realisasi Omset</th>
                        <th class="text-end">Anggaran Biaya (RAB)</th>
                        <th class="text-end">Realisasi Beban (HPP)</th>
                        <th class="text-end">Laba Kotor Realisasi</th>
                        <th class="text-center">Margin (%)</th>
                        <th class="text-center">Status Varian</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summaries as $item)
                        @php
                            $b = $item['batch'];
                            $bud = $item['budget'];
                            $act = $item['actuals'];
                            $var = $item['variance'];
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('batches.show', $b->id) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $b->batch_code }}
                                </a>
                                <div class="text-dark fw-semibold small">{{ $b->program->name }}</div>
                                <small class="text-muted">{{ $b->start_date->format('d/m/Y') }} | {{ $b->actual_participants }}/{{ $b->target_participants }} Pax</small>
                            </td>
                            <td class="text-end text-muted">
                                Rp {{ number_format($bud['target_revenue'], 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold text-primary">
                                Rp {{ number_format($act['actual_revenue'], 0, ',', '.') }}
                            </td>
                            <td class="text-end text-muted">
                                Rp {{ number_format($bud['target_cost'], 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold {{ $var['is_cost_overrun'] ? 'text-danger' : 'text-dark' }}">
                                Rp {{ number_format($act['actual_cost'], 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold {{ $act['actual_gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($act['actual_gross_profit'], 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $act['actual_margin_pct'] >= 30 ? 'bg-success-subtle text-success border border-success' : ($act['actual_margin_pct'] >= 15 ? 'bg-warning-subtle text-warning border border-warning' : 'bg-danger-subtle text-danger border border-danger') }} fw-bold px-2 py-1">
                                    {{ number_format($act['actual_margin_pct'], 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($var['is_cost_overrun'])
                                    <span class="badge bg-danger text-white">Cost Overrun</span>
                                @elseif (!$bud['has_budget'])
                                    <span class="badge bg-secondary text-white">No RAB</span>
                                @elseif ($act['actual_margin_pct'] >= 30)
                                    <span class="badge bg-success text-white">High Margin</span>
                                @else
                                    <span class="badge bg-info text-white">On Track</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('reports.batch_profitability.detail', $b->id) }}" class="btn btn-sm btn-outline-primary" title="Analisis Rinci">
                                    <i class="bi bi-search me-1"></i> Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                Belum ada data angkatan batch pelatihan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js CDN initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('profitComparisonChart').getContext('2d');
    
    const labels = [
        @foreach (array_slice($summaries, 0, 10) as $s)
            "{{ $s['batch']->batch_code }}",
        @endforeach
    ];

    const revenueData = [
        @foreach (array_slice($summaries, 0, 10) as $s)
            {{ $s['actuals']['actual_revenue'] }},
        @endforeach
    ];

    const costData = [
        @foreach (array_slice($summaries, 0, 10) as $s)
            {{ $s['actuals']['actual_cost'] }},
        @endforeach
    ];

    const profitData = [
        @foreach (array_slice($summaries, 0, 10) as $s)
            {{ $s['actuals']['actual_gross_profit'] }},
        @endforeach
    ];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Realisasi Pendapatan (Omset)',
                    data: revenueData,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                },
                {
                    label: 'Realisasi Biaya (HPP)',
                    data: costData,
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                },
                {
                    label: 'Laba Kotor (Gross Profit)',
                    data: profitData,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000) + ' Jt';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
