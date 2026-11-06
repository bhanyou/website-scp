@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Informasi Kelas</h1>
        <p class="page-subtitle">Informasi rombongan belajar dan wali kelas</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="detail-item">
        <strong>Nama Kelas</strong>
        <p>{{ $class->name }}</p>
    </div>

    <div class="detail-item">
        <strong>Tingkat Kelas</strong>
        <p>Kelas {{ $class->grade }}</p>
    </div>

    <div class="detail-item">
        <strong>Program Keahlian / Jurusan</strong>
        <p><span class="badge badge-info">{{ $class->department->name ?? $class->major }}</span></p>
    </div>

    <div class="detail-item">
        <strong>Guru Wali Kelas</strong>
        <p>{{ $class->homeroomTeacher?->name ?? 'Belum ditentukan' }}</p>
    </div>

    <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
        <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-primary">
            Edit Data Kelas
        </a>
        <a href="{{ route('classes.index') }}" class="btn btn-secondary">
            ← Kembali ke Data Kelas
        </a>
    </div>
</div>
@endsection