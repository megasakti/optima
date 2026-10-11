<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - OPTINERGI</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --optima-primary: #1e3a8a;
            --optima-sidebar-bg: #0f172a;
            --optima-sidebar-hover: #1e293b;
            --optima-sidebar-active: #2563eb;
            --optima-accent: #3b82f6;
            --optima-card-bg: #ffffff;
            --optima-body-bg: #f8fafc;
            --optima-text-main: #1e293b;
            --optima-border: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--optima-body-bg);
            color: var(--optima-text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #sidebar {
            width: 270px;
            background-color: var(--optima-sidebar-bg);
            color: #f8fafc;
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1030;
            flex-shrink: 0;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand-badge {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        .nav-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 700;
            padding: 1rem 1.5rem 0.4rem;
        }

        .sidebar-nav .nav-link {
            color: #94a3b8;
            padding: 0.65rem 1.5rem;
            display: flex;
            align-items: center;
            font-size: 0.88rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.15s ease-in-out;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.15rem;
            margin-right: 0.85rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav .nav-link:hover {
            color: #ffffff;
            background-color: var(--optima-sidebar-hover);
            border-left-color: rgba(59, 130, 246, 0.5);
        }

        .sidebar-nav .nav-link.active {
            color: #ffffff;
            background-color: rgba(37, 99, 235, 0.15);
            border-left-color: var(--optima-sidebar-active);
            font-weight: 600;
        }

        /* Top Navbar Styling */
        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--optima-border);
            padding: 0.75rem 1.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        /* Main Content Container */
        .main-content {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-width: 0;
            width: calc(100% - 270px);
            max-width: calc(100% - 270px);
            background-color: var(--optima-body-bg);
            overflow-x: hidden;
        }

        @media (max-width: 991.98px) {
            .main-content {
                width: 100%;
                max-width: 100%;
            }
        }

        /* Cards & UI Utilities */
        .card-optima {
            background-color: #ffffff;
            border: 1px solid var(--optima-border);
            border-radius: 0.875rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-optima:hover {
            box-shadow: 0 6px 16px -2px rgba(0, 0, 0, 0.06);
        }

        .badge-soft-primary { background-color: rgba(59, 130, 246, 0.12); color: #2563eb; }
        .badge-soft-success { background-color: rgba(34, 197, 94, 0.12); color: #16a34a; }
        .badge-soft-warning { background-color: rgba(245, 158, 11, 0.12); color: #d97706; }
        .badge-soft-danger  { background-color: rgba(239, 68, 68, 0.12); color: #dc2626; }
        .badge-soft-info    { background-color: rgba(6, 182, 212, 0.12); color: #0891b2; }
        .badge-soft-secondary { background-color: #f1f5f9; color: #64748b; }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        /* Table Aesthetics */
        .table-optima {
            width: 100%;
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table-optima th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--optima-border);
            padding: 0.75rem 1rem;
            white-space: nowrap;
        }

        .table-optima td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            font-size: 0.85rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-optima tbody tr:hover {
            background-color: rgba(248, 250, 252, 0.8);
        }

        /* Custom Modern Pagination */
        .pagination {
            margin-bottom: 0;
            gap: 4px;
        }

        .page-item .page-link {
            color: #475569;
            border-radius: 6px !important;
            border: 1px solid #e2e8f0;
            padding: 0.35rem 0.75rem;
            font-size: 0.825rem;
            font-weight: 500;
            background-color: #ffffff;
            transition: all 0.15s ease;
        }

        .page-item .page-link:hover {
            background-color: #f1f5f9;
            color: #1e293b;
            border-color: #cbd5e1;
        }

        .page-item.active .page-link {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
        }

        .page-item.disabled .page-link {
            color: #94a3b8;
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Navigation -->
        <aside id="sidebar">
            <!-- Sidebar Header -->
            <div class="sidebar-brand">
                <div class="sidebar-brand-badge me-3">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-white mb-0" style="letter-spacing: 0.03em;">OPTINERGI</h6>
                    <small class="text-secondary" style="font-size: 0.72rem;">Training & Sertifikasi BNSP</small>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul class="nav flex-column sidebar-nav py-2">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                <!-- SECTION: AKUNTANSI -->
                @canany(['view-ledger', 'view-journals', 'view-accounts'])
                <div class="nav-section-title">Akuntansi & Buku Besar</div>
                <li class="nav-item">
                    <a href="{{ route('accounting.accounts.index') }}" class="nav-link {{ request()->routeIs('accounting.accounts.*') ? 'active' : '' }}">
                        <i class="bi bi-list-columns-reverse"></i> Bagan Akun (COA)
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.journals.index') }}" class="nav-link {{ request()->routeIs('accounting.journals.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-check"></i> Jurnal Umum
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.ledger.index') }}" class="nav-link {{ request()->routeIs('accounting.ledger.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> Buku Besar (GL)
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.trial_balance.index') }}" class="nav-link {{ request()->routeIs('accounting.trial_balance.*') ? 'active' : '' }}">
                        <i class="bi bi-balance-scale"></i> Neraca Saldo
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.periods.index') }}" class="nav-link {{ request()->routeIs('accounting.periods.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check"></i> Periode & Tutup Buku
                    </a>
                </li>
                @endcanany

                <!-- SECTION: PENJUALAN & PIUTANG -->
                @canany(['view-invoices', 'record-customer-payments'])
                <div class="nav-section-title">Piutang & Invoice (AR)</div>
                @can('view-invoices')
                <li class="nav-item">
                    <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i> Faktur & Invoice
                    </a>
                </li>
                @endcan
                @can('view-customer-payments')
                <li class="nav-item">
                    <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-coin"></i> Kwitansi & Pelunasan
                    </a>
                </li>
                @endcan
                @can('view-financial-reports')
                <li class="nav-item">
                    <a href="{{ route('reports.aging_receivable') }}" class="nav-link {{ request()->routeIs('reports.aging_receivable') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Umur Piutang (Aging AR)
                    </a>
                </li>
                @endcan
                @endcanany

                <!-- SECTION: PEMBELIAN & HUTANG -->
                @canany(['view-bills', 'record-vendor-payments', 'view-vendor-payments'])
                <div class="nav-section-title">Hutang & Tagihan LSP (AP)</div>
                @can('view-bills')
                <li class="nav-item">
                    <a href="{{ route('bills.index') }}" class="nav-link {{ request()->routeIs('bills.*') ? 'active' : '' }}">
                        <i class="bi bi-building-check"></i> Tagihan LSP & Vendor
                    </a>
                </li>
                @endcan
                @can('view-vendor-payments')
                <li class="nav-item">
                    <a href="{{ route('vendor-payments.index') }}" class="nav-link {{ request()->routeIs('vendor-payments.*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i> Voucher Bayar Hutang
                    </a>
                </li>
                @endcan
                @can('view-financial-reports')
                <li class="nav-item">
                    <a href="{{ route('reports.aging_payable') }}" class="nav-link {{ request()->routeIs('reports.aging_payable') ? 'active' : '' }}">
                        <i class="bi bi-hourglass-split"></i> Umur Hutang (Aging AP)
                    </a>
                </li>
                @endcan
                @endcanany

                <!-- SECTION: KAS & BANK -->
                @can('view-cash-bank')
                <div class="nav-section-title">Kas & Perbankan</div>
                <li class="nav-item">
                    <a href="{{ route('cash-bank.index') }}" class="nav-link {{ request()->routeIs('cash-bank.index') ? 'active' : '' }}">
                        <i class="bi bi-bank"></i> Rekening Bank & Kas
                    </a>
                </li>
                @can('manage-cash-bank')
                <li class="nav-item">
                    <a href="{{ route('cash-bank.transfer') }}" class="nav-link {{ request()->routeIs('cash-bank.transfer*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right"></i> Pemindahbukuan / Transfer
                    </a>
                </li>
                @endcan
                @endcan

                <!-- SECTION: PELATIHAN & ANGGARAN -->
                @canany(['view-batches', 'view-programs', 'view-budgets', 'view-lsps', 'view-trainers', 'view-participants'])
                <div class="nav-section-title">Pelatihan & Anggaran</div>
                @can('view-participants')
                <li class="nav-item">
                    <a href="{{ route('participants.index') }}" class="nav-link {{ request()->routeIs('participants.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Data Peserta Pelatihan
                    </a>
                </li>
                @endcan
                @can('view-programs')
                <li class="nav-item">
                    <a href="{{ route('programs.index') }}" class="nav-link {{ request()->routeIs('programs.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-bookmark"></i> Skema Pelatihan
                    </a>
                </li>
                @endcan
                @can('view-lsps')
                <li class="nav-item">
                    <a href="{{ route('lsps.index') }}" class="nav-link {{ request()->routeIs('lsps.*') ? 'active' : '' }}">
                        <i class="bi bi-award"></i> Mitra LSP BNSP
                    </a>
                </li>
                @endcan
                @can('view-trainers')
                <li class="nav-item">
                    <a href="{{ route('trainers.index') }}" class="nav-link {{ request()->routeIs('trainers.*') ? 'active' : '' }}">
                        <i class="bi bi-person-video3"></i> Instruktur & Trainer
                    </a>
                </li>
                @endcan
                @can('view-batches')
                <li class="nav-item">
                    <a href="{{ route('batches.index') }}" class="nav-link {{ (request()->routeIs('batches.*') || request()->routeIs('training.batches.*')) ? 'active' : '' }}">
                        <i class="bi bi-mortarboard"></i> Batch Pelatihan
                    </a>
                </li>
                @endcan
                @can('view-budgets')
                <li class="nav-item">
                    <a href="{{ route('budgets.index') }}" class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}">
                        <i class="bi bi-pie-chart"></i> RAB & Budgeting
                    </a>
                </li>
                @endcan
                @can('view-financial-reports')
                <li class="nav-item">
                    <a href="{{ route('reports.batch_profitability') }}" class="nav-link {{ request()->routeIs('reports.batch_profitability*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i> Profit Margin Proyek
                    </a>
                </li>
                @endcan
                @endcanany

                <!-- SECTION: LAPORAN KEUANGAN SAK EMKM (TAHAP 12) -->
                @can('view-financial-reports')
                <div class="nav-section-title">Laporan Keuangan SAK EMKM</div>
                <li class="nav-item">
                    <a href="{{ route('reports.income_statement') }}" class="nav-link {{ request()->routeIs('reports.income_statement*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i> Laba Rugi
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.balance_sheet') }}" class="nav-link {{ request()->routeIs('reports.balance_sheet*') ? 'active' : '' }}">
                        <i class="bi bi-bank"></i> Posisi Keuangan (Neraca)
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.cash_flow') }}" class="nav-link {{ request()->routeIs('reports.cash_flow*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i> Laporan Arus Kas
                    </a>
                </li>
                @endcan

                <!-- SECTION: PENGATURAN & AUDIT -->
                @canany(['view-audit-logs'])
                <div class="nav-section-title">Sistem & Keamanan</div>
                @can('view-audit-logs')
                <li class="nav-item">
                    <a href="{{ route('admin.audit_logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit_logs.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i> Audit Trail & Log
                    </a>
                </li>
                @endcan
                @hasrole('Super Admin')
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Manajemen Pengguna / PIC
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.deletion_requests.index') }}" class="nav-link {{ request()->routeIs('admin.deletion_requests.*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-shield-exclamation text-warning"></i> Persetujuan Hapus Data</span>
                        @php
                            $pendingDelCount = \App\Models\DeletionRequest::where('company_id', auth()->user()->company_id ?? 1)->where('status', 'pending')->count();
                        @endphp
                        @if($pendingDelCount > 0)
                            <span class="badge bg-danger rounded-pill">{{ $pendingDelCount }}</span>
                        @endif
                    </a>
                </li>
                @endhasrole
                @endcanany
            </ul>
        </aside>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Top Navbar -->
            <nav class="top-navbar d-flex justify-content-between align-items-center">
                <!-- Left Title / Organization Badge -->
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark border px-2 py-1 me-3 d-none d-md-inline-block">
                        <i class="bi bi-building me-1 text-primary"></i> {{ auth()->user()->company->name ?? 'PT Optima Environergi Consulta' }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="bi bi-calendar-event me-1"></i> Periode: 2026
                    </span>
                </div>

                <!-- Right User Menu -->
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end d-none d-sm-block">
                        <div class="fw-semibold small text-dark">{{ auth()->user()->name }}</div>
                        <div class="badge badge-soft-primary" style="font-size: 0.7rem;">
                            {{ auth()->user()->roles->first()?->name ?? 'User' }}
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle p-2 dropdown-toggle-split" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px; border: 1px solid #cbd5e1;">
                            <i class="bi bi-person-fill text-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="min-width: 200px;">
                            <li class="px-3 py-2 border-bottom">
                                <small class="text-muted d-block">Login sebagai:</small>
                                <span class="fw-bold text-dark">{{ auth()->user()->email }}</span>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-gear me-2 text-muted"></i> Profil & Keamanan
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Page Body -->
            <main class="p-4 flex-grow-1">
                <!-- Flash Notification Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top py-3 px-4 text-secondary small d-flex justify-content-between align-items-center">
                <span>&copy; 2026 PT Optima Environergi Consulta</span>
                <span>Desain AMS</span>
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js via CDN (Zero Node.js dependency!) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @stack('scripts')
</body>
</html>
