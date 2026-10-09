@extends('layouts.app')

@section('title', 'Skema & Program Pelatihan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-journal-bookmark text-primary me-2"></i>Skema & Program Pelatihan
        </h4>
        <p class="text-secondary small mb-0">Manajemen paket pelatihan dan skema sertifikasi kompetensi BNSP</p>
    </div>
    @can('create-programs')
    <a href="{{ route('programs.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Program Baru
    </a>
    @endcan
</div>

<!-- Filter Card -->
<div class="card card-optima mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('programs.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari kode atau nama skema..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    <option value="certification" {{ request('category') === 'certification' ? 'selected' : '' }}>Sertifikasi BNSP</option>
                    <option value="non_certification" {{ request('category') === 'non_certification' ? 'selected' : '' }}>Non-Sertifikasi</option>
                    <option value="inhouse" {{ request('category') === 'inhouse' ? 'selected' : '' }}>In-House Training</option>
                    <option value="consulting" {{ request('category') === 'consulting' ? 'selected' : '' }}>Konsultasi</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="lsp_id" class="form-select form-select-sm">
                    <option value="">Semua Mitra LSP</option>
                    @foreach($lsps as $lsp)
                        <option value="{{ $lsp->id }}" {{ request('lsp_id') == $lsp->id ? 'selected' : '' }}>{{ $lsp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('programs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card card-optima shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Kode & Nama Program</th>
                    <th>Mitra LSP / Skema BNSP</th>
                    <th>Kategori</th>
                    <th>Durasi</th>
                    <th class="text-end">Tarif Default / Peserta</th>
                    <th class="text-end">Biaya LSP / Peserta</th>
                    <th class="text-center">Jml Batch</th>
                    <th class="text-center">Status</th>
                    <th class="text-center pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-dark border me-1">{{ $program->code }}</span>
                            <a href="{{ route('programs.show', $program->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $program->name }}
                            </a>
                        </td>
                        <td>
                            @if($program->lsp)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ $program->lsp->name }}
                                </span>
                            @else
                                <span class="text-muted small">Internal / Tanpa LSP</span>
                            @endif
                            @if($program->skkni_scheme_name)
                                <div class="text-muted small">{{ $program->skkni_scheme_name }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $program->category === 'certification' ? 'bg-success-subtle text-success' : 'bg-info-subtle text-info' }}">
                                {{ ucfirst(str_replace('_', ' ', $program->category)) }}
                            </span>
                        </td>
                        <td>{{ $program->duration_days }} Hari</td>
                        <td class="text-end fw-semibold text-dark">
                            Rp {{ number_format($program->default_price_per_participant, 0, ',', '.') }}
                        </td>
                        <td class="text-end text-danger fw-semibold">
                            Rp {{ number_format($program->default_lsp_cost_per_participant, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border">{{ $program->batches_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($program->is_active)
                                <span class="badge badge-soft-success">Aktif</span>
                            @else
                                <span class="badge badge-soft-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('programs.show', $program->id) }}" class="btn btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit-programs')
                                <a href="{{ route('programs.edit', $program->id) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProgramModal{{ $program->id }}" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <!-- Modal Delete / Request Delete -->
                            <div class="modal fade text-start" id="deleteProgramModal{{ $program->id }}" tabindex="-1" aria-hidden="true">
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
                                                        <i class="bi bi-info-circle me-1"></i> Sebagai Super Admin, tindakan ini akan langsung menghapus data dari sistem.
                                                    </div>
                                                @else
                                                    <div class="alert alert-info py-2 small mb-3">
                                                        <i class="bi bi-shield-lock me-1"></i> <strong>Persetujuan Super Admin Diperlukan:</strong> Permintaan ini akan diteruskan ke Super Administrator untuk diverifikasi sebelum dihapus.
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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Belum ada skema/program pelatihan yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($programs->hasPages())
    <div class="p-3 border-top">
        {{ $programs->links() }}
    </div>
    @endif
</div>
@endsection
