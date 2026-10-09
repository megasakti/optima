@extends('layouts.app')

@section('title', 'Tambah Skema / Program Pelatihan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Skema / Program Baru
        </h4>
        <p class="text-secondary small mb-0">Input paket pelatihan baru, skema kompetensi BNSP, dan tarif standar</p>
    </div>
    <a href="{{ route('programs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-optima shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Formulir Program Pelatihan & Sertifikasi</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('programs.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kode Program <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Misal: SKM-K3-01" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nama Program Pelatihan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Misal: Ahli K3 Umum Sertifikasi BNSP" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Mitra LSP BNSP</label>
                            <select name="lsp_id" class="form-select @error('lsp_id') is-invalid @enderror">
                                <option value="">-- Tanpa LSP (Internal Pelatihan Saja) --</option>
                                @foreach($lsps as $lsp)
                                    <option value="{{ $lsp->id }}" {{ old('lsp_id') == $lsp->id ? 'selected' : '' }}>
                                        {{ $lsp->name }} ({{ $lsp->bnsp_license_number ?? 'Lisensi BNSP' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('lsp_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Skema SKKNI / BNSP</label>
                            <input type="text" name="skkni_scheme_name" class="form-control @error('skkni_scheme_name') is-invalid @enderror" value="{{ old('skkni_scheme_name') }}" placeholder="Misal: Skema Okupasi Ahli K3 Muda">
                            @error('skkni_scheme_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kategori Program <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="certification" {{ old('category', 'certification') === 'certification' ? 'selected' : '' }}>Sertifikasi BNSP</option>
                                <option value="non_certification" {{ old('category') === 'non_certification' ? 'selected' : '' }}>Non-Sertifikasi (Workshop)</option>
                                <option value="inhouse" {{ old('category') === 'inhouse' ? 'selected' : '' }}>In-House Training</option>
                                <option value="consulting" {{ old('category') === 'consulting' ? 'selected' : '' }}>Konsultasi & Asesmen</option>
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Durasi Pelatihan (Hari) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="duration_days" class="form-control @error('duration_days') is-invalid @enderror" value="{{ old('duration_days', 2) }}" min="1" max="90" required>
                                <span class="input-group-text bg-light">Hari</span>
                            </div>
                            @error('duration_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tarif Jual Default / Peserta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" name="default_price_per_participant" class="form-control @error('default_price_per_participant') is-invalid @enderror" value="{{ old('default_price_per_participant', 0) }}" required>
                            </div>
                            @error('default_price_per_participant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Biaya Asesmen LSP Default / Peserta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" name="default_lsp_cost_per_participant" class="form-control @error('default_lsp_cost_per_participant') is-invalid @enderror" value="{{ old('default_lsp_cost_per_participant', 0) }}" required>
                            </div>
                            <small class="text-muted">Komponen HPP tagihan LSP per asesi/peserta</small>
                            @error('default_lsp_cost_per_participant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Deskripsi & Silabus Singkat</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Keterangan singkat tentang program atau target peserta">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('programs.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Program
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
