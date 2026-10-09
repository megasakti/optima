@extends('layouts.app')

@section('title', 'Detail Batch ' . $batch->batch_code)

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('batches.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold text-dark mb-0">{{ $batch->batch_code }} - {{ $batch->name }}</h4>
                <span class="badge badge-soft-{{ $batch->status === 'confirmed' ? 'primary' : ($batch->status === 'ongoing' ? 'warning' : 'success') }} px-2 py-1">
                    {{ ucfirst($batch->status) }}
                </span>
            </div>
            <p class="text-secondary small mb-0">
                {{ $batch->program->code }} - {{ $batch->program->name }} | 
                Pelaksanaan: {{ $batch->start_date->format('d/m/Y') }} s/d {{ $batch->end_date->format('d/m/Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if ($batch->budget)
                <a href="{{ route('budgets.show', $batch->budget->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Lihat Dokumen RAB
                </a>
            @else
                @can('create-budgets')
                <a href="{{ route('budgets.create', ['batch_id' => $batch->id]) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Susun RAB Batch
                </a>
                @endcan
            @endif

            <a href="{{ route('reports.batch_profitability.detail', $batch->id) }}" class="btn btn-outline-success">
                <i class="bi bi-graph-up me-1"></i> Analisis Profit
            </a>

            @can('create-invoices')
            <a href="{{ route('invoices.create', ['batch_id' => $batch->id]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-receipt me-1"></i> Terbitkan Invoice
            </a>
            @endcan

            @can('create-bills')
            <a href="{{ route('vendor_bills.create', ['batch_id' => $batch->id]) }}" class="btn btn-outline-danger">
                <i class="bi bi-cash me-1"></i> Catat Biaya/LSP
            </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Financial KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Revenue Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Realisasi Pendapatan</span>
                        <div class="bg-primary-subtle text-primary p-2 rounded">
                            <i class="bi bi-receipt fs-5"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark">Rp {{ number_format($financials['actuals']['actual_revenue'], 0, ',', '.') }}</h5>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Target RAB:</span>
                        <span class="fw-semibold">Rp {{ number_format($financials['budget']['target_revenue'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Direct Cost Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Realisasi Biaya (HPP)</span>
                        <div class="bg-danger-subtle text-danger p-2 rounded">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1 {{ $financials['variance']['is_cost_overrun'] ? 'text-danger' : 'text-dark' }}">
                        Rp {{ number_format($financials['actuals']['actual_cost'], 0, ',', '.') }}
                    </h5>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Anggaran RAB:</span>
                        <span class="fw-semibold">Rp {{ number_format($financials['budget']['target_cost'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gross Margin Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Laba Kotor Realisasi</span>
                        <div class="bg-success-subtle text-success p-2 rounded">
                            <i class="bi bi-graph-up-arrow fs-5"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1 {{ $financials['actuals']['actual_gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($financials['actuals']['actual_gross_profit'], 0, ',', '.') }}
                    </h5>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Profit Margin:</span>
                        <span class="badge {{ $financials['actuals']['actual_margin_pct'] >= 30 ? 'bg-success' : ($financials['actuals']['actual_margin_pct'] >= 15 ? 'bg-warning text-dark' : 'bg-danger') }}">
                            {{ number_format($financials['actuals']['actual_margin_pct'], 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Collection Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Kas Diterima vs Piutang</span>
                        <div class="bg-info-subtle text-info p-2 rounded">
                            <i class="bi bi-cash-coin fs-5"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1 text-primary">Rp {{ number_format($financials['actuals']['paid_revenue'], 0, ',', '.') }}</h5>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Sisa Piutang (AR):</span>
                        <span class="text-danger fw-semibold">Rp {{ number_format($financials['actuals']['receivable_revenue'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs nav-tabs-optima mb-4" id="batchTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="bi bi-info-circle me-1"></i> Ringkasan Operasional
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="budget-tab" data-bs-toggle="tab" data-bs-target="#budget" type="button" role="tab">
                <i class="bi bi-pie-chart me-1"></i> Rencana Anggaran (RAB)
                @if ($batch->budget)
                    <span class="badge bg-success-subtle text-success ms-1">Tersedia</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary ms-1">Belum Ada</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">
                <i class="bi bi-receipt me-1"></i> Invoices Klien ({{ $batch->invoices->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bills-tab" data-bs-toggle="tab" data-bs-target="#bills" type="button" role="tab">
                <i class="bi bi-wallet2 me-1"></i> Tagihan Biaya & LSP ({{ $batch->vendorBills->count() }})
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="batchTabContent">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="card card-optima shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="fw-bold mb-0 text-primary">Detail Jadwal & Pelaksanaan</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="text-muted small">Program Sertifikasi</label>
                                    <div class="fw-semibold text-dark">{{ $batch->program->name }}</div>
                                    <small class="text-muted">Kode: {{ $batch->program->code }}</small>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small">Mitra LSP Pelaksana</label>
                                    <div class="fw-semibold text-dark">{{ $batch->lsp->name ?? 'Belum Ditentukan' }}</div>
                                    @if ($batch->lsp)
                                        <small class="text-muted">No. Lisensi: {{ $batch->lsp->license_number ?? '-' }}</small>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small">Instruktur / Trainer</label>
                                    <div class="fw-semibold text-dark">{{ $batch->trainer->name ?? 'Belum Ditentukan' }}</div>
                                    @if ($batch->trainer)
                                        <small class="text-muted">{{ $batch->trainer->phone ?? '-' }}</small>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small">PIC Operasional Internal</label>
                                    <div class="fw-semibold text-dark">{{ $batch->pic->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $batch->pic->email ?? '-' }}</small>
                                </div>
                                <hr class="my-2">
                                <div class="col-sm-4">
                                    <label class="text-muted small">Tanggal Pelatihan</label>
                                    <div class="fw-semibold">{{ $batch->start_date->format('d/m/Y') }} - {{ $batch->end_date->format('d/m/Y') }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="text-muted small">Tanggal Asesmen BNSP</label>
                                    <div class="fw-semibold text-primary">
                                        {{ $batch->assessment_date ? $batch->assessment_date->format('d/m/Y') : 'Belum Dijadwalkan' }}
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="text-muted small">Metode & Venue</label>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $batch->delivery_type)) }}</div>
                                    <small class="text-muted">{{ $batch->venue_name ?? '-' }} ({{ $batch->venue_city ?? '-' }})</small>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small">Catatan Operasional</label>
                                    <div class="p-2 bg-light rounded text-secondary small">{{ $batch->notes ?? 'Tidak ada catatan.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card card-optima shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="fw-bold mb-0 text-primary">Target Komersial & Kuota Peserta</h6>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Target Peserta</span>
                                    <span class="fw-bold fs-6">{{ $batch->target_participants }} Pax</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Peserta Terdaftar Saat Ini</span>
                                    <span class="fw-bold fs-6 text-primary">{{ $batch->actual_participants }} Pax</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Harga / Peserta</span>
                                    <span class="fw-bold fs-6 text-dark">Rp {{ number_format($batch->price_per_participant, 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Target Total Omset</span>
                                    <span class="fw-bold fs-6 text-success">Rp {{ number_format($batch->target_participants * $batch->price_per_participant, 0, ',', '.') }}</span>
                                </li>
                            </ul>

                            <div class="mt-4 p-3 bg-light rounded border">
                                <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-lightbulb me-1"></i> Status Pengendalian Biaya</h6>
                                @if ($financials['variance']['is_cost_overrun'])
                                    <div class="text-danger small fw-semibold">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Perhatian: Biaya aktual telah melampaui RAB sebesar Rp {{ number_format(abs($financials['variance']['cost_diff']), 0, ',', '.') }}.
                                    </div>
                                @else
                                    <div class="text-success small fw-semibold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Biaya operasional berada dalam batas aman anggaran RAB.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Tab -->
        <div class="tab-pane fade" id="budget" role="tabpanel">
            @if ($batch->budget)
                <div class="card card-optima shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-primary">Rencana Anggaran Biaya (RAB): {{ $batch->budget->budget_number }}</h6>
                            <small class="text-muted">Status: <span class="badge bg-primary-subtle text-primary">{{ ucfirst($batch->budget->status) }}</span></small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('budgets.show', $batch->budget->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square me-1"></i> Buka Detail RAB
                            </a>
                            <a href="{{ route('budgets.pdf', $batch->budget->id) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-file-pdf me-1"></i> Unduh PDF
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Kategori</th>
                                    <th>Deskripsi Komponen</th>
                                    <th>Akun Beban</th>
                                    <th class="text-center">Vol</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batch->budget->items as $item)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-uppercase small text-muted">{{ $item->category }}</td>
                                        <td class="fw-semibold text-dark">{{ $item->description }}</td>
                                        <td>
                                            @if ($item->account)
                                                <span class="badge bg-light text-dark border">{{ $item->account->code }} - {{ $item->account->name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                                        <td class="text-center text-muted">{{ $item->unit }}</td>
                                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="text-end pe-3 fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="6" class="text-end">Total Biaya Anggaran (HPP):</th>
                                    <th class="text-end pe-3 text-danger">Rp {{ number_format($batch->budget->total_cost_budget, 0, ',', '.') }}</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end">Target Omset Penjualan:</th>
                                    <th class="text-end pe-3 text-success">Rp {{ number_format($batch->budget->total_revenue_budget, 0, ',', '.') }}</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end">Estimasi Laba Kotor:</th>
                                    <th class="text-end pe-3 text-primary">
                                        Rp {{ number_format($batch->budget->projected_gross_margin, 0, ',', '.') }} 
                                        ({{ number_format($batch->budget->projected_margin_percentage, 1) }}%)
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                <div class="card card-optima shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-muted d-block mb-3"></i>
                        <h5 class="fw-bold text-dark">Rencana Anggaran Biaya (RAB) Belum Disusun</h5>
                        <p class="text-muted small mx-auto" style="max-width: 500px;">
                            Susun RAB untuk menetapkan baseline pengeluaran sertifikasi LSP, fee trainer, sewa hotel, katering, dan modul agar margin laba terkontrol.
                        </p>
                        @can('create-budgets')
                        <a href="{{ route('budgets.create', ['batch_id' => $batch->id]) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Susun RAB Sekarang
                        </a>
                        @endcan
                    </div>
                </div>
            @endif
        </div>

        <!-- Invoices Tab -->
        <div class="tab-pane fade" id="invoices" role="tabpanel">
            <div class="card card-optima shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary">Invoices Tagihan Peserta / Perusahaan</h6>
                    @can('create-invoices')
                    <a href="{{ route('invoices.create', ['batch_id' => $batch->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Buat Invoice Baru
                    </a>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No. Invoice</th>
                                <th>Klien / Perusahaan</th>
                                <th>Tanggal</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-end">Total Nilai</th>
                                <th class="text-end">Terbayar</th>
                                <th class="text-end">Sisa Piutang</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($batch->invoices as $inv)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">
                                        <a href="{{ route('invoices.show', $inv->id) }}">{{ $inv->invoice_number }}</a>
                                    </td>
                                    <td>{{ $inv->customer->name ?? '-' }}</td>
                                    <td>{{ $inv->issue_date->format('d/m/Y') }}</td>
                                    <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-end text-success">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                                    <td class="text-end text-danger fw-bold">Rp {{ number_format($inv->balance_due, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-soft-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'partial' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Belum ada invoice yang diterbitkan untuk batch ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vendor Bills Tab -->
        <div class="tab-pane fade" id="bills" role="tabpanel">
            <div class="card card-optima shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary">Tagihan Vendor & Beban Pelatihan (LSP, Trainer, Venue)</h6>
                    @can('create-bills')
                    <a href="{{ route('vendor_bills.create', ['batch_id' => $batch->id]) }}" class="btn btn-sm btn-danger">
                        <i class="bi bi-plus-circle me-1"></i> Catat Tagihan Biaya
                    </a>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No. Tagihan</th>
                                <th>Vendor / LSP / Instruktur</th>
                                <th>Tanggal</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-end">Total Beban</th>
                                <th class="text-end">Dibayar</th>
                                <th class="text-end">Sisa Hutang</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($batch->vendorBills as $bill)
                                <tr>
                                    <td class="ps-3 fw-bold text-danger">
                                        <a href="{{ route('vendor_bills.show', $bill->id) }}" class="text-danger text-decoration-none">
                                            {{ $bill->bill_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $bill->vendor->name ?? ($bill->lsp->name ?? 'Vendor') }}</div>
                                        <small class="text-muted">{{ $bill->category ?? 'Direct Cost' }}</small>
                                    </td>
                                    <td>{{ $bill->bill_date->format('d/m/Y') }}</td>
                                    <td>{{ $bill->due_date->format('d/m/Y') }}</td>
                                    <td class="text-end fw-semibold text-danger">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-end text-success">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</td>
                                    <td class="text-end text-dark fw-bold">Rp {{ number_format($bill->balance_due, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-soft-{{ $bill->status === 'paid' ? 'success' : ($bill->status === 'approved' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <a href="{{ route('vendor_bills.show', $bill->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Belum ada tagihan biaya vendor atau sertifikasi LSP untuk batch ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
