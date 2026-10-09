@extends('layouts.app')

@section('title', 'Mitra Lembaga Sertifikasi Profesi (LSP)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-award text-primary me-2"></i>Mitra LSP BNSP
        </h4>
        <p class="text-secondary small mb-0">Manajemen Lembaga Sertifikasi Profesi mitra pelaksana uji kompetensi BNSP</p>
    </div>
    @can('create-lsps')
    <a href="{{ route('lsps.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Mitra LSP
    </a>
    @endcan
</div>

<!-- Filter Card -->
<div class="card card-optima mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('lsps.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-8">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama LSP, kode, no lisensi, atau kontak PIC..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Cari
                </button>
                <a href="{{ route('lsps.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th class="ps-3">Kode & Nama LSP</th>
                    <th>No. Lisensi BNSP</th>
                    <th>Kontak PIC</th>
                    <th>Rekening Pembayaran</th>
                    <th class="text-center">Skema</th>
                    <th class="text-center">Batch</th>
                    <th class="text-center">Status</th>
                    <th class="text-center pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lsps as $lsp)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-dark border me-1">{{ $lsp->code }}</span>
                            <a href="{{ route('lsps.show', $lsp->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $lsp->name }}
                            </a>
                        </td>
                        <td>
                            @if($lsp->bnsp_license_number)
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    {{ $lsp->bnsp_license_number }}
                                </span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $lsp->pic_name ?: '-' }}</div>
                            <small class="text-muted">{{ $lsp->pic_phone ?: $lsp->pic_email }}</small>
                        </td>
                        <td>
                            @if($lsp->bank_name && $lsp->bank_account_number)
                                <div class="small fw-semibold text-dark">{{ $lsp->bank_name }} - {{ $lsp->bank_account_number }}</div>
                                <small class="text-muted">a.n {{ $lsp->bank_account_holder }}</small>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border">{{ $lsp->training_programs_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border">{{ $lsp->training_batches_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($lsp->is_active)
                                <span class="badge badge-soft-success">Aktif</span>
                            @else
                                <span class="badge badge-soft-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('lsps.show', $lsp->id) }}" class="btn btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit-lsps')
                                <a href="{{ route('lsps.edit', $lsp->id) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteLspModal{{ $lsp->id }}" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <!-- Modal Delete / Request Delete -->
                            <div class="modal fade text-start" id="deleteLspModal{{ $lsp->id }}" tabindex="-1" aria-hidden="true">
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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada mitra LSP yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lsps->hasPages())
    <div class="p-3 border-top">
        {{ $lsps->links() }}
    </div>
    @endif
</div>
@endsection
