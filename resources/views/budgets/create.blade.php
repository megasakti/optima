@extends('layouts.app')

@section('title', 'Susun Rencana Anggaran Biaya (RAB) Pelatihan')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('budgets.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold text-dark mb-0">Penyusunan Rencana Anggaran Biaya (RAB)</h4>
            </div>
            <p class="text-secondary small mb-0">Rancang estimasi biaya langsung operasional, proyeksi omset, dan kalkulasi margin laba kotor sebelum pelaksanaan batch.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terdapat kesalahan pada input form:</h6>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('budgets.store') }}" method="POST" id="budgetForm">
        @csrf

        <div class="row g-4 mb-4">
            <!-- Left Card: Batch Selection & Target Revenue -->
            <div class="col-12 col-lg-8">
                <div class="card card-optima shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="bi bi-mortarboard me-2"></i>Pilih Angkatan / Batch & Target Penjualan
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Pilih Batch Pelatihan <span class="text-danger">*</span></label>
                                <select name="batch_id" id="batch_select" class="form-select @error('batch_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Batch --</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}" 
                                            data-pax="{{ $batch->target_participants }}"
                                            data-price="{{ $batch->price_per_participant }}"
                                            {{ (old('batch_id', $selectedBatch?->id) == $batch->id) ? 'selected' : '' }}>
                                            {{ $batch->batch_code }} - {{ $batch->name }} ({{ $batch->program->name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('batch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">Target Peserta (Pax) <span class="text-danger">*</span></label>
                                <input type="number" name="target_participants" id="target_participants" 
                                    class="form-control text-end @error('target_participants') is-invalid @enderror"
                                    value="{{ old('target_participants', $selectedBatch?->target_participants ?? 15) }}" min="1" required>
                                @error('target_participants')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">Harga Jual / Pax (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="target_revenue_per_pax" id="target_revenue_per_pax" 
                                    class="form-control text-end @error('target_revenue_per_pax') is-invalid @enderror"
                                    value="{{ old('target_revenue_per_pax', $selectedBatch?->price_per_participant ?? 4500000) }}" min="0" required>
                                @error('target_revenue_per_pax')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan / Asumsi Anggaran</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Asumsi kuota minimal break-even, negosiasi harga venue/hotel, atau kesepakatan honor trainer...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Card: Live Margin Calculator Summary -->
            <div class="col-12 col-lg-4">
                <div class="card card-optima shadow-sm h-100 bg-white border-primary">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-calculator me-2"></i>Simulasi Margin Laba (Live)
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Target Omset (Revenue):</span>
                            <span class="fw-bold text-success fs-6" id="disp_total_revenue">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Total Anggaran Biaya (HPP):</span>
                            <span class="fw-bold text-danger fs-6" id="disp_total_cost">Rp 0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark">Proyeksi Laba Kotor:</span>
                            <span class="fw-bold fs-5 text-primary" id="disp_gross_margin">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Proyeksi Margin (%):</span>
                            <span class="badge bg-success fs-6 px-3 py-1" id="disp_margin_pct">0.0%</span>
                        </div>
                        <div class="mt-4 p-2 rounded text-center small" id="margin_alert_box">
                            <span id="margin_alert_text">Menghitung kelayakan margin...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Cost Items Table -->
        <div class="card card-optima shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-list-check me-2"></i>Rincian Pos Biaya Langsung (HPP Training & Sertifikasi BNSP)
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add_row_btn">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Pos Biaya
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="items_table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 16%;">Kategori</th>
                            <th style="width: 25%;">Deskripsi Komponen Biaya</th>
                            <th style="width: 20%;">Akun Beban Akuntansi</th>
                            <th style="width: 10%;" class="text-center">Vol / Qty</th>
                            <th style="width: 9%;" class="text-center">Satuan</th>
                            <th style="width: 14%;" class="text-end">Harga Satuan (Rp)</th>
                            <th style="width: 16%;" class="text-end">Subtotal (Rp)</th>
                            <th style="width: 5%;" class="text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="items_tbody">
                        <!-- Default Rows generated via JavaScript or Server Blade -->
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end fw-bold">TOTAL ANGGARAN BIAYA LANGSUNG (HPP):</th>
                            <th class="text-end fw-bold text-danger fs-6" id="table_total_cost">Rp 0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Pastikan akun beban disesuaikan dengan skema akuntansi (Pos 504 LSP Sertifikasi, 505 Honor Trainer, 515 Hotel/Venue, 514 Konsumsi, 503 Seminar Kit).
                </small>
                <div class="d-flex gap-2">
                    <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Draft RAB
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Account options hidden template -->
<div id="account_options_template" class="d-none">
    <option value="">-- Pilih Akun Beban --</option>
    @foreach ($expenseAccounts as $acc)
        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const batchSelect = document.getElementById('batch_select');
    const targetPaxInput = document.getElementById('target_participants');
    const pricePaxInput = document.getElementById('target_revenue_per_pax');
    const tbody = document.getElementById('items_tbody');
    const addRowBtn = document.getElementById('add_row_btn');

    const dispTotalRev = document.getElementById('disp_total_revenue');
    const dispTotalCost = document.getElementById('disp_total_cost');
    const dispGrossMargin = document.getElementById('disp_gross_margin');
    const dispMarginPct = document.getElementById('disp_margin_pct');
    const marginAlertBox = document.getElementById('margin_alert_box');
    const marginAlertText = document.getElementById('margin_alert_text');
    const tableTotalCost = document.getElementById('table_total_cost');

    const accountOptionsHtml = document.getElementById('account_options_template').innerHTML;

    let rowIndex = 0;

    // Default template data for initial load
    const defaultItems = [
        { category: 'certification', desc: 'Uji Kompetensi & Sertifikasi Asesmen BNSP (per peserta)', accCode: '504', qty: 15, unit: 'Pax', price: 1500000 },
        { category: 'trainer', desc: 'Honorarium Instruktur Utama (2 Hari)', accCode: '505', qty: 2, unit: 'Hari', price: 4000000 },
        { category: 'venue', desc: 'Sewa Ruang Meeting Fullboard Hotel (2 Hari)', accCode: '515', qty: 15, unit: 'Pax/Hari', price: 450000 },
        { category: 'materials', desc: 'Seminar Kit, Modul, Binder & Souvenir', accCode: '503', qty: 15, unit: 'Paket', price: 150000 },
        { category: 'catering', desc: 'Konsumsi Coffee Break Tambahan & Snack Panitia', accCode: '514', qty: 2, unit: 'Hari', price: 350000 }
    ];

    function createRow(data = {}) {
        const tr = document.createElement('tr');
        const idx = rowIndex++;

        tr.innerHTML = `
            <td>
                <select name="items[${idx}][category]" class="form-select form-select-sm item-category" required>
                    <option value="certification" ${data.category === 'certification' ? 'selected' : ''}>Sertifikasi LSP (504)</option>
                    <option value="trainer" ${data.category === 'trainer' ? 'selected' : ''}>Honor Trainer (505)</option>
                    <option value="venue" ${data.category === 'venue' ? 'selected' : ''}>Sewa Hotel/Venue (515)</option>
                    <option value="catering" ${data.category === 'catering' ? 'selected' : ''}>Konsumsi/Katering (514)</option>
                    <option value="materials" ${data.category === 'materials' ? 'selected' : ''}>Seminar Kit & Modul (503)</option>
                    <option value="operational" ${data.category === 'operational' ? 'selected' : ''}>Operasional Lainnya</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${idx}][description]" class="form-control form-select-sm item-desc" 
                    value="${data.desc || ''}" placeholder="Deskripsi biaya..." required>
            </td>
            <td>
                <select name="items[${idx}][account_id]" class="form-select form-select-sm item-account">
                    ${accountOptionsHtml}
                </select>
            </td>
            <td>
                <input type="number" step="any" name="items[${idx}][quantity]" class="form-control form-select-sm text-center item-qty" 
                    value="${data.qty || 1}" min="0.01" required>
            </td>
            <td>
                <input type="text" name="items[${idx}][unit]" class="form-control form-select-sm text-center item-unit" 
                    value="${data.unit || 'Pax'}" required>
            </td>
            <td>
                <input type="number" step="any" name="items[${idx}][unit_price]" class="form-control form-select-sm text-end item-price" 
                    value="${data.price || 0}" min="0" required>
            </td>
            <td class="text-end fw-bold item-subtotal-disp">
                Rp 0
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Hapus Baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Pre-select account matching code if supplied
        if (data.accCode) {
            const accSelect = tr.querySelector('.item-account');
            for (let opt of accSelect.options) {
                if (opt.text.startsWith(data.accCode)) {
                    accSelect.value = opt.value;
                    break;
                }
            }
        }

        // Attach listeners for calculation
        const qtyInput = tr.querySelector('.item-qty');
        const priceInput = tr.querySelector('.item-price');
        const removeBtn = tr.querySelector('.remove-row-btn');

        qtyInput.addEventListener('input', calculateAll);
        priceInput.addEventListener('input', calculateAll);
        removeBtn.addEventListener('click', function () {
            if (tbody.children.length > 1) {
                tr.remove();
                calculateAll();
            } else {
                alert('RAB harus memiliki minimal 1 pos biaya.');
            }
        });

        calculateRowSubtotal(tr);
    }

    function calculateRowSubtotal(tr) {
        const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
        const price = parseFloat(tr.querySelector('.item-price').value) || 0;
        const subtotal = qty * price;
        tr.querySelector('.item-subtotal-disp').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        return subtotal;
    }

    function calculateAll() {
        const pax = parseFloat(targetPaxInput.value) || 0;
        const pricePerPax = parseFloat(pricePaxInput.value) || 0;
        const totalRevenue = pax * pricePerPax;

        let totalCost = 0;
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(tr => {
            totalCost += calculateRowSubtotal(tr);
        });

        const grossMargin = totalRevenue - totalCost;
        const marginPct = (totalRevenue > 0) ? (grossMargin / totalRevenue) * 100 : 0;

        dispTotalRev.textContent = 'Rp ' + totalRevenue.toLocaleString('id-ID');
        dispTotalCost.textContent = 'Rp ' + totalCost.toLocaleString('id-ID');
        tableTotalCost.textContent = 'Rp ' + totalCost.toLocaleString('id-ID');
        dispGrossMargin.textContent = 'Rp ' + grossMargin.toLocaleString('id-ID');
        dispMarginPct.textContent = marginPct.toFixed(1) + '%';

        // Badge & Alert styling
        if (marginPct >= 30) {
            dispMarginPct.className = 'badge bg-success fs-6 px-3 py-1';
            marginAlertBox.className = 'mt-4 p-2 rounded text-center small bg-success-subtle text-success border border-success';
            marginAlertText.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Margin Sangat Sehat (&ge; 30%)';
        } else if (marginPct >= 15) {
            dispMarginPct.className = 'badge bg-warning text-dark fs-6 px-3 py-1';
            marginAlertBox.className = 'mt-4 p-2 rounded text-center small bg-warning-subtle text-warning-emphasis border border-warning';
            marginAlertText.innerHTML = '<i class="bi bi-info-circle-fill me-1"></i> Margin Cukup (15% - 29%)';
        } else {
            dispMarginPct.className = 'badge bg-danger fs-6 px-3 py-1';
            marginAlertBox.className = 'mt-4 p-2 rounded text-center small bg-danger-subtle text-danger border border-danger';
            marginAlertText.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Margin Rendah / Defisit (&lt; 15%)';
        }
    }

    addRowBtn.addEventListener('click', function () {
        createRow();
        calculateAll();
    });

    targetPaxInput.addEventListener('input', calculateAll);
    pricePaxInput.addEventListener('input', calculateAll);

    batchSelect.addEventListener('change', function () {
        const opt = batchSelect.options[batchSelect.selectedIndex];
        const pPax = opt.getAttribute('data-pax');
        const pPrice = opt.getAttribute('data-price');
        if (pPax) targetPaxInput.value = pPax;
        if (pPrice) pricePaxInput.value = pPrice;

        // update items default pax
        const rows = tbody.querySelectorAll('tr');
        if (pPax && parseFloat(pPax) > 0) {
            rows.forEach(tr => {
                const cat = tr.querySelector('.item-category').value;
                if (cat === 'certification' || cat === 'materials') {
                    tr.querySelector('.item-qty').value = pPax;
                }
            });
        }
        calculateAll();
    });

    // Populate initial items
    defaultItems.forEach(item => createRow(item));
    calculateAll();
});
</script>
@endsection
