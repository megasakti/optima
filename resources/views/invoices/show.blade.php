@extends('layouts.app')

@section('title', 'Detail Faktur ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid px-0">
    <!-- Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold text-dark mb-0">
                    {{ $invoice->invoice_number }}
                    @if ($invoice->status === 'draft')
                        <span class="badge badge-soft-warning ms-2 fs-6">Draft</span>
                    @elseif ($invoice->status === 'issued')
                        <span class="badge badge-soft-primary ms-2 fs-6">Issued (Aktif)</span>
                    @elseif ($invoice->status === 'partial')
                        <span class="badge badge-soft-info ms-2 fs-6">Sebagian (Partial)</span>
                    @elseif ($invoice->status === 'paid')
                        <span class="badge badge-soft-success ms-2 fs-6">Lunas (Paid)</span>
                    @elseif ($invoice->status === 'cancelled')
                        <span class="badge bg-secondary text-white ms-2 fs-6">Dibatalkan</span>
                    @endif
                </h4>
                <small class="text-muted">Dibuat oleh {{ $invoice->createdBy->name ?? 'Admin' }} pada {{ $invoice->created_at->format('d/m/Y H:i') }}</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank" class="btn btn-outline-danger">
                <i class="bi bi-file-pdf me-1"></i> Cetak PDF
            </a>

            @if ($invoice->status === 'draft')
                @can('approve-invoices')
                <form action="{{ route('invoices.issue', $invoice->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerbitkan invoice ini? Jurnal akuntansi piutang akan otomatis diposting.')">
                    @csrf
                    <button type="submit" class="btn btn-success shadow-sm">
                        <i class="bi bi-send-check me-1"></i> Terbitkan Invoice & Posting Jurnal
                    </button>
                </form>
                @endcan
            @endif

            @if (in_array($invoice->status, ['issued', 'partial']) && $invoice->balance_due > 0)
                @can('record-customer-payments')
                <a href="{{ route('payments.create', ['customer_id' => $invoice->customer_id, 'invoice_id' => $invoice->id]) }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-cash-coin me-1"></i> Catat Pembayaran / Pelunasan
                </a>
                @endcan
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Invoice Content -->
        <div class="col-12 col-lg-8">
            <!-- Invoice Details Card -->
            <div class="card card-optima shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase fw-bold">Ditujukan Kepada (Klien):</span>
                            <h5 class="fw-bold text-dark mt-1 mb-1">{{ $invoice->customer->name }}</h5>
                            <div class="text-secondary small">{{ $invoice->customer->address ?? 'Alamat klien belum diisi' }}</div>
                            <div class="text-secondary small">Email: {{ $invoice->customer->email ?? '-' }} | Telp: {{ $invoice->customer->phone ?? '-' }}</div>
                            @if ($invoice->customer->npwp)
                                <div class="text-secondary small">NPWP: {{ $invoice->customer->npwp }}</div>
                            @endif
                        </div>
                        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                            <span class="text-muted small text-uppercase fw-bold">Rincian Faktur:</span>
                            <table class="table table-sm table-borderless mt-1 mb-0 text-sm-end">
                                <tr>
                                    <td class="text-muted small">No. Faktur:</td>
                                    <td class="fw-bold text-primary">{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">Tgl. Terbit:</td>
                                    <td class="fw-semibold">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">Jatuh Tempo:</td>
                                    <td class="fw-semibold text-danger">{{ $invoice->due_date->format('d/m/Y') }}</td>
                                </tr>
                                @if ($invoice->po_number)
                                <tr>
                                    <td class="text-muted small">No. PO:</td>
                                    <td class="fw-semibold">{{ $invoice->po_number }}</td>
                                </tr>
                                @endif
                                @if ($invoice->batch)
                                <tr>
                                    <td class="text-muted small">Batch Pelatihan:</td>
                                    <td class="fw-semibold">{{ $invoice->batch->batch_code }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-optima mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 20%;">Kategori</th>
                                    <th>Deskripsi Layanan</th>
                                    <th class="text-end" style="width: 10%;">Qty</th>
                                    <th class="text-end" style="width: 20%;">Harga Satuan</th>
                                    <th class="text-end" style="width: 20%;">Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->items as $idx => $item)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            @if ($item->item_type === 'training')
                                                <span class="badge bg-primary-subtle text-primary">Pelatihan</span>
                                            @elseif ($item->item_type === 'certification')
                                                <span class="badge bg-success-subtle text-success">Sertifikasi BNSP</span>
                                            @elseif ($item->item_type === 'module')
                                                <span class="badge bg-warning-subtle text-warning">Modul / KIT</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($item->item_type) }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $item->description }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Financial Summary Breakdown -->
                    <div class="row justify-content-end">
                        <div class="col-12 col-md-6">
                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between py-1 small">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="fw-semibold text-dark">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 small">
                                    <span class="text-muted">PPN Keluaran ({{ $invoice->tax_ppn_rate }}%):</span>
                                    <span class="fw-semibold text-dark">Rp {{ number_format($invoice->tax_ppn_amount, 0, ',', '.') }}</span>
                                </div>
                                @if ((float) $invoice->down_payment_applied > 0)
                                <div class="d-flex justify-content-between py-1 small">
                                    <span class="text-muted">Potongan Uang Muka (DP):</span>
                                    <span class="fw-semibold text-danger">- Rp {{ number_format($invoice->down_payment_applied, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                <hr class="my-2">
                                <div class="d-flex justify-content-between py-1">
                                    <span class="fw-bold text-dark">Total Faktur:</span>
                                    <span class="fw-bold text-primary fs-5">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 small text-success">
                                    <span>Sudah Dibayar:</span>
                                    <span class="fw-semibold">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                    <span>Sisa Tagihan (Balance Due):</span>
                                    <span>Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</span>
                                </div>
                                @if ((float) $invoice->tax_pph23_amount > 0)
                                    <div class="d-flex justify-content-between py-1 small text-muted border-top mt-2 pt-2">
                                        <span>Potongan PPh 23 ({{ $invoice->tax_pph23_rate }}%):</span>
                                        <span>Rp {{ number_format($invoice->tax_pph23_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($invoice->notes)
                        <div class="mt-4 p-3 bg-light rounded small">
                            <strong>Catatan:</strong> {{ $invoice->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Allocations History -->
            <div class="card card-optima shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pembayaran Klien</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-optima mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">No. Kwitansi</th>
                                    <th>Tgl. Bayar</th>
                                    <th>Akun Rekening Kas/Bank</th>
                                    <th class="text-end">Jumlah Diterapkan</th>
                                    <th class="text-center pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoice->paymentAllocations as $alloc)
                                    <tr>
                                        <td class="ps-3 fw-bold text-primary">
                                            <a href="{{ route('payments.show', $alloc->customerPayment->id) }}">
                                                {{ $alloc->customerPayment->payment_number }}
                                            </a>
                                        </td>
                                        <td>{{ $alloc->customerPayment->payment_date->format('d/m/Y') }}</td>
                                        <td>{{ $alloc->customerPayment->cashBankAccount->name ?? '-' }}</td>
                                        <td class="text-end fw-bold text-success">
                                            Rp {{ number_format($alloc->amount_applied, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center pe-3">
                                            <a href="{{ route('payments.receipt-pdf', $alloc->customerPayment->id) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Cetak Kwitansi">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted small">
                                            Belum ada pembayaran yang dicatat untuk invoice ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Jurnal & Log Aktivitas -->
        <div class="col-12 col-lg-4">
            <!-- Jurnal Double-Entry Terkait -->
            <div class="card card-optima shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check me-2 text-primary"></i>Jurnal Akuntansi</h6>
                    @if ($invoice->journalEntry)
                        <span class="badge bg-success-subtle text-success">Posted</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">Belum Ada</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($invoice->journalEntry)
                        <div class="mb-3">
                            <span class="text-muted small">No. Bukti Jurnal:</span>
                            <div class="fw-bold text-primary">
                                <a href="{{ route('accounting.journals.show', $invoice->journalEntry->id) }}">
                                    {{ $invoice->journalEntry->entry_number }}
                                </a>
                            </div>
                            <small class="text-muted">Tgl: {{ $invoice->journalEntry->entry_date->format('d/m/Y') }}</small>
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
                                    @foreach ($invoice->journalEntry->lines as $line)
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
                        <div class="alert alert-light border small text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i> Invoice masih berstatus <strong>Draft</strong>. Jurnal piutang otomatis (Dr. Piutang, Cr. Pendapatan, Cr. PPN) akan dibentuk saat diterbitkan.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval & Audit Trail Info -->
            <div class="card card-optima shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Status & Verifikasi</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Dibuat Oleh:</span>
                            <span class="fw-semibold">{{ $invoice->createdBy->name ?? '-' }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Tanggal Buat:</span>
                            <span>{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @if ($invoice->approvedBy)
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Diterbitkan Oleh:</span>
                            <span class="fw-semibold text-success">{{ $invoice->approvedBy->name }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Tgl Penerbitan:</span>
                            <span>{{ $invoice->approved_at ? $invoice->approved_at->format('d/m/Y H:i') : '-' }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
