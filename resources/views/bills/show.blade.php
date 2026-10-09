@extends('layouts.app')

@section('title', 'Detail Tagihan ' . $bill->bill_number)

@section('content')
<div class="container-fluid px-0">
    <!-- Header Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold text-dark mb-0">
                    {{ $bill->bill_number }}
                    @if ($bill->status === 'draft')
                        <span class="badge badge-soft-warning ms-2 fs-6">Draft</span>
                    @elseif ($bill->status === 'approved')
                        <span class="badge badge-soft-primary ms-2 fs-6">Disetujui (Aktif)</span>
                    @elseif ($bill->status === 'partial')
                        <span class="badge badge-soft-info ms-2 fs-6">Sebagian (Partial)</span>
                    @elseif ($bill->status === 'paid')
                        <span class="badge badge-soft-success ms-2 fs-6">Lunas (Paid)</span>
                    @endif
                </h4>
                <small class="text-muted">Dibuat oleh {{ $bill->createdBy->name ?? 'Admin' }} pada {{ $bill->created_at->format('d/m/Y H:i') }}</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if ($bill->status === 'draft')
                @can('approve-bills')
                <form action="{{ route('bills.approve', $bill->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui tagihan ini? Jurnal akuntansi beban & hutang akan otomatis diposting.')">
                    @csrf
                    <button type="submit" class="btn btn-success shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Setujui Tagihan & Posting Jurnal
                    </button>
                </form>
                @endcan
            @endif

            @if (in_array($bill->status, ['approved', 'partial']) && $bill->balance_due > 0)
                @can('record-vendor-payments')
                @php
                    $ptype = $bill->lsp_id ? 'lsp' : ($bill->vendor_id ? 'vendor' : 'trainer');
                    $pid = $bill->lsp_id ?: ($bill->vendor_id ?: $bill->trainer_id);
                @endphp
                <a href="{{ route('vendor-payments.create', ['payee_type' => $ptype, 'payee_id' => $pid, 'bill_id' => $bill->id]) }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-credit-card me-1"></i> Bayar Tagihan Hutang
                </a>
                @endcan
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Bill Info -->
        <div class="col-12 col-lg-8">
            <div class="card card-optima shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            @php
                                $payee = $bill->lsp ?? $bill->vendor ?? $bill->trainer;
                            @endphp
                            <span class="text-muted small text-uppercase fw-bold">Pihak Penagih (Payee):</span>
                            <h5 class="fw-bold text-dark mt-1 mb-1">{{ $payee?->name ?? 'Vendor' }}</h5>
                            <div class="text-secondary small">
                                @if ($bill->lsp)
                                    <span class="badge bg-primary-subtle text-primary border">LSP Mitra BNSP</span>
                                @elseif ($bill->trainer)
                                    <span class="badge bg-success-subtle text-success border">Instruktur / Asesor</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">Vendor Umum</span>
                                @endif
                            </div>
                            <div class="text-secondary small mt-1">Alamat: {{ $payee?->address ?? '-' }}</div>
                            <div class="text-secondary small">Kontak: {{ $payee?->phone ?? '-' }} | Rekening: {{ $payee?->bank_account ?? ($payee?->bank_name . ' - ' . $payee?->account_number ?? '-') }}</div>
                        </div>
                        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                            <span class="text-muted small text-uppercase fw-bold">Detail Tagihan:</span>
                            <table class="table table-sm table-borderless mt-1 mb-0 text-sm-end">
                                <tr>
                                    <td class="text-muted small">No. Tagihan Sistem:</td>
                                    <td class="fw-bold text-primary">{{ $bill->bill_number }}</td>
                                </tr>
                                @if ($bill->vendor_invoice_number)
                                <tr>
                                    <td class="text-muted small">No. Inv Asli:</td>
                                    <td class="fw-semibold">{{ $bill->vendor_invoice_number }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted small">Tgl. Tagihan:</td>
                                    <td class="fw-semibold">{{ $bill->bill_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">Jatuh Tempo:</td>
                                    <td class="fw-semibold text-danger">{{ $bill->due_date->format('d/m/Y') }}</td>
                                </tr>
                                @if ($bill->batch)
                                <tr>
                                    <td class="text-muted small">Batch Terkait:</td>
                                    <td class="fw-semibold">{{ $bill->batch->batch_code }}</td>
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
                                    <th style="width: 30%;">Akun Beban (COA)</th>
                                    <th>Deskripsi Pos Biaya</th>
                                    <th class="text-end" style="width: 10%;">Qty</th>
                                    <th class="text-end" style="width: 18%;">Harga Satuan</th>
                                    <th class="text-end" style="width: 18%;">Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bill->items as $idx => $item)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $item->account->code }}</span>
                                            <div class="text-muted small">{{ $item->account->name }}</div>
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

                    <!-- Summary -->
                    <div class="row justify-content-end">
                        <div class="col-12 col-md-6">
                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between py-1 small">
                                    <span class="text-muted">Subtotal Beban:</span>
                                    <span class="fw-semibold text-dark">Rp {{ number_format($bill->subtotal, 0, ',', '.') }}</span>
                                </div>
                                @if ((float) $bill->tax_amount > 0)
                                <div class="d-flex justify-content-between py-1 small">
                                    <span class="text-muted">Pajak Masukan:</span>
                                    <span class="fw-semibold text-dark">Rp {{ number_format($bill->tax_amount, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                <hr class="my-2">
                                <div class="d-flex justify-content-between py-1">
                                    <span class="fw-bold text-dark">Total Hutang:</span>
                                    <span class="fw-bold text-danger fs-5">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 small text-success">
                                    <span>Telah Dibayarkan:</span>
                                    <span class="fw-semibold">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 fw-bold {{ $bill->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                    <span>Sisa Hutang:</span>
                                    <span>Rp {{ number_format($bill->balance_due, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($bill->notes)
                        <div class="mt-4 p-3 bg-light rounded small">
                            <strong>Catatan:</strong> {{ $bill->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Riwayat Pembayaran / Payment Allocations -->
            <div class="card card-optima shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pembayaran ke Vendor</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-optima mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">No. Voucher</th>
                                    <th>Tgl. Bayar</th>
                                    <th>Rekening Kas/Bank</th>
                                    <th class="text-end">Jumlah Dibayarkan</th>
                                    <th class="text-center pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bill->paymentAllocations as $alloc)
                                    <tr>
                                        <td class="ps-3 fw-bold text-primary">
                                            <a href="{{ route('vendor-payments.show', $alloc->vendorPayment->id) }}">
                                                {{ $alloc->vendorPayment->payment_number }}
                                            </a>
                                        </td>
                                        <td>{{ $alloc->vendorPayment->payment_date->format('d/m/Y') }}</td>
                                        <td>{{ $alloc->vendorPayment->cashBankAccount->name ?? '-' }}</td>
                                        <td class="text-end fw-bold text-success">
                                            Rp {{ number_format($alloc->amount_applied, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center pe-3">
                                            <a href="{{ route('vendor-payments.voucher-pdf', $alloc->vendorPayment->id) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Cetak Voucher">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted small">
                                            Belum ada pembayaran yang dicatat untuk tagihan ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Jurnal Akuntansi & Approval -->
        <div class="col-12 col-lg-4">
            <div class="card card-optima shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check me-2 text-primary"></i>Jurnal Akuntansi</h6>
                    @if ($bill->journalEntry)
                        <span class="badge bg-success-subtle text-success">Posted</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">Draft</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($bill->journalEntry)
                        <div class="mb-3">
                            <span class="text-muted small">No. Bukti Jurnal:</span>
                            <div class="fw-bold text-primary">
                                <a href="{{ route('accounting.journals.show', $bill->journalEntry->id) }}">
                                    {{ $bill->journalEntry->entry_number }}
                                </a>
                            </div>
                            <small class="text-muted">Tgl: {{ $bill->journalEntry->entry_date->format('d/m/Y') }}</small>
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
                                    @foreach ($bill->journalEntry->lines as $line)
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
                            <i class="bi bi-info-circle me-1"></i> Tagihan masih berstatus <strong>Draft</strong>. Jurnal pengakuan beban dan hutang otomatis terbentuk setelah disetujui.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval Status Card -->
            <div class="card card-optima shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Persetujuan & Audit</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Dibuat Oleh:</span>
                            <span class="fw-semibold">{{ $bill->createdBy->name ?? '-' }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Tgl Pembuatan:</span>
                            <span>{{ $bill->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @if ($bill->approvedBy)
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Disetujui Oleh:</span>
                            <span class="fw-semibold text-success">{{ $bill->approvedBy->name }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Tgl Persetujuan:</span>
                            <span>{{ $bill->approved_at ? $bill->approved_at->format('d/m/Y H:i') : '-' }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
