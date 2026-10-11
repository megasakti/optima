@extends('layouts.app')

@section('title', 'Tambah Data Peserta Pelatihan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-person-plus-fill text-primary me-2"></i>Tambah Peserta Pelatihan Baru
        </h4>
        <p class="text-secondary small mb-0">Input registrasi peserta pelatihan baru dan integrasikan dengan data klien perusahaan</p>
    </div>
    <a href="{{ route('participants.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-optima border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Formulir Pendaftaran Peserta Pelatihan</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('participants.store') }}" id="participantForm">
                    @csrf

                    <!-- 1. KATEGORI & SUMBER -->
                    <div class="p-3 bg-light rounded-3 mb-4 border">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    Kategori Pelatihan <span class="text-danger">*</span>
                                </label>
                                <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="public" {{ old('category') === 'public' ? 'selected' : '' }}>Public Training (Terbuka Umum)</option>
                                    <option value="iht" {{ old('category') === 'iht' ? 'selected' : '' }}>In-House Training / IHT (Khusus Perusahaan)</option>
                                    <option value="webinar" {{ old('category') === 'webinar' ? 'selected' : '' }}>Webinar / Seminar Online</option>
                                </select>
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted" style="font-size: 0.72rem;">Pilih model pelaksanaan pelatihan</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    Sumber Leads / Informasi <span class="text-danger">*</span>
                                </label>
                                <select name="source" id="source" class="form-select @error('source') is-invalid @enderror" required>
                                    <option value="web" {{ old('source') === 'web' ? 'selected' : '' }}>Website (Form Pendaftaran / Google Search)</option>
                                    <option value="ig" {{ old('source') === 'ig' ? 'selected' : '' }}>Instagram (Media Sosial / Ads)</option>
                                    <option value="bod" {{ old('source') === 'bod' ? 'selected' : '' }}>BOD / Referral Manajemen Internal</option>
                                    <option value="other" {{ old('source') === 'other' ? 'selected' : '' }}>Lainnya / Rekomendasi Teman</option>
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
                                   value="{{ old('name') }}" placeholder="Contoh: Budi Santoso, S.T." required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone') }}" placeholder="Contoh: 081234567890" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Alamat Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" placeholder="budi@example.com">
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
                                <option value="">-- Pilih Klien Terdaftar (Atau Ketik Manual di Kanan) --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" data-name="{{ $cust->name }}" {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
                                        {{ $cust->name }} ({{ $cust->code }}) - {{ ucfirst($cust->customer_type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted" style="font-size: 0.72rem;">Terkoneksi langsung dengan master data klien untuk penerbitan invoice</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Perusahaan / Instansi</label>
                            <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                                   value="{{ old('company_name') }}" placeholder="Misal: PT Semen Nusantara Perkasa">
                            @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted" style="font-size: 0.72rem;">Otomatis terisi jika memilih klien di atas, atau bisa diketik manual jika klien belum ada</small>
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
                                            {{ old('training_program_id') == $prog->id ? 'selected' : '' }}>
                                        [{{ $prog->code }}] {{ $prog->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('training_program_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted" style="font-size: 0.72rem;">Memilih skema akan otomatis mengisikan jenis pelatihan, harga, dan LSP</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jenis Pelatihan <span class="text-danger">*</span></label>
                            <input type="text" name="training_title" id="training_title" class="form-control @error('training_title') is-invalid @enderror" 
                                   value="{{ old('training_title') }}" placeholder="Contoh: Ahli K3 Umum Sertifikasi BNSP" required>
                            @error('training_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Mitra LSP BNSP</label>
                            <select name="lsp_id" id="lsp_id" class="form-select @error('lsp_id') is-invalid @enderror">
                                <option value="">-- Tanpa LSP (Internal Non-Sertifikasi) --</option>
                                @foreach($lsps as $lsp)
                                    <option value="{{ $lsp->id }}" {{ old('lsp_id') == $lsp->id ? 'selected' : '' }}>
                                        {{ $lsp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lsp_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tanggal Pelatihan <span class="text-danger">*</span></label>
                            <input type="date" name="training_date" class="form-control @error('training_date') is-invalid @enderror" 
                                   value="{{ old('training_date', date('Y-m-d')) }}" required>
                            @error('training_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Lokasi Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" 
                                   value="{{ old('location', 'Hotel Santika Premiere Slipi / Jakarta') }}" placeholder="Misal: Hotel / Online Zoom" required>
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
                                       value="{{ old('price', 4500000) }}" min="0" required>
                            </div>
                            @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Status Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                <option value="unpaid" {{ old('payment_status', 'unpaid') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                                <option value="partial" {{ old('payment_status') === 'partial' ? 'selected' : '' }}>Sebagian / Uang Muka (DP)</option>
                                <option value="paid" {{ old('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                            </select>
                            @error('payment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Status Sertifikat <span class="text-danger">*</span></label>
                            <select name="certificate_status" class="form-select @error('certificate_status') is-invalid @enderror" required>
                                <option value="process" {{ old('certificate_status', 'process') === 'process' ? 'selected' : '' }}>Dalam Proses</option>
                                <option value="passed" {{ old('certificate_status') === 'passed' ? 'selected' : '' }}>Kompeten (K)</option>
                                <option value="issued" {{ old('certificate_status') === 'issued' ? 'selected' : '' }}>Sertifikat Terbit</option>
                                <option value="failed" {{ old('certificate_status') === 'failed' ? 'selected' : '' }}>Belum Kompeten (BK)</option>
                            </select>
                            @error('certificate_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Catatan Tambahan</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" 
                                      placeholder="Catatan pendaftaran, preferensi kamar, sertifikat, atau instruksi khusus...">{{ old('notes') }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('participants.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Data Peserta
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

    // Auto-fill company name when customer is selected
    if (customerSelect) {
        customerSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                const name = selected.getAttribute('data-name');
                if (name) {
                    companyInput.value = name;
                }
            }
        });
    }

    // Auto-fill training details when program is selected
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
