@extends('layouts.app')

@section('title', 'Laporan Posisi Keuangan (Neraca) SAK EMKM')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-bank text-primary me-2"></i>Laporan Posisi Keuangan / Neraca (Balance Sheet)
            </h4>
            <p class="text-secondary small mb-0">Posisi Aset, Liabilitas, dan Ekuitas Berdasarkan SAK EMKM - PT OPTIMA MITRA SERTIFIKASI</p>
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
            <form method="GET" action="{{ route('reports.balance_sheet') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">Posisi Per Tanggal:</span>
                        <input type="date" name="as_of_date" class="form-control" value="{{ $data['as_of_date'] }}">
                    </div>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Tampilkan Posisi Neraca
                    </button>
                    <a href="{{ route('reports.balance_sheet') }}" class="btn btn-sm btn-outline-secondary" title="Hari Ini">
                        <i class="bi bi-clock-history me-1"></i> Hari Ini
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
                    <span class="text-muted small fw-semibold text-uppercase">Total Aset (Aktiva)</span>
                    <h4 class="fw-bold text-primary mt-2 mb-1">Rp {{ number_format($data['total_assets'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Aset Lancar: Rp {{ number_format($data['total_current_assets'], 0, ',', '.') }}</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Total Liabilitas (Utang)</span>
                    <h4 class="fw-bold text-danger mt-2 mb-1">Rp {{ number_format($data['total_liabilities'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Kewajiban Jangka Pendek & LSP</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Total Ekuitas (Modal)</span>
                    <h4 class="fw-bold text-success mt-2 mb-1">Rp {{ number_format($data['total_equity'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Termasuk Laba Berjalan: Rp {{ number_format($data['current_period_earnings'], 0, ',', '.') }}</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Status Keseimbangan</span>
                    <div class="mt-2 mb-1">
                        @if ($data['is_balanced'])
                            <h5 class="fw-bold text-success mb-0">
                                <i class="bi bi-check-circle-fill me-1"></i> SEIMBANG (BALANCED)
                            </h5>
                        @else
                            <h5 class="fw-bold text-danger mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> TIDAK SEIMBANG
                            </h5>
                        @endif
                    </div>
                    <small class="text-muted">Aset = Liabilitas + Ekuitas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Sheet Side-by-Side Presentation -->
    <div class="card card-optima shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 text-center">
            <h5 class="fw-bold mb-0 text-dark">{{ $companyName }}</h5>
            <h6 class="fw-semibold text-primary mb-1">LAPORAN POSISI KEUANGAN (NERACA)</h6>
            <small class="text-muted">Per Tanggal {{ date('d F Y', strtotime($data['as_of_date'])) }}</small>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- SISI KIRI: ASET -->
                <div class="col-12 col-lg-6 border-end">
                    <h6 class="fw-bold text-primary text-uppercase border-bottom pb-2 mb-3">
                        <i class="bi bi-wallet2 me-1"></i> ASET (AKTIVA)
                    </h6>

                    <!-- 1. Aset Lancar -->
                    <div class="mb-4">
                        <div class="fw-semibold text-dark small text-uppercase mb-2">Aset Lancar</div>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                @forelse ($data['current_assets'] as $ca)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark border me-1">{{ $ca['code'] }}</span>
                                            {{ $ca['name'] }}
                                        </td>
                                        <td class="text-end fw-semibold">
                                            Rp {{ number_format($ca['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-muted small">Tidak ada aset lancar.</td></tr>
                                @endforelse
                                <tr class="border-top fw-bold text-primary">
                                    <td>Total Aset Lancar</td>
                                    <td class="text-end">Rp {{ number_format($data['total_current_assets'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 2. Aset Tidak Lancar -->
                    <div class="mb-4">
                        <div class="fw-semibold text-dark small text-uppercase mb-2">Aset Tidak Lancar (Aset Tetap)</div>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                @forelse ($data['non_current_assets'] as $nca)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark border me-1">{{ $nca['code'] }}</span>
                                            {{ $nca['name'] }}
                                        </td>
                                        <td class="text-end fw-semibold {{ $nca['amount'] < 0 ? 'text-danger' : '' }}">
                                            Rp {{ number_format($nca['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-muted small">Tidak ada aset tetap.</td></tr>
                                @endforelse
                                <tr class="border-top fw-bold text-primary">
                                    <td>Total Aset Tidak Lancar</td>
                                    <td class="text-end">Rp {{ number_format($data['total_non_current_assets'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- TOTAL ASET -->
                    <div class="p-3 bg-primary-subtle rounded border border-primary d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary-emphasis fs-6 text-uppercase">TOTAL ASET</span>
                        <span class="fw-bold text-primary fs-5">Rp {{ number_format($data['total_assets'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- SISI KANAN: LIABILITAS & EKUITAS -->
                <div class="col-12 col-lg-6">
                    <h6 class="fw-bold text-danger text-uppercase border-bottom pb-2 mb-3">
                        <i class="bi bi-shield-check me-1"></i> LIABILITAS & EKUITAS (PASIVA)
                    </h6>

                    <!-- 1. Liabilitas Jangka Pendek -->
                    <div class="mb-4">
                        <div class="fw-semibold text-dark small text-uppercase mb-2">Liabilitas Jangka Pendek</div>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                @forelse ($data['current_liabilities'] as $cl)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark border me-1">{{ $cl['code'] }}</span>
                                            {{ $cl['name'] }}
                                        </td>
                                        <td class="text-end fw-semibold text-danger">
                                            Rp {{ number_format($cl['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-muted small">Tidak ada liabilitas tercatat.</td></tr>
                                @endforelse
                                <tr class="border-top fw-bold text-danger">
                                    <td>Total Liabilitas</td>
                                    <td class="text-end">Rp {{ number_format($data['total_liabilities'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 2. Ekuitas -->
                    <div class="mb-4">
                        <div class="fw-semibold text-dark small text-uppercase mb-2">Ekuitas Pemilik</div>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                @forelse ($data['equities'] as $eq)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark border me-1">{{ $eq['code'] }}</span>
                                            {{ $eq['name'] }}
                                        </td>
                                        <td class="text-end fw-semibold">
                                            Rp {{ number_format($eq['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-muted small">Tidak ada akun ekuitas.</td></tr>
                                @endforelse
                                <tr>
                                    <td>
                                        <i class="bi bi-graph-up text-success me-1"></i>
                                        Laba Periode Berjalan (Current Earnings)
                                    </td>
                                    <td class="text-end fw-semibold text-success">
                                        Rp {{ number_format($data['current_period_earnings'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr class="border-top fw-bold text-success">
                                    <td>Total Ekuitas</td>
                                    <td class="text-end">Rp {{ number_format($data['total_equity'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- TOTAL LIABILITAS & EKUITAS -->
                    <div class="p-3 bg-success-subtle rounded border border-success d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-success-emphasis fs-6 text-uppercase">TOTAL LIABILITAS & EKUITAS</span>
                        <span class="fw-bold text-success fs-5">Rp {{ number_format($data['total_liabilities_and_equity'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
