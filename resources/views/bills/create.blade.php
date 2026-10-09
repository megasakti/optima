@extends('layouts.app')

@section('title', 'Catat Tagihan Vendor / LSP Baru')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-file-earmark-plus text-primary me-2"></i>Catat Tagihan Vendor & LSP Baru
            </h4>
            <p class="text-secondary small mb-0">Input kewajiban biaya sertifikasi BNSP, hotel, katering, atau honor narasumber/asesor.</p>
        </div>
        <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('bills.store') }}" method="POST" id="billForm">
        @csrf
        <div class="row g-4">
            <!-- Left: Info Tagihan & Payee -->
            <div class="col-12 col-lg-4">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Pihak Penagih (Payee)</h6>
                    </div>
                    <div class="card-body">
                        <!-- Tipe Penagih -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Kategori Penagih <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="payee_type" id="type_lsp" value="lsp" {{ old('payee_type', 'lsp') === 'lsp' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="type_lsp"><i class="bi bi-award me-1"></i> LSP Mitra</label>

                                <input type="radio" class="btn-check" name="payee_type" id="type_vendor" value="vendor" {{ old('payee_type') === 'vendor' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="type_vendor"><i class="bi bi-building me-1"></i> Vendor Umum</label>

                                <input type="radio" class="btn-check" name="payee_type" id="type_trainer" value="trainer" {{ old('payee_type') === 'trainer' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="type_trainer"><i class="bi bi-person me-1"></i> Instruktur</label>
                            </div>
                        </div>

                        <!-- Dropdown LSP -->
                        <div class="mb-3" id="select_lsp_container">
                            <label class="form-label fw-semibold small">Lembaga Sertifikasi Profesi (LSP) <span class="text-danger">*</span></label>
                            <select name="lsp_id" id="lsp_id" class="form-select form-select-sm">
                                <option value="">-- Pilih LSP Mitra BNSP --</option>
                                @foreach ($lsps as $l)
                                    <option value="{{ $l->id }}" {{ old('lsp_id') == $l->id ? 'selected' : '' }}>
                                        {{ $l->name }} ({{ $l->code ?? 'LSP' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Akan dicatat ke Akun Utang 202 (Utang Sertifikasi LSP).</small>
                        </div>

                        <!-- Dropdown Vendor -->
                        <div class="mb-3 d-none" id="select_vendor_container">
                            <label class="form-label fw-semibold small">Vendor Rekanan <span class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendor_id" class="form-select form-select-sm">
                                <option value="">-- Pilih Vendor Rekanan --</option>
                                @foreach ($vendors as $v)
                                    <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>
                                        {{ $v->name }} ({{ ucfirst($v->category ?? 'Umum') }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Akan dicatat ke Akun Utang 201 (Utang Usaha).</small>
                        </div>

                        <!-- Dropdown Trainer -->
                        <div class="mb-3 d-none" id="select_trainer_container">
                            <label class="form-label fw-semibold small">Instruktur / Asesor <span class="text-danger">*</span></label>
                            <select name="trainer_id" id="trainer_id" class="form-select form-select-sm">
                                <option value="">-- Pilih Instruktur / Asesor --</option>
                                @foreach ($trainers as $t)
                                    <option value="{{ $t->id }}" {{ old('trainer_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }} ({{ $t->specialization ?? 'Narasumber' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Akan dicatat ke Akun Utang 201 (Utang Usaha).</small>
                        </div>

                        <!-- Batch Terkait -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Terkait Batch Pelatihan</label>
                            <select name="batch_id" class="form-select form-select-sm">
                                <option value="">-- Tanpa Batch (Beban Umum/Operasional) --</option>
                                @foreach ($batches as $b)
                                    <option value="{{ $b->id }}" {{ (old('batch_id', $selectedBatchId) == $b->id) ? 'selected' : '' }}>
                                        {{ $b->batch_code }} - {{ $b->program->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- No Invoice / Tagihan Asli Vendor -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">No. Faktur / Tagihan Asli Vendor</label>
                            <input type="text" name="vendor_invoice_number" class="form-control form-control-sm" placeholder="Contoh: INV/LSP-GEO/2026/044" value="{{ old('vendor_invoice_number') }}">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Tanggal Tagihan <span class="text-danger">*</span></label>
                                <input type="date" name="bill_date" class="form-control form-control-sm" value="{{ old('bill_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Jatuh Tempo <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control form-control-sm" value="{{ old('due_date', date('Y-m-d', strtotime('+14 days'))) }}" required>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Catatan Tagihan</label>
                            <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Catatan instruksi pembayaran atau termin...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Item Biaya & Rincian Beban -->
            <div class="col-12 col-lg-8">
                <div class="card card-optima shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-check me-2 text-primary"></i>Rincian Pos Biaya / Beban Tagihan</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Pos Biaya
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-optima mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Akun Beban (COA)</th>
                                        <th style="width: 32%;">Deskripsi Beban</th>
                                        <th style="width: 10%;" class="text-end">Qty</th>
                                        <th style="width: 15%;" class="text-end">Harga Satuan</th>
                                        <th style="width: 13%;" class="text-end">Subtotal</th>
                                        <th style="width: 40px;" class="text-center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- Row 0 -->
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[0][account_id]" class="form-select form-select-sm" required>
                                                @foreach ($expenseAccounts as $acc)
                                                    <option value="{{ $acc->id }}" {{ $acc->code === '504' ? 'selected' : '' }}>
                                                        {{ $acc->code }} - {{ $acc->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="Contoh: Biaya Uji Asesmen 15 Asesi K3" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control form-control-sm text-end item-qty" value="1" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-sm text-end item-price" value="0" min="0" required>
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

                <!-- Total & Simpan -->
                <div class="card card-optima shadow-sm">
                    <div class="card-header bg-light border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calculator me-2 text-primary"></i>Total Tagihan & Kewajiban Hutang</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row justify-content-end">
                            <div class="col-12 col-md-8">
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-secondary">Subtotal Beban Item:</span>
                                    <span class="fw-semibold text-dark" id="display-subtotal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="text-secondary">Pajak Masukan (PPN / Lainnya):</span>
                                    <div class="input-group input-group-sm w-50">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="number" step="0.01" name="tax_amount" id="tax_amount" class="form-control text-end" value="{{ old('tax_amount', 0) }}">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between py-3 border-bottom bg-light px-3 rounded mt-2">
                                    <span class="fw-bold text-dark fs-5">Total Hutang Tagihan:</span>
                                    <span class="fw-bold text-danger fs-5" id="display-grand-total">Rp 0</span>
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                        <i class="bi bi-check2-circle me-1"></i> Simpan Draft Tagihan
                                    </button>
                                    <small class="text-center text-muted">
                                        Tagihan disimpan sebagai <strong>Draft</strong>. Jurnal pengakuan beban dan utang otomatis diposting setelah tombol <em>"Setujui Tagihan"</em> ditekan.
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
    // 1. Payee Type Switcher
    const radioLsp = document.getElementById('type_lsp');
    const radioVendor = document.getElementById('type_vendor');
    const radioTrainer = document.getElementById('type_trainer');
    const containerLsp = document.getElementById('select_lsp_container');
    const containerVendor = document.getElementById('select_vendor_container');
    const containerTrainer = document.getElementById('select_trainer_container');

    function updatePayeeVisibility() {
        if (radioLsp.checked) {
            containerLsp.classList.remove('d-none');
            containerVendor.classList.add('d-none');
            containerTrainer.classList.add('d-none');
        } else if (radioVendor.checked) {
            containerLsp.classList.add('d-none');
            containerVendor.classList.remove('d-none');
            containerTrainer.classList.add('d-none');
        } else {
            containerLsp.classList.add('d-none');
            containerVendor.classList.add('d-none');
            containerTrainer.classList.remove('d-none');
        }
    }

    [radioLsp, radioVendor, radioTrainer].forEach(r => r.addEventListener('change', updatePayeeVisibility));
    updatePayeeVisibility();

    // 2. Dynamic Items Table
    let rowIndex = 1;
    const itemsBody = document.getElementById('itemsBody');
    const btnAddItem = document.getElementById('btn-add-item');
    const taxInp = document.getElementById('tax_amount');

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const lineTotal = qty * price;
            row.querySelector('.item-line-total').innerText = formatRupiah(lineTotal);
            subtotal += lineTotal;
        });

        const tax = parseFloat(taxInp.value) || 0;
        const grandTotal = subtotal + tax;

        document.getElementById('display-subtotal').innerText = formatRupiah(subtotal);
        document.getElementById('display-grand-total').innerText = formatRupiah(grandTotal);
    }

    btnAddItem.addEventListener('click', function () {
        const firstRowSelect = itemsBody.querySelector('select').innerHTML;
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td>
                <select name="items[${rowIndex}][account_id]" class="form-select form-select-sm" required>
                    ${firstRowSelect}
                </select>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm" placeholder="Deskripsi pos biaya..." required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm text-end item-qty" value="1" min="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm text-end item-price" value="0" min="0" required>
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
        if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price') || e.target.id === 'tax_amount') {
            calculateTotals();
        }
    });

    calculateTotals();
});
</script>
@endpush
@endsection
