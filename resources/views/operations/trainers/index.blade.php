@extends('layouts.app')

@section('title', 'Instruktur & Trainer Pelatihan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-person-video3 text-primary me-2"></i>Instruktur Pelatihan
        </h4>
        <p class="text-secondary small mb-0">Manajemen profil pengajar, spesialisasi kompetensi, honorarium harian, dan rekening</p>
    </div>
    @can('create-trainers')
    <a href="{{ route('trainers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Instruktur Baru
    </a>
    @endcan
</div>

<!-- Filter Card -->
<div class="card card-optima mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('trainers.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-8">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama instruktur, kode, spesialisasi, atau nomor telepon..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Cari
                </button>
                <a href="{{ route('trainers.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th class="ps-3">Kode & Nama Instruktur</th>
                    <th>Bidang Spesialisasi</th>
                    <th>Kontak (HP/Email)</th>
                    <th class="text-end">Tarif Honor Default / Hari</th>
                    <th>Rekening Bank</th>
                    <th class="text-center">Jml Batch</th>
                    <th class="text-center">Status</th>
                    <th class="text-center pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trainers as $trainer)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-dark border me-1">{{ $trainer->code }}</span>
                            <a href="{{ route('trainers.show', $trainer->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $trainer->name }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info">
                                {{ $trainer->specialization ?: 'Umum / Soft Skills' }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $trainer->phone ?: '-' }}</div>
                            <small class="text-muted">{{ $trainer->email ?: '-' }}</small>
                        </td>
                        <td class="text-end fw-semibold text-primary">
                            Rp {{ number_format($trainer->default_daily_rate, 0, ',', '.') }}
                        </td>
                        <td>
                            @if($trainer->bank_name && $trainer->bank_account_number)
                                <div class="small fw-semibold text-dark">{{ $trainer->bank_name }} - {{ $trainer->bank_account_number }}</div>
                                <small class="text-muted">a.n {{ $trainer->bank_account_holder }}</small>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border">{{ $trainer->training_batches_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($trainer->is_active)
                                <span class="badge badge-soft-success">Aktif</span>
                            @else
                                <span class="badge badge-soft-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('trainers.show', $trainer->id) }}" class="btn btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit-trainers')
                                <a href="{{ route('trainers.edit', $trainer->id) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteTrainerModal{{ $trainer->id }}" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <!-- Modal Delete / Request Delete -->
                            <div class="modal fade text-start" id="deleteTrainerModal{{ $trainer->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form method="POST" action="{{ route('trainers.destroy', $trainer->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-danger">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    {{ auth()->user()->hasRole('Super Admin') ? 'Konfirmasi Hapus Instruktur' : 'Pengajuan Hapus (Persetujuan Super Admin)' }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="mb-2">Anda akan menghapus data instruktur: <strong>{{ $trainer->name }} ({{ $trainer->code }})</strong>.</p>
                                                
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
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada instruktur yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($trainers->hasPages())
    <div class="p-3 border-top">
        {{ $trainers->links() }}
    </div>
    @endif
</div>
@endsection
