@extends('layouts.app')

@section('title', 'Buat Faktur Invoice Baru')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-file-earmark-plus text-primary me-2"></i>Buat Faktur Invoice Baru
            </h4>
            <p class="text-secondary small mb-0">Input rincian tagihan pelatihan/sertifikasi BNSP, PPN 11%, dan potongan PPh 23.</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
        @csrf
        <div class="row g-4">
            <!-- Left: Info Klien & Invoice -->
            <div class="col-12 col-lg-4">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Tagihan</h6>
                    </div>
                    <div class="card-body">
                        <!-- Customer -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Klien / Perusahaan <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Klien --</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->company_name ?? 'Korporat' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Batch Pelatihan -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Terkait Batch Pelatihan</label>
                            <select name="batch_id" class="form-select @error('batch_id') is-invalid @enderror">
                                <option value="">-- Tanpa Batch (Umum) --</option>
                                @foreach ($batches as $b)
                                    <option value="{{ $b->id }}" {{ (old('batch_id', $selectedBatchId) == $b->id) ? 'selected' : '' }}>
                                        {{ $b->batch_code }} - {{ $b->program->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Invoice -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Tanggal Invoice <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror" 
                                value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>

                        <!-- Tanggal Jatuh Tempo -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Jatuh Tempo (Due Date) <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                                value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                        </div>

                        <!-- No PO / Kontrak -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nomor PO / Kontrak Klien</label>
                            <input type="text" name="po_number" class="form-control" placeholder="Contoh: PO-PLN-2026-009" value="{{ old('po_number') }}">
                        </div>

                        <!-- Catatan / Keterangan -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Catatan Tambahan</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Keterangan transfer atau instruksi pembayaran...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Card Pengaturan Pajak & DP -->
                <div class="card card-optima shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-percent me-2 text-primary"></i>Pajak & Potongan DP</h6>
                    </div>
                    <div class="card-body">
                        <!-- PPN -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Tarif PPN Keluaran (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="tax_ppn_rate" id="tax_ppn_rate" class="form-control" value="{{ old('tax_ppn_rate', 11.00) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Standar PPN Indonesia: 11% (Kode Akun 204)</small>
                        </div>

                        <!-- PPh 23 -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Potongan PPh 23 Jasa (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="tax_pph23_rate" id="tax_pph23_rate" class="form-control" value="{{ old('tax_pph23_rate', 2.00) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Standar PPh 23 Jasa Sertifikasi/Pelatihan: 2%</small>
                        </div>

                        <!-- Potongan Uang Muka DP -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Realisasi Uang Muka (DP) Klien</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" name="down_payment_applied" id="down_payment_applied" class="form-control" value="{{ old('down_payment_applied', 0) }}">
                            </div>
                            <small class="text-muted">Potong jika klien sudah membayar DP di muka (Akun 205)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Item Rincian & Total Kalkulasi -->
            <div class="col-12 col-lg-8">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-check me-2 text-primary"></i>Rincian Jasa / Item Tagihan</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Baris Item
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-optima mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Kategori</th>
                                        <th style="width: 35%;">Deskripsi Jasa</th>
                                        <th style="width: 12%;" class="text-end">Qty</th>
                                        <th style="width: 18%;" class="text-end">Harga Satuan (Rp)</th>
                                        <th style="width: 15%;" class="text-end">Subtotal (Rp)</th>
                                        <th style="width: 50px;" class="text-center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- Default Row 1 -->
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[0][item_type]" class="form-select form-select-sm" required>
                                                <option value="training" selected>Jasa Pelatihan</option>
                                                <option value="certification">Uji Sertifikasi BNSP</option>
                                                <option value="module">Modul & KIT</option>
                                                <option value="other">Lainnya</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="Contoh: Paket Pelatihan Ahli K3 Umum Batch 10" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control form-control-sm text-end item-qty" value="1" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-sm text-end item-price" value="0" min="0" required>
                                            <input type="hidden" name="items[0][discount]" value="0">
                                        </td>
                                        <td class="text-end fw-bold text-dark item-line-total">
                                            Rp 0
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm text-danger btn-remove-row" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Live Summary Calculation Card -->
                <div class="card card-optima shadow-sm">
                    <div class="card-header bg-light border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calculator me-2 text-primary"></i>Ringkasan Tagihan (Otomatis)</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row justify-content-end">
                            <div class="col-12 col-md-8">
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-secondary">Subtotal Item:</span>
                                    <span class="fw-semibold text-dark" id="display-subtotal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-secondary">PPN Keluaran (<span id="display-ppn-rate">11</span>%):</span>
                                    <span class="fw-semibold text-dark" id="display-ppn-amount">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-secondary">Potongan DP (Uang Muka):</span>
                                    <span class="fw-semibold text-danger" id="display-dp-amount">- Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between py-3 border-bottom bg-light px-3 rounded mt-2">
                                    <span class="fw-bold text-dark fs-5">Total Tagihan (Invoice):</span>
                                    <span class="fw-bold text-primary fs-5" id="display-grand-total">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 text-muted small px-3">
                                    <span>Estimasi PPh 23 (<span id="display-pph23-rate">2</span>%) saat dibayar:</span>
                                    <span id="display-pph23-amount">Rp 0</span>
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                        <i class="bi bi-check2-circle me-1"></i> Simpan Draft Invoice
                                    </button>
                                    <small class="text-center text-muted">
                                        Invoice akan disimpan sebagai <strong>Draft</strong>. Posting jurnal double-entry otomatis dilakukan saat tombol <em>"Terbitkan Invoice"</em> ditekan.
                                    </small>
                                </div>
                            </div>
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
    let rowIndex = 1;
    const itemsBody = document.getElementById('itemsBody');
    const btnAddItem = document.getElementById('btn-add-item');

    function formatRupiah(number) {
        return 'Rp ' + Number(number).toLocaleString('id-ID');
    }

    function calculateTotals() {
        let subtotal = 0;
        const rows = document.querySelectorAll('.item-row');

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const lineTotal = qty * price;
            row.querySelector('.item-line-total').innerText = formatRupiah(lineTotal);
            subtotal += lineTotal;
        });

        const ppnRate = parseFloat(document.getElementById('tax_ppn_rate').value) || 0;
        const ppnAmount = (ppnRate > 0) ? (subtotal * (ppnRate / 100)) : 0;

        const pphRate = parseFloat(document.getElementById('tax_pph23_rate').value) || 0;
        const pphAmount = (pphRate > 0) ? (subtotal * (pphRate / 100)) : 0;

        const dpApplied = parseFloat(document.getElementById('down_payment_applied').value) || 0;
        const grandTotal = Math.max(0, (subtotal + ppnAmount) - dpApplied);

        document.getElementById('display-subtotal').innerText = formatRupiah(subtotal);
        document.getElementById('display-ppn-rate').innerText = ppnRate;
        document.getElementById('display-ppn-amount').innerText = formatRupiah(ppnAmount);
        document.getElementById('display-dp-amount').innerText = '- ' + formatRupiah(dpApplied);
        document.getElementById('display-grand-total').innerText = formatRupiah(grandTotal);
        document.getElementById('display-pph23-rate').innerText = pphRate;
        document.getElementById('display-pph23-amount').innerText = formatRupiah(pphAmount);
    }

    btnAddItem.addEventListener('click', function () {
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td>
                <select name="items[${rowIndex}][item_type]" class="form-select form-select-sm" required>
                    <option value="training">Jasa Pelatihan</option>
                    <option value="certification" selected>Uji Sertifikasi BNSP</option>
                    <option value="module">Modul & KIT</option>
                    <option value="other">Lainnya</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm" placeholder="Deskripsi jasa..." required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm text-end item-qty" value="1" min="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm text-end item-price" value="0" min="0" required>
                <input type="hidden" name="items[${rowIndex}][discount]" value="0">
            </td>
            <td class="text-end fw-bold text-dark item-line-total">
                Rp 0
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm text-danger btn-remove-row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        itemsBody.appendChild(newRow);
        rowIndex++;
        calculateTotals();
    });

    itemsBody.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-row')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            }
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-qty') || 
            e.target.classList.contains('item-price') || 
            e.target.id === 'tax_ppn_rate' || 
            e.target.id === 'tax_pph23_rate' || 
            e.target.id === 'down_payment_applied') {
            calculateTotals();
        }
    });

    calculateTotals();
});
</script>
@endpush
@endsection
