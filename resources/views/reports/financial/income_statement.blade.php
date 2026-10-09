@extends('layouts.app')

@section('title', 'Laporan Laba Rugi SAK EMKM')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Laporan Laba Rugi (Income Statement)
            </h4>
            <p class="text-secondary small mb-0">Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK EMKM) - PT OPTIMA MITRA SERTIFIKASI</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" target="_blank" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('reports.income_statement') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">Periode Mulai:</span>
                        <input type="date" name="start_date" class="form-control" value="{{ $data['start_date'] }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">Sampai Dengan:</span>
                        <input type="date" name="end_date" class="form-control" value="{{ $data['end_date'] }}">
                    </div>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Tampilkan Laporan
                    </button>
                    <a href="{{ route('reports.income_statement') }}" class="btn btn-sm btn-outline-secondary" title="Bulan Berjalan">
                        <i class="bi bi-calendar-event me-1"></i> Bulan Ini
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Pendapatan Usaha</span>
                    <h4 class="fw-bold text-primary mt-2 mb-1">Rp {{ number_format($data['total_revenue'], 0, ',', '.') }}</h4>
                    <small class="text-muted">{{ $data['revenues']->count() }} Akun Pendapatan</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Beban Pokok Pelatihan (HPP)</span>
                    <h4 class="fw-bold text-danger mt-2 mb-1">Rp {{ number_format($data['total_cogs'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Biaya Sertifikasi, Trainer, Hotel & Kit</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Laba Kotor (Gross Profit)</span>
                    <h4 class="fw-bold text-success mt-2 mb-1">Rp {{ number_format($data['gross_profit'], 0, ',', '.') }}</h4>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Gross Margin:</span>
                        <span class="badge bg-success-subtle text-success">{{ number_format($data['gross_margin_percentage'], 1) }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Laba Bersih Periode</span>
                    <h4 class="fw-bold mt-2 mb-1 {{ $data['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($data['net_profit'], 0, ',', '.') }}
                    </h4>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Net Margin:</span>
                        <span class="badge {{ $data['net_margin_percentage'] >= 15 ? 'bg-success' : ($data['net_margin_percentage'] >= 0 ? 'bg-warning text-dark' : 'bg-danger') }}">
                            {{ number_format($data['net_margin_percentage'], 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statement Table -->
    <div class="card card-optima shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 text-center">
            <h5 class="fw-bold mb-0 text-dark">{{ $companyName }}</h5>
            <h6 class="fw-semibold text-primary mb-1">LAPORAN LABA RUGI</h6>
            <small class="text-muted">Periode {{ date('d F Y', strtotime($data['start_date'])) }} s/d {{ date('d F Y', strtotime($data['end_date'])) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <tbody>
                    <!-- 1. PENDAPATAN USAHA -->
                    <tr class="table-light">
                        <th colspan="2" class="ps-3 text-primary text-uppercase">
                            <i class="bi bi-caret-down-fill me-1"></i> Pendapatan Usaha
                        </th>
                        <th></th>
                    </tr>
                    @forelse ($data['revenues'] as $rev)
                        <tr>
                            <td class="ps-4" style="width: 60%;">
                                <span class="badge bg-light text-dark border me-2">{{ $rev['code'] }}</span>
                                {{ $rev['name'] }}
                            </td>
                            <td class="text-end fw-semibold text-dark" style="width: 25%;">
                                Rp {{ number_format($rev['amount'], 0, ',', '.') }}
                            </td>
                            <td style="width: 15%;"></td>
                        </tr>
                    @empty
                        <tr>
                            <td class="ps-4 text-muted fst-italic" colspan="3">Tidak ada transaksi pendapatan pada periode ini.</td>
                        </tr>
                    @endforelse
                    <tr class="fw-bold border-top">
                        <td class="ps-3 text-uppercase">Total Pendapatan Usaha</td>
                        <td></td>
                        <td class="text-end text-primary pe-3 fs-6">
                            Rp {{ number_format($data['total_revenue'], 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- 2. BEBAN POKOK PENJUALAN -->
                    <tr class="table-light">
                        <th colspan="2" class="ps-3 text-danger text-uppercase">
                            <i class="bi bi-caret-down-fill me-1"></i> Beban Pokok Pelatihan & Sertifikasi (HPP)
                        </th>
                        <th></th>
                    </tr>
                    @forelse ($data['cogs'] as $c)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border me-2">{{ $c['code'] }}</span>
                                {{ $c['name'] }}
                            </td>
                            <td class="text-end fw-semibold text-danger">
                                Rp {{ number_format($c['amount'], 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    @empty
                        <tr>
                            <td class="ps-4 text-muted fst-italic" colspan="3">Tidak ada beban langsung pada periode ini.</td>
                        </tr>
                    @endforelse
                    <tr class="fw-bold border-top">
                        <td class="ps-3 text-uppercase">Total Beban Pokok Pelatihan</td>
                        <td></td>
                        <td class="text-end text-danger pe-3 fs-6">
                            (Rp {{ number_format($data['total_cogs'], 0, ',', '.') }})
                        </td>
                    </tr>

                    <!-- LABA KOTOR -->
                    <tr class="table-primary fw-bold">
                        <td class="ps-3 fs-6 text-uppercase">LABA KOTOR (GROSS PROFIT)</td>
                        <td></td>
                        <td class="text-end text-primary pe-3 fs-6">
                            Rp {{ number_format($data['gross_profit'], 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- 3. BEBAN OPERASIONAL -->
                    <tr class="table-light">
                        <th colspan="2" class="ps-3 text-secondary text-uppercase">
                            <i class="bi bi-caret-down-fill me-1"></i> Beban Operasional & Administrasi Umum
                        </th>
                        <th></th>
                    </tr>
                    @forelse ($data['operating_expenses'] as $opex)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border me-2">{{ $opex['code'] }}</span>
                                {{ $opex['name'] }}
                            </td>
                            <td class="text-end fw-semibold text-secondary">
                                Rp {{ number_format($opex['amount'], 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    @empty
                        <tr>
                            <td class="ps-4 text-muted fst-italic" colspan="3">Tidak ada beban operasional tercatat pada periode ini.</td>
                        </tr>
                    @endforelse
                    <tr class="fw-bold border-top">
                        <td class="ps-3 text-uppercase">Total Beban Operasional</td>
                        <td></td>
                        <td class="text-end text-secondary pe-3 fs-6">
                            (Rp {{ number_format($data['total_operating_expenses'], 0, ',', '.') }})
                        </td>
                    </tr>

                    <!-- LABA OPERASIONAL -->
                    <tr class="table-light fw-bold">
                        <td class="ps-3 fs-6 text-uppercase">LABA OPERASIONAL (OPERATING PROFIT)</td>
                        <td></td>
                        <td class="text-end text-dark pe-3 fs-6">
                            Rp {{ number_format($data['operating_profit'], 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- 4. PENDAPATAN / BEBAN LAIN-LAIN -->
                    @if ($data['other_revenues']->isNotEmpty() || $data['other_expenses']->isNotEmpty())
                        <tr class="table-light">
                            <th colspan="2" class="ps-3 text-muted text-uppercase">Pendapatan / (Beban) Lain-lain</th>
                            <th></th>
                        </tr>
                        @foreach ($data['other_revenues'] as $or)
                            <tr>
                                <td class="ps-4">{{ $or['code'] }} - {{ $or['name'] }}</td>
                                <td class="text-end text-success">Rp {{ number_format($or['amount'], 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                        @foreach ($data['other_expenses'] as $oe)
                            <tr>
                                <td class="ps-4">{{ $oe['code'] }} - {{ $oe['name'] }}</td>
                                <td class="text-end text-danger">(Rp {{ number_format($oe['amount'], 0, ',', '.') }})</td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif

                    <!-- LABA BERSIH -->
                    <tr class="{{ $data['net_profit'] >= 0 ? 'table-success' : 'table-danger' }} fw-bold border-top border-bottom border-3">
                        <td class="ps-3 fs-5 text-uppercase">LABA BERSIH BERJALAN (NET PROFIT)</td>
                        <td></td>
                        <td class="text-end {{ $data['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} pe-3 fs-5">
                            Rp {{ number_format($data['net_profit'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
