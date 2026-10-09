@extends('layouts.app')

@section('title', 'Kwitansi ' . $payment->payment_number)

@section('content')
<div class="container-fluid px-0">
    <!-- Header Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold text-dark mb-0">
                    {{ $payment->payment_number }}
                    <span class="badge badge-soft-success ms-2 fs-6">Terverifikasi (Posted)</span>
                </h4>
                <small class="text-muted">Dicatat oleh {{ $payment->createdBy->name ?? 'Admin' }} pada {{ $payment->created_at->format('d/m/Y H:i') }}</small>
            </div>
        </div>

        <div>
            <a href="{{ route('payments.receipt-pdf', $payment->id) }}" target="_blank" class="btn btn-outline-danger">
                <i class="bi bi-file-pdf me-1"></i> Cetak Kwitansi Resmi PDF
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Kwitansi Information -->
        <div class="col-12 col-lg-8">
            <div class="card card-optima shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase fw-bold">Diterima Dari:</span>
                            <h5 class="fw-bold text-dark mt-1 mb-1">{{ $payment->customer->name }}</h5>
                            <div class="text-secondary small">{{ $payment->customer->address ?? '-' }}</div>
                            <div class="text-secondary small">Email: {{ $payment->customer->email ?? '-' }} | Telp: {{ $payment->customer->phone ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                            <span class="text-muted small text-uppercase fw-bold">Rincian Penerimaan:</span>
                            <table class="table table-sm table-borderless mt-1 mb-0 text-sm-end">
                                <tr>
                                    <td class="text-muted small">No. Kwitansi:</td>
                                    <td class="fw-bold text-success">{{ $payment->payment_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">Tanggal Masuk:</td>
                                    <td class="fw-semibold">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">Rekening Bank:</td>
                                    <td class="fw-semibold text-primary">{{ $payment->cashBankAccount->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">Metode Bayar:</td>
                                    <td class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                </tr>
                                @if ($payment->reference_number)
                                <tr>
                                    <td class="text-muted small">No. Referensi Bank:</td>
                                    <td class="fw-semibold">{{ $payment->reference_number }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Nominal Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-light rounded text-center border">
                                <span class="text-muted small text-uppercase">Uang Masuk (Net Kas)</span>
                                <h5 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($payment->amount, 0, ',', '.') }}</h5>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-light rounded text-center border">
                                <span class="text-muted small text-uppercase">Bukti Potong PPh 23</span>
                                <h5 class="fw-bold text-dark mb-0 mt-1">
                                    {{ (float) $payment->pph23_deducted > 0 ? 'Rp ' . number_format($payment->pph23_deducted, 0, ',', '.') : '-' }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-primary-subtle rounded text-center border border-primary-subtle">
                                <span class="text-primary small text-uppercase fw-bold">Total Kredit Piutang</span>
                                <h5 class="fw-bold text-primary mb-0 mt-1">Rp {{ number_format($payment->total_credited, 0, ',', '.') }}</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Alokasi Invoice -->
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-link-45deg me-1 text-primary"></i>Faktur Invoice yang Dilunasi</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-optima mb-0">
                            <thead>
                                <tr>
                                    <th>No. Faktur</th>
                                    <th>Tgl Invoice</th>
                                    <th class="text-end">Total Tagihan</th>
                                    <th class="text-end">Sisa Sebelum Bayar</th>
                                    <th class="text-end">Alokasi Pelunasan (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payment->allocations as $alloc)
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <a href="{{ route('invoices.show', $alloc->invoice->id) }}">
                                                {{ $alloc->invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $alloc->invoice->invoice_date->format('d/m/Y') }}</td>
                                        <td class="text-end">Rp {{ number_format($alloc->invoice->total_amount, 0, ',', '.') }}</td>
                                        <td class="text-end text-muted">
                                            Rp {{ number_format($alloc->invoice->balance_due + $alloc->amount_applied, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            Rp {{ number_format($alloc->amount_applied, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($payment->notes)
                        <div class="p-3 bg-light rounded small mt-3">
                            <strong>Catatan:</strong> {{ $payment->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Jurnal Double Entry Akuntansi -->
        <div class="col-12 col-lg-4">
            <div class="card card-optima shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check me-2 text-primary"></i>Jurnal Akuntansi</h6>
                    <span class="badge bg-success-subtle text-success">Posted</span>
                </div>
                <div class="card-body">
                    @if ($payment->journalEntry)
                        <div class="mb-3">
                            <span class="text-muted small">No. Bukti Jurnal:</span>
                            <div class="fw-bold text-primary">
                                <a href="{{ route('accounting.journals.show', $payment->journalEntry->id) }}">
                                    {{ $payment->journalEntry->entry_number }}
                                </a>
                            </div>
                            <small class="text-muted">Tgl: {{ $payment->journalEntry->entry_date->format('d/m/Y') }}</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Akun COA</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payment->journalEntry->lines as $line)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $line->account->code }} - {{ $line->account->name }}</div>
                                            </td>
                                            <td class="text-end {{ $line->debit > 0 ? 'fw-bold' : 'text-muted' }}">
                                                {{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-end {{ $line->credit > 0 ? 'fw-bold' : 'text-muted' }}">
                                                {{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted small">Tidak ada jurnal terkait.</div>
                    @endif
                </div>
            </div>

            <!-- Rekening Kas/Bank Info Card -->
            <div class="card card-optima shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bank me-2 text-primary"></i>Rekening Bank Penerima</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Nama Bank/Kas:</span>
                            <span class="fw-semibold">{{ $payment->cashBankAccount->name }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Nomor Rekening:</span>
                            <span class="fw-semibold">{{ $payment->cashBankAccount->account_number ?? '-' }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Saldo Terkini:</span>
                            <span class="fw-bold text-success">Rp {{ number_format($payment->cashBankAccount->current_balance, 0, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
