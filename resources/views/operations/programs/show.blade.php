@extends('layouts.app')

@section('title', 'Detail Skema: ' . $program->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-dark">{{ $program->name }}</h4>
            <span class="badge bg-light text-dark border">{{ $program->code }}</span>
            @if($program->is_active)
                <span class="badge badge-soft-success">Aktif</span>
            @else
                <span class="badge badge-soft-danger">Non-Aktif</span>
            @endif
        </div>
        <p class="text-secondary small mb-0">{{ $program->skkni_scheme_name ?: 'Skema Sertifikasi Kompetensi Pelatihan' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('programs.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        @can('edit-programs')
        <a href="{{ route('programs.edit', $program->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Program
        </a>
        @endcan
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteProgramModal">
            <i class="bi bi-trash me-1"></i> Hapus
        </button>
    </div>
</div>

<!-- Modal Delete / Request Delete -->
<div class="modal fade text-start" id="deleteProgramModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('programs.destroy', $program->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ auth()->user()->hasRole('Super Admin') ? 'Konfirmasi Hapus Program' : 'Pengajuan Hapus (Persetujuan Super Admin)' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Anda akan menghapus data program: <strong>{{ $program->name }} ({{ $program->code }})</strong>.</p>
                    
                    @if(auth()->user()->hasRole('Super Admin'))
                        <div class="alert alert-warning py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i> Sebagai Super Admin, tindakan ini akan langsung menghapus data dari sistem secara permanen.
                        </div>
                    @else
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-shield-lock me-1"></i> <strong>Persetujuan Super Admin Diperlukan:</strong> Permintaan ini akan dikirimkan ke Super Administrator untuk diverifikasi sebelum dihapus.
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alasan Penghapusan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control form-control-sm" rows="3" placeholder="Sertakan alasan penghapusan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        {{ auth()->user()->hasRole('Super Admin') ? 'Hapus Permanen' : 'Kirim Permohonan Hapus' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Spesifikasi Program -->
    <div class="col-lg-5">
        <div class="card card-optima shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Informasi Skema Pelatihan</h6>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Kode Skema:</td>
                        <td class="fw-bold text-dark">{{ $program->code }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kategori:</td>
                        <td>
                            <span class="badge {{ $program->category === 'certification' ? 'bg-success-subtle text-success' : 'bg-info-subtle text-info' }}">
                                {{ ucfirst(str_replace('_', ' ', $program->category)) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Mitra LSP BNSP:</td>
                        <td>
                            @if($program->lsp)
                                <a href="{{ route('lsps.show', $program->lsp->id) }}" class="fw-semibold text-primary text-decoration-none">
                                    {{ $program->lsp->name }}
                                </a>
                            @else
                                <span class="text-muted">Internal (Non-LSP)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Durasi:</td>
                        <td class="fw-semibold text-dark">{{ $program->duration_days }} Hari</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tarif Standar / Peserta:</td>
                        <td class="fw-bold text-primary">
                            Rp {{ number_format($program->default_price_per_participant, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Biaya LSP Standar:</td>
                        <td class="fw-bold text-danger">
                            Rp {{ number_format($program->default_lsp_cost_per_participant, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Estimasi Gross Margin:</td>
                        <td class="fw-bold text-success">
                            @php
                                $margin = $program->default_price_per_participant > 0
                                    ? (($program->default_price_per_participant - $program->default_lsp_cost_per_participant) / $program->default_price_per_participant) * 100
                                    : 0;
                            @endphp
                            {{ number_format($margin, 1) }}%
                        </td>
                    </tr>
                </table>

                @if($program->description)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted fw-semibold">Silabus / Deskripsi:</small>
                        <p class="small text-secondary mt-1 mb-0">{{ $program->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Histori Angkatan (Batches) -->
    <div class="col-lg-7">
        <div class="card card-optima shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">Angkatan / Batch Terkait Skema Ini</h6>
                @can('create-batches')
                <a href="{{ route('batches.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg me-1"></i> Buka Batch Baru
                </a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Kode Batch</th>
                            <th>Jadwal Pelaksanaan</th>
                            <th>Kota & Venue</th>
                            <th class="text-center">Peserta</th>
                            <th class="text-center pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($program->batches as $batch)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('batches.show', $batch->id) }}" class="fw-bold text-primary text-decoration-none">
                                        {{ $batch->batch_code }}
                                    </a>
                                </td>
                                <td>{{ date('d/m/Y', strtotime($batch->start_date)) }} - {{ date('d/m/Y', strtotime($batch->end_date)) }}</td>
                                <td>{{ $batch->venue_city ?: 'Online' }}</td>
                                <td class="text-center">{{ $batch->actual_participants }} / {{ $batch->target_participants }}</td>
                                <td class="text-center pe-3">
                                    <span class="badge bg-light text-dark border">{{ ucfirst($batch->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada batch pelatihan yang diselenggarakan untuk skema ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
