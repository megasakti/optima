@extends('layouts.app')

@section('title', 'Manajemen Pengguna & PIC')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-people text-primary me-2"></i>Manajemen Pengguna & PIC
        </h4>
        <p class="text-secondary small mb-0">Kelola akun staf, koordinator PIC angkatan, finance manager, dan penugasan peran (Role)</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Tambah Pengguna Baru
    </a>
</div>

<!-- Filter Card -->
<div class="card card-optima mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama pengguna, email, atau no HP..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">Semua Peran (Role)</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th class="ps-3">Nama Pengguna</th>
                    <th>Email</th>
                    <th>Nomor Telepon</th>
                    <th>Peran / Hak Akses</th>
                    <th class="text-center">Status</th>
                    <th class="text-center pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 13px;">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.users.show', $u->id) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $u->name }}
                                    </a>
                                    @if($u->id === auth()->id())
                                        <span class="badge bg-secondary-subtle text-secondary small ms-1">Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone ?: '-' }}</td>
                        <td>
                            @foreach($u->roles as $r)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ $r->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="text-center">
                            @if($u->is_active)
                                <span class="badge badge-soft-success">Aktif</span>
                            @else
                                <span class="badge badge-soft-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.show', $u->id) }}" class="btn btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $u->id }}" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>

                            @if($u->id !== auth()->id())
                            <!-- Modal Delete / Request Delete -->
                            <div class="modal fade text-start" id="deleteUserModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}">
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
                                                <p class="mb-2">Anda akan menghapus akun pengguna: <strong>{{ $u->name }} ({{ $u->email }})</strong>.</p>
                                                
                                                @if(auth()->user()->hasRole('Super Admin'))
                                                    <div class="alert alert-warning py-2 small mb-3">
                                                        <i class="bi bi-info-circle me-1"></i> Sebagai Super Admin, tindakan ini akan langsung menghapus akun dari sistem.
                                                    </div>
                                                @else
                                                    <div class="alert alert-info py-2 small mb-3">
                                                        <i class="bi bi-shield-lock me-1"></i> <strong>Persetujuan Super Admin Diperlukan:</strong> Permintaan ini akan diajukan ke Super Administrator untuk diverifikasi.
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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada pengguna yang sesuai kriteria pencarian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="p-3 border-top">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
