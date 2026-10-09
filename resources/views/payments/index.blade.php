@extends('layouts.app')

@section('title', 'Kwitansi & Pembayaran Pelanggan')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-cash-coin text-success me-2"></i>Kwitansi & Penerimaan Pembayaran
            </h4>
            <p class="text-secondary small mb-0">Catat pelunasan invoice klien, bukti potong PPh 23, mutasi bank, dan posting jurnal kas/piutang.</p>
        </div>
        <div>
            @can('record-customer-payments')
            <a href="{{ route('payments.create') }}" class="btn btn-success shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Catat Pembayaran Baru
            </a>
            @endcan
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('payments.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari No Kwitansi, No Ref, Klien..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <select name="customer_id" class="form-select form-select-sm">
                        <option value="">-- Semua Klien / Pembayar --</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (request()->hasAny(['search', 'customer_id']))
                        <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
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
                        <th class="ps-3">No. Kwitansi</th>
                        <th>Klien Pembayar</th>
                        <th>Tgl Penerimaan</th>
                        <th>Metode & Rekening Bank</th>
                        <th class="text-end">Jumlah Kas Masuk</th>
                        <th class="text-end">PPh 23</th>
                        <th class="text-end">Total Kredit Piutang</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $pay)
                        <tr>
                            <td class="ps-3 fw-bold text-success">
                                <a href="{{ route('payments.show', $pay->id) }}" class="text-decoration-none">
                                    {{ $pay->payment_number }}
                                </a>
                                @if ($pay->reference_number)
                                    <div class="text-muted" style="font-size: 0.72rem;">Ref: {{ $pay->reference_number }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $pay->customer->name }}</div>
                                <small class="text-muted">
                                    Alokasi ke {{ $pay->allocations->count() }} Invoice
                                </small>
                            </td>
                            <td>{{ $pay->payment_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-bank me-1 text-primary"></i> {{ $pay->cashBankAccount->name }}
                                </span>
                                <div class="text-muted small mt-1">{{ ucfirst(str_replace('_', ' ', $pay->payment_method)) }}</div>
                            </td>
                            <td class="text-end fw-bold text-success">
                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end text-muted">
                                {{ (float) $pay->pph23_deducted > 0 ? 'Rp ' . number_format($pay->pph23_deducted, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end fw-bold text-primary">
                                Rp {{ number_format($pay->total_credited, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-success px-2 py-1">Posted</span>
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('payments.show', $pay->id) }}" class="btn btn-outline-secondary" title="Detail Kwitansi">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('payments.receipt-pdf', $pay->id) }}" target="_blank" class="btn btn-outline-danger" title="Cetak PDF">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-cash-stack fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada bukti penerimaan pembayaran pelanggan.
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
