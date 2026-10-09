@extends('layouts.auth')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
        <div class="card auth-card p-4 p-md-5">
            <!-- Brand Header -->
            <div class="text-center mb-4">
                <div class="brand-badge mb-3">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="fw-bold text-white mb-1">OPTIMA FINANCE</h3>
                <p class="text-secondary small mb-0">Sistem Keuangan Lembaga Pelatihan & Sertifikasi BNSP</p>
            </div>

            <!-- Validation Errors & Flash Messages -->
            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 small border-0 mb-4" role="alert" style="background-color: rgba(239, 68, 68, 0.2); color: #fca5a5;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <!-- Email Input -->
                <div class="mb-3">
                    <label for="email" class="form-label text-light small fw-medium">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color: rgba(255,255,255,0.15)">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input id="email" type="email" name="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                               value="{{ old('email', 'admin@optima.local') }}" required autofocus placeholder="nama@optima.local">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label text-light small fw-medium mb-0">Kata Sandi</label>
                    </div>
                    <div class="input-group mt-1">
                        <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color: rgba(255,255,255,0.15)">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input id="password" type="password" name="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                               required placeholder="••••••••" value="password123">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="form-check mb-4">
                    <input class="form-check-input bg-transparent" type="checkbox" name="remember" id="remember" 
                           style="border-color: rgba(255,255,255,0.3)" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label text-secondary small" for="remember">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Dashboard
                </button>
            </form>

            <!-- Quick Demo Role Switcher -->
            <div class="mt-4 pt-3 border-top" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                <p class="text-secondary small fw-medium mb-2 text-center">Akun Demo (Klik untuk mengisi cepat):</p>
                <div class="d-flex flex-wrap gap-1 justify-content-center">
                    <button type="button" class="btn role-pill py-1 px-2" onclick="fillCredentials('admin@optima.local')">
                        <i class="bi bi-shield-fill text-danger me-1"></i>Super Admin
                    </button>
                    <button type="button" class="btn role-pill py-1 px-2" onclick="fillCredentials('finance.manager@optima.local')">
                        <i class="bi bi-cash-stack text-success me-1"></i>Finance Mgr
                    </button>
                    <button type="button" class="btn role-pill py-1 px-2" onclick="fillCredentials('accountant@optima.local')">
                        <i class="bi bi-journal-check text-info me-1"></i>Akuntan
                    </button>
                    <button type="button" class="btn role-pill py-1 px-2" onclick="fillCredentials('operations@optima.local')">
                        <i class="bi bi-mortarboard text-warning me-1"></i>Koordinator
                    </button>
                    <button type="button" class="btn role-pill py-1 px-2" onclick="fillCredentials('auditor@optima.local')">
                        <i class="bi bi-eye text-primary me-1"></i>Auditor
                    </button>
                </div>
            </div>

            <!-- Footer note -->
            <div class="text-center mt-4">
                <small class="text-secondary" style="font-size: 0.75rem;">
                    &copy; 2026 PT Optima Mitra Sertifikasi. Terlindungi Enkripsi SAK/BNSP.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function fillCredentials(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password123';
    }
</script>
@endpush
