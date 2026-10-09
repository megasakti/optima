@extends('layouts.app')

@section('title', 'Kas & Rekening Bank (Treasury)')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-bank text-primary me-2"></i>Rekening Bank & Kas Operasional
            </h4>
            <p class="text-secondary small mb-0">Manajemen perbendaharaan, saldo riil rekening bank, mutasi keluar-masuk, dan rekonsiliasi bank.</p>
        </div>
        <div class="d-flex gap-2">
            @can('manage-cash-bank')
            <a href="{{ route('cash-bank.transfer') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-arrow-left-right me-1"></i> Transfer Antar Kas/Bank
            </a>
            @endcan
        </div>
    </div>

    <!-- Total Liquidity Stat Card -->
    <div class="card card-optima p-3 mb-4 border-start border-4 border-primary">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Total Likuiditas Kas & Bank</span>
                <h3 class="fw-bold text-primary mt-1 mb-0">Rp {{ number_format($totalLiquidity, 0, ',', '.') }}</h3>
                <small class="text-muted">Total dana siap pakai di seluruh rekening operasional & kas fisik</small>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary" style="width: 56px; height: 56px; font-size: 26px;">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>

    <!-- Bank Accounts Overview Grid -->
    <div class="row g-3 mb-4">
        @foreach ($accounts as $acc)
            @php
                $isSelected = ($selectedAccountId == $acc->id);
            @endphp
            <div class="col-12 col-md-4">
                <div class="card card-optima p-3 h-100 {{ $isSelected ? 'border-primary shadow-sm' : '' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge {{ $acc->account_type === 'bank' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }} mb-1">
                                {{ strtoupper($acc->account_type) }}
                            </span>
                            <h6 class="fw-bold text-dark mb-0">{{ $acc->name }}</h6>
                        </div>
                        <a href="{{ route('cash-bank.index', ['account_id' => $acc->id]) }}" class="btn btn-sm {{ $isSelected ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $isSelected ? 'Aktif' : 'Pilih' }}
                        </a>
                    </div>
                    <div class="text-muted small mb-2">
                        {{ $acc->bank_name ? $acc->bank_name . ' - ' : '' }}{{ $acc->account_number ?? 'Buku Kas Fisik' }}
                    </div>
                    <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-baseline">
                        <small class="text-muted">Saldo Saat Ini:</small>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filter Mutasi Transaksi -->
    <div class="card card-optima mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('cash-bank.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="account_id" value="{{ $selectedAccountId }}">
                <div class="col-12 col-md-4">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">-- Semua Jenis Mutasi --</option>
                        <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Setoran Masuk (Deposit)</option>
                        <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Pengeluaran (Withdrawal)</option>
                        <option value="transfer_in" {{ request('type') === 'transfer_in' ? 'selected' : '' }}>Transfer Masuk</option>
                        <option value="transfer_out" {{ request('type') === 'transfer_out' ? 'selected' : '' }}>Transfer Keluar</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <select name="reconciled" class="form-select form-select-sm">
                        <option value="">-- Status Rekonsiliasi --</option>
                        <option value="1" {{ request('reconciled') === '1' ? 'selected' : '' }}>Telah Direkonsiliasi</option>
                        <option value="0" {{ request('reconciled') === '0' ? 'selected' : '' }}>Belum Direkonsiliasi</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter Mutasi
                    </button>
                    @if (request()->hasAny(['type', 'reconciled']))
                        <a href="{{ route('cash-bank.index', ['account_id' => $selectedAccountId]) }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Transaction Ledger Table -->
    <div class="card card-optima shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-columns me-2 text-primary"></i>Buku Mutasi Rekening Bank / Kas</h6>
            <span class="badge bg-light text-dark border">
                Total {{ $transactions->total() }} Transaksi Terdaftar
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-optima table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Tgl Mutasi</th>
                        <th>Akun Bank</th>
                        <th>Keterangan / Memo</th>
                        <th>No. Referensi</th>
                        <th class="text-end">Masuk (Debit)</th>
                        <th class="text-end">Keluar (Kredit)</th>
                        <th class="text-center">Rekonsiliasi</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        @php
                            $isDeposit = in_array($tx->transaction_type, ['deposit', 'transfer_in']);
                        @endphp
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $tx->cashBankAccount->name }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $tx->description }}</div>
                                <span class="badge {{ $isDeposit ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}" style="font-size: 0.68rem;">
                                    {{ strtoupper(str_replace('_', ' ', $tx->transaction_type)) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $tx->reference_number ?? '-' }}</small>
                            </td>
                            <td class="text-end fw-bold {{ $isDeposit ? 'text-success' : 'text-muted' }}">
                                {{ $isDeposit ? 'Rp ' . number_format($tx->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end fw-bold {{ !$isDeposit ? 'text-danger' : 'text-muted' }}">
                                {{ !$isDeposit ? 'Rp ' . number_format($tx->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-center">
                                @if ($tx->is_reconciled)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" title="Direkonsiliasi {{ $tx->reconciled_at?->format('d/m/Y H:i') }}">
                                        <i class="bi bi-check2-all me-1"></i> Cocok
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                @can('reconcile-bank')
                                <form action="{{ route('cash-bank.reconcile', $tx->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $tx->is_reconciled ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $tx->is_reconciled ? 'Batal Rekonsiliasi' : 'Tandai Cocok Rekening Koran' }}">
                                        <i class="bi {{ $tx->is_reconciled ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                                    </button>
                                </form>
                                @endcan
                                @if ($tx->journalEntry)
                                    <a href="{{ route('accounting.journals.show', $tx->journalEntry->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat Jurnal">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada mutasi transaksi pada rekening ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
