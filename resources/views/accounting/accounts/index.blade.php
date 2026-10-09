@extends('layouts.app')

@section('title', 'Bagan Akun (Chart of Accounts)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Bagan Akun (Chart of Accounts)</h4>
        <p class="text-secondary small mb-0">Daftar kode akun standar akuntansi lembaga pelatihan dan sertifikasi kompetensi BNSP.</p>
    </div>
    @can('create-accounts')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAccountModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Akun Baru
    </button>
    @endcan
</div>

<div class="alert alert-info border-0 shadow-sm py-2 mb-4 small d-flex align-items-center">
    <i class="bi bi-shield-lock-fill me-2 fs-5 text-primary"></i>
    <div>
        <strong>Standar Bagan Akun (COA):</strong> Demi menjamin konsistensi saldo buku besar historis dan neraca saldo, Bagan Akun tidak menyediakan aksi edit maupun hapus akun yang telah dibuat. Anda dapat menambahkan akun sub-klasifikasi baru sesuai kebutuhan.
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-optima mb-4 p-3">
    <form method="GET" action="{{ route('accounting.accounts.index') }}" class="row g-2">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama akun..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select">
                <option value="">-- Semua Tipe Akun --</option>
                <option value="asset" {{ request('type') == 'asset' ? 'selected' : '' }}>Aset (1-xxx)</option>
                <option value="liability" {{ request('type') == 'liability' ? 'selected' : '' }}>Kewajiban / Utang (2-xxx)</option>
                <option value="equity" {{ request('type') == 'equity' ? 'selected' : '' }}>Ekuitas / Modal (3-xxx)</option>
                <option value="revenue" {{ request('type') == 'revenue' ? 'selected' : '' }}>Pendapatan (4-xxx)</option>
                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Beban / HPP (5-xxx & 6-xxx)</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('accounting.accounts.index') }}" class="btn btn-light border w-100">Reset</a>
        </div>
    </form>
</div>

<!-- Accounts Table -->
<div class="card card-optima">
    <div class="table-responsive">
        <table class="table table-optima table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 120px;">Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Saldo Normal</th>
                    <th>Status Kas/Bank</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($accounts as $acc)
                    <tr>
                        <td>
                            <span class="fw-bold font-monospace text-primary fs-6">{{ $acc->code }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $acc->name }}</div>
                            @if ($acc->description)
                                <small class="text-muted">{{ $acc->description }}</small>
                            @endif
                        </td>
                        <td>
                            @switch($acc->type)
                                @case('asset')
                                    <span class="badge badge-soft-primary">Aset</span>
                                    @break
                                @case('liability')
                                    <span class="badge badge-soft-danger">Kewajiban</span>
                                    @break
                                @case('equity')
                                    <span class="badge badge-soft-warning">Ekuitas</span>
                                    @break
                                @case('revenue')
                                    <span class="badge badge-soft-success">Pendapatan</span>
                                    @break
                                @case('expense')
                                    <span class="badge badge-soft-info">Beban / Biaya</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            <small class="text-uppercase text-secondary fw-medium">{{ str_replace('_', ' ', $acc->category) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ strtoupper($acc->normal_balance) }}
                            </span>
                        </td>
                        <td>
                            @if ($acc->is_cash_bank)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check2-circle me-1"></i> Kas / Bank
                                </span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($acc->is_active)
                                <span class="badge badge-soft-success">Aktif</span>
                            @else
                                <span class="badge badge-soft-danger">Non-Aktif</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada akun yang sesuai kriteria pencarian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($accounts->hasPages())
    <div class="p-3 border-top">
        {{ $accounts->links() }}
    </div>
    @endif
</div>

<!-- Modal Tambah Akun Baru -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('accounting.accounts.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createAccountModalLabel">Tambah Akun Baru (COA)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="code" class="form-label small fw-medium">Kode Akun</label>
                        <input type="text" name="code" id="code" class="form-control" placeholder="Contoh: 516" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-medium">Nama Akun</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: BIAYA ASURANSI PESERTA" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label small fw-medium">Tipe Akun</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="asset">Aset (Asset)</option>
                                <option value="liability">Kewajiban (Liability)</option>
                                <option value="equity">Ekuitas (Equity)</option>
                                <option value="revenue">Pendapatan (Revenue)</option>
                                <option value="expense">Beban (Expense)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="normal_balance" class="form-label small fw-medium">Saldo Normal</label>
                            <select name="normal_balance" id="normal_balance" class="form-select" required>
                                <option value="debit">Debit</option>
                                <option value="credit">Kredit</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label small fw-medium">Kategori Finansial</label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="current_asset">Aset Lancar (Current Asset)</option>
                            <option value="non_current_asset">Aset Tetap (Non-Current Asset)</option>
                            <option value="current_liability">Kewajiban Lancar (Current Liability)</option>
                            <option value="long_term_liability">Kewajiban Jangka Panjang</option>
                            <option value="equity">Ekuitas</option>
                            <option value="operating_revenue">Pendapatan Operasional</option>
                            <option value="direct_cost">HPP / Biaya Langsung Batch</option>
                            <option value="operating_expense">Beban Operasional Umum</option>
                            <option value="other_expense_income">Pendapatan / Biaya Lain-Lain</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_cash_bank" value="1" id="is_cash_bank">
                        <label class="form-check-label small" for="is_cash_bank">
                            Tandai sebagai Rekening Kas atau Bank
                        </label>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label small fw-medium">Keterangan / Deskripsi</label>
                        <textarea name="description" id="description" class="form-control" rows="2" placeholder="Catatan fungsi akun..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
