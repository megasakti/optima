@extends('layouts.app')

@section('title', 'Rencana Anggaran Biaya (RAB) Pelatihan')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-pie-chart text-primary me-2"></i>Rencana Anggaran Biaya (RAB) Pelatihan
            </h4>
            <p class="text-secondary small mb-0">Baseline penetapan target omset, estimasi biaya langsung (HPP), dan proyeksi laba kotor per batch.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('batches.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-mortarboard me-1"></i> Daftar Batch
            </a>
            @can('view-financial-reports')
            <a href="{{ route('reports.batch_profitability') }}" class="btn btn-outline-primary">
                <i class="bi bi-graph-up-arrow me-1"></i> Analisis Realisasi & Margin
            </a>
            @endcan
            @can('create-budgets')
            <a href="{{ route('budgets.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Susun RAB Baru
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

    <!-- Filter Card -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('budgets.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-8">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Semua Status Persetujuan RAB --</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Penyusunan)</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted (Menunggu Approval)</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (Disetujui & Terkunci)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (request()->has('status'))
                        <a href="{{ route('budgets.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Budgets Table Card -->
    <div class="card card-optima shadow-sm">
        <div class="table-responsive">
            <table class="table table-optima table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">No. Dokumen RAB</th>
                        <th>Batch / Program</th>
                        <th class="text-center">Target Pax</th>
                        <th class="text-end">Target Omset</th>
                        <th class="text-end">Anggaran Biaya (HPP)</th>
                        <th class="text-end">Estimasi Laba Kotor</th>
                        <th class="text-center">Margin (%)</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($budgets as $budget)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">
                                <a href="{{ route('budgets.show', $budget->id) }}" class="text-decoration-none">
                                    {{ $budget->budget_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <a href="{{ route('batches.show', $budget->batch_id) }}" class="text-decoration-none text-dark">
                                        {{ $budget->batch->batch_code }}
                                    </a>
                                </div>
                                <small class="text-muted">{{ $budget->batch->program->name ?? '-' }}</small>
                            </td>
                            <td class="text-center fw-semibold">
                                {{ number_format($budget->target_participants, 0) }} Pax
                            </td>
                            <td class="text-end fw-semibold text-success">
                                Rp {{ number_format($budget->total_revenue_budget, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold text-danger">
                                Rp {{ number_format($budget->total_cost_budget, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold text-primary">
                                Rp {{ number_format($budget->projected_gross_margin, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $budget->projected_margin_percentage >= 30 ? 'bg-success-subtle text-success border border-success' : ($budget->projected_margin_percentage >= 15 ? 'bg-warning-subtle text-warning border border-warning' : 'bg-danger-subtle text-danger border border-danger') }} fw-bold px-2 py-1">
                                    {{ number_format($budget->projected_margin_percentage, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($budget->status === 'approved')
                                    <span class="badge badge-soft-success px-2 py-1"><i class="bi bi-check2-circle me-1"></i>Approved</span>
                                @elseif ($budget->status === 'submitted')
                                    <span class="badge badge-soft-warning px-2 py-1"><i class="bi bi-clock-history me-1"></i>Submitted</span>
                                @elseif ($budget->status === 'rejected')
                                    <span class="badge badge-soft-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                @else
                                    <span class="badge badge-soft-secondary px-2 py-1"><i class="bi bi-pencil me-1"></i>Draft</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('budgets.show', $budget->id) }}" class="btn btn-outline-secondary" title="Detail RAB">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('budgets.pdf', $budget->id) }}" target="_blank" class="btn btn-outline-danger" title="Cetak PDF">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-pie-chart fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada dokumen RAB yang tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($budgets->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $budgets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
