@extends('layouts.app')

@section('title', 'Neraca Saldo (Trial Balance)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Neraca Saldo (Trial Balance)</h4>
        <p class="text-secondary small mb-0">Daftar saldo debit dan kredit seluruh akun buku besar per tanggal tertentu.</p>
    </div>
    <div>
        @if ($trialBalance['is_balanced'])
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                <i class="bi bi-check-circle-fill me-1"></i> Neraca Saldo Seimbang
            </span>
        @else
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6">
                <i class="bi bi-x-circle-fill me-1"></i> Tidak Seimbang
            </span>
        @endif
    </div>
</div>

<!-- Filter Date Bar -->
<div class="card card-optima mb-4 p-3">
    <form method="GET" action="{{ route('accounting.trial_balance.index') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-medium text-dark mb-1">Posisi Saldo Per Tanggal</label>
            <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-arrow-repeat me-1"></i> Hitung Saldo
            </button>
        </div>
    </form>
</div>

<!-- Trial Balance Table -->
<div class="card card-optima">
    <div class="table-responsive">
        <table class="table table-optima table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 120px;">Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Tipe / Kategori</th>
                    <th class="text-end" style="width: 200px;">Debit (Rp)</th>
                    <th class="text-end" style="width: 200px;">Kredit (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($trialBalance['rows'] as $row)
                    <tr>
                        <td>
                            <a href="{{ route('accounting.ledger.index', ['account_id' => $row['account_id']]) }}" class="fw-bold font-monospace text-primary">
                                {{ $row['code'] }}
                            </a>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $row['name'] }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ strtoupper($row['type']) }}
                            </span>
                            <small class="text-muted ms-1 text-uppercase" style="font-size: 0.7rem;">
                                {{ str_replace('_', ' ', $row['category']) }}
                            </small>
                        </td>
                        <td class="text-end font-monospace">
                            @if ($row['debit'] > 0)
                                <span class="fw-bold text-dark">Rp {{ number_format($row['debit'], 2, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace">
                            @if ($row['credit'] > 0)
                                <span class="fw-bold text-dark">Rp {{ number_format($row['credit'], 2, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada akun dengan saldo mutasi aktif.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3" class="text-end fs-6">TOTAL KESELURUHAN:</td>
                    <td class="text-end text-primary font-monospace fs-6">
                        Rp {{ number_format($trialBalance['total_debit'], 2, ',', '.') }}
                    </td>
                    <td class="text-end text-primary font-monospace fs-6">
                        Rp {{ number_format($trialBalance['total_credit'], 2, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
