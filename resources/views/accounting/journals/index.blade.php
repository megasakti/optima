@extends('layouts.app')

@section('title', 'Jurnal Umum (Journal Entries)')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small text-muted">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item text-secondary">Akuntansi</li>
                <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">Jurnal Umum</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-journal-bookmark-fill text-primary"></i>
            <span>Jurnal Umum & Transaksi Keuangan</span>
        </h4>
        <p class="text-secondary small mb-0">Catatan kronologis seluruh mutasi debit dan kredit dengan prinsip double-entry balance.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('accounting.journals.template') }}" class="btn btn-outline-success shadow-sm btn-sm px-3">
            <i class="bi bi-file-earmark-excel me-1"></i> Format Excel (.xlsx)
        </a>
        @can('create-journals')
        <button type="button" class="btn btn-outline-primary shadow-sm btn-sm px-3" data-bs-toggle="modal" data-bs-target="#importJournalModal">
            <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import
        </button>
        <a href="{{ route('accounting.journals.create') }}" class="btn btn-primary shadow-sm btn-sm px-3">
            <i class="bi bi-plus-circle me-1"></i> Jurnal Manual
        </a>
        @endcan
    </div>
</div>

<!-- Executive Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-optima p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-journals"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Transaksi</span>
                    <h5 class="fw-bold text-dark mb-0 font-monospace">{{ number_format($journals->total(), 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-optima p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Mutasi (Hal. ini)</span>
                    <h5 class="fw-bold text-dark mb-0 font-monospace">Rp {{ number_format($journals->sum('total_debit'), 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-optima p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-check2-all"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem;">Double-Entry</span>
                    <h5 class="fw-bold text-dark mb-0">100% Balanced</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-optima p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div>
                    <span class="text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem;">Sifat Buku Besar</span>
                    <h5 class="fw-bold text-dark mb-0">Permanen (Immutable)</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Alert Audit Integrity -->
<div class="alert alert-light border shadow-sm py-2 px-3 mb-4 small d-flex align-items-center text-secondary rounded-3">
    <i class="bi bi-shield-check text-primary me-2 fs-5"></i>
    <div>
        <strong>Audit Trail & Kepatuhan:</strong> Seluruh transaksi buku besar bersifat permanen tanpa edit dan hapus langsung. Koreksi kesalahan akuntansi dilakukan secara transparan melalui fitur <strong>Jurnal Pembalik (Storno/Reversal)</strong>.
    </div>
</div>

<!-- Filter & Search Toolbar -->
<div class="card card-optima mb-4 p-3 shadow-sm border-0">
    <form method="GET" action="{{ route('accounting.journals.index') }}" class="row g-2 align-items-center">
        <!-- Search -->
        <div class="col-lg-3 col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari No. Jurnal atau Keterangan..." value="{{ request('search') }}">
            </div>
        </div>

        <!-- Periode -->
        <div class="col-lg-2 col-md-6">
            <select name="period_id" class="form-select form-select-sm">
                <option value="">-- Semua Periode --</option>
                @foreach ($periods as $p)
                    <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} {{ $p->is_closed ? '(Tutup)' : '(Buka)' }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Modul Sumber -->
        <div class="col-lg-2 col-md-4">
            <select name="source_module" class="form-select form-select-sm">
                <option value="">-- Semua Modul --</option>
                <option value="general" {{ request('source_module') == 'general' ? 'selected' : '' }}>Manual (General)</option>
                <option value="invoice" {{ request('source_module') == 'invoice' ? 'selected' : '' }}>Invoice AR</option>
                <option value="customer_payment" {{ request('source_module') == 'customer_payment' ? 'selected' : '' }}>Pembayaran AR</option>
                <option value="vendor_bill" {{ request('source_module') == 'vendor_bill' ? 'selected' : '' }}>Tagihan AP</option>
                <option value="vendor_payment" {{ request('source_module') == 'vendor_payment' ? 'selected' : '' }}>Pembayaran AP</option>
                <option value="reversal" {{ request('source_module') == 'reversal' ? 'selected' : '' }}>Pembalik (Reversal)</option>
                <option value="closing" {{ request('source_module') == 'closing' ? 'selected' : '' }}>Tutup Buku</option>
            </select>
        </div>

        <!-- Batch Terkait -->
        <div class="col-lg-2 col-md-4">
            <select name="batch_id" class="form-select form-select-sm">
                <option value="">-- Semua Batch --</option>
                @foreach ($batches as $b)
                    <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->batch_code }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Status -->
        <div class="col-lg-1 col-md-4">
            <select name="status" class="form-select form-select-sm">
                <option value="">-- Status --</option>
                <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>

        <!-- Buttons -->
        <div class="col-lg-2 col-md-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'period_id', 'source_module', 'batch_id', 'status']))
                <a href="{{ route('accounting.journals.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Table Journals Card -->
<div class="card card-optima shadow-sm border-0 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table table-optima table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-nowrap" style="width: 175px;">No. Jurnal</th>
                    <th class="text-nowrap" style="width: 110px;">Tanggal</th>
                    <th>Keterangan Transaksi</th>
                    <th class="text-center text-nowrap" style="width: 130px;">Modul Sumber</th>
                    <th class="text-center text-nowrap" style="width: 130px;">Batch Terkait</th>
                    <th class="text-end text-nowrap" style="width: 160px;">Total Mutasi</th>
                    <th class="text-center text-nowrap" style="width: 110px;">Status</th>
                    <th class="text-center pe-3 text-nowrap" style="width: 90px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($journals as $j)
                    <tr>
                        <td class="ps-3 text-nowrap">
                            <a href="{{ route('accounting.journals.show', $j->id) }}" class="fw-bold text-primary font-monospace text-decoration-none d-inline-flex align-items-center gap-1" title="Lihat rincian {{ $j->entry_number }}">
                                <i class="bi bi-file-earmark-text text-secondary opacity-75"></i>
                                <span>{{ $j->entry_number }}</span>
                            </a>
                        </td>
                        <td class="text-nowrap">
                            <span class="text-secondary small font-monospace">
                                <i class="bi bi-calendar3 text-muted me-1"></i>{{ $j->entry_date->format('d/m/Y') }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark text-truncate" style="max-width: 320px;" title="{{ $j->description }}">
                                {{ $j->description }}
                            </div>
                            <div class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-layers-half text-secondary"></i>
                                <span>{{ $j->lines->count() }} baris akun</span>
                            </div>
                        </td>
                        <td class="text-center text-nowrap">
                            @php
                                $badgeClass = match($j->source_module) {
                                    'general' => 'badge-soft-primary',
                                    'invoice' => 'badge-soft-success',
                                    'customer_payment' => 'badge-soft-info',
                                    'vendor_bill', 'vendor_payment' => 'badge-soft-warning',
                                    'reversal' => 'badge-soft-danger',
                                    'closing' => 'badge-soft-secondary',
                                    default => 'bg-light text-secondary border'
                                };
                                $moduleLabel = match($j->source_module) {
                                    'general' => 'Manual',
                                    'invoice' => 'Invoice AR',
                                    'customer_payment' => 'Bayar AR',
                                    'vendor_bill' => 'Tagihan AP',
                                    'vendor_payment' => 'Bayar AP',
                                    'reversal' => 'Pembalik',
                                    'closing' => 'Tutup Buku',
                                    default => strtoupper($j->source_module)
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">
                                {{ $moduleLabel }}
                            </span>
                        </td>
                        <td class="text-center text-nowrap">
                            @if ($j->batch)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-mortarboard me-1"></i>{{ $j->batch->batch_code }}
                                </span>
                            @else
                                <span class="text-muted small">&mdash;</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <span class="fw-bold text-dark font-monospace">
                                Rp {{ number_format($j->total_debit, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-center text-nowrap">
                            @if ($j->status === 'posted')
                                <span class="badge badge-soft-success px-2 py-1">
                                    <i class="bi bi-check2-circle me-1"></i>Posted
                                </span>
                            @elseif ($j->status === 'reversed')
                                <span class="badge badge-soft-danger px-2 py-1">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reversed
                                </span>
                            @else
                                <span class="badge badge-soft-warning px-2 py-1">
                                    <i class="bi bi-clock me-1"></i>Draft
                                </span>
                            @endif
                        </td>
                        <td class="text-center pe-3 text-nowrap">
                            <a href="{{ route('accounting.journals.show', $j->id) }}" class="btn btn-sm btn-outline-primary px-2 py-1" title="Lihat Rincian Jurnal">
                                <i class="bi bi-eye me-1"></i>Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="my-4">
                                <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-3"></i>
                                <h6 class="fw-semibold text-dark mb-1">Belum ada transaksi jurnal ditemukan</h6>
                                <p class="small text-muted mb-0">Coba ubah kata kunci pencarian atau reset filter untuk melihat data lain.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Card Footer with Stats & Modern Bootstrap 5 Pagination -->
    <div class="card-footer bg-white border-top py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="text-secondary small">
            Menampilkan <span class="fw-semibold text-dark">{{ $journals->firstItem() ?? 0 }}</span> &ndash; <span class="fw-semibold text-dark">{{ $journals->lastItem() ?? 0 }}</span> dari <span class="fw-semibold text-dark">{{ number_format($journals->total(), 0, ',', '.') }}</span> transaksi jurnal
        </div>
        <div>
            {{ $journals->links() }}
        </div>
    </div>
</div>

<!-- Modal Bulk Import Jurnal Excel -->
<div class="modal fade" id="importJournalModal" tabindex="-1" aria-labelledby="importJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('accounting.journals.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="importJournalModalLabel">
                        <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Bulk Import Jurnal Umum (.xlsx)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Banner Unduh Template -->
                    <div class="card bg-light border-0 mb-4 p-3 rounded-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">
                                    <i class="bi bi-info-circle text-primary me-1"></i> Belum Memiliki Format Berkas?
                                </h6>
                                <p class="text-secondary small mb-0">Unduh template standar yang telah memuat sheet referensi 33 Bagan Akun (COA) aktif & contoh transaksi riil yang seimbang.</p>
                            </div>
                            <a href="{{ route('accounting.journals.template') }}" class="btn btn-sm btn-success px-3">
                                <i class="bi bi-download me-1"></i> Unduh Format Template (.xlsx)
                            </a>
                        </div>
                    </div>

                    <!-- Input File -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Pilih Berkas Excel (.xlsx / .xls) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control form-control-lg" accept=".xlsx, .xls" required>
                        <small class="text-muted">Ukuran maksimal file: 10 MB. Mendukung file berekstensi Microsoft Excel (.xlsx).</small>
                    </div>

                    <!-- Aturan & Ketentuan Akuntansi -->
                    <div class="border rounded-3 p-3 bg-white">
                        <h6 class="fw-bold text-dark mb-2 small text-uppercase">
                            <i class="bi bi-check2-circle text-success me-1"></i> Ketentuan Validasi Double-Entry:
                        </h6>
                        <ul class="small text-secondary mb-0 ps-3">
                            <li><strong>Pengelompokan:</strong> Baris transaksi dengan nilai <code>No Transaksi</code> yang sama (misal <code>TRX-001</code>) akan digabung menjadi 1 nomor jurnal resmi sistem.</li>
                            <li><strong>Keseimbangan:</strong> Total nominal Debit <strong>wajib sama persis</strong> dengan Total Kredit pada setiap <code>No Transaksi</code>.</li>
                            <li><strong>Minimal Baris:</strong> Setiap transaksi wajib memiliki minimal 2 baris (minimal 1 baris debit dan 1 baris kredit).</li>
                            <li><strong>Kode Akun:</strong> Pastikan kode akun terdaftar di Bagan Akun (COA). Lihat sheet kedua pada template file.</li>
                            <li><strong>Format Tanggal:</strong> Format yang disarankan adalah <code>YYYY-MM-DD</code> (contoh: <code>2026-03-15</code>).</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Proses Impor Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
