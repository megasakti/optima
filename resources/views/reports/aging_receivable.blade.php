@extends('layouts.app')

@section('title', 'Laporan Analisis Umur Piutang (AR Aging)')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-clock-history text-primary me-2"></i>Laporan Analisis Umur Piutang (AR Aging)
            </h4>
            <p class="text-secondary small mb-0">Pemantauan kolektibilitas piutang klien pelatihan & sertifikasi BNSP berdasarkan periode jatuh tempo.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-receipt me-1"></i> Daftar Invoice
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Filter Cut-off Date -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('reports.aging_receivable') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white fw-semibold">Posisi Per Tanggal (Cut-off):</span>
                        <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate }}">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-repeat me-1"></i> Hitung Ulang Analisis
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Visual Dashboard Cards & Chart -->
    <div class="row g-4 mb-4">
        <!-- Aging Bucket Stat Cards -->
        <div class="col-12 col-lg-8">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="card card-optima p-3 border-start border-4 border-success h-100">
                        <span class="text-muted small fw-semibold text-uppercase">Belum Jatuh Tempo</span>
                        <h5 class="fw-bold text-success mt-1 mb-0">Rp {{ number_format($report['totals']['current'], 0, ',', '.') }}</h5>
                        <small class="text-muted">Kolektibilitas Lancar</small>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card card-optima p-3 border-start border-4 border-info h-100">
                        <span class="text-muted small fw-semibold text-uppercase">1 - 30 Hari</span>
                        <h5 class="fw-bold text-info mt-1 mb-0">Rp {{ number_format($report['totals']['days_1_30'], 0, ',', '.') }}</h5>
                        <small class="text-muted">Lewat Jatuh Tempo</small>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card card-optima p-3 border-start border-4 border-warning h-100">
                        <span class="text-muted small fw-semibold text-uppercase">31 - 60 Hari</span>
                        <h5 class="fw-bold text-warning mt-1 mb-0">Rp {{ number_format($report['totals']['days_31_60'], 0, ',', '.') }}</h5>
                        <small class="text-muted">Perlu Follow Up</small>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card card-optima p-3 border-start border-4 border-danger h-100">
                        <span class="text-muted small fw-semibold text-uppercase">61 - 90 Hari</span>
                        <h5 class="fw-bold text-danger mt-1 mb-0">Rp {{ number_format($report['totals']['days_61_90'], 0, ',', '.') }}</h5>
                        <small class="text-muted">Peringatan Keras</small>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card card-optima p-3 border-start border-4 border-dark h-100">
                        <span class="text-muted small fw-semibold text-uppercase">&gt; 90 Hari (Macet)</span>
                        <h5 class="fw-bold text-dark mt-1 mb-0">Rp {{ number_format($report['totals']['over_90'], 0, ',', '.') }}</h5>
                        <small class="text-muted">Risiko Piutang Ragu</small>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card card-optima p-3 border-start border-4 border-primary h-100 bg-primary-subtle">
                        <span class="text-primary small fw-bold text-uppercase">Total Piutang Usaha</span>
                        <h4 class="fw-bold text-primary mt-1 mb-0">Rp {{ number_format($report['totals']['total'], 0, ',', '.') }}</h4>
                        <small class="text-muted">{{ $report['customers']->count() }} Klien Menunggak</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doughnut Chart Distribution -->
        <div class="col-12 col-lg-4">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-2">
                    <h6 class="fw-bold text-dark mb-0 small"><i class="bi bi-pie-chart me-1 text-primary"></i>Distribusi Umur Piutang</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <div style="width: 100%; max-height: 200px;">
                        <canvas id="agingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aging Report Matrix Table -->
    <div class="card card-optima shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table me-2 text-primary"></i>Matriks Kolektibilitas Piutang per Klien</h6>
            <span class="badge bg-light text-dark border">Posisi: {{ date('d F Y', strtotime($asOfDate)) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-optima table-bordered table-hover mb-0">
                <thead class="text-center align-middle">
                    <tr>
                        <th rowspan="2" class="text-start ps-3" style="width: 25%;">Nama Klien / Perusahaan</th>
                        <th rowspan="2" class="text-end" style="width: 13%;">Belum JT (Current)</th>
                        <th colspan="3" class="bg-light">Lewat Jatuh Tempo (Overdue)</th>
                        <th rowspan="2" class="text-end" style="width: 12%;">&gt; 90 Hari</th>
                        <th rowspan="2" class="text-end pe-3" style="width: 15%;">Total Piutang (Rp)</th>
                    </tr>
                    <tr>
                        <th class="text-end" style="width: 12%;">1 - 30 Hari</th>
                        <th class="text-end" style="width: 12%;">31 - 60 Hari</th>
                        <th class="text-end" style="width: 12%;">61 - 90 Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['customers'] as $row)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $row['customer']->name }}</div>
                                <small class="text-muted">{{ $row['customer']->phone ?? '-' }}</small>
                            </td>
                            <td class="text-end {{ $row['current'] > 0 ? 'fw-semibold text-success' : 'text-muted' }}">
                                {{ $row['current'] > 0 ? 'Rp ' . number_format($row['current'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end {{ $row['days_1_30'] > 0 ? 'fw-semibold text-info' : 'text-muted' }}">
                                {{ $row['days_1_30'] > 0 ? 'Rp ' . number_format($row['days_1_30'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end {{ $row['days_31_60'] > 0 ? 'fw-semibold text-warning' : 'text-muted' }}">
                                {{ $row['days_31_60'] > 0 ? 'Rp ' . number_format($row['days_31_60'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end {{ $row['days_61_90'] > 0 ? 'fw-semibold text-danger' : 'text-muted' }}">
                                {{ $row['days_61_90'] > 0 ? 'Rp ' . number_format($row['days_61_90'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end {{ $row['over_90'] > 0 ? 'fw-bold text-dark bg-danger-subtle' : 'text-muted' }}">
                                {{ $row['over_90'] > 0 ? 'Rp ' . number_format($row['over_90'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end pe-3 fw-bold text-primary">
                                Rp {{ number_format($row['total'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-shield-check fs-2 text-success d-block mb-2"></i>
                                Tidak ada piutang tertunggak pada posisi per tanggal {{ date('d F Y', strtotime($asOfDate)) }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($report['customers']->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td class="ps-3 text-uppercase">TOTAL KESELURUHAN</td>
                        <td class="text-end text-success">
                            Rp {{ number_format($report['totals']['current'], 0, ',', '.') }}
                        </td>
                        <td class="text-end text-info">
                            Rp {{ number_format($report['totals']['days_1_30'], 0, ',', '.') }}
                        </td>
                        <td class="text-end text-warning">
                            Rp {{ number_format($report['totals']['days_31_60'], 0, ',', '.') }}
                        </td>
                        <td class="text-end text-danger">
                            Rp {{ number_format($report['totals']['days_61_90'], 0, ',', '.') }}
                        </td>
                        <td class="text-end text-dark">
                            Rp {{ number_format($report['totals']['over_90'], 0, ',', '.') }}
                        </td>
                        <td class="text-end pe-3 text-primary fs-6">
                            Rp {{ number_format($report['totals']['total'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('agingChart');
    if (!ctx) return;

    const current = {{ $report['totals']['current'] }};
    const days130 = {{ $report['totals']['days_1_30'] }};
    const days3160 = {{ $report['totals']['days_31_60'] }};
    const days6190 = {{ $report['totals']['days_61_90'] }};
    const over90 = {{ $report['totals']['over_90'] }};

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Lancar (Current)', '1-30 Hari', '31-60 Hari', '61-90 Hari', '>90 Hari'],
            datasets: [{
                data: [current, days130, days3160, days6190, over90],
                backgroundColor: ['#22c55e', '#06b6d4', '#f59e0b', '#ef4444', '#1e293b'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        font: { size: 10 }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
