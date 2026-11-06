@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Siswa</h1>
        <p class="page-subtitle">Informasi profil dan data induk siswa</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <div class="detail-item">
        <strong>Nomor Induk Siswa (NIS)</strong>
        <p>{{ $student->nis }}</p>
    </div>

    <div class="detail-item">
        <strong>Username Login Siswa</strong>
        <p><code style="color: var(--accent); font-weight: 600;">{{ $student->username ?? $student->user?->username ?? '-' }}</code></p>
    </div>

    <div class="detail-item">
        <strong>Nama Lengkap Siswa</strong>
        <p>{{ $student->name }}</p>
    </div>

    <div class="detail-item">
        <strong>Akun Email Login</strong>
        <p>{{ $student->user?->email ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Kelas</strong>
        <p><span class="badge badge-info">{{ $student->schoolClass?->name ?? '-' }}</span></p>
    </div>

    <div class="detail-item">
        <strong>Orang Tua / Wali</strong>
        <p>{{ $student->parent?->name ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Nomor Telepon</strong>
        <p>{{ $student->phone ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Alamat</strong>
        <p>{{ $student->address ?? '-' }}</p>
    </div>

    <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-primary">
            Edit Data Siswa
        </a>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">
            ← Kembali ke Data Siswa
        </a>
    </div>
</div>
@endsection