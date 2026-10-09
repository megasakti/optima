@extends('layouts.app')

@section('title', 'Daftar Faktur Invoice & Piutang')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-receipt text-primary me-2"></i>Faktur Invoice & Piutang Usaha
            </h4>
            <p class="text-secondary small mb-0">Kelola penagihan klien pelatihan, sertifikasi BNSP, PPN, dan PPh 23.</p>
        </div>
        <div class="d-flex gap-2">
            @can('view-financial-reports')
            <a href="{{ route('reports.aging_receivable') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-1"></i> Analisis Umur Piutang
            </a>
            @endcan
            @can('create-invoices')
            <a href="{{ route('invoices.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Buat Invoice Baru
            </a>
            @endcan
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-optima p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Sisa Piutang Aktif</span>
                        <h4 class="fw-bold text-dark mt-1 mb-0">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h4>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-optima p-3 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Invoice Terdaftar</span>
                        <h4 class="fw-bold text-dark mt-1 mb-0">{{ $invoices->total() }} Invoice</h4>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-optima p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Alur Otomatisasi Jurnal</span>
                        <h6 class="fw-bold text-success mt-1 mb-0">Double-Entry AR Terintegrasi</h6>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-check2-all"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('invoices.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari No Inv, PO, Klien..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select name="customer_id" class="form-select form-select-sm">
                        <option value="">-- Semua Klien / Perusahaan --</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued (Belum Lunas)</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Sebagian (Partial)</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Jatuh Tempo (Overdue)</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select name="batch_id" class="form-select form-select-sm">
                        <option value="">-- Semua Batch --</option>
                        @foreach ($batches as $b)
                            <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->batch_code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (request()->hasAny(['search', 'customer_id', 'status', 'batch_id']))
                        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table Card -->
    <div class="card card-optima shadow-sm">
        <div class="table-responsive">
            <table class="table table-optima table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">No. Faktur</th>
                        <th>Klien / Pelanggan</th>
                        <th>Batch / Jasa</th>
                        <th>Tgl Terbit</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-end">Sisa Piutang</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $inv)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="text-decoration-none">
                                    {{ $inv->invoice_number }}
                                </a>
                                @if ($inv->po_number)
                                    <div class="text-muted" style="font-size: 0.72rem;">PO: {{ $inv->po_number }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $inv->customer->name }}</div>
                                <small class="text-muted">{{ $inv->customer->email ?? '-' }}</small>
                            </td>
                            <td>
                                @if ($inv->batch)
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        {{ $inv->batch->batch_code }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $isOverdue = $inv->due_date->isPast() && in_array($inv->status, ['issued', 'partial']);
                                @endphp
                                <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $inv->due_date->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold">
                                Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold {{ $inv->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($inv->balance_due, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if ($inv->status === 'draft')
                                    <span class="badge badge-soft-warning px-2 py-1">Draft</span>
                                @elseif ($inv->status === 'issued')
                                    <span class="badge badge-soft-primary px-2 py-1">Issued</span>
                                @elseif ($inv->status === 'partial')
                                    <span class="badge badge-soft-info px-2 py-1">Sebagian</span>
                                @elseif ($inv->status === 'paid')
                                    <span class="badge badge-soft-success px-2 py-1">Lunas</span>
                                @elseif ($inv->status === 'cancelled')
                                    <span class="badge bg-secondary text-white px-2 py-1">Batal</span>
                                @else
                                    <span class="badge bg-light text-dark border px-2 py-1">{{ ucfirst($inv->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-outline-secondary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('invoices.pdf', $inv->id) }}" target="_blank" class="btn btn-outline-danger" title="Cetak PDF">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                    @if (in_array($inv->status, ['issued', 'partial']) && $inv->balance_due > 0)
                                        @can('record-customer-payments')
                                        <a href="{{ route('payments.create', ['customer_id' => $inv->customer_id, 'invoice_id' => $inv->id]) }}" class="btn btn-outline-success" title="Terima Pembayaran">
                                            <i class="bi bi-cash-coin"></i>
                                        </a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Tidak ada data faktur invoice yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
