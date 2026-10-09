@extends('layouts.app')

@section('title', 'Catat Pembayaran Pelanggan')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-cash-coin text-success me-2"></i>Catat Penerimaan Pembayaran Pelanggan
            </h4>
            <p class="text-secondary small mb-0">Input kwitansi pelunasan invoice, bukti potong PPh 23, dan pemutakhiran saldo bank.</p>
        </div>
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
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

    <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
        @csrf
        <div class="row g-4">
            <!-- Left: Info Kas & Pembayaran -->
            <div class="col-12 col-lg-5">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Transaksi Kas/Bank</h6>
                    </div>
                    <div class="card-body">
                        <!-- Customer Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Klien / Pembayar <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required onchange="window.location.href='{{ route('payments.create') }}?customer_id=' + this.value">
                                <option value="">-- Pilih Klien / Perusahaan --</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}" {{ (old('customer_id', $selectedCustomerId) == $c->id) ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Memilih klien akan memuat daftar tagihan invoice yang belum lunas.</small>
                        </div>

                        <!-- Bank Account Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Rekening Kas / Bank Penerima <span class="text-danger">*</span></label>
                            <select name="cash_bank_account_id" class="form-select @error('cash_bank_account_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Akun Bank/Kas --</option>
                                @foreach ($cashBankAccounts as $cb)
                                    <option value="{{ $cb->id }}" {{ old('cash_bank_account_id') == $cb->id ? 'selected' : '' }}>
                                        {{ $cb->name }} ({{ $cb->account_number ?? 'Kas Fisik' }}) - Saldo: Rp {{ number_format($cb->current_balance, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <!-- Tanggal Bayar -->
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>
                            <!-- Metode Pembayaran -->
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Metode Bayar <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Kas Tunai</option>
                                    <option value="giro" {{ old('payment_method') === 'giro' ? 'selected' : '' }}>Bilyet Giro / Cek</option>
                                    <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Kartu Kredit</option>
                                </select>
                            </div>
                        </div>

                        <!-- Jumlah Uang Masuk ke Bank -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nominal Uang Masuk (Net Diterima Kas/Bank) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control form-control-lg fw-bold text-success @error('amount') is-invalid @enderror" value="{{ old('amount', 0) }}" required>
                            </div>
                            <small class="text-muted">Jumlah riil mutasi masuk di rekening koran bank.</small>
                        </div>

                        <!-- Bukti Potong PPh 23 (Jika Ada) -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nilai Bukti Potong PPh 23 (Jika Dipotong Klien)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" name="pph23_deducted" id="pph23_deducted" class="form-control" value="{{ old('pph23_deducted', 0) }}">
                            </div>
                            <small class="text-muted">Akan didebit ke Akun 106 (Pajak Dibayar di Muka PPh 23).</small>
                        </div>

                        <!-- No Referensi Transfer -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">No. Referensi / Bukti Transfer Bank</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Contoh: TRF-BCA-8921827" value="{{ old('reference_number') }}">
                        </div>

                        <!-- Catatan -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Keterangan / Catatan</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Catatan pembayaran...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Alokasi ke Invoice Outstanding -->
            <div class="col-12 col-lg-7">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-receipt me-2 text-primary"></i>Alokasi Pelunasan Invoice Tagihan
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @if ($outstandingInvoices->isEmpty())
                            <div class="p-4 text-center text-muted">
                                @if ($selectedCustomerId)
                                    <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                                    Klien ini tidak memiliki invoice dengan sisa tagihan yang belum lunas.
                                @else
                                    <i class="bi bi-arrow-left-circle fs-1 text-secondary d-block mb-2"></i>
                                    Silakan pilih klien terlebih dahulu untuk menampilkan daftar invoice tagihan.
                                @endif
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-optima mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;" class="text-center">Pilih</th>
                                            <th>No. Faktur</th>
                                            <th>Tgl / Jatuh Tempo</th>
                                            <th class="text-end">Sisa Piutang (Rp)</th>
                                            <th class="text-end" style="width: 200px;">Alokasi Bayar (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($outstandingInvoices as $index => $inv)
                                            @php
                                                $isSelected = ($preselectedInvoiceId == $inv->id);
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input invoice-checkbox" data-index="{{ $index }}" data-balance="{{ $inv->balance_due }}" {{ $isSelected ? 'checked' : '' }}>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-primary">{{ $inv->invoice_number }}</div>
                                                    <small class="text-muted">{{ $inv->batch ? 'Batch ' . $inv->batch->batch_code : 'Umum' }}</small>
                                                </td>
                                                <td>
                                                    <div class="small">{{ $inv->invoice_date->format('d/m/Y') }}</div>
                                                    <div class="small text-danger">Due: {{ $inv->due_date->format('d/m/Y') }}</div>
                                                </td>
                                                <td class="text-end fw-semibold text-danger">
                                                    Rp {{ number_format($inv->balance_due, 0, ',', '.') }}
                                                </td>
                                                <td>
                                                    <input type="hidden" name="allocations[{{ $index }}][invoice_id]" value="{{ $inv->id }}" {{ $isSelected ? '' : 'disabled' }} class="alloc-invoice-id">
                                                    <input type="number" step="0.01" name="allocations[{{ $index }}][amount_applied]" class="form-control form-control-sm text-end alloc-amount" value="{{ $isSelected ? $inv->balance_due : 0 }}" max="{{ $inv->balance_due }}" {{ $isSelected ? '' : 'disabled' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Total Verification Card -->
                <div class="card card-optima shadow-sm">
                    <div class="card-header bg-light border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-check-all me-2 text-primary"></i>Validasi Rekonsiliasi Pembayaran</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-secondary">Uang Diterima Kas/Bank:</span>
                            <span class="fw-semibold text-success" id="display-amount">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-secondary">Potongan Pajak PPh 23:</span>
                            <span class="fw-semibold text-dark" id="display-pph23">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom bg-light px-3 rounded">
                            <span class="fw-bold text-dark">Total Kredit Piutang (Amount + PPh 23):</span>
                            <span class="fw-bold text-primary" id="display-total-credit">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom px-3">
                            <span class="fw-bold text-dark">Total Alokasi Invoice:</span>
                            <span class="fw-bold text-dark" id="display-total-allocated">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 fw-bold" id="balance-check-container">
                            <span>Selisih Rekonsiliasi:</span>
                            <span id="display-difference">Rp 0</span>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm" id="btn-submit" {{ $outstandingInvoices->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-check2-circle me-1"></i> Simpan Pembayaran & Posting Jurnal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputAmount = document.getElementById('amount');
    const inputPph23 = document.getElementById('pph23_deducted');
    const checkboxes = document.querySelectorAll('.invoice-checkbox');

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function updateReconciliation() {
        const amt = parseFloat(inputAmount.value) || 0;
        const pph = parseFloat(inputPph23.value) || 0;
        const totalCredit = amt + pph;

        let totalAllocated = 0;
        document.querySelectorAll('.alloc-amount:not([disabled])').forEach(inp => {
            totalAllocated += parseFloat(inp.value) || 0;
        });

        const diff = Math.abs(totalCredit - totalAllocated);

        document.getElementById('display-amount').innerText = formatRupiah(amt);
        document.getElementById('display-pph23').innerText = formatRupiah(pph);
        document.getElementById('display-total-credit').innerText = formatRupiah(totalCredit);
        document.getElementById('display-total-allocated').innerText = formatRupiah(totalAllocated);

        const diffContainer = document.getElementById('balance-check-container');
        const diffSpan = document.getElementById('display-difference');

        if (diff < 0.01 && totalCredit > 0) {
            diffSpan.innerText = 'SEIMBANG (Rp 0)';
            diffContainer.className = 'd-flex justify-content-between py-2 px-3 fw-bold text-success bg-success-subtle rounded mt-2';
        } else {
            diffSpan.innerText = 'SELISIH ' + formatRupiah(diff);
            diffContainer.className = 'd-flex justify-content-between py-2 px-3 fw-bold text-danger bg-danger-subtle rounded mt-2';
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const row = this.closest('tr');
            const invIdInp = row.querySelector('.alloc-invoice-id');
            const amtInp = row.querySelector('.alloc-amount');
            const bal = parseFloat(this.dataset.balance) || 0;

            if (this.checked) {
                invIdInp.disabled = false;
                amtInp.disabled = false;
                if (!amtInp.value || parseFloat(amtInp.value) === 0) {
                    amtInp.value = bal;
                }
            } else {
                invIdInp.disabled = true;
                amtInp.disabled = true;
                amtInp.value = 0;
            }
            updateReconciliation();
        });
    });

    document.addEventListener('input', function (e) {
        if (e.target.id === 'amount' || e.target.id === 'pph23_deducted' || e.target.classList.contains('alloc-amount')) {
            updateReconciliation();
        }
    });

    updateReconciliation();
});
</script>
@endpush
@endsection
