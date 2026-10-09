@extends('layouts.app')

@section('title', 'Detail Pengguna: ' . $targetUser->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-dark">{{ $targetUser->name }}</h4>
            @if($targetUser->is_active)
                <span class="badge badge-soft-success">Aktif</span>
            @else
                <span class="badge badge-soft-danger">Non-Aktif</span>
            @endif
        </div>
        <p class="text-secondary small mb-0">{{ $targetUser->email }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <a href="{{ route('admin.users.edit', $targetUser->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Pengguna
        </a>
        @if($targetUser->id !== auth()->id())
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
            <i class="bi bi-trash me-1"></i> Hapus
        </button>
        @endif
    </div>
</div>

@if($targetUser->id !== auth()->id())
<!-- Modal Delete / Request Delete -->
<div class="modal fade text-start" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.users.destroy', $targetUser->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ auth()->user()->hasRole('Super Admin') ? 'Konfirmasi Hapus Pengguna' : 'Pengajuan Hapus (Persetujuan Super Admin)' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Anda akan menghapus akun: <strong>{{ $targetUser->name }} ({{ $targetUser->email }})</strong>.</p>
                    
                    @if(auth()->user()->hasRole('Super Admin'))
                        <div class="alert alert-warning py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i> Sebagai Super Admin, tindakan ini akan langsung menghapus akun dari sistem secara permanen.
                        </div>
                    @else
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-shield-lock me-1"></i> <strong>Persetujuan Super Admin Diperlukan:</strong> Permintaan ini akan diajukan ke Super Administrator untuk diverifikasi sebelum dihapus.
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alasan Penghapusan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control form-control-sm" rows="3" placeholder="Sertakan alasan penghapusan akun..." required></textarea>
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
@endif

<div class="row g-4">
    <!-- Profil Pengguna -->
    <div class="col-lg-5">
        <div class="card card-optima shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Profil & Hak Akses</h6>
            </div>
            <div class="card-body p-3">
                <div class="text-center my-3">
                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-inline-flex align-items-center justify-content-center fs-3 mb-2" style="width: 70px; height: 70px;">
                        {{ strtoupper(substr($targetUser->name, 0, 2)) }}
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $targetUser->name }}</h5>
                    <div class="d-flex justify-content-center gap-1">
                        @foreach($targetUser->roles as $role)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>

                <hr>

                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Email:</td>
                        <td class="fw-semibold text-dark">{{ $targetUser->email }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Telepon / WA:</td>
                        <td>{{ $targetUser->phone ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Perusahaan:</td>
                        <td>{{ $targetUser->company->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Terdaftar Pada:</td>
                        <td>{{ $targetUser->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Aktivitas Pengguna (Audit Log) -->
    <div class="col-lg-7">
        <div class="card card-optima shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Aktivitas Terakhir Pengguna (Audit Trail)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Waktu</th>
                            <th>Aksi</th>
                            <th class="pe-3">Deskripsi Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($targetUser->auditLogs as $log)
                            <tr>
                                <td class="ps-3 small text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ strtoupper($log->action) }}</span>
                                </td>
                                <td class="pe-3 small text-dark">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Belum ada riwayat aktivitas tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
