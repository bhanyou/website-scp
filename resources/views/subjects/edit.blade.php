@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Mata Pelajaran</h1>
        <p class="page-subtitle">Perbarui data kurikulum mata pelajaran</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('subjects.update', $subject->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="code">Kode Mata Pelajaran <span style="color: var(--danger);">*</span></label>
            <input type="text" id="code" name="code" value="{{ old('code', $subject->code) }}" required>
        </div>

        <div class="form-group">
            <label for="name">Nama Mata Pelajaran <span style="color: var(--danger);">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $subject->name) }}" required>
        </div>

        <div class="form-group">
            <label for="category">Kategori Mapel</label>
            <select id="category" name="category">
                <option value="Umum" {{ old('category', $subject->category) == 'Umum' ? 'selected' : '' }}>Umum</option>
                <option value="Kejuruan" {{ old('category', $subject->category) == 'Kejuruan' ? 'selected' : '' }}>Kejuruan</option>
                <option value="Olahraga" {{ old('category', $subject->category) == 'Olahraga' ? 'selected' : '' }}>Olahraga</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
            <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
