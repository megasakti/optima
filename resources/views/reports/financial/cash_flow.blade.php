@extends('layouts.app')

@section('title', 'Laporan Arus Kas SAK EMKM')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-cash-stack text-primary me-2"></i>Laporan Arus Kas (Cash Flow Statement)
            </h4>
            <p class="text-secondary small mb-0">Arus Kas Masuk dan Keluar Berdasarkan SAK EMKM - PT OPTIMA MITRA SERTIFIKASI</p>
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
            <form method="GET" action="{{ route('reports.cash_flow') }}" class="row g-2 align-items-center">
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
                        <i class="bi bi-funnel me-1"></i> Tampilkan Arus Kas
                    </button>
                    <a href="{{ route('reports.cash_flow') }}" class="btn btn-sm btn-outline-secondary" title="Bulan Berjalan">
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
                    <span class="text-muted small fw-semibold text-uppercase">Saldo Kas Awal</span>
                    <h4 class="fw-bold text-dark mt-2 mb-1">Rp {{ number_format($data['beginning_cash'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Posisi per {{ date('d/m/Y', strtotime($data['start_date'])) }}</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Penerimaan Kas Operasi</span>
                    <h4 class="fw-bold text-success mt-2 mb-1">Rp {{ number_format($data['operating_receipts'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Pelunasan Piutang & Invoice Peserta</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Pengeluaran Kas Operasi</span>
                    <h4 class="fw-bold text-danger mt-2 mb-1">Rp {{ number_format($data['operating_payments'] + $data['operating_other_payments'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Bayar LSP, Vendor, Venue & Operasional</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Saldo Kas Akhir</span>
                    <h4 class="fw-bold text-primary mt-2 mb-1">Rp {{ number_format($data['ending_cash'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Per {{ date('d/m/Y', strtotime($data['end_date'])) }} (Kas & Bank)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statement Table -->
    <div class="card card-optima shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 text-center">
            <h5 class="fw-bold mb-0 text-dark">{{ $companyName }}</h5>
            <h6 class="fw-semibold text-primary mb-1">LAPORAN ARUS KAS</h6>
            <small class="text-muted">Periode {{ date('d F Y', strtotime($data['start_date'])) }} s/d {{ date('d F Y', strtotime($data['end_date'])) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <tbody>
                    <!-- 1. AKTIVITAS OPERASI -->
                    <tr class="table-light">
                        <th colspan="2" class="ps-3 text-primary text-uppercase">
                            <i class="bi bi-caret-down-fill me-1"></i> Arus Kas dari Aktivitas Operasi
                        </th>
                        <th></th>
                    </tr>
                    <tr>
                        <td class="ps-4" style="width: 60%;">Penerimaan Kas dari Pelanggan & Penjualan Jasa Pelatihan</td>
                        <td class="text-end fw-semibold text-success" style="width: 25%;">
                            Rp {{ number_format($data['operating_receipts'], 0, ',', '.') }}
                        </td>
                        <td style="width: 15%;"></td>
                    </tr>
                    <tr>
                        <td class="ps-4">Pembayaran Kas kepada Mitra LSP & Pemasok Bahan Pelatihan</td>
                        <td class="text-end fw-semibold text-danger">
                            (Rp {{ number_format($data['operating_payments'], 0, ',', '.') }})
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="ps-4">Pembayaran Kas untuk Beban Operasional, Gaji, & Sewa Kantor</td>
                        <td class="text-end fw-semibold text-danger">
                            (Rp {{ number_format($data['operating_other_payments'], 0, ',', '.') }})
                        </td>
                        <td></td>
                    </tr>
                    <tr class="fw-bold border-top">
                        <td class="ps-3 text-uppercase">Arus Kas Bersih dari Aktivitas Operasi</td>
                        <td></td>
                        <td class="text-end {{ $data['net_operating_cash'] >= 0 ? 'text-success' : 'text-danger' }} pe-3 fs-6">
                            Rp {{ number_format($data['net_operating_cash'], 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- 2. AKTIVITAS INVESTASI -->
                    <tr class="table-light">
                        <th colspan="2" class="ps-3 text-info text-uppercase">
                            <i class="bi bi-caret-down-fill me-1"></i> Arus Kas dari Aktivitas Investasi
                        </th>
                        <th></th>
                    </tr>
                    <tr>
                        <td class="ps-4">Pembelian Peralatan Kantor / Aset Tetap</td>
                        <td class="text-end text-muted">Rp 0</td>
                        <td></td>
                    </tr>
                    <tr class="fw-bold border-top">
                        <td class="ps-3 text-uppercase">Arus Kas Bersih dari Aktivitas Investasi</td>
                        <td></td>
                        <td class="text-end text-dark pe-3 fs-6">Rp 0</td>
                    </tr>

                    <!-- 3. AKTIVITAS PENDANAAN -->
                    <tr class="table-light">
                        <th colspan="2" class="ps-3 text-secondary text-uppercase">
                            <i class="bi bi-caret-down-fill me-1"></i> Arus Kas dari Aktivitas Pendanaan
                        </th>
                        <th></th>
                    </tr>
                    <tr>
                        <td class="ps-4">Penyetoran Modal Pemilik / (Penarikan Prive)</td>
                        <td class="text-end text-muted">Rp 0</td>
                        <td></td>
                    </tr>
                    <tr class="fw-bold border-top">
                        <td class="ps-3 text-uppercase">Arus Kas Bersih dari Aktivitas Pendanaan</td>
                        <td></td>
                        <td class="text-end text-dark pe-3 fs-6">Rp 0</td>
                    </tr>

                    <!-- REKONSILIASI KAS -->
                    <tr class="table-light fw-bold border-top border-2">
                        <td class="ps-3 text-uppercase">KENAIKAN / (PENURUNAN) BERSIH KAS & SETARA KAS</td>
                        <td></td>
                        <td class="text-end {{ $data['net_change_in_cash'] >= 0 ? 'text-success' : 'text-danger' }} pe-3 fs-6">
                            Rp {{ number_format($data['net_change_in_cash'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-3 text-uppercase fw-semibold">SALDO KAS & SETARA KAS PADA AWAL PERIODE</td>
                        <td></td>
                        <td class="text-end fw-semibold pe-3 text-dark">
                            Rp {{ number_format($data['beginning_cash'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="table-primary fw-bold border-top border-bottom border-3">
                        <td class="ps-3 fs-5 text-uppercase">SALDO KAS & SETARA KAS PADA AKHIR PERIODE</td>
                        <td></td>
                        <td class="text-end text-primary pe-3 fs-5">
                            Rp {{ number_format($data['ending_cash'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
