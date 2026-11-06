@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Ruangan</h1>
        <p class="page-subtitle">Form registrasi ruangan atau laboratorium baru</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('rooms.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Nama Ruangan</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Lab Komputer 1 / Ruang Teori 05" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="type">Tipe Ruangan</label>
                <select id="type" name="type">
                    <option value="Teori" {{ old('type') == 'Teori' ? 'selected' : '' }}>Teori (Kelas Teori Umum)</option>
                    <option value="Praktik" {{ old('type') == 'Praktik' ? 'selected' : '' }}>Praktik (Laboratorium / Bengkel)</option>
                    <option value="Olahraga" {{ old('type') == 'Olahraga' ? 'selected' : '' }}>Olahraga (Lapangan Olahraga / Hall)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="capacity">Kapasitas Maksimal Siswa</label>
                <input type="number" id="capacity" name="capacity" value="{{ old('capacity') }}" min="1" placeholder="Contoh: 36">
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Ruangan
            </button>
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection