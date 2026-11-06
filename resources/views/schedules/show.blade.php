@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Jadwal Mengajar</h1>
        <p class="page-subtitle">Informasi jadwal pembelajaran guru dan alokasi ruangan</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="detail-item">
        <strong>Guru Pengajar</strong>
        <p>{{ $schedule->teacher?->name ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Siklus / Putaran Blok</strong>
        <p><span class="badge badge-info">{{ $schedule->blockCycle?->cycle ?? '-' }}</span></p>
    </div>

    <div class="detail-item">
        <strong>Mata Pelajaran</strong>
        <p style="font-size: 1.1rem; font-weight: 700; color: var(--accent);">{{ $schedule->subject?->name ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Kelas & Ruangan</strong>
        <p>{{ $schedule->schoolClass?->name ?? '-' }} • Ruang {{ $schedule->room?->name ?? '-' }}</p>
    </div>

    <div class="detail-item">
        <strong>Hari & Jam Pembelajaran</strong>
        <p>{{ $schedule->day }}, {{ $schedule->session_label }}</p>
    </div>

    <div class="detail-item">
        <strong>Status Putaran</strong>
        <p>
            @if ($schedule->blockCycle?->is_locked)
                <span class="badge badge-danger">🔒 Terkunci</span>
            @else
                <span class="badge badge-success">🔓 Terbuka</span>
            @endif
        </p>
    </div>

    <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
        @if(!$schedule->blockCycle?->is_locked)
            <a href="{{ route('schedules.edit', $schedule->id) }}" class="btn btn-primary">
                Edit Jadwal
            </a>
        @endif
        <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
            ← Kembali ke Jadwal
        </a>
    </div>
</div>
@endsection