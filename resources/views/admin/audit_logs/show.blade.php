@extends('layouts.app')

@section('title', 'Rincian Log Audit #' . $log->id)

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.audit_logs.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold text-dark mb-0">Rincian Log Audit #{{ $log->id }}</h4>
            </div>
            <p class="text-secondary small mb-0">Detail rekaman jejak audit perubahan data pada {{ $log->created_at->format('d/m/Y H:i:s') }}.</p>
        </div>
    </div>

    <!-- Meta Card -->
    <div class="card card-optima shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="bi bi-info-circle me-2"></i>Informasi Transaksi & Pengguna
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-sm-3">
                    <span class="text-muted small">Waktu Pencatatan:</span>
                    <div class="fw-semibold text-dark">{{ $log->created_at->format('d F Y, H:i:s') }}</div>
                </div>
                <div class="col-sm-3">
                    <span class="text-muted small">Pengguna (User):</span>
                    <div class="fw-semibold text-dark">{{ $log->user->name ?? 'Sistem' }}</div>
                    <small class="text-muted">{{ $log->user->email ?? '-' }}</small>
                </div>
                <div class="col-sm-3">
                    <span class="text-muted small">Jenis Aksi:</span>
                    <div>
                        <span class="badge bg-primary text-uppercase">{{ str_replace('_', ' ', $log->action) }}</span>
                    </div>
                </div>
                <div class="col-sm-3">
                    <span class="text-muted small">Alamat IP & Klien:</span>
                    <div class="fw-semibold text-dark font-monospace">{{ $log->ip_address ?? '127.0.0.1' }}</div>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small">Model / Data Terkait:</span>
                    <div class="fw-semibold text-dark">
                        {{ $log->auditable_type ?? 'Tidak spesifik' }} #{{ $log->auditable_id ?? '-' }}
                    </div>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small">Deskripsi:</span>
                    <div class="fw-semibold text-dark">{{ $log->description ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Values Diff Row -->
    <div class="row g-4 mb-4">
        <!-- Old Values -->
        <div class="col-12 col-lg-6">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Nilai Sebelum Perubahan (Old Values)
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if ($log->old_values && count($log->old_values) > 0)
                        <pre class="bg-light p-3 rounded border font-monospace small mb-0" style="max-height: 380px; overflow-y: auto;"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    @else
                        <div class="text-muted p-4 text-center">
                            <i class="bi bi-dash-circle fs-3 d-block mb-1"></i>
                            Tidak ada nilai lama (Entri Baru / Aksi Tanpa Nilai Sebelumnya)
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- New Values -->
        <div class="col-12 col-lg-6">
            <div class="card card-optima shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-success">
                        <i class="bi bi-check-circle me-2"></i>Nilai Sesudah Perubahan (New Values)
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if ($log->new_values && count($log->new_values) > 0)
                        <pre class="bg-light p-3 rounded border font-monospace small mb-0" style="max-height: 380px; overflow-y: auto;"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    @else
                        <div class="text-muted p-4 text-center">
                            <i class="bi bi-dash-circle fs-3 d-block mb-1"></i>
                            Tidak ada payload nilai baru tercatat.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
