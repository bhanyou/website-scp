@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Mata Pelajaran</h1>
        <p class="page-subtitle">Form pendaftaran mata pelajaran kurikulum baru</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('subjects.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="code">Kode Mata Pelajaran <span style="color: var(--danger);">*</span></label>
            <input type="text" id="code" name="code" value="{{ old('code') }}" placeholder="Contoh: MP-001 / RPL-01" required>
        </div>

        <div class="form-group">
            <label for="name">Nama Mata Pelajaran <span style="color: var(--danger);">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Pemrograman Web" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="category">Kategori Mapel</label>
                <select id="category" name="category">
                    <option value="Umum" {{ old('category') == 'Umum' ? 'selected' : '' }}>Umum</option>
                    <option value="Kejuruan" {{ old('category') == 'Kejuruan' ? 'selected' : '' }}>Kejuruan</option>
                    <option value="Olahraga" {{ old('category') == 'Olahraga' ? 'selected' : '' }}>Olahraga</option>
                </select>
            </div>

            <div class="form-group">
                <label for="total_hours">Beban Jam Pelajaran (JP)</label>
                <input type="number" id="total_hours" name="total_hours" value="{{ old('total_hours', 2) }}" min="1" max="20" required>
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Mata Pelajaran
            </button>
            <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
