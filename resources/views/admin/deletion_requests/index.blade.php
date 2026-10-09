@extends('layouts.app')

@section('title', 'Persetujuan Hapus Data (Approval Super Admin)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-shield-check text-danger me-2"></i>Persetujuan Hapus Data (Deletion Approvals)
        </h4>
        <p class="text-secondary small mb-0">Otorisasi terpusat oleh Super Administrator untuk seluruh pengajuan penghapusan data master dan operasional</p>
    </div>
    @if($pendingCount > 0)
        <span class="badge bg-danger fs-6 px-3 py-2">
            <i class="bi bi-bell-fill me-1"></i> {{ $pendingCount }} Permintaan Menunggu Persetujuan
        </span>
    @endif
</div>

<!-- Filter Card -->
<div class="card card-optima mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.deletion_requests.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari data yang diajukan atau alasan..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan (Pending)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.deletion_requests.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th class="ps-3">Waktu Pengajuan</th>
                    <th>Pemohon</th>
                    <th>Data Yang Diajukan Hapus</th>
                    <th>Alasan Pengajuan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center pe-3">Aksi Otorisasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr class="{{ $req->status === 'pending' ? 'table-warning bg-opacity-10' : '' }}">
                        <td class="ps-3 small text-muted">
                            {{ $req->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $req->requester->name ?? 'User' }}</div>
                            <small class="text-muted">{{ $req->requester->email ?? '-' }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $req->model_label }}</div>
                            <small class="text-muted font-monospace">{{ class_basename($req->model_type) }} #{{ $req->model_id }}</small>
                        </td>
                        <td>
                            <div class="small text-secondary">{{ $req->reason }}</div>
                            @if($req->status === 'rejected' && $req->rejection_reason)
                                <div class="small text-danger mt-1">
                                    <strong>Catatan Penolakan:</strong> {{ $req->rejection_reason }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($req->status === 'pending')
                                <span class="badge bg-warning text-dark border border-warning">Menunggu Otorisasi</span>
                            @elseif($req->status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Disetujui & Dihapus</span>
                                <div class="text-muted" style="font-size: 0.72rem;">Oleh: {{ $req->approver->name ?? 'Admin' }}</div>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Ditolak</span>
                                <div class="text-muted" style="font-size: 0.72rem;">Oleh: {{ $req->approver->name ?? 'Admin' }}</div>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            @if($req->status === 'pending')
                                <div class="btn-group btn-group-sm">
                                    <!-- Tombol Approve Form -->
                                    <form method="POST" action="{{ route('admin.deletion_requests.approve', $req->id) }}" onsubmit="return confirm('Apakah Anda yakin menyetujui penghapusan data ini secara permanen?');" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Setujui & Hapus">
                                            <i class="bi bi-check-lg me-1"></i> Setujui
                                        </button>
                                    </form>

                                    <!-- Tombol Reject Modal -->
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}" title="Tolak Permohonan">
                                        <i class="bi bi-x-lg me-1"></i> Tolak
                                    </button>
                                </div>

                                <!-- Modal Reject -->
                                <div class="modal fade text-start" id="rejectModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <form method="POST" action="{{ route('admin.deletion_requests.reject', $req->id) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold text-danger">Tolak Pengajuan Hapus Data</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-2">Anda akan menolak penghapusan data: <strong>{{ $req->model_label }}</strong>.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control form-control-sm" rows="3" placeholder="Sertakan alasan mengapa permohonan ini ditolak..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-sm btn-danger">Tolak Permohonan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">- Selesai -</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada permohonan penghapusan data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div class="p-3 border-top">
        {{ $requests->links() }}
    </div>
    @endif
</div>
@endsection
