@extends('layouts.app')

@section('title', 'Detail Mitra LSP: ' . $lsp->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-dark">{{ $lsp->name }}</h4>
            <span class="badge bg-light text-dark border">{{ $lsp->code }}</span>
            @if($lsp->is_active)
                <span class="badge badge-soft-success">Aktif</span>
            @else
                <span class="badge badge-soft-danger">Non-Aktif</span>
            @endif
        </div>
        <p class="text-secondary small mb-0">{{ $lsp->bnsp_license_number ? 'No. Lisensi BNSP: ' . $lsp->bnsp_license_number : 'Lembaga Sertifikasi Profesi Mitra' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('lsps.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        @can('edit-lsps')
        <a href="{{ route('lsps.edit', $lsp->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit LSP
        </a>
        @endcan
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteLspModal">
            <i class="bi bi-trash me-1"></i> Hapus
        </button>
    </div>
</div>

<!-- Modal Delete / Request Delete -->
<div class="modal fade text-start" id="deleteLspModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('lsps.destroy', $lsp->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ auth()->user()->hasRole('Super Admin') ? 'Konfirmasi Hapus Mitra LSP' : 'Pengajuan Hapus (Persetujuan Super Admin)' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Anda akan menghapus data mitra: <strong>{{ $lsp->name }} ({{ $lsp->code }})</strong>.</p>
                    
                    @if(auth()->user()->hasRole('Super Admin'))
                        <div class="alert alert-warning py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i> Sebagai Super Admin, tindakan ini akan langsung menghapus data dari sistem secara permanen.
                        </div>
                    @else
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-shield-lock me-1"></i> <strong>Persetujuan Super Admin Diperlukan:</strong> Permintaan ini akan diajukan ke Super Administrator untuk diverifikasi sebelum dihapus.
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
    <!-- Profil LSP -->
    <div class="col-lg-5">
        <div class="card card-optima shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Informasi Lembaga & Kontak</h6>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Kode:</td>
                        <td class="fw-bold text-dark">{{ $lsp->code }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lisensi BNSP:</td>
                        <td>
                            @if($lsp->bnsp_license_number)
                                <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $lsp->bnsp_license_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Narahubung (PIC):</td>
                        <td class="fw-semibold text-dark">{{ $lsp->pic_name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Telepon/WA:</td>
                        <td>{{ $lsp->pic_phone ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td>{{ $lsp->pic_email ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alamat:</td>
                        <td>{{ $lsp->address ?: '-' }}</td>
                    </tr>
                </table>

                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted fw-semibold text-uppercase">Rekening Bank:</small>
                    <div class="fw-bold text-primary mt-1">{{ $lsp->bank_name ?: '-' }} {{ $lsp->bank_account_number }}</div>
                    <small class="text-muted">a.n {{ $lsp->bank_account_holder ?: '-' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Skema Pelatihan & Batch Terkait -->
    <div class="col-lg-7">
        <div class="card card-optima shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Daftar Skema Sertifikasi yang Bermitra</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Kode & Nama Skema</th>
                            <th>Durasi</th>
                            <th class="text-end pe-3">Biaya Asesmen Standar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lsp->trainingPrograms as $prog)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('programs.show', $prog->id) }}" class="fw-semibold text-primary text-decoration-none">
                                        {{ $prog->name }}
                                    </a>
                                    <div class="text-muted small">{{ $prog->code }}</div>
                                </td>
                                <td>{{ $prog->duration_days }} Hari</td>
                                <td class="text-end pe-3 text-danger fw-semibold">
                                    Rp {{ number_format($prog->default_lsp_cost_per_participant, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Belum ada skema pelatihan yang ditautkan ke LSP ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
