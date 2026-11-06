@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Ruangan</h1>
        <p class="page-subtitle">Informasi lengkap data ruangan dan daya tampung</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="detail-item">
        <strong>Nama Ruangan</strong>
        <p>{{ $room->name }}</p>
    </div>

    <div class="detail-item">
        <strong>Tipe Ruangan</strong>
        <p>{{ $room->type_label }}</p>
    </div>

    <div class="detail-item">
        <strong>Kapasitas Siswa</strong>
        <p><span class="badge badge-info">{{ $room->capacity ?? '-' }} Siswa</span></p>
    </div>

    <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
        <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-primary">
            Edit Ruangan
        </a>
        <a href="{{ route('rooms.index') }}" class="btn btn-secondary">
            ← Kembali ke Data Ruangan
        </a>
    </div>
</div>
@endsection