@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Profil Saya</h1>
        <p class="page-subtitle">Kelola informasi data akun dan kata sandi Anda</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="form-group">
            <label for="phone">Nomor Telepon / WhatsApp</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
        </div>

        <div class="form-group">
            <label for="photo">Foto Profil</label>
            <input type="file" id="photo" name="photo" accept="image/*">
            @if ($user->photo ?? false)
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.35rem;">
                    Foto saat ini: <a href="{{ asset('storage/' . $user->photo) }}" target="_blank">Lihat Foto</a>
                </p>
            @endif
        </div>

        <hr style="border: none; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">

        <div class="form-group">
            <label for="password">Kata Sandi Baru <span style="font-weight: 400; color: var(--text-muted);">(Kosongkan jika tidak ingin mengubah)</span></label>
            <input type="password" id="password" name="password" placeholder="Minimal 8 karakter">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi baru">
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </form>
</div>
@endsection