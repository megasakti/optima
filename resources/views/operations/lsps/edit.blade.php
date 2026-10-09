@extends('layouts.app')

@section('title', 'Ubah Data Mitra LSP')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Data LSP: {{ $lsp->name }}
        </h4>
        <p class="text-secondary small mb-0">Perbarui profil Lembaga Sertifikasi Profesi dan rekening tujuan pembayaran</p>
    </div>
    <a href="{{ route('lsps.show', $lsp->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-optima shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Edit Profil Lembaga Sertifikasi Profesi</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('lsps.update', $lsp->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kode LSP <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $lsp->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nama Lembaga Sertifikasi Profesi <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $lsp->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Lisensi BNSP</label>
                            <input type="text" name="bnsp_license_number" class="form-control @error('bnsp_license_number') is-invalid @enderror" value="{{ old('bnsp_license_number', $lsp->bnsp_license_number) }}">
                            @error('bnsp_license_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Alamat Kantor LSP</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $lsp->address) }}">
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama PIC / Narahubung</label>
                            <input type="text" name="pic_name" class="form-control @error('pic_name') is-invalid @enderror" value="{{ old('pic_name', $lsp->pic_name) }}">
                            @error('pic_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nomor Telepon / WhatsApp PIC</label>
                            <input type="text" name="pic_phone" class="form-control @error('pic_phone') is-invalid @enderror" value="{{ old('pic_phone', $lsp->pic_phone) }}">
                            @error('pic_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Email Resmi PIC</label>
                            <input type="email" name="pic_email" class="form-control @error('pic_email') is-invalid @enderror" value="{{ old('pic_email', $lsp->pic_email) }}">
                            @error('pic_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mt-4 mb-3 border-bottom pb-2">Informasi Rekening Bank & Status</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $lsp->bank_name) }}">
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number', $lsp->bank_account_number) }}">
                            @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama Pemilik Rekening</label>
                            <input type="text" name="bank_account_holder" class="form-control @error('bank_account_holder') is-invalid @enderror" value="{{ old('bank_account_holder', $lsp->bank_account_holder) }}">
                            @error('bank_account_holder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $lsp->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">Status LSP Aktif</label>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('lsps.show', $lsp->id) }}" class="btn btn-light border">Batal</a>
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
