@extends('layouts.app')

@section('title', 'Ubah Data Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Pengguna: {{ $targetUser->name }}
        </h4>
        <p class="text-secondary small mb-0">Perbarui profil pengguna, penetapan peran (role), dan status aktif</p>
    </div>
    <a href="{{ route('admin.users.show', $targetUser->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-optima shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">Edit Data Akun Pengguna</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.users.update', $targetUser->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $targetUser->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $targetUser->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $targetUser->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Peran (Role Hak Akses) <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role', $targetUser->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Ubah Password (Kosongkan jika tidak diubah)</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Status Akun</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $targetUser->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Akun Aktif (Dapat Login)</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.show', $targetUser->id) }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
