@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')
<!-- Header Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Ringkasan Eksekutif Keuangan</h4>
        <p class="text-secondary small mb-0">Posisi likuiditas kas, piutang, hutang sertifikasi LSP, dan performa batch pelatihan.</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-white text-secondary border px-3 py-2 d-flex align-items-center">
            <i class="bi bi-clock-history me-2 text-primary"></i> Update: {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </span>
    </div>
</div>

<!-- KPI Cards Row -->
<div class="row g-3 mb-4">
    <!-- 1. Kas & Bank BCA -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-optima p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-secondary small fw-medium text-uppercase">Saldo Kas & Bank</span>
                    <h4 class="fw-bold text-dark mt-2 mb-1">Rp {{ number_format($totalCashBank, 0, ',', '.') }}</h4>
                    <span class="badge badge-soft-success small">
                        <i class="bi bi-check-circle me-1"></i> Likuiditas Tersedia
                    </span>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-bank2"></i>
                </div>
            </div>
            <hr class="my-2 border-light">
            <div class="small text-muted d-flex justify-content-between">
                <span>BCA Operasional:</span>
                <span class="fw-semibold">Rp {{ number_format($cashBankAccounts->where('account_type', 'bank')->sum('current_balance'), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- 2. Piutang Usaha (AR) -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-optima p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-secondary small fw-medium text-uppercase">Piutang Usaha (AR)</span>
                    <h4 class="fw-bold text-dark mt-2 mb-1">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</h4>
                    <span class="badge badge-soft-warning small">
                        <i class="bi bi-hourglass-split me-1"></i> Outstanding Klien
                    </span>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <hr class="my-2 border-light">
            <div class="small text-muted d-flex justify-content-between">
                <span>Invoice Aktif:</span>
                <span class="fw-semibold">{{ $pendingInvoices->count() }} Faktur</span>
            </div>
        </div>
    </div>

    <!-- 3. Utang Usaha & LSP (AP) -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-optima p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-secondary small fw-medium text-uppercase">Utang LSP & Vendor</span>
                    <h4 class="fw-bold text-dark mt-2 mb-1">Rp {{ number_format($totalPayables, 0, ',', '.') }}</h4>
                    <span class="badge badge-soft-danger small">
                        <i class="bi bi-exclamation-circle me-1"></i> Kewajiban Berjalan
                    </span>
                </div>
                <div class="stat-icon bg-danger-subtle text-danger">
                    <i class="bi bi-building-down"></i>
                </div>
            </div>
            <hr class="my-2 border-light">
            <div class="small text-muted d-flex justify-content-between">
                <span>Tagihan Tertunda:</span>
                <span class="fw-semibold">{{ $pendingBills->count() }} Tagihan</span>
            </div>
        </div>
    </div>

    <!-- 4. Batch Pelatihan Berjalan -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-optima p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-secondary small fw-medium text-uppercase">Batch Pelatihan</span>
                    <h4 class="fw-bold text-dark mt-2 mb-1">{{ $activeBatchesCount }} Angkatan</h4>
                    <span class="badge badge-soft-info small">
                        <i class="bi bi-activity me-1"></i> Sedang Berjalan
                    </span>
                </div>
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
            </div>
            <hr class="my-2 border-light">
            <div class="small text-muted d-flex justify-content-between">
                <span>Skema BNSP:</span>
                <span class="fw-semibold">K3 Umum & Manajemen</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Section: Recent Batches & Quick Actions -->
<div class="row g-4 mb-4">
    <!-- Left: Batch Pelatihan Aktif -->
    <div class="col-lg-8">
        <div class="card card-optima">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar3-range text-primary me-2"></i> Batch Pelatihan & Sertifikasi Terbaru
                </h6>
                <span class="badge badge-soft-primary">{{ $recentBatches->count() }} Batch Terdaftar</span>
            </div>
            <div class="table-responsive">
                <table class="table table-optima table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Kode & Nama Batch</th>
                            <th>LSP Mitra</th>
                            <th>Tanggal</th>
                            <th>Peserta</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentBatches as $batch)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $batch->batch_code }}</div>
                                    <small class="text-muted">{{ $batch->name }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $batch->lsp->name ?? 'LSP K3 Nasional' }}</span>
                                </td>
                                <td>
                                    <small class="d-block text-dark">{{ $batch->start_date->format('d/m/Y') }}</small>
                                    <small class="text-muted">s/d {{ $batch->end_date->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $batch->actual_participants }}</span> / {{ $batch->target_participants }} pax
                                </td>
                                <td>
                                    @if ($batch->status === 'ongoing')
                                        <span class="badge badge-soft-success">Berjalan</span>
                                    @elseif ($batch->status === 'confirmed')
                                        <span class="badge badge-soft-primary">Konfirmasi</span>
                                    @else
                                        <span class="badge badge-soft-warning">{{ ucfirst($batch->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada batch pelatihan yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Outstanding Tagihan LSP & Audit Feed -->
    <div class="col-lg-4">
        <!-- Tagihan LSP -->
        <div class="card card-optima mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-clock text-danger me-2"></i> Tagihan Utang LSP
                </h6>
                <span class="badge badge-soft-danger">Jatuh Tempo</span>
            </div>
            <div class="p-3">
                @forelse ($pendingBills as $bill)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <div class="fw-semibold small text-dark">{{ $bill->bill_number }}</div>
                            <small class="text-muted">{{ $bill->lsp->name ?? 'LSP Mitra' }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-danger small">Rp {{ number_format($bill->balance_due, 0, ',', '.') }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">Jatuh tempo: {{ $bill->due_date->format('d/m/Y') }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted small my-3">Tidak ada tagihan jatuh tempo.</p>
                @endforelse
            </div>
        </div>

        <!-- Audit Trail Feed -->
        <div class="card card-optima">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-check text-success me-2"></i> Log Aktivitas Finansial
                </h6>
            </div>
            <div class="p-3">
                <ul class="list-unstyled mb-0" style="font-size: 0.82rem;">
                    @forelse ($recentAuditLogs as $log)
                        <li class="mb-2 pb-2 border-bottom">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold text-dark">{{ $log->user->name ?? 'System' }}</span>
                                <span class="text-muted" style="font-size: 0.7rem;">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-secondary small">{{ $log->description ?? $log->action }}</div>
                        </li>
                    @empty
                        <li class="text-center text-muted py-2">Belum ada aktivitas tercatat.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
