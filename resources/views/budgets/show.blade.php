@extends('layouts.app')

@section('title', 'Dokumen RAB ' . $budget->budget_number)

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('budgets.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold text-dark mb-0">Rencana Anggaran Biaya: {{ $budget->budget_number }}</h4>
                <span class="badge badge-soft-{{ $budget->status === 'approved' ? 'success' : ($budget->status === 'submitted' ? 'warning' : ($budget->status === 'rejected' ? 'danger' : 'secondary')) }} px-2 py-1">
                    {{ ucfirst($budget->status) }}
                </span>
            </div>
            <p class="text-secondary small mb-0">
                Batch: <a href="{{ route('batches.show', $budget->batch_id) }}" class="fw-semibold text-decoration-none">{{ $budget->batch->batch_code }} - {{ $budget->batch->name }}</a> | 
                Program: {{ $budget->batch->program->name }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('budgets.pdf', $budget->id) }}" target="_blank" class="btn btn-outline-danger">
                <i class="bi bi-file-pdf me-1"></i> Cetak Dokumen PDF
            </a>
            <a href="{{ route('batches.show', $budget->batch_id) }}" class="btn btn-outline-primary">
                <i class="bi bi-mortarboard me-1"></i> Lihat Data Batch
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Approval Status / Action Banner -->
    @if ($budget->status === 'draft')
        <div class="card bg-light border-warning mb-4">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h6 class="fw-bold text-warning-emphasis mb-1">
                        <i class="bi bi-pencil-square me-2"></i>Status Draft - Perlu Pengajuan Persetujuan
                    </h6>
                    <p class="text-secondary small mb-0">RAB ini masih berstatus rancangan operasional. Silakan ajukan ke Finance Manager untuk validasi dan penguncian baseline anggaran.</p>
                </div>
                <form action="{{ route('budgets.submit', $budget->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan RAB ini ke Finance Manager?');">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="comment" class="form-control form-control-sm" placeholder="Catatan pengajuan (opsional)...">
                        <button type="submit" class="btn btn-warning btn-sm fw-semibold">
                            <i class="bi bi-send-check me-1"></i> Ajukan RAB Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @elseif ($budget->status === 'submitted')
        <div class="card bg-warning-subtle border-warning mb-4">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h6 class="fw-bold text-warning-emphasis mb-1">
                        <i class="bi bi-clock-history me-2"></i>Menunggu Verifikasi & Persetujuan Finance Manager
                    </h6>
                    <p class="text-secondary small mb-0">RAB telah diajukan dan sedang menunggu otorisasi Finance Manager sebelum pengeluaran biaya batch dapat diproses.</p>
                </div>
                @can('approve-budgets')
                <form action="{{ route('budgets.approve', $budget->id) }}" method="POST" onsubmit="return confirm('Setujui RAB ini dan kunci baseline anggaran batch?');">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="comment" class="form-control form-control-sm" placeholder="Catatan persetujuan...">
                        <button type="submit" class="btn btn-success btn-sm fw-semibold">
                            <i class="bi bi-check-circle-fill me-1"></i> Setujui RAB (Approve)
                        </button>
                    </div>
                </form>
                @endcan
            </div>
        </div>
    @elseif ($budget->status === 'approved')
        <div class="card bg-success-subtle border-success mb-4">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                    <div>
                        <div class="fw-bold text-success-emphasis">Rencana Anggaran Biaya Telah Disetujui (Baseline Locked)</div>
                        <small class="text-secondary">Disetujui oleh {{ $budget->approvedBy->name ?? 'Finance Manager' }} pada {{ $budget->updated_at->format('d/m/Y H:i') }}. Baseline ini digunakan sebagai pembanding realisasi biaya operasional.</small>
                    </div>
                </div>
                <span class="badge bg-success px-3 py-2 fs-6">Approved</span>
            </div>
        </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Target Omset (Revenue)</span>
                    <h4 class="fw-bold text-success mt-2 mb-1">Rp {{ number_format($budget->total_revenue_budget, 0, ',', '.') }}</h4>
                    <small class="text-muted">{{ number_format($budget->target_participants, 0) }} Pax @ Rp {{ number_format($budget->target_revenue_per_pax, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Total Anggaran Biaya (HPP)</span>
                    <h4 class="fw-bold text-danger mt-2 mb-1">Rp {{ number_format($budget->total_cost_budget, 0, ',', '.') }}</h4>
                    <small class="text-muted">{{ $budget->items->count() }} pos rincian biaya</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Proyeksi Laba Kotor</span>
                    <h4 class="fw-bold text-primary mt-2 mb-1">Rp {{ number_format($budget->projected_gross_margin, 0, ',', '.') }}</h4>
                    <small class="text-muted">Target laba kotor sebelum beban operasional umum</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Target Profit Margin</span>
                    <h4 class="fw-bold mt-2 mb-1 {{ $budget->projected_margin_percentage >= 30 ? 'text-success' : ($budget->projected_margin_percentage >= 15 ? 'text-warning' : 'text-danger') }}">
                        {{ number_format($budget->projected_margin_percentage, 1) }}%
                    </h4>
                    <small class="text-muted">Status: {{ $budget->projected_margin_percentage >= 30 ? 'Margin Sangat Sehat' : 'Margin Memadai' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Detail Table -->
    <div class="card card-optima shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="bi bi-list-columns-reverse me-2"></i>Rincian Pos Anggaran Pengeluaran
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Kategori</th>
                        <th>Deskripsi Komponen</th>
                        <th>Akun Beban Akuntansi</th>
                        <th class="text-center">Volume</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end pe-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($budget->items as $index => $item)
                        <tr>
                            <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                            <td>
                                @if ($item->category === 'certification')
                                    <span class="badge bg-primary-subtle text-primary border">Sertifikasi LSP</span>
                                @elseif ($item->category === 'trainer')
                                    <span class="badge bg-info-subtle text-info-emphasis border">Honor Trainer</span>
                                @elseif ($item->category === 'venue')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border">Hotel / Venue</span>
                                @elseif ($item->category === 'catering')
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border">Konsumsi</span>
                                @elseif ($item->category === 'materials')
                                    <span class="badge bg-light text-dark border">Kit & Modul</span>
                                @else
                                    <span class="badge bg-light text-muted border">{{ ucfirst($item->category) }}</span>
                                @endif
                            </td>
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
                            <td class="text-end pe-3 fw-bold text-dark">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="7" class="text-end">TOTAL ANGGARAN BIAYA LANGSUNG (HPP):</th>
                        <th class="text-end pe-3 fw-bold text-danger fs-6">Rp {{ number_format($budget->total_cost_budget, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-end">TARGET OMSET PENJUALAN:</th>
                        <th class="text-end pe-3 fw-bold text-success fs-6">Rp {{ number_format($budget->total_revenue_budget, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-end">ESTIMASI LABA KOTOR (GROSS PROFIT):</th>
                        <th class="text-end pe-3 fw-bold text-primary fs-6">Rp {{ number_format($budget->projected_gross_margin, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Approvals & Audit Trail Card -->
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-clock-history me-2"></i>Riwayat Persetujuan RAB
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if ($budget->approvals->isNotEmpty())
                        <ul class="timeline list-unstyled mb-0">
                            @foreach ($budget->approvals as $approval)
                                <li class="mb-3 ps-3 border-start border-2 border-primary position-relative">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark">{{ $approval->user->name ?? 'User' }}</span>
                                        <small class="text-muted">{{ $approval->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <div class="badge bg-{{ $approval->action === 'approved' ? 'success' : ($approval->action === 'rejected' ? 'danger' : 'warning') }}-subtle text-{{ $approval->action === 'approved' ? 'success' : ($approval->action === 'rejected' ? 'danger' : 'warning') }} mb-1">
                                        {{ ucfirst($approval->action) }}
                                    </div>
                                    <p class="text-muted small mb-0">{{ $approval->comments ?? 'Tidak ada komentar.' }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-hourglass-split fs-2 d-block mb-2"></i>
                            Belum ada riwayat approval tercatat.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-info-circle me-2"></i>Informasi Pembuat Dokumen
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small">Disusun Oleh:</span>
                            <div class="fw-semibold text-dark">{{ $budget->createdBy->name ?? '-' }}</div>
                            <small class="text-muted">{{ $budget->createdBy->email ?? '' }}</small>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small">Disetujui Oleh:</span>
                            <div class="fw-semibold text-dark">{{ $budget->approvedBy->name ?? 'Menunggu Persetujuan' }}</div>
                            <small class="text-muted">{{ $budget->approvedBy->email ?? '' }}</small>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small">Tanggal Pembuatan:</span>
                            <div class="fw-semibold text-dark">{{ $budget->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small">Terakhir Diperbarui:</span>
                            <div class="fw-semibold text-dark">{{ $budget->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small">Catatan Dokumen:</span>
                            <div class="p-2 bg-light rounded text-secondary small">{{ $budget->notes ?? 'Tidak ada catatan.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
