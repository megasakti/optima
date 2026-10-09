@extends('layouts.app')

@section('title', 'Tagihan LSP & Vendor (Accounts Payable)')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-building-check text-primary me-2"></i>Tagihan LSP & Vendor (Accounts Payable)
            </h4>
            <p class="text-secondary small mb-0">Kelola kewajiban biaya sertifikasi BNSP kepada LSP, vendor hotel/akomodasi, dan honor narasumber/asesor.</p>
        </div>
        <div class="d-flex gap-2">
            @can('view-financial-reports')
            <a href="{{ route('reports.aging_payable') }}" class="btn btn-outline-secondary">
                <i class="bi bi-hourglass-split me-1"></i> Analisis Umur Hutang
            </a>
            @endcan
            @can('create-bills')
            <a href="{{ route('bills.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Catat Tagihan Baru
            </a>
            @endcan
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-optima p-3 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Sisa Hutang Aktif</span>
                        <h4 class="fw-bold text-danger mt-1 mb-0">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h4>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-optima p-3 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Tagihan Terdaftar</span>
                        <h4 class="fw-bold text-dark mt-1 mb-0">{{ $bills->total() }} Tagihan</h4>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-optima p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Posting Jurnal Otomatis</span>
                        <h6 class="fw-bold text-success mt-1 mb-0">Hutang LSP 202 & Utang 201</h6>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-journal-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('bills.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari No Bill, No Inv Vendor..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select name="lsp_id" class="form-select form-select-sm">
                        <option value="">-- Semua LSP Mitra --</option>
                        @foreach ($lsps as $l)
                            <option value="{{ $l->id }}" {{ request('lsp_id') == $l->id ? 'selected' : '' }}>
                                {{ $l->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Sebagian (Partial)</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
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
                    @if (request()->hasAny(['search', 'lsp_id', 'status', 'batch_id']))
                        <a href="{{ route('bills.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Bills Table Card -->
    <div class="card card-optima shadow-sm">
        <div class="table-responsive">
            <table class="table table-optima table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">No. Tagihan</th>
                        <th>Pihak Penagih (Payee)</th>
                        <th>Kategori / Batch</th>
                        <th>Tgl Tagihan</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Total Biaya</th>
                        <th class="text-end">Sisa Hutang</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bills as $bill)
                        @php
                            $payeeName = $bill->lsp?->name ?? $bill->vendor?->name ?? $bill->trainer?->name ?? '-';
                            $payeeType = $bill->lsp ? 'LSP Mitra BNSP' : ($bill->trainer ? 'Instruktur/Asesor' : 'Vendor Umum');
                            $isOverdue = $bill->due_date->isPast() && in_array($bill->status, ['approved', 'partial']);
                        @endphp
                        <tr>
                            <td class="ps-3 fw-bold text-primary">
                                <a href="{{ route('bills.show', $bill->id) }}" class="text-decoration-none">
                                    {{ $bill->bill_number }}
                                </a>
                                @if ($bill->vendor_invoice_number)
                                    <div class="text-muted" style="font-size: 0.72rem;">No Ref: {{ $bill->vendor_invoice_number }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $payeeName }}</div>
                                <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">{{ $payeeType }}</span>
                            </td>
                            <td>
                                @if ($bill->batch)
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        {{ $bill->batch->batch_code }}
                                    </span>
                                @else
                                    <span class="text-muted small">Umum / Rutin</span>
                                @endif
                            </td>
                            <td>{{ $bill->bill_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $bill->due_date->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold">
                                Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold {{ $bill->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($bill->balance_due, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if ($bill->status === 'draft')
                                    <span class="badge badge-soft-warning px-2 py-1">Draft</span>
                                @elseif ($bill->status === 'approved')
                                    <span class="badge badge-soft-primary px-2 py-1">Disetujui</span>
                                @elseif ($bill->status === 'partial')
                                    <span class="badge badge-soft-info px-2 py-1">Sebagian</span>
                                @elseif ($bill->status === 'paid')
                                    <span class="badge badge-soft-success px-2 py-1">Lunas</span>
                                @else
                                    <span class="badge bg-secondary text-white px-2 py-1">{{ ucfirst($bill->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('bills.show', $bill->id) }}" class="btn btn-outline-secondary" title="Detail Tagihan">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if (in_array($bill->status, ['approved', 'partial']) && $bill->balance_due > 0)
                                        @can('record-vendor-payments')
                                        @php
                                            $ptype = $bill->lsp_id ? 'lsp' : ($bill->vendor_id ? 'vendor' : 'trainer');
                                            $pid = $bill->lsp_id ?: ($bill->vendor_id ?: $bill->trainer_id);
                                        @endphp
                                        <a href="{{ route('vendor-payments.create', ['payee_type' => $ptype, 'payee_id' => $pid, 'bill_id' => $bill->id]) }}" class="btn btn-outline-success" title="Bayar Tagihan">
                                            <i class="bi bi-credit-card"></i>
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
                                Tidak ada tagihan hutang vendor yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($bills->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $bills->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
