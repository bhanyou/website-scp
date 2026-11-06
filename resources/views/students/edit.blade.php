@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Data Siswa</h1>
        <p class="page-subtitle">Perbarui informasi identitas siswa dan akun login</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <form action="{{ route('students.update', $student->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="user_id">Akun Siswa Terhubung</label>
            <select name="user_id" id="user_id" required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id', $student->user_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="email">Email Login Akun</label>
                <input type="email" name="email" id="email" value="{{ old('email', $student->user?->email) }}" required>
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi Baru <span style="font-weight: normal; color: var(--text-muted);">(Opsional)</span></label>
                <input type="password" name="password" id="password" placeholder="Kosongkan jika tidak diubah">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="nis">NIS</label>
                <input type="text" name="nis" id="nis" value="{{ old('nis', $student->nis) }}" required>
            </div>

            <div class="form-group">
                <label for="username">Username Akun Siswa</label>
                <input type="text" name="username" id="username" value="{{ old('username', $student->username ?? $student->user?->username) }}" placeholder="Username login siswa">
            </div>
        </div>

        <div class="form-group">
            <label for="name">Nama Siswa</label>
            <input type="text" name="name" id="name" value="{{ old('name', $student->name) }}" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="class_id">Kelas</label>
                <select name="class_id" id="class_id" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="parent_id">Orang Tua / Wali (Opsional)</label>
                <select name="parent_id" id="parent_id">
                    <option value="">-- Belum Ditentukan / Tidak Ada --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $student->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Nomor Telepon</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone) }}">
        </div>

        <div class="form-group">
            <label for="address">Alamat Tempat Tinggal</label>
            <textarea name="address" id="address" rows="3">{{ old('address', $student->address) }}</textarea>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection