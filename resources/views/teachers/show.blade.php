@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Informasi Guru</h1>
        <p class="page-subtitle">Informasi profil dan identitas data guru</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <div class="detail-item">
        <strong>Nama Lengkap</strong>
        <p>{{ $teacher->name }}</p>
    </div>

    <div class="detail-item">
        <strong>NIP</strong>
        <p>{{ $teacher->nip ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Username Login</strong>
        <p><code style="background-color: var(--bg-surface-hover, #f1f5f9); color: #4f46e5; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600;">{{ $teacher->username ?? ($teacher->user->username ?? '-') }}</code></p>
    </div>

    <div class="detail-item">
        <strong>Mata Pelajaran (Mapel)</strong>
        <p>{{ $teacher->mapel ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Alamat Email</strong>
        <p>{{ $teacher->user->email ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Nomor Telepon</strong>
        <p>{{ $teacher->phone ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Alamat</strong>
        <p>{{ $teacher->address ?? '-' }}</p>
    </div>

    <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
        <a href="{{ route('teachers.edit', $teacher->id) }}" class="btn btn-primary">
            Edit Data Guru
        </a>
        <a href="{{ route('teachers.index') }}" class="btn btn-secondary">
            ← Kembali ke Data Guru
        </a>
    </div>
</div>
@endsection