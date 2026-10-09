@extends('layouts.app')

@section('title', 'Buku Besar (General Ledger)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Buku Besar (General Ledger)</h4>
        <p class="text-secondary small mb-0">Rincian pergerakan mutasi debit, kredit, dan saldo berjalan per akun akuntansi.</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-optima mb-4 p-3">
    <form method="GET" action="{{ route('accounting.ledger.index') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-medium text-dark mb-1">Pilih Akun Buku Besar</label>
            <select name="account_id" class="form-select" onchange="this.form.submit()">
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                        {{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type) }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-medium text-dark mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-medium text-dark mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-medium text-dark mb-1">Batch Pelatihan</label>
            <select name="batch_id" class="form-select">
                <option value="">-- Semua Batch --</option>
                @foreach ($batches as $b)
                    <option value="{{ $b->id }}" {{ $batchId == $b->id ? 'selected' : '' }}>
                        {{ $b->batch_code }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i> Tampilkan
            </button>
        </div>
    </form>
</div>

@if ($ledgerData)
    <!-- Summary Header Cards -->
    <div class="row g-3 mb-4">
        <!-- Saldo Awal -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-optima p-3 h-100">
                <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Saldo Awal (Sebelum {{ date('d/m/Y', strtotime($startDate)) }})</small>
                <h5 class="fw-bold text-dark mt-2 mb-0 font-monospace">
                    Rp {{ number_format($ledgerData['initial_balance'], 2, ',', '.') }}
                </h5>
            </div>
        </div>
        <!-- Total Mutasi Debit -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-optima p-3 h-100">
                <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Mutasi Debit</small>
                <h5 class="fw-bold text-primary mt-2 mb-0 font-monospace">
                    Rp {{ number_format($ledgerData['total_debit'], 2, ',', '.') }}
                </h5>
            </div>
        </div>
        <!-- Total Mutasi Kredit -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-optima p-3 h-100">
                <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Mutasi Kredit</small>
                <h5 class="fw-bold text-primary mt-2 mb-0 font-monospace">
                    Rp {{ number_format($ledgerData['total_credit'], 2, ',', '.') }}
                </h5>
            </div>
        </div>
        <!-- Saldo Akhir -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-optima p-3 h-100 border-primary">
                <small class="text-primary text-uppercase fw-semibold" style="font-size: 0.72rem;">Saldo Akhir (Posisi {{ date('d/m/Y', strtotime($endDate)) }})</small>
                <h5 class="fw-bold text-primary mt-2 mb-0 font-monospace">
                    Rp {{ number_format($ledgerData['ending_balance'], 2, ',', '.') }}
                </h5>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card card-optima">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-book me-2 text-primary"></i>
                Buku Besar: {{ $ledgerData['account']->code }} - {{ $ledgerData['account']->name }}
            </h6>
            <span class="badge badge-soft-primary">Saldo Normal: {{ strtoupper($ledgerData['account']->normal_balance) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-optima table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 100px;">Tanggal</th>
                        <th style="width: 150px;">No. Jurnal</th>
                        <th>Keterangan / Memo</th>
                        <th style="width: 120px;">Batch</th>
                        <th class="text-end" style="width: 150px;">Debit (Rp)</th>
                        <th class="text-end" style="width: 150px;">Kredit (Rp)</th>
                        <th class="text-end" style="width: 160px;">Saldo Berjalan (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Baris Saldo Awal -->
                    <tr class="table-light">
                        <td class="text-muted">{{ date('d/m/Y', strtotime($startDate)) }}</td>
                        <td colspan="3" class="fw-semibold text-secondary">
                            <em>Saldo Awal Periode</em>
                        </td>
                        <td class="text-end text-muted">-</td>
                        <td class="text-end text-muted">-</td>
                        <td class="text-end fw-bold font-monospace text-dark">
                            Rp {{ number_format($ledgerData['initial_balance'], 2, ',', '.') }}
                        </td>
                    </tr>

                    @forelse ($ledgerData['entries'] as $line)
                        <tr>
                            <td>{{ $line->entry->entry_date->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('accounting.journals.show', $line->entry->id) }}" class="fw-bold font-monospace text-primary">
                                    {{ $line->entry->entry_number }}
                                </a>
                            </td>
                            <td>
                                <div class="text-dark">{{ $line->entry->description }}</div>
                                @if ($line->memo)
                                    <small class="text-muted">{{ $line->memo }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($line->entry->batch)
                                    <span class="badge bg-light text-dark border">
                                        {{ $line->entry->batch->batch_code }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace">
                                @if ((float) $line->debit > 0)
                                    Rp {{ number_format($line->debit, 2, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace">
                                @if ((float) $line->credit > 0)
                                    Rp {{ number_format($line->credit, 2, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold font-monospace text-dark">
                                Rp {{ number_format($line->running_balance, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Tidak ada mutasi transaksi pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">TOTAL MUTASI & SALDO AKHIR:</td>
                        <td class="text-end text-primary font-monospace">
                            Rp {{ number_format($ledgerData['total_debit'], 2, ',', '.') }}
                        </td>
                        <td class="text-end text-primary font-monospace">
                            Rp {{ number_format($ledgerData['total_credit'], 2, ',', '.') }}
                        </td>
                        <td class="text-end text-primary font-monospace fs-6">
                            Rp {{ number_format($ledgerData['ending_balance'], 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif
@endsection
