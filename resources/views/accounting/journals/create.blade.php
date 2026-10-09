@extends('layouts.app')

@section('title', 'Buat Jurnal Manual')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Buat Jurnal Umum Manual</h4>
        <p class="text-secondary small mb-0">Input mutasi akuntansi double-entry (penyesuaian, pengeluaran kas, akrual).</p>
    </div>
    <a href="{{ route('accounting.journals.index') }}" class="btn btn-light border">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Jurnal
    </a>
</div>

<form method="POST" action="{{ route('accounting.journals.store') }}" id="journalForm">
    @csrf

    <!-- Header Information Card -->
    <div class="card card-optima p-4 mb-4">
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
            <i class="bi bi-info-circle text-primary me-2"></i> Informasi Dokumen Jurnal
        </h6>
        <div class="row g-3">
            <div class="col-md-3">
                <label for="entry_date" class="form-label small fw-medium">Tanggal Jurnal</label>
                <input type="date" name="entry_date" id="entry_date" class="form-control" value="{{ old('entry_date', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label for="batch_id" class="form-label small fw-medium">Tag Batch Pelatihan (Opsional)</label>
                <select name="batch_id" id="batch_id" class="form-select">
                    <option value="">-- Tanpa Tag Batch (Beban Umum) --</option>
                    @foreach ($batches as $b)
                        <option value="{{ $b->id }}" {{ old('batch_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->batch_code }} - {{ $b->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted" style="font-size: 0.72rem;">Digunakan untuk perhitungan laba rugi batch.</small>
            </div>
            <div class="col-md-5">
                <label for="description" class="form-label small fw-medium">Keterangan Transaksi</label>
                <input type="text" name="description" id="description" class="form-control" placeholder="Contoh: Pembayaran Biaya Hotel Batch 01" value="{{ old('description') }}" required>
            </div>
        </div>
    </div>

    <!-- Lines Items Table Card -->
    <div class="card card-optima p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-list-task text-primary me-2"></i> Rincian Baris Akun (Debit & Kredit)
            </h6>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                <i class="bi bi-plus-circle me-1"></i> Tambah Baris
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="linesTable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 280px;">Akun COA</th>
                        <th style="min-width: 160px;" class="text-end">Debit (Rp)</th>
                        <th style="min-width: 160px;" class="text-end">Kredit (Rp)</th>
                        <th>Memo / Catatan Baris</th>
                        <th style="width: 50px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="linesTbody">
                    <!-- Row 1 (Default Debit) -->
                    <tr>
                        <td>
                            <select name="lines[0][account_id]" class="form-select account-select" required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type) }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="lines[0][debit]" class="form-control text-end debit-input" value="0" oninput="calculateBalance()" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="lines[0][credit]" class="form-control text-end credit-input" value="0" oninput="calculateBalance()" required>
                        </td>
                        <td>
                            <input type="text" name="lines[0][memo]" class="form-control" placeholder="Memo...">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger" onclick="removeRow(this)" disabled>
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Row 2 (Default Credit) -->
                    <tr>
                        <td>
                            <select name="lines[1][account_id]" class="form-select account-select" required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type) }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="lines[1][debit]" class="form-control text-end debit-input" value="0" oninput="calculateBalance()" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="lines[1][credit]" class="form-control text-end credit-input" value="0" oninput="calculateBalance()" required>
                        </td>
                        <td>
                            <input type="text" name="lines[1][memo]" class="form-control" placeholder="Memo...">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light text-danger" onclick="removeRow(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td class="text-end">TOTAL:</td>
                        <td class="text-end text-primary" id="totalDebitDisplay">Rp 0,00</td>
                        <td class="text-end text-primary" id="totalCreditDisplay">Rp 0,00</td>
                        <td colspan="2">
                            <div id="balanceStatus">
                                <span class="badge bg-secondary">Masukkan nilai mutasi</span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('accounting.journals.index') }}" class="btn btn-light border">Batal</a>
        <button type="submit" class="btn btn-primary px-4 py-2" id="submitBtn" disabled>
            <i class="bi bi-check-circle me-1"></i> Posting Jurnal Sekarang
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    let rowIndex = 2;

    function addRow() {
        const tbody = document.getElementById('linesTbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="lines[${rowIndex}][account_id]" class="form-select account-select" required>
                    <option value="">-- Pilih Akun --</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type) }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="lines[${rowIndex}][debit]" class="form-control text-end debit-input" value="0" oninput="calculateBalance()" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="lines[${rowIndex}][credit]" class="form-control text-end credit-input" value="0" oninput="calculateBalance()" required>
            </td>
            <td>
                <input type="text" name="lines[${rowIndex}][memo]" class="form-control" placeholder="Memo...">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-light text-danger" onclick="removeRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
        calculateBalance();
    }

    function removeRow(btn) {
        const row = btn.closest('tr');
        const tbody = document.getElementById('linesTbody');
        if (tbody.children.length > 2) {
            row.remove();
            calculateBalance();
        } else {
            alert('Jurnal minimal memiliki 2 baris transaksi.');
        }
    }

    function calculateBalance() {
        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('.debit-input').forEach(input => {
            totalDebit += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.credit-input').forEach(input => {
            totalCredit += parseFloat(input.value) || 0;
        });

        document.getElementById('totalDebitDisplay').innerText = 'Rp ' + totalDebit.toLocaleString('id-ID', { minimumFractionDigits: 2 });
        document.getElementById('totalCreditDisplay').innerText = 'Rp ' + totalCredit.toLocaleString('id-ID', { minimumFractionDigits: 2 });

        const statusEl = document.getElementById('balanceStatus');
        const submitBtn = document.getElementById('submitBtn');

        const diff = Math.abs(totalDebit - totalCredit);

        if (totalDebit > 0 && diff < 0.009) {
            statusEl.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Seimbang (Balanced)</span>';
            submitBtn.disabled = false;
        } else if (totalDebit === 0 && totalCredit === 0) {
            statusEl.innerHTML = '<span class="badge bg-secondary">Nilai transaksi belum diisi</span>';
            submitBtn.disabled = true;
        } else {
            statusEl.innerHTML = `<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Selisih: Rp ${diff.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</span>`;
            submitBtn.disabled = true;
        }
    }
</script>
@endpush
