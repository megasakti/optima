@extends('layouts.app')

@section('title', 'Ubah Data Peserta Pelatihan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Data Peserta
        </h4>
        <p class="text-secondary small mb-0">Perbarui rincian peserta {{ $participant->name }}, program, dan status administrasi</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('participants.show', $participant->id) }}" class="btn btn-outline-info">
            <i class="bi bi-eye me-1"></i> Lihat Rincian
        </a>
        <a href="{{ route('participants.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-optima border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Formulir Perubahan Data Peserta: {{ $participant->name }}</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('participants.update', $participant->id) }}" id="participantForm">
                    @csrf
                    @method('PUT')

                    <!-- 1. KATEGORI & SUMBER -->
                    <div class="p-3 bg-light rounded-3 mb-4 border">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    Kategori Pelatihan <span class="text-danger">*</span>
                                </label>
                                <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="public" {{ old('category', $participant->category) === 'public' ? 'selected' : '' }}>Public Training (Terbuka Umum)</option>
                                    <option value="iht" {{ old('category', $participant->category) === 'iht' ? 'selected' : '' }}>In-House Training / IHT (Khusus Perusahaan)</option>
                                    <option value="webinar" {{ old('category', $participant->category) === 'webinar' ? 'selected' : '' }}>Webinar / Seminar Online</option>
                                </select>
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    Sumber Leads / Informasi <span class="text-danger">*</span>
                                </label>
                                <select name="source" id="source" class="form-select @error('source') is-invalid @enderror" required>
                                    <option value="web" {{ old('source', $participant->source) === 'web' ? 'selected' : '' }}>Website (Form Pendaftaran / Google Search)</option>
                                    <option value="ig" {{ old('source', $participant->source) === 'ig' ? 'selected' : '' }}>Instagram (Media Sosial / Ads)</option>
                                    <option value="bod" {{ old('source', $participant->source) === 'bod' ? 'selected' : '' }}>BOD / Referral Manajemen Internal</option>
                                    <option value="other" {{ old('source', $participant->source) === 'other' ? 'selected' : '' }}>Lainnya / Rekomendasi Teman</option>
                                </select>
                                @error('source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 2. DATA DIRI PESERTA -->
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-person-badge me-2"></i>Identitas Peserta
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Lengkap Peserta <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $participant->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $participant->phone) }}" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Alamat Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $participant->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- 3. RELASI KLIEN / PERUSAHAAN -->
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-building me-2"></i>Data Klien / Perusahaan
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Pilih dari Master Klien / Perusahaan</label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                <option value="">-- Tanpa Klien Terdaftar / Individu --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" data-name="{{ $cust->name }}" 
                                            {{ old('customer_id', $participant->customer_id) == $cust->id ? 'selected' : '' }}>
                                        {{ $cust->name }} ({{ $cust->code }}) - {{ ucfirst($cust->customer_type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Perusahaan / Instansi</label>
                            <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                                   value="{{ old('company_name', $participant->company_name) }}">
                            @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- 4. PELATIHAN & LSP -->
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-journal-check me-2"></i>Rincian Program Pelatihan & LSP
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Pilih Skema Master (Opsional)</label>
                            <select id="program_select" name="training_program_id" class="form-select @error('training_program_id') is-invalid @enderror">
                                <option value="">-- Ketik Judul Manual atau Pilih Skema --</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog->id }}" 
                                            data-name="{{ $prog->name }}" 
                                            data-price="{{ $prog->default_price_per_participant }}"
                                            data-lsp="{{ $prog->lsp_id }}"
                                            {{ old('training_program_id', $participant->training_program_id) == $prog->id ? 'selected' : '' }}>
                                        [{{ $prog->code }}] {{ $prog->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('training_program_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jenis Pelatihan <span class="text-danger">*</span></label>
                            <input type="text" name="training_title" id="training_title" class="form-control @error('training_title') is-invalid @enderror" 
                                   value="{{ old('training_title', $participant->training_title) }}" required>
                            @error('training_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Mitra LSP BNSP</label>
                            <select name="lsp_id" id="lsp_id" class="form-select @error('lsp_id') is-invalid @enderror">
                                <option value="">-- Tanpa LSP (Internal Non-Sertifikasi) --</option>
                                @foreach($lsps as $lsp)
                                    <option value="{{ $lsp->id }}" {{ old('lsp_id', $participant->lsp_id) == $lsp->id ? 'selected' : '' }}>
                                        {{ $lsp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lsp_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tanggal Pelatihan <span class="text-danger">*</span></label>
                            <input type="date" name="training_date" class="form-control @error('training_date') is-invalid @enderror" 
                                   value="{{ old('training_date', $participant->training_date->format('Y-m-d')) }}" required>
                            @error('training_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Lokasi Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" 
                                   value="{{ old('location', $participant->location) }}" required>
                            @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- 5. BIAYA & STATUS -->
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-wallet2 me-2"></i>Biaya & Status Registrasi
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Harga / Biaya Pelatihan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" step="any" name="price" id="price" class="form-control @error('price') is-invalid @enderror text-end fw-bold" 
                                       value="{{ old('price', $participant->price) }}" min="0" required>
                            </div>
                            @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Status Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                <option value="unpaid" {{ old('payment_status', $participant->payment_status) === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                                <option value="partial" {{ old('payment_status', $participant->payment_status) === 'partial' ? 'selected' : '' }}>Sebagian / Uang Muka (DP)</option>
                                <option value="paid" {{ old('payment_status', $participant->payment_status) === 'paid' ? 'selected' : '' }}>Lunas</option>
                            </select>
                            @error('payment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Status Sertifikat <span class="text-danger">*</span></label>
                            <select name="certificate_status" class="form-select @error('certificate_status') is-invalid @enderror" required>
                                <option value="process" {{ old('certificate_status', $participant->certificate_status) === 'process' ? 'selected' : '' }}>Dalam Proses</option>
                                <option value="passed" {{ old('certificate_status', $participant->certificate_status) === 'passed' ? 'selected' : '' }}>Kompeten (K)</option>
                                <option value="issued" {{ old('certificate_status', $participant->certificate_status) === 'issued' ? 'selected' : '' }}>Sertifikat Terbit</option>
                                <option value="failed" {{ old('certificate_status', $participant->certificate_status) === 'failed' ? 'selected' : '' }}>Belum Kompeten (BK)</option>
                            </select>
                            @error('certificate_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Catatan Tambahan</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $participant->notes) }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('participants.show', $participant->id) }}" class="btn btn-outline-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Perbarui Data Peserta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const customerSelect = document.getElementById('customer_id');
    const companyInput = document.getElementById('company_name');
    const programSelect = document.getElementById('program_select');
    const titleInput = document.getElementById('training_title');
    const priceInput = document.getElementById('price');
    const lspSelect = document.getElementById('lsp_id');

    if (customerSelect) {
        customerSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                const name = selected.getAttribute('data-name');
                if (name) companyInput.value = name;
            }
        });
    }

    if (programSelect) {
        programSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                const name = selected.getAttribute('data-name');
                const price = selected.getAttribute('data-price');
                const lsp = selected.getAttribute('data-lsp');

                if (name) titleInput.value = name;
                if (price && parseFloat(price) > 0) priceInput.value = price;
                if (lsp) lspSelect.value = lsp;
            }
        });
    }
});
</script>
@endsection
