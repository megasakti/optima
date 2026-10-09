@extends('layouts.app')

@section('title', 'Ubah Skema / Program Pelatihan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Program: {{ $program->name }}
        </h4>
        <p class="text-secondary small mb-0">Perbarui data skema pelatihan, biaya sertifikasi, dan tarif default</p>
    </div>
    <a href="{{ route('programs.show', $program->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-optima shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Edit Data Skema Pelatihan</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('programs.update', $program->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kode Program <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $program->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nama Program Pelatihan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $program->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Mitra LSP BNSP</label>
                            <select name="lsp_id" class="form-select @error('lsp_id') is-invalid @enderror">
                                <option value="">-- Tanpa LSP (Internal Pelatihan Saja) --</option>
                                @foreach($lsps as $lsp)
                                    <option value="{{ $lsp->id }}" {{ old('lsp_id', $program->lsp_id) == $lsp->id ? 'selected' : '' }}>
                                        {{ $lsp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lsp_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Skema SKKNI / BNSP</label>
                            <input type="text" name="skkni_scheme_name" class="form-control @error('skkni_scheme_name') is-invalid @enderror" value="{{ old('skkni_scheme_name', $program->skkni_scheme_name) }}">
                            @error('skkni_scheme_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kategori Program <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="certification" {{ old('category', $program->category) === 'certification' ? 'selected' : '' }}>Sertifikasi BNSP</option>
                                <option value="non_certification" {{ old('category', $program->category) === 'non_certification' ? 'selected' : '' }}>Non-Sertifikasi (Workshop)</option>
                                <option value="inhouse" {{ old('category', $program->category) === 'inhouse' ? 'selected' : '' }}>In-House Training</option>
                                <option value="consulting" {{ old('category', $program->category) === 'consulting' ? 'selected' : '' }}>Konsultasi & Asesmen</option>
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Durasi Pelatihan (Hari) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="duration_days" class="form-control @error('duration_days') is-invalid @enderror" value="{{ old('duration_days', $program->duration_days) }}" min="1" max="90" required>
                                <span class="input-group-text bg-light">Hari</span>
                            </div>
                            @error('duration_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tarif Jual Default / Peserta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" name="default_price_per_participant" class="form-control @error('default_price_per_participant') is-invalid @enderror" value="{{ old('default_price_per_participant', $program->default_price_per_participant) }}" required>
                            </div>
                            @error('default_price_per_participant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Biaya Asesmen LSP Default / Peserta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" name="default_lsp_cost_per_participant" class="form-control @error('default_lsp_cost_per_participant') is-invalid @enderror" value="{{ old('default_lsp_cost_per_participant', $program->default_lsp_cost_per_participant) }}" required>
                            </div>
                            @error('default_lsp_cost_per_participant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Status Program</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $program->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Aktif dan Dapat Digunakan pada Angkatan Baru</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Deskripsi & Silabus Singkat</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $program->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('programs.show', $program->id) }}" class="btn btn-light border">Batal</a>
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
