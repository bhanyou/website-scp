@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Data Guru</h1>
        <p class="page-subtitle">Perbarui data informasi dan akun guru pengajar</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Nama Lengkap Guru</label>
            <input type="text" id="name" name="name" value="{{ old('name', $teacher->name) }}" required>
        </div>

        <div class="form-group">
            <label for="nip">NIP</label>
            <input type="text" id="nip" name="nip" value="{{ old('nip', $teacher->nip) }}" required>
        </div>

        <div class="form-group">
            <label for="username">Username Login</label>
            <input type="text" id="username" name="username" value="{{ old('username', $teacher->username ?? ($teacher->user->username ?? '')) }}" placeholder="Contoh: guru1">
        </div>

        <div class="form-group">
            <label for="mapel">Mata Pelajaran yang Diampu (Mapel)</label>
            <input type="text" id="mapel" name="mapel" value="{{ old('mapel', $teacher->mapel) }}" placeholder="Contoh: Pemrograman Web / Matematika">
        </div>

        <div class="form-group">
            <label for="email">Alamat Email <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Opsional)</span></label>
            <input type="email" id="email" name="email" value="{{ old('email', $teacher->user->email ?? '') }}" placeholder="Boleh dikosongkan">
        </div>

        <div class="form-group">
            <label for="phone">Nomor Telepon</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $teacher->phone) }}">
        </div>

        <div class="form-group">
            <label for="address">Alamat</label>
            <textarea id="address" name="address" rows="3">{{ old('address', $teacher->address) }}</textarea>
        </div>

        <div class="form-group">
            <label for="homeroom_class_id">Tugaskan Sebagai Wali Kelas (Opsional)</label>
            @php
                $currentClassId = old('homeroom_class_id', $teacher->homeroomClasses->first()?->id);
            @endphp
            <select id="homeroom_class_id" name="homeroom_class_id">
                <option value="">-- Bukan Wali Kelas (Guru Pengajar Saja) --</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $currentClassId == $class->id ? 'selected' : '' }}>
                        Wali Kelas {{ $class->name }} (Tingkat {{ $class->grade }} - {{ $class->major }})
                    </option>
                @endforeach
            </select>
            <small style="color: var(--text-muted); font-size: 0.75rem;">
                Pilih kelas jika guru ini sekaligus ditugaskan sebagai wali kelas.
            </small>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi Baru <span style="font-weight: normal; color: var(--text-muted);">(Kosongkan jika tidak diubah)</span></label>
            <input type="password" id="password" name="password" placeholder="Masukkan password baru">
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection