@extends('layouts.app')

@section('title', 'Audit Trail & Log Aktivitas')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-shield-check text-primary me-2"></i>Audit Trail & Log Aktivitas Sistem
            </h4>
            <p class="text-secondary small mb-0">Pemantauan transparansi integritas data, jejak otorisasi transaksi, dan riwayat modifikasi pengguna.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.audit_logs.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari deskripsi, IP, modul..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-2">
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">-- Semua Pengguna --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select name="action" class="form-select form-select-sm">
                        <option value="">-- Semua Aksi --</option>
                        @foreach ($actions as $act)
                            <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                                {{ ucfirst($act) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" title="Tanggal Mulai">
                </div>
                <div class="col-12 col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" title="Tanggal Selesai">
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Terapkan Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if (request()->hasAny(['search', 'user_id', 'action', 'start_date', 'end_date']))
                        <a href="{{ route('admin.audit_logs.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="card card-optima shadow-sm">
        <div class="table-responsive">
            <table class="table table-optima table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 16%;">Waktu & Tanggal</th>
                        <th style="width: 15%;">Pengguna</th>
                        <th style="width: 12%;">Aksi / Event</th>
                        <th style="width: 15%;">Modul / Data</th>
                        <th style="width: 25%;">Deskripsi Aktivitas</th>
                        <th style="width: 11%;">Alamat IP</th>
                        <th class="text-center pe-3" style="width: 6%;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $log->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                @if ($log->user)
                                    <div class="fw-semibold text-dark">{{ $log->user->name }}</div>
                                    <small class="text-muted">{{ $log->user->roles->first()?->name ?? 'User' }}</small>
                                @else
                                    <span class="text-muted fst-italic">Sistem / Tamu</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $actionClass = match($log->action) {
                                        'login', 'logout' => 'badge-soft-info',
                                        'create', 'save_budget' => 'badge-soft-primary',
                                        'update' => 'badge-soft-warning',
                                        'delete' => 'badge-soft-danger',
                                        'approve', 'approve_budget', 'post_journal' => 'badge-soft-success',
                                        default => 'badge bg-light text-dark border'
                                    };
                                @endphp
                                <span class="badge {{ $actionClass }} text-uppercase px-2 py-1">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>
                            <td>
                                @if ($log->auditable_type)
                                    <span class="badge bg-light text-dark border">
                                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark">{{ $log->description ?? '-' }}</div>
                            </td>
                            <td>
                                <small class="text-muted font-monospace">{{ $log->ip_address ?? '127.0.0.1' }}</small>
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('admin.audit_logs.show', $log->id) }}" class="btn btn-sm btn-outline-secondary" title="Rincian Nilai">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-shield-slash fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada log aktivitas tercatat yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
