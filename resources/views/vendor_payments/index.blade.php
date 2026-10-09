@extends('layouts.app')

@section('title', 'Voucher Pembayaran Hutang Vendor & LSP')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-credit-card text-success me-2"></i>Voucher Pembayaran Hutang Vendor & LSP
            </h4>
            <p class="text-secondary small mb-0">Catat pelunasan tagihan uji sertifikasi LSP, hotel, konsumsi, dan honor narasumber melalui rekening bank/kas.</p>
        </div>
        <div>
            @can('record-vendor-payments')
            <a href="{{ route('vendor-payments.create') }}" class="btn btn-success shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Catat Pembayaran Baru
            </a>
            @endcan
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('vendor-payments.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari No Voucher, No Referensi Bank, Nama Vendor/LSP..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (request()->has('search'))
                        <a href="{{ route('vendor-payments.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card card-optima shadow-sm">
        <div class="table-responsive">
            <table class="table table-optima table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">No. Voucher</th>
                        <th>Penerima Dana (Payee)</th>
                        <th>Tgl Pengeluaran</th>
                        <th>Metode & Rekening Asal</th>
                        <th class="text-end">Jumlah Dana Keluar</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $pay)
                        @php
                            $payeeName = $pay->lsp?->name ?? $pay->vendor?->name ?? $pay->trainer?->name ?? '-';
                        @endphp
                        <tr>
                            <td class="ps-3 fw-bold text-success">
                                <a href="{{ route('vendor-payments.show', $pay->id) }}" class="text-decoration-none">
                                    {{ $pay->payment_number }}
                                </a>
                                @if ($pay->reference_number)
                                    <div class="text-muted" style="font-size: 0.72rem;">Ref: {{ $pay->reference_number }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $payeeName }}</div>
                                <small class="text-muted">
                                    Alokasi ke {{ $pay->allocations->count() }} Tagihan
                                </small>
                            </td>
                            <td>{{ $pay->payment_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-bank me-1 text-primary"></i> {{ $pay->cashBankAccount->name }}
                                </span>
                                <div class="text-muted small mt-1">{{ ucfirst(str_replace('_', ' ', $pay->payment_method)) }}</div>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-success px-2 py-1">Posted</span>
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('vendor-payments.show', $pay->id) }}" class="btn btn-outline-secondary" title="Detail Voucher">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('vendor-payments.voucher-pdf', $pay->id) }}" target="_blank" class="btn btn-outline-danger" title="Cetak Voucher PDF">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-cash-stack fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada bukti pengeluaran kas / pembayaran hutang vendor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
