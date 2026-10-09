@extends('layouts.app')

@section('title', 'Detail Instruktur: ' . $trainer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-dark">{{ $trainer->name }}</h4>
            <span class="badge bg-light text-dark border">{{ $trainer->code }}</span>
            @if($trainer->is_active)
                <span class="badge badge-soft-success">Aktif</span>
            @else
                <span class="badge badge-soft-danger">Non-Aktif</span>
            @endif
        </div>
        <p class="text-secondary small mb-0">{{ $trainer->specialization ? 'Spesialisasi: ' . $trainer->specialization : 'Instruktur / Trainer Pelatihan' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('trainers.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        @can('edit-trainers')
        <a href="{{ route('trainers.edit', $trainer->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Instruktur
        </a>
        @endcan
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTrainerModal">
            <i class="bi bi-trash me-1"></i> Hapus
        </button>
    </div>
</div>

<!-- Modal Delete / Request Delete -->
<div class="modal fade text-start" id="deleteTrainerModal" tabindex="-1" aria-hidden="true">
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

<div class="row g-4">
    <!-- Profil Instruktur -->
    <div class="col-lg-5">
        <div class="card card-optima shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Informasi Pribadi & Kontak</h6>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Kode:</td>
                        <td class="fw-bold text-dark">{{ $trainer->code }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Spesialisasi:</td>
                        <td>
                            <span class="badge bg-info-subtle text-info">{{ $trainer->specialization ?: 'Umum' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Honor Default:</td>
                        <td class="fw-bold text-primary">
                            Rp {{ number_format($trainer->default_daily_rate, 0, ',', '.') }} / Hari
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Telepon/WA:</td>
                        <td class="fw-semibold text-dark">{{ $trainer->phone ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td>{{ $trainer->email ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">NPWP:</td>
                        <td>{{ $trainer->tax_id ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">NIK KTP:</td>
                        <td>{{ $trainer->id_card_number ?: '-' }}</td>
                    </tr>
                </table>

                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted fw-semibold text-uppercase">Rekening Transfer Honor:</small>
                    <div class="fw-bold text-primary mt-1">{{ $trainer->bank_name ?: '-' }} {{ $trainer->bank_account_number }}</div>
                    <small class="text-muted">a.n {{ $trainer->bank_account_holder ?: '-' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Angkatan Pelatihan -->
    <div class="col-lg-7">
        <div class="card card-optima shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Riwayat Angkatan yang Diampu</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Kode Batch</th>
                            <th>Periode</th>
                            <th>Lokasi</th>
                            <th class="text-center pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trainer->trainingBatches as $batch)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('batches.show', $batch->id) }}" class="fw-semibold text-primary text-decoration-none">
                                        {{ $batch->batch_code }}
                                    </a>
                                </td>
                                <td>{{ date('d/m/Y', strtotime($batch->start_date)) }} - {{ date('d/m/Y', strtotime($batch->end_date)) }}</td>
                                <td>{{ $batch->venue_city ?: 'Online' }}</td>
                                <td class="text-center pe-3">
                                    <span class="badge bg-light text-dark border">{{ ucfirst($batch->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada histori angkatan pelatihan untuk instruktur ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
