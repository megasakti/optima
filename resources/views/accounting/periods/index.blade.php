@extends('layouts.app')

@section('title', 'Periode Akuntansi & Tutup Buku')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Periode Akuntansi & Tutup Buku</h4>
        <p class="text-secondary small mb-0">Kelola siklus pembukuan tahunan/bulanan dan eksekusi tutup buku pemindahan laba/rugi.</p>
    </div>
    @can('close-period')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPeriodModal">
        <i class="bi bi-calendar-plus me-1"></i> Buka Periode Baru
    </button>
    @endcan
</div>

<!-- Periods Table -->
<div class="card card-optima">
    <div class="table-responsive">
        <table class="table table-optima table-hover mb-0">
            <thead>
                <tr>
                    <th>Nama Periode</th>
                    <th>Rentang Tanggal</th>
                    <th>Keterangan</th>
                    <th class="text-center">Status</th>
                    <th>Tanggal Penutupan</th>
                    <th>Ditutup Oleh</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($periods as $p)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $p->name }}</div>
                        </td>
                        <td>
                            <span class="text-dark">{{ $p->start_date->format('d/m/Y') }}</span>
                            <span class="text-muted mx-1">s/d</span>
                            <span class="text-dark">{{ $p->end_date->format('d/m/Y') }}</span>
                        </td>
                        <td>
                            <small class="text-secondary">{{ $p->notes ?? '-' }}</small>
                        </td>
                        <td class="text-center">
                            @if ($p->is_closed)
                                <span class="badge badge-soft-danger px-3 py-1">
                                    <i class="bi bi-lock-fill me-1"></i> Ditutup (Closed)
                                </span>
                            @else
                                <span class="badge badge-soft-success px-3 py-1">
                                    <i class="bi bi-unlock-fill me-1"></i> Terbuka (Aktif)
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($p->closed_at)
                                <small class="text-dark">{{ $p->closed_at->translatedFormat('d/m/Y H:i') }}</small>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($p->closedBy)
                                <small class="text-dark fw-medium">{{ $p->closedBy->name }}</small>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if (!$p->is_closed)
                                @can('close-period')
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="openCloseModal('{{ $p->id }}', '{{ $p->name }}', '{{ $p->end_date->format('Y-m-d') }}')">
                                    <i class="bi bi-lock me-1"></i> Tutup Buku
                                </button>
                                @endcan
                            @else
                                <button class="btn btn-sm btn-light border" disabled>
                                    <i class="bi bi-check2"></i> Selesai
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada periode akuntansi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Periode Baru -->
<div class="modal fade" id="createPeriodModal" tabindex="-1" aria-labelledby="createPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('accounting.periods.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createPeriodModalLabel">Buka Periode Akuntansi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-medium">Nama Periode</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: April 2026 atau Tahun Buku 2027" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label small fw-medium">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label small fw-medium">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label small fw-medium">Catatan</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Catatan kebijakan pembukuan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Buka Periode</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Tutup Buku -->
<div class="modal fade" id="closingModal" tabindex="-1" aria-labelledby="closingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="closingForm" action="">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="closingModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Tutup Buku Periode
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-dark">
                        Anda akan menutup periode: <strong id="closingPeriodName">-</strong>.
                    </p>
                    <div class="alert alert-warning small border-0" role="alert">
                        <strong>Perhatian:</strong>
                        <ul class="mb-0 ps-3">
                            <li>Seluruh akun nominal Pendapatan (4-xxx) dan Beban (5-xxx & 6-xxx) akan ditutup secara otomatis ke Akun <strong>303 Ikhtisar Laba Rugi</strong>.</li>
                            <li>Laba bersih atau rugi bersih akan dipindahkan ke Akun <strong>302 Saldo Laba (Retained Earnings)</strong>.</li>
                            <li>Setelah ditutup, tidak ada transaksi baru yang dapat diposting ke periode ini.</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label for="closing_date" class="form-label small fw-medium">Tanggal Efektif Jurnal Penutup</label>
                        <input type="date" name="closing_date" id="closing_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Eksekusi Tutup Buku</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCloseModal(periodId, periodName, defaultDate) {
        document.getElementById('closingPeriodName').innerText = periodName;
        document.getElementById('closing_date').value = defaultDate;
        document.getElementById('closingForm').action = "/accounting/periods/" + periodId + "/close";
        
        const modal = new bootstrap.Modal(document.getElementById('closingModal'));
        modal.show();
    }
</script>
@endpush
