@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Jurnal & Presensi Kelas</h1>
        <p class="page-subtitle">Pilih jadwal mengajar untuk membuka kelas dan mengisi jurnal presensi hari ini</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.25rem;">
        {{ session('success') }}
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info" style="margin-bottom: 1.25rem;">
        {{ session('info') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        {{ session('error') }}
    </div>
@endif

{{-- ========================================================================= --}}
{{-- KARTU 1: JADWAL MENGAJAR HARI INI (HANYA YANG BELUM DIISI JURNAL)        --}}
{{-- Saat diisi, jadwal langsung hilang dari sini dan berpindah ke riwayat      --}}
{{-- ========================================================================= --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 700; font-size: 1.125rem;">Jadwal Mengajar Hari Ini ({{ $todayName }})</span>
        @if($todayPendingSchedules->count() > 0)
            <span class="badge badge-info">{{ $todayPendingSchedules->count() }} Perlu Diisi</span>
        @else
            <span class="badge badge-success">Semua Selesai</span>
        @endif
    </div>

    @if($todayPendingSchedules->count())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th>MATA PELAJARAN</th>
                        <th>KELAS</th>
                        <th>RUANGAN</th>
                        <th>JAM PELAJARAN</th>
                        <th style="text-align: right;">AKSI PEMBELAJARAN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayPendingSchedules as $schedule)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong style="color: #0f172a; font-weight: 700;">{{ $schedule->subject?->name ?? '-' }}</strong></td>
                            <td><span class="badge badge-info">{{ $schedule->schoolClass?->name ?? '-' }}</span></td>
                            <td>{{ $schedule->room?->name ?? '-' }}</td>
                            <td>
                                <strong>{{ $schedule->start_time ? substr($schedule->start_time, 0, 5) : '-' }} - {{ $schedule->end_time ? substr($schedule->end_time, 0, 5) : '-' }}</strong>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="{{ route('journals.create', ['schedule_id' => $schedule->id]) }}" class="btn btn-primary btn-sm">
                                    Buka Kelas / Isi Jurnal
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="padding: 1.75rem; text-align: center; color: var(--text-secondary); background-color: var(--bg-surface-hover); border-radius: var(--radius-sm);">
            @if($todayJournals->count() > 0)
                <p style="font-size: 0.95rem; font-weight: 600; color: #059669; margin-bottom: 0.25rem;">
                    ✓ Semua jadwal mengajar hari ini telah selesai diisi jurnalnya.
                </p>
                <p style="font-size: 0.8rem; color: var(--text-muted);">
                    Data jurnal yang telah Anda buat dapat ditinjau pada tabel Riwayat Jurnal Mengajar di bawah.
                </p>
            @else
                <p style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.25rem;">
                    Tidak ada jadwal mengajar pada hari {{ $todayName }}.
                </p>
                <p style="font-size: 0.8rem; color: var(--text-muted);">
                    Gunakan waktu luang untuk evaluasi materi atau persiapan perangkat pembelajaran.
                </p>
            @endif
        </div>
    @endif
</div>

{{-- ========================================================================= --}}
{{-- KARTU 2: RIWAYAT JURNAL MENGAJAR GURU (HARI INI SAJA, OTOMATIS BERSIH BESOK) --}}
{{-- Menampung jadwal hari ini yang sudah selesai diisi (Read-Only)              --}}
{{-- ========================================================================= --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <div>
            <span style="font-weight: 700; font-size: 1.125rem;">Riwayat Jurnal Mengajar Hari Ini</span>
            <small style="display: block; color: var(--text-muted); font-size: 0.75rem; margin-top: 2px;">
                Hanya menampilkan jurnal yang diisi pada hari ini ({{ $today->format('d/m/Y') }}). Data hari sebelumnya tersimpan aman di rekap presensi.
            </small>
        </div>
        @if($todayJournals->count() > 0)
            <span class="badge badge-success">{{ $todayJournals->count() }} Selesai Hari Ini</span>
        @endif
    </div>

    @if($todayJournals->count())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Waktu / Jam</th>
                        <th>Kelas & Ruang</th>
                        <th>Mata Pelajaran</th>
                        <th>Materi Pembelajaran</th>
                        <th>Waktu Catat</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayJournals as $journal)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $journal->schedule?->start_time ? substr($journal->schedule->start_time, 0, 5) : '-' }} - {{ $journal->schedule?->end_time ? substr($journal->schedule->end_time, 0, 5) : '-' }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $journal->schedule?->schoolClass?->name ?? '-' }}</span>
                                <small style="display: block; color: var(--text-muted); font-size: 0.75rem; margin-top: 2px;">
                                    {{ $journal->schedule?->room?->name ?? '-' }}
                                </small>
                            </td>
                            <td><strong>{{ $journal->schedule?->subject?->name ?? '-' }}</strong></td>
                            <td style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $journal->material }}
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $journal->submitted_at ? $journal->submitted_at->format('H:i') : $journal->created_at->format('H:i') }} WIB
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="{{ route('journals.edit', $journal->id) }}" class="btn btn-warning btn-sm" style="margin-right: 0.35rem;">
                                    Edit Jurnal
                                </a>
                                <a href="{{ route('journals.show', $journal->id) }}" class="btn btn-secondary btn-sm">
                                    Lihat Jurnal
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="padding: 1.5rem; text-align: center; color: var(--text-muted); background-color: var(--bg-surface-hover); border-radius: var(--radius-sm);">
            <p style="font-size: 0.9rem; margin-bottom: 0.25rem;">Belum ada riwayat jurnal yang diisi untuk hari ini.</p>
            <p style="font-size: 0.75rem;">Jadwal yang telah Anda isi di atas akan otomatis berpindah ke sini (Read-Only).</p>
        </div>
    @endif
</div>

<div style="margin-top: 1rem;">
    <a href="{{ route('dashboard.guru') }}" class="btn-back">
        Kembali ke Dashboard Guru
    </a>
</div>
@endsection
