@extends('layouts.app')

@section('title', 'Buka Batch Pelatihan & Sertifikasi Baru')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('batches.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold text-dark mb-0">Buka Batch Pelatihan Baru</h4>
            </div>
            <p class="text-secondary small mb-0">Daftarkan jadwal pelatihan, skema sertifikasi BNSP, mitra LSP, target peserta, dan harga tiket.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terdapat kesalahan pengisian data:</h6>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('batches.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Program & Scheduling -->
            <div class="col-12 col-lg-8">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="bi bi-info-circle me-2"></i>Informasi Program & Jadwal Pelatihan
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Program / Skema Pelatihan <span class="text-danger">*</span></label>
                                <select name="program_id" class="form-select @error('program_id') is-invalid @enderror" required id="program_select">
                                    <option value="">-- Pilih Program --</option>
                                    @foreach ($programs as $program)
                                        <option value="{{ $program->id }}" 
                                            data-price="{{ $program->default_price ?? 0 }}"
                                            data-lsp="{{ $program->default_lsp_id ?? '' }}"
                                            {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                            {{ $program->code }} - {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('program_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Nama / Judul Batch <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                    placeholder="Contoh: Training & Uji Sertifikasi BNSP - Batch 12"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                    value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                                    value="{{ old('end_date', date('Y-m-d', strtotime('+2 days'))) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Tanggal Uji Asesmen BNSP</label>
                                <input type="date" name="assessment_date" class="form-control @error('assessment_date') is-invalid @enderror" 
                                    value="{{ old('assessment_date', date('Y-m-d', strtotime('+2 days'))) }}">
                                <small class="text-muted">Hari pelaksanaan uji kompetensi BNSP</small>
                                @error('assessment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Metode Pelaksanaan <span class="text-danger">*</span></label>
                                <select name="delivery_type" class="form-select @error('delivery_type') is-invalid @enderror" required>
                                    <option value="offline_hotel" {{ old('delivery_type') === 'offline_hotel' ? 'selected' : '' }}>Offline di Hotel / Venue</option>
                                    <option value="offline_inhouse" {{ old('delivery_type') === 'offline_inhouse' ? 'selected' : '' }}>In-House Training (Klien)</option>
                                    <option value="online" {{ old('delivery_type') === 'online' ? 'selected' : '' }}>Online (Virtual Class / Zoom)</option>
                                    <option value="hybrid" {{ old('delivery_type') === 'hybrid' ? 'selected' : '' }}>Hybrid (Online + Praktik Offline)</option>
                                </select>
                                @error('delivery_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Nama Lokasi / Hotel</label>
                                <input type="text" name="venue_name" class="form-control @error('venue_name') is-invalid @enderror" 
                                    placeholder="Contoh: Hotel Santika Premiere" value="{{ old('venue_name') }}">
                                @error('venue_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Kota Pelaksanaan</label>
                                <input type="text" name="venue_city" class="form-control @error('venue_city') is-invalid @enderror" 
                                    placeholder="Contoh: Jakarta Selatan" value="{{ old('venue_city', 'Jakarta') }}">
                                @error('venue_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan Tambahan</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan instruktur, rundown, atau logistik...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Commercial & Partners -->
            <div class="col-12 col-lg-4">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="bi bi-cash-stack me-2"></i>Komersial & Target
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Target Peserta (Pax) <span class="text-danger">*</span></label>
                            <input type="number" name="target_participants" id="target_participants" 
                                class="form-control @error('target_participants') is-invalid @enderror" 
                                min="1" value="{{ old('target_participants', 15) }}" required>
                            @error('target_participants')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Harga Jual per Peserta (Rp) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="price_per_participant" id="price_per_participant" 
                                class="form-control @error('price_per_participant') is-invalid @enderror" 
                                min="0" value="{{ old('price_per_participant', 4500000) }}" required>
                            @error('price_per_participant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="p-3 bg-light rounded border mb-3">
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>Estimasi Total Omset:</span>
                            </div>
                            <h5 class="fw-bold text-success mb-0" id="estimated_revenue_display">Rp 67.500.000</h5>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mitra LSP Pelaksana</label>
                            <select name="lsp_id" id="lsp_select" class="form-select @error('lsp_id') is-invalid @enderror">
                                <option value="">-- Pilih LSP (Jika Sertifikasi) --</option>
                                @foreach ($lsps as $lsp)
                                    <option value="{{ $lsp->id }}" {{ old('lsp_id') == $lsp->id ? 'selected' : '' }}>
                                        {{ $lsp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lsp_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Instruktur / Trainer Utama</label>
                            <select name="trainer_id" class="form-select @error('trainer_id') is-invalid @enderror">
                                <option value="">-- Pilih Instruktur --</option>
                                @foreach ($trainers as $trainer)
                                    <option value="{{ $trainer->id }}" {{ old('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                        {{ $trainer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('trainer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">PIC Operasional</label>
                            <select name="pic_user_id" class="form-select @error('pic_user_id') is-invalid @enderror">
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ (old('pic_user_id', Auth::id()) == $u->id) ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('pic_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary py-2 fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Batch & Lanjut RAB
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const targetPaxInput = document.getElementById('target_participants');
    const pricePaxInput = document.getElementById('price_per_participant');
    const revDisplay = document.getElementById('estimated_revenue_display');
    const programSelect = document.getElementById('program_select');
    const lspSelect = document.getElementById('lsp_select');

    function updateEstRevenue() {
        const pax = parseFloat(targetPaxInput.value) || 0;
        const price = parseFloat(pricePaxInput.value) || 0;
        const total = pax * price;
        revDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    targetPaxInput.addEventListener('input', updateEstRevenue);
    pricePaxInput.addEventListener('input', updateEstRevenue);

    programSelect.addEventListener('change', function () {
        const selected = programSelect.options[programSelect.selectedIndex];
        const defaultPrice = selected.getAttribute('data-price');
        const defaultLsp = selected.getAttribute('data-lsp');

        if (defaultPrice && parseFloat(defaultPrice) > 0) {
            pricePaxInput.value = defaultPrice;
        }
        if (defaultLsp && lspSelect) {
            lspSelect.value = defaultLsp;
        }
        updateEstRevenue();
    });

    updateEstRevenue();
});
</script>
@endsection
