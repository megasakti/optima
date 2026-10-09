@extends('layouts.app')

@section('title', 'Tambah Mitra LSP')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-award text-primary me-2"></i>Tambah Mitra LSP Baru
        </h4>
        <p class="text-secondary small mb-0">Input profil Lembaga Sertifikasi Profesi mitra BNSP dan informasi rekening</p>
    </div>
    <a href="{{ route('lsps.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-optima shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Formulir Pendaftaran Mitra LSP</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('lsps.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kode LSP <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Misal: LSP-TI-01" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nama Lembaga Sertifikasi Profesi <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Misal: LSP Teknologi Informatika Nusantara" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Lisensi BNSP</label>
                            <input type="text" name="bnsp_license_number" class="form-control @error('bnsp_license_number') is-invalid @enderror" value="{{ old('bnsp_license_number') }}" placeholder="Misal: BNSP-LSP-123-ID">
                            @error('bnsp_license_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Alamat Kantor LSP</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" placeholder="Alamat sekretariat LSP">
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama PIC / Narahubung</label>
                            <input type="text" name="pic_name" class="form-control @error('pic_name') is-invalid @enderror" value="{{ old('pic_name') }}" placeholder="Nama PIC">
                            @error('pic_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nomor Telepon / WhatsApp PIC</label>
                            <input type="text" name="pic_phone" class="form-control @error('pic_phone') is-invalid @enderror" value="{{ old('pic_phone') }}" placeholder="08xxxxxxxxxx">
                            @error('pic_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Email Resmi PIC</label>
                            <input type="email" name="pic_email" class="form-control @error('pic_email') is-invalid @enderror" value="{{ old('pic_email') }}" placeholder="pic@lsp.or.id">
                            @error('pic_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mt-4 mb-3 border-bottom pb-2">Informasi Rekening Bank (Untuk Pembayaran AP)</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}" placeholder="Misal: Bank Mandiri, BCA">
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number') }}" placeholder="Nomor rekening bank">
                            @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama Pemilik Rekening</label>
                            <input type="text" name="bank_account_holder" class="form-control @error('bank_account_holder') is-invalid @enderror" value="{{ old('bank_account_holder') }}" placeholder="Atas nama rekening">
                            @error('bank_account_holder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('lsps.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan LSP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
