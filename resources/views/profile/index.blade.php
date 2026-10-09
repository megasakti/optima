@extends('layouts.app')

@section('title', 'Profil & Keamanan Akun')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Page Title -->
        <div class="mb-4">
            <h4 class="fw-bold mb-1 text-dark">Pengaturan Profil & Keamanan Akun</h4>
            <p class="text-secondary small mb-0">Kelola identitas pengguna dan pembaruan kata sandi akun.</p>
        </div>

        <!-- 1. Update Profile Information -->
        <div class="card card-optima mb-4 p-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="bi bi-person-badge text-primary me-2"></i> Informasi Pengguna
            </h6>
            
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label small fw-medium">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label small fw-medium">Alamat Email (Akun Utama)</label>
                        <input type="email" id="email" class="form-control bg-light" value="{{ $user->email }}" disabled>
                        <small class="text-muted" style="font-size: 0.72rem;">Email sistem diatur oleh Administrator.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label small fw-medium">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                               value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-medium">Peran / Hak Akses</label>
                        <div>
                            <span class="badge badge-soft-primary px-3 py-2 fw-semibold">
                                <i class="bi bi-shield-lock me-1"></i> {{ $user->roles->first()?->name ?? 'User' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Update Password -->
        <div class="card card-optima p-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="bi bi-key text-warning me-2"></i> Ganti Kata Sandi
            </h6>

            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12">
                        <label for="current_password" class="form-label small fw-medium">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" 
                               class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label small fw-medium">Kata Sandi Baru</label>
                        <input type="password" name="password" id="password" 
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label small fw-medium">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-warning px-4 text-dark fw-semibold">
                        <i class="bi bi-shield-check me-1"></i> Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
