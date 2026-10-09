@extends('layouts.app')

@section('title', 'Daftar Batch Pelatihan & Sertifikasi BNSP')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-mortarboard text-primary me-2"></i>Angkatan / Batch Pelatihan & Sertifikasi BNSP
            </h4>
            <p class="text-secondary small mb-0">Kelola jadwal pelaksanaan, target peserta, mitra LSP, serta pengendalian anggaran RAB per angkatan.</p>
        </div>
        <div class="d-flex gap-2">
            @can('view-budgets')
            <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-pie-chart me-1"></i> Rencana Anggaran (RAB)
            </a>
            @endcan
            @can('view-financial-reports')
            <a href="{{ route('reports.batch_profitability') }}" class="btn btn-outline-primary">
                <i class="bi bi-graph-up-arrow me-1"></i> Analisis Profit Margin
            </a>
            @endcan
            @can('create-batches')
            <a href="{{ route('batches.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Buka Batch Baru
            </a>
            @endcan
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('batches.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari Kode Batch, Nama Program..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Semua Status Pelaksanaan --</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Perencanaan (Draft)</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Terkonfirmasi (Confirmed)</option>
                        <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Sedang Berjalan (Ongoing)</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai Pelatihan (Completed)</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Selesai & Tutup Buku (Closed)</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('batches.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Batches Table Card -->
    <div class="card card-optima shadow-sm">
        <div class="table-responsive">
            <table class="table table-optima table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Kode Batch</th>
                        <th>Program / Skema BNSP</th>
                        <th>Jadwal Pelatihan</th>
                        <th>Asesmen BNSP</th>
                        <th class="text-center">Peserta</th>
                        <th class="text-end">Harga / Pax</th>
                        <th class="text-center">Status RAB</th>
                        <th class="text-center">Status Batch</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">
                                <a href="{{ route('batches.show', $batch->id) }}" class="text-decoration-none">
                                    {{ $batch->batch_code }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $batch->program->name }}</div>
                                <small class="text-muted">{{ $batch->lsp->name ?? 'LSP Mitra' }}</small>
                            </td>
                            <td>
                                <div>{{ $batch->start_date->format('d/m/Y') }}</div>
                                <small class="text-muted">s/d {{ $batch->end_date->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                @if ($batch->assessment_date)
                                    <span class="badge bg-primary-subtle text-primary border">
                                        {{ $batch->assessment_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">{{ $batch->actual_participants }}</span>
                                <small class="text-muted">/ {{ $batch->target_participants }} Pax</small>
                            </td>
                            <td class="text-end fw-semibold">
                                Rp {{ number_format($batch->price_per_participant, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if ($batch->budget)
                                    @if ($batch->budget->status === 'approved')
                                        <span class="badge bg-success-subtle text-success border">
                                            RAB Approved ({{ number_format($batch->budget->projected_margin_percentage, 0) }}%)
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border">
                                            RAB {{ ucfirst($batch->budget->status) }}
                                        </span>
                                    @endif
                                @else
                                    <a href="{{ route('budgets.create', ['batch_id' => $batch->id]) }}" class="badge bg-light text-primary border text-decoration-none">
                                        <i class="bi bi-plus"></i> Buat RAB
                                    </a>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($batch->status === 'confirmed')
                                    <span class="badge badge-soft-primary px-2 py-1">Confirmed</span>
                                @elseif ($batch->status === 'ongoing')
                                    <span class="badge badge-soft-warning px-2 py-1">Ongoing</span>
                                @elseif ($batch->status === 'completed' || $batch->status === 'closed')
                                    <span class="badge badge-soft-success px-2 py-1">{{ ucfirst($batch->status) }}</span>
                                @else
                                    <span class="badge bg-secondary text-white px-2 py-1">{{ ucfirst($batch->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('batches.show', $batch->id) }}" class="btn btn-outline-secondary" title="Detail Batch">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('reports.batch_profitability.detail', $batch->id) }}" class="btn btn-outline-success" title="Analisis Profit">
                                        <i class="bi bi-graph-up"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-mortarboard fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada batch pelatihan yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($batches->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
