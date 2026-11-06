@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Siklus Blok</h1>
        <p class="page-subtitle">Informasi lengkap putaran siklus dan status penguncian jadwal</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="detail-item">
        <strong>Nama Siklus / Putaran</strong>
        <p style="font-size: 1.15rem; font-weight: 700; color: var(--accent);">{{ $cycle->cycle }}</p>
    </div>

    <div class="detail-item">
        <strong>Tanggal Mulai</strong>
        <p>{{ $cycle->start_date?->format('d-m-Y') ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Tanggal Selesai</strong>
        <p>{{ $cycle->end_date?->format('d-m-Y') ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Status Aktif</strong>
        <p>
            @if ($cycle->is_active)
                <span class="badge badge-success">✓ Sedang Aktif</span>
            @else
                <span class="badge badge-secondary" style="background-color: var(--bg-surface-hover); color: var(--text-muted);">Non-Aktif</span>
            @endif
        </p>
    </div>

    <div class="detail-item">
        <strong>Status Penguncian Jadwal</strong>
        <p>
            @if ($cycle->is_locked)
                <span class="badge badge-danger">🔒 Terkunci (Guru tidak dapat mengubah jadwal)</span>
            @else
                <span class="badge badge-info">🔓 Terbuka (Pengisian jadwal diizinkan)</span>
            @endif
        </p>
    </div>

    <div class="detail-item">
        <strong>Dikunci Oleh</strong>
        <p>{{ $cycle->lockedBy?->name ?? 'Belum ada data' }}</p>
    </div>

    @if ($cycle->locked_at)
        <div class="detail-item">
            <strong>Waktu Penguncian</strong>
            <p>{{ $cycle->locked_at->format('d-m-Y H:i') }}</p>
        </div>
    @endif

    <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
        <a href="{{ route('block-cycles.edit', $cycle->id) }}" class="btn btn-primary">
            Edit Siklus
        </a>
        <a href="{{ route('block-cycles.index') }}" class="btn btn-secondary">
            ← Kembali ke Siklus Blok
        </a>
    </div>
</div>
@endsection