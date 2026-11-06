@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Data Ruangan</h1>
        <p class="page-subtitle">Perbarui data nama ruangan, gedung, dan kapasitas siswa</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('rooms.update', $room->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Nama Ruangan</label>
            <input type="text" id="name" name="name" value="{{ old('name', $room->name) }}" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="type">Tipe Ruangan</label>
                <select id="type" name="type">
                    <option value="Teori" {{ old('type', $room->type_label) == 'Teori' ? 'selected' : '' }}>Teori (Kelas Teori Umum)</option>
                    <option value="Praktik" {{ old('type', $room->type_label) == 'Praktik' ? 'selected' : '' }}>Praktik (Laboratorium / Bengkel)</option>
                    <option value="Olahraga" {{ old('type', $room->type_label) == 'Olahraga' ? 'selected' : '' }}>Olahraga (Lapangan Olahraga / Hall)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="capacity">Kapasitas</label>
                <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity) }}" min="1">
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection