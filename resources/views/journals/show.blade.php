@extends('layouts.app')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
    <div>
        <h1 class="page-title">Detail Jurnal & Presensi</h1>
        <p class="page-subtitle">Informasi materi ajar dan daftar kehadiran siswa kelas</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        @if($journal->journal_date && $journal->journal_date->isToday())
            <a href="{{ route('journals.edit', $journal->id) }}" class="btn btn-warning">
                Edit Jurnal dan Presensi
            </a>
        @endif
        <a href="{{ route('journals.index') }}" class="btn btn-secondary">
            Kembali ke Jurnal Mengajar
        </a>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    {{-- Informasi Jurnal --}}
    <div class="card" style="margin-bottom: 0;">
        <h2 class="card-title">Informasi Pembelajaran</h2>
        
        <div class="detail-grid">
            <div class="detail-field">
                <span class="detail-label">Guru Pengajar</span>
                <p class="detail-value font-semibold">{{ $journal->teacher?->name ?? '-' }}</p>
            </div>

            <div class="detail-field">
                <span class="detail-label">Putaran Blok</span>
                <p class="detail-value">
                    <span class="badge badge-info">{{ $journal->schedule?->blockCycle?->cycle ?? '-' }}</span>
                </p>
            </div>

            <div class="detail-field">
                <span class="detail-label">Mata Pelajaran & Kelas</span>
                <p class="detail-value">
                    <strong style="color: var(--accent);">{{ $journal->schedule?->subject?->name ?? '-' }}</strong> 
                    • <span class="badge badge-secondary">{{ $journal->schedule?->schoolClass?->name ?? '-' }}</span>
                </p>
            </div>

            <div class="detail-field">
                <span class="detail-label">Ruangan & Waktu</span>
                <p class="detail-value">
                    {{ $journal->schedule?->room?->name ?? '-' }} • 
                    {{ $journal->schedule?->day ?? '-' }}, {{ $journal->journal_date?->format('d/m/Y') ?? '-' }} 
                    <span style="color: var(--text-muted); font-size: 0.8rem;">
                        ({{ $journal->schedule?->start_time ? substr($journal->schedule->start_time, 0, 5) : '-' }} - {{ $journal->schedule?->end_time ? substr($journal->schedule->end_time, 0, 5) : '-' }})
                    </span>
                </p>
            </div>

            <div class="detail-field">
                <span class="detail-label">Materi Pembelajaran</span>
                <p class="detail-value font-medium" style="color: var(--text-primary); text-align: left; line-height: 1.5; margin: 0;">{{ trim($journal->material) }}</p>
            </div>

            <div class="detail-field">
                <span class="detail-label">Kegiatan Kelas</span>
                <p class="detail-value font-medium" style="color: var(--text-primary); text-align: left; line-height: 1.5; margin: 0;">{{ trim($journal->activity) }}</p>
            </div>

            <div class="detail-field">
                <span class="detail-label">Catatan Tambahan</span>
                <p class="detail-value" style="color: var(--text-secondary); text-align: left; font-style: {{ $journal->notes ? 'normal' : 'italic' }}; margin: 0;">{{ trim($journal->notes ?? 'Tidak ada catatan tambahan.') }}</p>
            </div>
        </div>
    </div>

    {{-- Foto Dokumentasi --}}
    <div class="card" style="margin-bottom: 0;">
        <h2 class="card-title">Foto Dokumentasi Kegiatan</h2>
        @if($journal->photo_path)
            <div style="background-color: var(--bg-surface-hover); border-radius: var(--radius-sm); border: 1px solid var(--border-color); overflow: hidden; max-height: 380px; display: flex; align-items: center; justify-content: center;">
                <img src="{{ asset('storage/' . $journal->photo_path) }}" alt="Foto kegiatan pembelajaran" style="width: 100%; height: auto; max-height: 380px; object-fit: contain;">
            </div>
            <div style="margin-top: 0.75rem; font-size: 0.75rem; color: var(--text-muted); text-align: right;">
                Waktu Rekam/Input: {{ $journal->submitted_at ? $journal->submitted_at->format('H:i') . ' WIB' : '-' }}
            </div>
        @else
            <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted); background-color: var(--bg-surface-hover); border-radius: var(--radius-sm); border: 1px dashed var(--border-color);">
                <p style="font-size: 0.875rem;">Tidak ada foto kegiatan yang diunggah.</p>
            </div>
        @endif
    </div>
</div>

{{-- Presensi Siswa --}}
<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <span style="font-weight: 700; font-size: 1.125rem;">Presensi Kehadiran Siswa</span>
        <span class="badge badge-info">{{ $attendances->count() }} Total Siswa</span>
    </div>

    {{-- Keterangan Rekap Hadir, Sakit, Izin, Alpa --}}
    @php
        $hadirCount = $attendances->where('status', 'Hadir')->count();
        $sakitCount = $attendances->where('status', 'Sakit')->count();
        $izinCount  = $attendances->where('status', 'Izin')->count();
        $alpaCount  = $attendances->where('status', 'Alpa')->count();
    @endphp
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
        <div style="background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
            <strong style="color: #059669;">Hadir:</strong> 
            <span style="font-weight: 700; color: #059669;">{{ $hadirCount }}</span> Siswa
        </div>
        <div style="background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
            <strong style="color: #d97706;">Sakit:</strong> 
            <span style="font-weight: 700; color: #d97706;">{{ $sakitCount }}</span> Siswa
        </div>
        <div style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
            <strong style="color: #2563eb;">Izin:</strong> 
            <span style="font-weight: 700; color: #2563eb;">{{ $izinCount }}</span> Siswa
        </div>
        <div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
            <strong style="color: #dc2626;">Alpa:</strong> 
            <span style="font-weight: 700; color: #dc2626;">{{ $alpaCount }}</span> Siswa
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th style="width: 120px;">NIS</th>
                    <th>Nama Siswa</th>
                    <th style="width: 150px;">Status Kehadiran</th>
                    <th>Catatan Izin / Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $attendance->student?->nis ?? '-' }}</td>
                        <td><strong>{{ $attendance->student?->name ?? '-' }}</strong></td>
                        <td>
                            @if($attendance->status === 'Hadir')
                                <span class="badge badge-success">Hadir</span>
                            @elseif($attendance->status === 'Sakit')
                                <span class="badge badge-warning">Sakit</span>
                            @elseif($attendance->status === 'Izin')
                                <span class="badge badge-info">Izin</span>
                            @elseif($attendance->status === 'Alpa')
                                <span class="badge badge-danger">Alpa</span>
                            @else
                                <span class="badge badge-secondary">{{ $attendance->status }}</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                {{ $attendance->note ?? '-' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Belum ada data presensi yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; align-items: center;">
        @if($journal->journal_date && $journal->journal_date->isToday())
            <a href="{{ route('journals.edit', $journal->id) }}" class="btn btn-warning">
                Edit Jurnal dan Presensi
            </a>
        @endif
        <a href="{{ route('journals.index') }}" class="btn btn-secondary">
            Kembali ke Jurnal Mengajar
        </a>
    </div>
</div>

<style>
.detail-grid {
    display: flex;
    flex-direction: column;
    gap: 1.15rem;
    align-items: stretch;
}

.detail-field {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    text-align: left;
    display: block;
}

.detail-value {
    font-size: 0.95rem;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.5;
    text-align: left;
    width: 100%;
}
</style>
@endsection
