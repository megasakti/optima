@extends('layouts.app')

@section('title', 'Rincian Peserta: ' . $participant->name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-dark">{{ $participant->name }}</h4>
            <span class="{{ $participant->category_badge }}">
                {{ $participant->category_label }}
            </span>
            <span class="{{ $participant->payment_status_badge }}">
                {{ $participant->payment_status_label }}
            </span>
            <span class="{{ $participant->certificate_status_badge }}">
                {{ $participant->certificate_status_label }}
            </span>
        </div>
        <p class="text-secondary small mb-0">
            Terdaftar pada {{ $participant->created_at->format('d M Y, H:i') }} WIB
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('participants.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        @can('edit-participants')
        <a href="{{ route('participants.edit', $participant->id) }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-pencil me-1"></i> Edit Peserta
        </a>
        @endcan
        @can('delete-participants')
        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class="bi bi-trash me-1"></i> Hapus
        </button>
        @endcan
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Profil & Klien -->
    <div class="col-lg-4">
        <!-- Kartu Identitas Peserta -->
        <div class="card card-optima border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-badge text-primary me-2"></i>Informasi Kontak Peserta
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="text-center py-3 border-bottom mb-3">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 64px; height: 64px; font-size: 28px;">
                        <i class="bi bi-person"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $participant->name }}</h5>
                    <div class="text-muted small">{{ $participant->display_company_name }}</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Nomor Telepon / WhatsApp:</small>
                    @if($participant->phone)
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $participant->phone);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                        @endphp
                        <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                            <span class="font-monospace fw-semibold">{{ $participant->phone }}</span>
                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-sm btn-success px-2 py-1">
                                <i class="bi bi-whatsapp me-1"></i> Chat WA
                            </a>
                        </div>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Email:</small>
                    @if($participant->email)
                        <a href="mailto:{{ $participant->email }}" class="text-primary text-decoration-none fw-medium">
                            <i class="bi bi-envelope me-1"></i>{{ $participant->email }}
                        </a>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </div>

                <div>
                    <small class="text-muted d-block mb-1">Sumber Pendaftaran (Leads):</small>
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-broadcast me-1 text-primary"></i>{{ $participant->source_label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Kartu Relasi Klien / Perusahaan -->
        <div class="card card-optima border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-building text-primary me-2"></i>Data Klien / Perusahaan
                </h6>
            </div>
            <div class="card-body p-3">
                @if($participant->customer)
                    <div class="alert alert-primary-subtle border-primary-subtle p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0 text-primary">{{ $participant->customer->name }}</h6>
                            <span class="badge bg-primary">{{ $participant->customer->code }}</span>
                        </div>
                        <small class="text-muted">Tipe Klien: {{ ucfirst($participant->customer->customer_type) }}</small>
                    </div>

                    <ul class="list-group list-group-flush small">
                        @if($participant->customer->pic_name)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">PIC Perusahaan:</span>
                            <span class="fw-semibold text-dark">{{ $participant->customer->pic_name }}</span>
                        </li>
                        @endif
                        @if($participant->customer->pic_phone)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Kontak PIC:</span>
                            <span class="font-monospace">{{ $participant->customer->pic_phone }}</span>
                        </li>
                        @endif
                        @if($participant->customer->tax_id)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">NPWP Klien:</span>
                            <span class="font-monospace">{{ $participant->customer->tax_id }}</span>
                        </li>
                        @endif
                        @if($participant->customer->city)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Kota:</span>
                            <span>{{ $participant->customer->city }}</span>
                        </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Termin Pembayaran:</span>
                            <span>{{ $participant->customer->payment_term_days }} Hari</span>
                        </li>
                    </ul>
                @else
                    <div class="p-3 bg-light rounded text-center">
                        <i class="bi bi-building-slash text-muted fs-3 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">{{ $participant->company_name ?: 'Peserta Mandiri / Non-Perusahaan' }}</h6>
                        <small class="text-muted">Peserta tidak terhubung ke master klien terdaftar.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rincian Pelatihan & Status Finansial -->
    <div class="col-lg-8">
        <!-- Kartu Rincian Pelatihan -->
        <div class="card card-optima border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-bookmark text-primary me-2"></i>Rincian Program & Pelaksanaan
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-7">
                        <small class="text-muted d-block mb-1">Jenis & Judul Pelatihan</small>
                        <h5 class="fw-bold text-dark mb-1">{{ $participant->training_title }}</h5>
                        @if($participant->trainingProgram)
                            <div class="text-muted small">
                                Skema Master: <strong>[{{ $participant->trainingProgram->code }}] {{ $participant->trainingProgram->name }}</strong>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <small class="text-muted d-block mb-1">Model Pelaksanaan</small>
                        <span class="{{ $participant->category_badge }} fs-6">
                            {{ $participant->category_label }}
                        </span>
                    </div>
                </div>

                <div class="row g-3 p-3 bg-light rounded-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-1"><i class="bi bi-calendar3 me-1 text-primary"></i>Tanggal Pelaksanaan:</small>
                        <span class="fw-bold text-dark fs-6">{{ $participant->training_date->format('d F Y') }}</span>
                        <div class="text-muted small">({{ $participant->training_date->diffForHumans() }})</div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-1"><i class="bi bi-geo-alt me-1 text-danger"></i>Lokasi Pelaksanaan:</small>
                        <span class="fw-semibold text-dark">{{ $participant->location }}</span>
                    </div>
                </div>

                <!-- Informasi Mitra LSP BNSP -->
                <div class="border rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-award text-primary me-2"></i>Sertifikasi Kompetensi & LSP Mitra BNSP
                        </h6>
                        <span class="{{ $participant->certificate_status_badge }}">
                            {{ $participant->certificate_status_label }}
                        </span>
                    </div>
                    @if($participant->lsp)
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
                            <div>
                                <span class="fw-bold text-dark">{{ $participant->lsp->name }}</span>
                                <div class="text-muted small">
                                    No. Lisensi: {{ $participant->lsp->bnsp_license_number ?? '-' }} | PIC: {{ $participant->lsp->pic_name ?? '-' }}
                                </div>
                            </div>
                            <a href="{{ route('lsps.show', $participant->lsp->id) }}" class="btn btn-sm btn-outline-primary">
                                Profil LSP
                            </a>
                        </div>
                    @else
                        <p class="text-muted small mb-0 pt-2 border-top">
                            Pelatihan ini tidak terikat dengan lembaga sertifikasi profesi (Program Internal / Non-BNSP).
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kartu Keuangan & Pembayaran -->
        <div class="card card-optima border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-credit-card text-primary me-2"></i>Status Keuangan & Pembayaran
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-1">Biaya / Harga Pelatihan:</small>
                        <h3 class="fw-bold text-success mb-0">
                            Rp {{ number_format($participant->price, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted d-block mb-1">Status Pelunasan:</small>
                        <span class="{{ $participant->payment_status_badge }} fs-6 py-2 px-3">
                            {{ $participant->payment_status_label }}
                        </span>
                    </div>
                </div>

                @if($participant->notes)
                <div class="mt-4 pt-3 border-top">
                    <small class="text-muted d-block mb-1 fw-semibold">Catatan Khusus:</small>
                    <div class="p-3 bg-light rounded text-dark small">
                        {{ $participant->notes }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
@can('delete-participants')
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('participants.destroy', $participant->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Peserta
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-2 text-dark">
                        Apakah Anda yakin ingin menghapus data peserta <strong>{{ $participant->name }}</strong>?
                    </p>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Alasan Penghapusan (Opsional):</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="2" placeholder="Tuliskan alasan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger">Hapus Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
