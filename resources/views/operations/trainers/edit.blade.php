@extends('layouts.app')

@section('title', 'Ubah Data Instruktur')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Data: {{ $trainer->name }}
        </h4>
        <p class="text-secondary small mb-0">Perbarui profil instruktur, tarif honor harian, dan informasi perbankan</p>
    </div>
    <a href="{{ route('trainers.show', $trainer->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-optima shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Edit Data Instruktur Pelatihan</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('trainers.update', $trainer->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kode Instruktur <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $trainer->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nama Lengkap & Gelar Instruktur <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $trainer->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Bidang Spesialisasi / Keahlian</label>
                            <input type="text" name="specialization" class="form-control @error('specialization') is-invalid @enderror" value="{{ old('specialization', $trainer->specialization) }}">
                            @error('specialization') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tarif Honor Default / Hari <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" name="default_daily_rate" class="form-control @error('default_daily_rate') is-invalid @enderror" value="{{ old('default_daily_rate', $trainer->default_daily_rate) }}" required>
                            </div>
                            @error('default_daily_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor HP / WhatsApp</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $trainer->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Alamat Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $trainer->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor NPWP</label>
                            <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" value="{{ old('tax_id', $trainer->tax_id) }}">
                            @error('tax_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Induk Kependudukan (NIK KTP)</label>
                            <input type="text" name="id_card_number" class="form-control @error('id_card_number') is-invalid @enderror" value="{{ old('id_card_number', $trainer->id_card_number) }}">
                            @error('id_card_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mt-4 mb-3 border-bottom pb-2">Informasi Rekening Bank Pembayaran Honor</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $trainer->bank_name) }}">
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number', $trainer->bank_account_number) }}">
                            @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Atas Nama Rekening</label>
                            <input type="text" name="bank_account_holder" class="form-control @error('bank_account_holder') is-invalid @enderror" value="{{ old('bank_account_holder', $trainer->bank_account_holder) }}">
                            @error('bank_account_holder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $trainer->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">Status Instruktur Aktif</label>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('trainers.show', $trainer->id) }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
