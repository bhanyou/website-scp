@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Data Kelas</h1>
        <p class="page-subtitle">Form penambahan rombongan belajar baru</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('classes.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Nama Kelas</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: XII RPL 1" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="grade">Tingkat Kelas</label>
                <input type="text" id="grade" name="grade" value="{{ old('grade') }}" placeholder="Contoh: 12" required>
            </div>

            <div class="form-group">
                <label for="major">Jurusan / Konsentrasi</label>
                <input type="text" id="major" name="major" value="{{ old('major') }}" placeholder="Contoh: RPL" required>
            </div>
        </div>

        <div class="form-group">
            <label for="homeroom_teacher_id">Wali Kelas</label>
            <select name="homeroom_teacher_id" id="homeroom_teacher_id" required>
                <option value="">-- Pilih Guru Wali Kelas --</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id') == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Kelas
            </button>
            <a href="{{ route('classes.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection