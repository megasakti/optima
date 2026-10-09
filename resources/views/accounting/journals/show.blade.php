@extends('layouts.app')

@section('title', 'Detail Jurnal ' . $journal->entry_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-dark">{{ $journal->entry_number }}</h4>
            @if ($journal->status === 'posted')
                <span class="badge badge-soft-success fs-6">Posted</span>
            @elseif ($journal->status === 'reversed')
                <span class="badge badge-soft-danger fs-6">Reversed (Dibalik)</span>
            @else
                <span class="badge badge-soft-warning fs-6">Draft</span>
            @endif
        </div>
        <p class="text-secondary small mb-0">{{ $journal->description }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('accounting.journals.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>

        @if ($journal->status === 'posted' && !$journal->reversed_by_id)
            @can('reverse-journals')
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#reverseModal">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Balikkan Jurnal (Reversal)
            </button>
            @endcan
        @endif
    </div>
</div>

<!-- Reversal Alert if already reversed -->
@if ($journal->status === 'reversed' && $journal->reversedBy)
    <div class="alert alert-danger d-flex align-items-center justify-content-between mb-4 border-0 shadow-sm" role="alert">
        <div>
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
            Jurnal ini telah dibatalkan dengan Jurnal Pembalik: 
            <strong>{{ $journal->reversedBy->entry_number }}</strong> pada tanggal {{ $journal->reversedBy->entry_date->format('d/m/Y') }}.
        </div>
        <a href="{{ route('accounting.journals.show', $journal->reversedBy->id) }}" class="btn btn-sm btn-outline-danger">
            Lihat Jurnal Pembalik &rarr;
        </a>
    </div>
@endif

<!-- Document Meta Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-optima p-3">
            <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Tanggal Jurnal</small>
            <div class="fw-bold text-dark fs-6 mt-1">{{ $journal->entry_date->translatedFormat('d F Y') }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-optima p-3">
            <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Periode Akuntansi</small>
            <div class="fw-bold text-dark fs-6 mt-1">{{ $journal->period->name ?? '-' }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-optima p-3">
            <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Modul Asal</small>
            <div class="fw-bold text-dark fs-6 mt-1 text-uppercase">{{ $journal->source_module }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-optima p-3">
            <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Batch Pelatihan</small>
            <div class="fw-bold text-dark fs-6 mt-1">
                {{ $journal->batch->batch_code ?? 'Beban Umum (Non-Batch)' }}
            </div>
        </div>
    </div>
</div>

<!-- Journal Lines Detail Table -->
<div class="card card-optima p-4 mb-4">
    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
        <i class="bi bi-list-check text-primary me-2"></i> Rincian Baris Jurnal Double-Entry
    </h6>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 140px;">Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Memo / Keterangan Baris</th>
                    <th class="text-end" style="width: 180px;">Debit (Rp)</th>
                    <th class="text-end" style="width: 180px;">Kredit (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($journal->lines as $line)
                    <tr>
                        <td>
                            <a href="{{ route('accounting.ledger.index', ['account_id' => $line->account_id]) }}" class="font-monospace fw-bold text-primary">
                                {{ $line->account->code }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $line->account->name }}</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">{{ $line->account->category }}</small>
                        </td>
                        <td>
                            <span class="text-secondary">{{ $line->memo ?? '-' }}</span>
                        </td>
                        <td class="text-end font-monospace">
                            @if ((float) $line->debit > 0)
                                <span class="fw-bold text-dark">Rp {{ number_format($line->debit, 2, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace">
                            @if ((float) $line->credit > 0)
                                <span class="fw-bold text-dark">Rp {{ number_format($line->credit, 2, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3" class="text-end">TOTAL:</td>
                    <td class="text-end text-primary font-monospace fs-6">
                        Rp {{ number_format($journal->total_debit, 2, ',', '.') }}
                    </td>
                    <td class="text-end text-primary font-monospace fs-6">
                        Rp {{ number_format($journal->total_credit, 2, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Modal Pembalikan Jurnal (Reversal) -->
<div class="modal fade" id="reverseModal" tabindex="-1" aria-labelledby="reverseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('accounting.journals.reverse', $journal->id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="reverseModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Jurnal Pembalik (Storno)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-dark">
                        Sesuai kaidah akuntansi mutlak, <strong>jurnal yang telah diposting tidak boleh dihapus</strong>. 
                        Tindakan ini akan membuat jurnal pembalik otomatis yang menukar posisi debit dan kredit secara simetris.
                    </p>
                    <div class="mb-3">
                        <label for="reversal_date" class="form-label small fw-medium">Tanggal Pembalikan</label>
                        <input type="date" name="reversal_date" id="reversal_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label small fw-medium">Alasan Koreksi / Pembalikan</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Contoh: Salah alokasi akun beban atau pembatalan transaksi invoice..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Balikkan Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
