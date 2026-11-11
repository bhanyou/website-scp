@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Data Guru</h1>
        <p class="page-subtitle">Form pendaftaran data guru dan akun pengajar baru</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <form action="{{ route('teachers.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Nama Lengkap Guru</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso, S.Pd" required>
        </div>

        <div class="form-group">
            <label for="nip">NIP <span style="color: var(--danger);">*</span></label>
            <input type="text" id="nip" name="nip" value="{{ old('nip') }}" placeholder="Contoh: 198501152010011002 (Maksimal 18 digit)" maxlength="18" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Hanya angka, minimal 8 digit dan maksimal 18 digit.</small>
            @error('nip')
                <small class="text-danger" style="display: block; color: var(--danger);">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="username">Username Login (Opsional)</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Contoh: guru1 (otomatis jika dikosongkan)">
            <small style="color: var(--text-muted); font-size: 0.75rem;">Otomatis dibuatkan username berurutan (guru1, guru2, dst) jika dikosongkan.</small>
        </div>

        <div class="form-group">
            <label for="mapel">Mata Pelajaran (Mapel) <span style="color: var(--danger);">*</span></label>
            <select name="mapel" id="mapel" required>
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->name }}"
                        {{ old('mapel') == $subject->name ? 'selected' : '' }}>
                        {{ $subject->name }} ({{ $subject->code }})
                    </option>
                @endforeach
            </select>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Mata pelajaran diambil langsung dari master data Mapel.</small>
            @error('mapel')
                <small class="text-danger" style="display: block; color: var(--danger);">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Alamat Email (Opsional)</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="guru@sch.com (boleh dikosongkan)">
        </div>

        <div class="form-group">
            <label for="phone">Nomor Telepon / WhatsApp</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
        </div>

        <div class="form-group">
            <label for="address">Alamat Tempat Tinggal</label>
            <textarea id="address" name="address" rows="3" placeholder="Alamat lengkap...">{{ old('address') }}</textarea>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi Akun</label>
            <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Guru
            </button>
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection