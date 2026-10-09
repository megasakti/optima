@extends('layouts.app')

@section('title', 'Tambah Instruktur Pelatihan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-person-plus text-primary me-2"></i>Tambah Instruktur Baru
        </h4>
        <p class="text-secondary small mb-0">Input data pengajar/trainer, spesialisasi, tarif honor harian, dan rekening bank</p>
    </div>
    <a href="{{ route('trainers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-optima shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Formulir Pendaftaran Instruktur Pelatihan</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('trainers.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kode Instruktur <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Misal: TRN-001" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nama Lengkap & Gelar Instruktur <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Misal: Ir. Hendra Gunawan, M.T., CSP" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Bidang Spesialisasi / Keahlian</label>
                            <input type="text" name="specialization" class="form-control @error('specialization') is-invalid @enderror" value="{{ old('specialization') }}" placeholder="Misal: Keselamatan & Kesehatan Kerja (K3), ISO 9001">
                            @error('specialization') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tarif Honor Default / Hari <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" name="default_daily_rate" class="form-control @error('default_daily_rate') is-invalid @enderror" value="{{ old('default_daily_rate', 0) }}" required>
                            </div>
                            @error('default_daily_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor HP / WhatsApp</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Alamat Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="trainer@domain.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor NPWP</label>
                            <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" value="{{ old('tax_id') }}" placeholder="NPWP untuk pemotongan PPh 21">
                            @error('tax_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Induk Kependudukan (NIK KTP)</label>
                            <input type="text" name="id_card_number" class="form-control @error('id_card_number') is-invalid @enderror" value="{{ old('id_card_number') }}" placeholder="NIK KTP">
                            @error('id_card_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mt-4 mb-3 border-bottom pb-2">Informasi Rekening Bank Pembayaran Honor</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}" placeholder="Misal: BCA, BRI, Mandiri">
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number') }}" placeholder="Nomor rekening">
                            @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Atas Nama Rekening</label>
                            <input type="text" name="bank_account_holder" class="form-control @error('bank_account_holder') is-invalid @enderror" value="{{ old('bank_account_holder') }}" placeholder="Nama pemilik rekening">
                            @error('bank_account_holder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('trainers.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Instruktur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
