@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Data Siswa</h1>
        <p class="page-subtitle">Form registrasi data siswa dan pembuatan akun login</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <form action="{{ route('students.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="user_id">Akun Login Siswa</label>
            <select name="user_id" id="user_id" onchange="toggleAccountFields()">
                <option value="new" {{ old('user_id', 'new') === 'new' ? 'selected' : '' }}>
                    -- Buat Akun Siswa Baru Otomatis --
                </option>

                @if($users->isNotEmpty())
                    <optgroup label="Atau Gunakan Akun yang Sudah Ada:">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </optgroup>
                @endif
            </select>
        </div>

        <div id="new_account_fields" style="display: {{ old('user_id', 'new') === 'new' ? 'block' : 'none' }}; border: 1px dashed var(--border-color); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.25rem; background-color: var(--bg-surface-hover);">
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label for="email_input">Email Login Siswa <span style="color: var(--danger);">*</span></label>
                <input type="email" name="email" id="email_input" value="{{ old('email') }}" placeholder="siswa@sch.com">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="password_input">Kata Sandi Login Siswa <span style="color: var(--danger);">*</span></label>
                <input type="password" name="password" id="password_input" placeholder="Minimal 6 karakter">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="nis">NIS (Nomor Induk Siswa) <span style="color: var(--danger);">*</span></label>
                <input type="text" name="nis" id="nis" value="{{ old('nis') }}" placeholder="Contoh: 18044 (Maksimal 10 digit)" maxlength="10" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                <small style="color: var(--text-muted); font-size: 0.75rem;">Hanya angka, minimal 4 digit dan maksimal 10 digit.</small>
                @error('nis')
                    <small class="text-danger" style="display: block; color: var(--danger);">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="username">Username Siswa <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Opsional, default: NIS)</span></label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="Contoh: ahmad_dahlan atau NIS">
            </div>
        </div>

        <div class="form-group">
            <label for="name">Nama Lengkap Siswa <span style="color: var(--danger);">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Nama lengkap siswa" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="class_id">Kelas</label>
                <select name="class_id" id="class_id" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
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
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Nomor Telepon</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
        </div>

        <div class="form-group">
            <label for="address">Alamat Tempat Tinggal</label>
            <textarea name="address" id="address" rows="3" placeholder="Alamat lengkap...">{{ old('address') }}</textarea>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Data Siswa
            </button>
            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
    function toggleAccountFields() {
        var select = document.getElementById('user_id');
        var fields = document.getElementById('new_account_fields');
        var emailInput = document.getElementById('email_input');
        var passwordInput = document.getElementById('password_input');

        if (!select || select.value === 'new') {
            if (fields) fields.style.display = 'block';
            if (emailInput) emailInput.required = true;
            if (passwordInput) passwordInput.required = true;
        } else {
            if (fields) fields.style.display = 'none';
            if (emailInput) emailInput.required = false;
            if (passwordInput) passwordInput.required = false;
        }
    }
    toggleAccountFields();
</script>
@endsection