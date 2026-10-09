@extends('layouts.app')

@section('title', 'Pemindahbukuan / Transfer Kas & Bank')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-arrow-left-right text-primary me-2"></i>Pemindahbukuan / Transfer Antar Kas & Bank
            </h4>
            <p class="text-secondary small mb-0">Mutasi transfer likuiditas internal (misal: penarikan dana dari Rekening BCA untuk isi Kas Kecil Operasional).</p>
        </div>
        <a href="{{ route('cash-bank.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekening
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi kesalahan validasi:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card card-optima shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-send me-2 text-primary"></i>Formulir Transfer Internal</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('cash-bank.transfer.store') }}" method="POST">
                        @csrf

                        <!-- Rekening Asal -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Rekening Sumber / Asal Dana <span class="text-danger">*</span></label>
                            <select name="from_account_id" id="from_account_id" class="form-select @error('from_account_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Rekening Sumber --</option>
                                @foreach ($accounts as $a)
                                    <option value="{{ $a->id }}" {{ old('from_account_id') == $a->id ? 'selected' : '' }}>
                                        {{ $a->name }} ({{ $a->account_number ?? 'Kas Fisik' }}) - Saldo: Rp {{ number_format($a->current_balance, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rekening Tujuan -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Rekening Tujuan Transfer <span class="text-danger">*</span></label>
                            <select name="to_account_id" id="to_account_id" class="form-select @error('to_account_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Rekening Tujuan --</option>
                                @foreach ($accounts as $a)
                                    <option value="{{ $a->id }}" {{ old('to_account_id') == $a->id ? 'selected' : '' }}>
                                        {{ $a->name }} ({{ $a->account_number ?? 'Kas Fisik' }}) - Saldo: Rp {{ number_format($a->current_balance, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Nominal Transfer -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nominal Transfer (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" step="0.01" name="amount" class="form-control form-control-lg fw-bold text-primary @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required min="1">
                            </div>
                        </div>

                        <!-- Tanggal Transfer -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Tanggal Pemindahbukuan <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" class="form-control @error('transfer_date') is-invalid @enderror" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                        </div>

                        <!-- No Referensi -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">No. Referensi / Bukti Transfer</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Contoh: TRF-INT-2026-001" value="{{ old('reference_number') }}">
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Keterangan Transfer</label>
                            <textarea name="description" rows="2" class="form-control" placeholder="Contoh: Pengisian petty cash operasional mingguan">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Proses Transfer & Posting Jurnal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
