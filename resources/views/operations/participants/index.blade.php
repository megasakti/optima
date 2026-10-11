@extends('layouts.app')

@section('title', 'Data Peserta Pelatihan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-people-fill text-primary me-2"></i>Data Peserta Pelatihan
        </h4>
        <p class="text-secondary small mb-0">Manajemen database peserta pelatihan terintegrasi data klien, LSP, dan status pembayaran</p>
    </div>
    @can('create-participants')
    <a href="{{ route('participants.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Peserta Baru
    </a>
    @endcan
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-optima border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="stat-icon bg-primary-subtle text-primary me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Peserta</small>
                    <h5 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_participants']) }} Pax</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-optima border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="stat-icon bg-success-subtle text-success me-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Omset / Biaya</small>
                    <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-optima border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="stat-icon bg-warning-subtle text-warning me-3">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Belum Lunas</small>
                    <h5 class="fw-bold mb-0 text-warning">{{ number_format($stats['unpaid_count']) }} Pax</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-optima border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="stat-icon bg-info-subtle text-info me-3">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div>
                    <small class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem;">Kompeten / Bersertifikat</small>
                    <h5 class="fw-bold mb-0 text-info">{{ number_format($stats['certified_count']) }} Pax</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Box -->
<div class="card card-optima border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-funnel text-primary me-2"></i>Filter & Pencarian Peserta
            </h6>
            @if(request()->anyFilled(['search', 'category', 'customer_id', 'payment_status', 'certificate_status', 'source', 'lsp_id', 'date_from', 'date_to']))
                <a href="{{ route('participants.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0">
                    <i class="bi bi-x-circle me-1"></i>Reset Filter
                </a>
            @endif
        </div>
    </div>
    <div class="card-body p-3">
        <form method="GET" action="{{ route('participants.index') }}" class="row g-2">
            <!-- Search -->
            <div class="col-12 col-md-4">
                <label class="form-label small text-muted mb-1">Cari Peserta / Pelatihan</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nama, HP, email, atau judul..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Kategori -->
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Kategori</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    <option value="public" {{ request('category') === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="iht" {{ request('category') === 'iht' ? 'selected' : '' }}>IHT (In-House)</option>
                    <option value="webinar" {{ request('category') === 'webinar' ? 'selected' : '' }}>Webinar</option>
                </select>
            </div>

            <!-- Perusahaan / Klien -->
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1">Perusahaan / Klien</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">Semua Perusahaan</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}" {{ request('customer_id') == $cust->id ? 'selected' : '' }}>
                            {{ $cust->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Pembayaran -->
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1">Status Pembayaran</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Semua Pembayaran</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Sebagian / DP</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                </select>
            </div>

            <!-- Status Sertifikat -->
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1">Status Sertifikat</label>
                <select name="certificate_status" class="form-select form-select-sm">
                    <option value="">Semua Status Sertifikat</option>
                    <option value="process" {{ request('certificate_status') === 'process' ? 'selected' : '' }}>Dalam Proses</option>
                    <option value="passed" {{ request('certificate_status') === 'passed' ? 'selected' : '' }}>Kompeten (K)</option>
                    <option value="issued" {{ request('certificate_status') === 'issued' ? 'selected' : '' }}>Sertifikat Terbit</option>
                    <option value="failed" {{ request('certificate_status') === 'failed' ? 'selected' : '' }}>Belum Kompeten (BK)</option>
                </select>
            </div>

            <!-- Sumber Leads -->
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Sumber Leads</label>
                <select name="source" class="form-select form-select-sm">
                    <option value="">Semua Sumber</option>
                    <option value="web" {{ request('source') === 'web' ? 'selected' : '' }}>Website</option>
                    <option value="ig" {{ request('source') === 'ig' ? 'selected' : '' }}>Instagram</option>
                    <option value="bod" {{ request('source') === 'bod' ? 'selected' : '' }}>BOD / Referral</option>
                    <option value="other" {{ request('source') === 'other' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <!-- Mitra LSP -->
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1">Mitra LSP BNSP</label>
                <select name="lsp_id" class="form-select form-select-sm">
                    <option value="">Semua Mitra LSP</option>
                    @foreach($lsps as $lsp)
                        <option value="{{ $lsp->id }}" {{ request('lsp_id') == $lsp->id ? 'selected' : '' }}>
                            {{ $lsp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Submit Filter -->
            <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan Filter
                </button>
                <a href="{{ route('participants.index') }}" class="btn btn-sm btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card card-optima border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0 text-dark">Daftar Seluruh Peserta</h6>
            <small class="text-muted">Menampilkan {{ $participants->firstItem() ?? 0 }} - {{ $participants->lastItem() ?? 0 }} dari total {{ $participants->total() }} peserta</small>
        </div>
        <div>
            <span class="badge bg-light text-dark border">
                <i class="bi bi-table me-1"></i> {{ $participants->total() }} Baris
            </span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-optima table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th>Kategori</th>
                    <th>Nama Peserta</th>
                    <th>Perusahaan / Klien</th>
                    <th>No Telp</th>
                    <th>Jenis Pelatihan</th>
                    <th>Tanggal</th>
                    <th>Lokasi</th>
                    <th class="text-end">Harga</th>
                    <th class="text-center">Sumber</th>
                    <th>LSP</th>
                    <th class="text-center">Status Pembayaran</th>
                    <th class="text-center">Status Sertifikat</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($participants as $index => $participant)
                    <tr>
                        <!-- No -->
                        <td class="text-center text-muted fw-semibold">
                            {{ $participants->firstItem() + $index }}
                        </td>

                        <!-- Kategori (Public, IHT, Webinar) -->
                        <td>
                            <span class="{{ $participant->category_badge }}">
                                {{ $participant->category_label }}
                            </span>
                        </td>

                        <!-- Nama Peserta -->
                        <td>
                            <a href="{{ route('participants.show', $participant->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $participant->name }}
                            </a>
                            @if($participant->email)
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    <i class="bi bi-envelope me-1"></i>{{ $participant->email }}
                                </div>
                            @endif
                        </td>

                        <!-- Perusahaan / Klien (Relasi) -->
                        <td>
                            @if($participant->customer)
                                <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-building text-primary"></i>
                                    <strong>{{ $participant->customer->name }}</strong>
                                </span>
                            @elseif($participant->company_name)
                                <span class="text-dark small fw-medium">
                                    <i class="bi bi-building me-1 text-muted"></i>{{ $participant->company_name }}
                                </span>
                            @else
                                <span class="text-muted fst-italic small">Individu / Umum</span>
                            @endif
                        </td>

                        <!-- No Telp & WhatsApp Action -->
                        <td>
                            @if($participant->phone)
                                <div class="d-flex align-items-center gap-1">
                                    <span class="small font-monospace">{{ $participant->phone }}</span>
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $participant->phone);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-sm btn-outline-success p-0 px-1 border-0" title="Hubungi via WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <!-- Jenis Pelatihan -->
                        <td>
                            <span class="fw-semibold text-dark">{{ $participant->training_title }}</span>
                            @if($participant->trainingProgram)
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    Skema: {{ $participant->trainingProgram->code }}
                                </div>
                            @endif
                        </td>

                        <!-- Tanggal -->
                        <td>
                            <div class="small fw-semibold text-dark">
                                {{ $participant->training_date->format('d M Y') }}
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">
                                {{ $participant->training_date->diffForHumans() }}
                            </small>
                        </td>

                        <!-- Lokasi -->
                        <td>
                            <span class="small text-secondary" title="{{ $participant->location }}">
                                <i class="bi bi-geo-alt me-1 text-danger"></i>{{ Str::limit($participant->location, 25) }}
                            </span>
                        </td>

                        <!-- Harga -->
                        <td class="text-end fw-bold text-dark">
                            Rp {{ number_format($participant->price, 0, ',', '.') }}
                        </td>

                        <!-- Sumber (Web, IG, BOD) -->
                        <td class="text-center">
                            @if($participant->source === 'web')
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    <i class="bi bi-globe me-1"></i>Web
                                </span>
                            @elseif($participant->source === 'ig')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-instagram me-1"></i>IG
                                </span>
                            @elseif($participant->source === 'bod')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    <i class="bi bi-star-fill me-1"></i>BOD
                                </span>
                            @else
                                <span class="badge bg-light text-dark border">
                                    {{ $participant->source_label }}
                                </span>
                            @endif
                        </td>

                        <!-- LSP -->
                        <td>
                            @if($participant->lsp)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" title="{{ $participant->lsp->bnsp_license_number ?? '' }}">
                                    <i class="bi bi-award me-1"></i>{{ Str::limit($participant->lsp->name, 20) }}
                                </span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <!-- Status Pembayaran -->
                        <td class="text-center">
                            <span class="{{ $participant->payment_status_badge }}">
                                @if($participant->payment_status === 'paid')
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                @elseif($participant->payment_status === 'partial')
                                    <i class="bi bi-clock-fill me-1"></i>
                                @else
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                @endif
                                {{ $participant->payment_status_label }}
                            </span>
                        </td>

                        <!-- Status Sertifikat -->
                        <td class="text-center">
                            <span class="{{ $participant->certificate_status_badge }}">
                                @if($participant->certificate_status === 'issued')
                                    <i class="bi bi-patch-check-fill me-1"></i>
                                @elseif($participant->certificate_status === 'passed')
                                    <i class="bi bi-check2 me-1"></i>
                                @elseif($participant->certificate_status === 'failed')
                                    <i class="bi bi-x-circle me-1"></i>
                                @else
                                    <i class="bi bi-hourglass-split me-1"></i>
                                @endif
                                {{ $participant->certificate_status_label }}
                            </span>
                        </td>

                        <!-- Aksi -->
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('participants.show', $participant->id) }}" class="btn btn-outline-secondary" title="Lihat Rincian">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit-participants')
                                <a href="{{ route('participants.edit', $participant->id) }}" class="btn btn-outline-primary" title="Edit Peserta">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('delete-participants')
                                <button type="button" class="btn btn-outline-danger" title="Hapus Peserta"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal{{ $participant->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endcan
                            </div>

                            <!-- Delete Modal -->
                            @can('delete-participants')
                            <div class="modal fade" id="deleteModal{{ $participant->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('participants.destroy', $participant->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header border-bottom">
                                                <h6 class="modal-title fw-bold text-danger">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Peserta
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-go="modal" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start py-3">
                                                <p class="mb-2 text-dark">
                                                    Apakah Anda yakin ingin menghapus data peserta <strong>{{ $participant->name }}</strong>?
                                                </p>
                                                <div class="p-2 bg-light rounded small text-muted mb-3">
                                                    <div><strong>Pelatihan:</strong> {{ $participant->training_title }}</div>
                                                    <div><strong>Perusahaan:</strong> {{ $participant->display_company_name }}</div>
                                                    <div><strong>Biaya:</strong> Rp {{ number_format($participant->price, 0, ',', '.') }}</div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small text-muted">Alasan Penghapusan (Opsional):</label>
                                                    <textarea name="reason" class="form-control form-control-sm" rows="2" placeholder="Tuliskan alasan..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus Data</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                                <h6 class="fw-bold mb-1">Tidak ada data peserta ditemukan</h6>
                                <p class="small mb-3">Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
                                @can('create-participants')
                                <a href="{{ route('participants.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Peserta Baru
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($participants->hasPages())
    <div class="card-footer bg-white border-top py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">
                Halaman {{ $participants->currentPage() }} dari {{ $participants->lastPage() }}
            </small>
            <div>
                {{ $participants->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
