@extends('layouts.app')

@section('title', 'Catat Pembayaran Hutang Vendor & LSP')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-credit-card text-success me-2"></i>Catat Pembayaran Hutang Vendor & LSP
            </h4>
            <p class="text-secondary small mb-0">Input bukti pengeluaran kas/bank untuk pelunasan tagihan uji sertifikasi LSP, hotel, dan biaya lainnya.</p>
        </div>
        <a href="{{ route('vendor-payments.index') }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('vendor-payments.store') }}" method="POST" id="paymentForm">
        @csrf
        <div class="row g-4">
            <!-- Left: Info Transaksi & Payee -->
            <div class="col-12 col-lg-5">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Pihak Penerima & Rekening</h6>
                    </div>
                    <div class="card-body">
                        <!-- Tipe Penerima -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Kategori Penerima <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="payee_type" id="p_lsp" value="lsp" {{ $selectedPayeeType === 'lsp' ? 'checked' : '' }} onchange="reloadPayee('lsp')">
                                <label class="btn btn-outline-primary btn-sm" for="p_lsp"><i class="bi bi-award me-1"></i> LSP</label>

                                <input type="radio" class="btn-check" name="payee_type" id="p_vendor" value="vendor" {{ $selectedPayeeType === 'vendor' ? 'checked' : '' }} onchange="reloadPayee('vendor')">
                                <label class="btn btn-outline-primary btn-sm" for="p_vendor"><i class="bi bi-building me-1"></i> Vendor</label>

                                <input type="radio" class="btn-check" name="payee_type" id="p_trainer" value="trainer" {{ $selectedPayeeType === 'trainer' ? 'checked' : '' }} onchange="reloadPayee('trainer')">
                                <label class="btn btn-outline-primary btn-sm" for="p_trainer"><i class="bi bi-person me-1"></i> Instruktur</label>
                            </div>
                        </div>

                        <!-- Pilih Payee Dropdown -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Pilih Penerima Dana <span class="text-danger">*</span></label>
                            <select name="payee_id" id="payee_id" class="form-select @error('payee_id') is-invalid @enderror" required onchange="window.location.href='{{ route('vendor-payments.create') }}?payee_type={{ $selectedPayeeType }}&payee_id=' + this.value">
                                <option value="">-- Pilih Penerima --</option>
                                @if ($selectedPayeeType === 'lsp')
                                    @foreach ($lsps as $l)
                                        <option value="{{ $l->id }}" {{ $selectedPayeeId == $l->id ? 'selected' : '' }}>
                                            {{ $l->name }} ({{ $l->code ?? 'LSP' }})
                                        </option>
                                    @endforeach
                                @elseif ($selectedPayeeType === 'vendor')
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}" {{ $selectedPayeeId == $v->id ? 'selected' : '' }}>
                                            {{ $v->name }} ({{ ucfirst($v->category ?? 'Vendor') }})
                                        </option>
                                    @endforeach
                                @else
                                    @foreach ($trainers as $t)
                                        <option value="{{ $t->id }}" {{ $selectedPayeeId == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Rekening Kas/Bank -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Rekening Kas / Bank Sumber Dana <span class="text-danger">*</span></label>
                            <select name="cash_bank_account_id" class="form-select @error('cash_bank_account_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Rekening Kas/Bank --</option>
                                @foreach ($cashBankAccounts as $cb)
                                    <option value="{{ $cb->id }}" {{ old('cash_bank_account_id') == $cb->id ? 'selected' : '' }}>
                                        {{ $cb->name }} ({{ $cb->account_number ?? 'Kas Fisik' }}) - Saldo: Rp {{ number_format($cb->current_balance, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Metode Bayar <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Kas Tunai</option>
                                    <option value="giro" {{ old('payment_method') === 'giro' ? 'selected' : '' }}>Bilyet Giro / Cek</option>
                                </select>
                            </div>
                        </div>

                        <!-- Nominal Pembayaran -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Total Dana yang Dibayarkan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control form-control-lg fw-bold text-danger @error('amount') is-invalid @enderror" value="{{ old('amount', 0) }}" required>
                            </div>
                        </div>

                        <!-- No Referensi Transfer -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nomor Bukti Transfer Bank</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Contoh: TRF-BCA-OUT-0091" value="{{ old('reference_number') }}">
                        </div>

                        <!-- Catatan -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Keterangan Tambahan</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Catatan pembayaran...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Alokasi ke Tagihan Belum Lunas -->
            <div class="col-12 col-lg-7">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-receipt me-2 text-primary"></i>Alokasi Pelunasan Tagihan Hutang
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @if ($outstandingBills->isEmpty())
                            <div class="p-4 text-center text-muted">
                                @if ($selectedPayeeId)
                                    <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                                    Penerima ini tidak memiliki tagihan hutang yang belum lunas.
                                @else
                                    <i class="bi bi-arrow-left-circle fs-1 text-secondary d-block mb-2"></i>
                                    Silakan pilih penerima dana terlebih dahulu untuk menampilkan daftar tagihan hutang.
                                @endif
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-optima mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;" class="text-center">Pilih</th>
                                            <th>No. Tagihan</th>
                                            <th>Tgl / Jatuh Tempo</th>
                                            <th class="text-end">Sisa Hutang (Rp)</th>
                                            <th class="text-end" style="width: 200px;">Alokasi Bayar (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($outstandingBills as $index => $bill)
                                            @php
                                                $isSelected = ($preselectedBillId == $bill->id);
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input bill-checkbox" data-index="{{ $index }}" data-balance="{{ $bill->balance_due }}" {{ $isSelected ? 'checked' : '' }}>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-primary">{{ $bill->bill_number }}</div>
                                                    <small class="text-muted">{{ $bill->batch ? 'Batch ' . $bill->batch->batch_code : 'Umum' }}</small>
                                                </td>
                                                <td>
                                                    <div class="small">{{ $bill->bill_date->format('d/m/Y') }}</div>
                                                    <div class="small text-danger">Due: {{ $bill->due_date->format('d/m/Y') }}</div>
                                                </td>
                                                <td class="text-end fw-semibold text-danger">
                                                    Rp {{ number_format($bill->balance_due, 0, ',', '.') }}
                                                </td>
                                                <td>
                                                    <input type="hidden" name="allocations[{{ $index }}][vendor_bill_id]" value="{{ $bill->id }}" {{ $isSelected ? '' : 'disabled' }} class="alloc-bill-id">
                                                    <input type="number" step="0.01" name="allocations[{{ $index }}][amount_applied]" class="form-control form-control-sm text-end alloc-amount" value="{{ $isSelected ? $bill->balance_due : 0 }}" max="{{ $bill->balance_due }}" {{ $isSelected ? '' : 'disabled' }}>
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
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-check-all me-2 text-primary"></i>Validasi Rekonsiliasi Pengeluaran</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-secondary">Total Dana Keluar (Nominal Bayar):</span>
                            <span class="fw-semibold text-danger" id="display-amount">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-secondary">Total Alokasi Pelunasan Tagihan:</span>
                            <span class="fw-semibold text-dark" id="display-total-allocated">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 fw-bold" id="balance-check-container">
                            <span>Status Rekonsiliasi:</span>
                            <span id="display-difference">Rp 0</span>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm" id="btn-submit" {{ $outstandingBills->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-check2-circle me-1"></i> Simpan Pembayaran & Potong Saldo Kas/Bank
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
function reloadPayee(type) {
    window.location.href = '{{ route('vendor-payments.create') }}?payee_type=' + type;
}

document.addEventListener('DOMContentLoaded', function () {
    const inputAmount = document.getElementById('amount');
    const checkboxes = document.querySelectorAll('.bill-checkbox');

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function updateReconciliation() {
        const amt = parseFloat(inputAmount.value) || 0;
        let totalAllocated = 0;

        document.querySelectorAll('.alloc-amount:not([disabled])').forEach(inp => {
            totalAllocated += parseFloat(inp.value) || 0;
        });

        const diff = Math.abs(amt - totalAllocated);

        document.getElementById('display-amount').innerText = formatRupiah(amt);
        document.getElementById('display-total-allocated').innerText = formatRupiah(totalAllocated);

        const diffContainer = document.getElementById('balance-check-container');
        const diffSpan = document.getElementById('display-difference');

        if (diff < 0.01 && amt > 0) {
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
            const billIdInp = row.querySelector('.alloc-bill-id');
            const amtInp = row.querySelector('.alloc-amount');
            const bal = parseFloat(this.dataset.balance) || 0;

            if (this.checked) {
                billIdInp.disabled = false;
                amtInp.disabled = false;
                if (!amtInp.value || parseFloat(amtInp.value) === 0) {
                    amtInp.value = bal;
                }
            } else {
                billIdInp.disabled = true;
                amtInp.disabled = true;
                amtInp.value = 0;
            }
            updateReconciliation();
        });
    });

    document.addEventListener('input', function (e) {
        if (e.target.id === 'amount' || e.target.classList.contains('alloc-amount')) {
            updateReconciliation();
        }
    });

    updateReconciliation();
});
</script>
@endpush
@endsection
