@extends('layouts.app')

@section('content')
<div class="page-header" style="margin-bottom: 1.25rem;">
    <div>
        <h1 class="page-title">Jurnal Mengajar</h1>
        <p class="page-subtitle">Buka kelas, catat materi pembelajaran, dan isi presensi kehadiran siswa hari ini</p>
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
{{-- TAB NAVIGASI: HARI INI vs ARSIP RIWAYAT JURNAL                            --}}
{{-- ========================================================================= --}}
<div style="display: flex; gap: 0.65rem; margin-bottom: 1.5rem; flex-wrap: wrap; border-bottom: 2px solid var(--border-color); padding-bottom: 0.75rem;">
    <a href="{{ route('journals.index', ['tab' => 'today']) }}" 
       class="btn {{ $activeTab === 'today' ? 'btn-primary' : 'btn-secondary' }}" 
       style="border-radius: 9999px; font-weight: 600; padding: 0.45rem 1.15rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none;">
        <span>📅</span>
        <span>Hari Ini (Isi Jurnal & Presensi)</span>
        @if($todayPendingSchedules->count() > 0)
            <span style="background: #ef4444; color: #fff; font-size: 0.72rem; padding: 0.1rem 0.45rem; border-radius: 9999px; margin-left: 0.25rem;">
                {{ $todayPendingSchedules->count() }}
            </span>
        @endif
    </a>

    <a href="{{ route('journals.index', ['tab' => 'history']) }}" 
       class="btn {{ $activeTab === 'history' ? 'btn-primary' : 'btn-secondary' }}" 
       style="border-radius: 9999px; font-weight: 600; padding: 0.45rem 1.15rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none;">
        <span>📋</span>
        <span>Arsip Riwayat Jurnal</span>
    </a>

    <div style="margin-left: auto;">
        <a href="{{ route('student.schedule') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none;">
            <span>🗓️</span>
            <span>Lihat Jadwal Mengajar Mingguan</span>
        </a>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- TAB 1: HARI INI (JADWAL HARIAN + PENGISIAN JURNAL & PRESENSI)              --}}
{{-- ========================================================================= --}}
@if($activeTab === 'today')
    {{-- Kartu 1.1: Jadwal Mengajar Hari Ini yang Perlu Diisi Jurnal --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span style="font-weight: 700; font-size: 1.125rem;">Jadwal Mengajar Hari Ini ({{ $todayName }}, {{ $today->format('d/m/Y') }})</span>
                <span class="badge badge-info" style="margin-left: 0.5rem; font-size: 0.75rem;">
                    Siklus: Putaran {{ $activeCycleNumber }} (Aktif)
                </span>
            </div>
            @if($todayPendingSchedules->count() > 0)
                <span class="badge badge-warning" style="font-weight: 700;">{{ $todayPendingSchedules->count() }} Jadwal Perlu Diisi</span>
            @else
                <span class="badge badge-success" style="font-weight: 700;">✓ Semua Selesai Diisi</span>
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

    {{-- Kartu 1.2: Riwayat Jurnal Hari Ini (Read-Only) --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <div>
                <span style="font-weight: 700; font-size: 1.125rem;">Jurnal Mengajar yang Sudah Diisi Hari Ini</span>
                <small style="display: block; color: var(--text-muted); font-size: 0.75rem; margin-top: 2px;">
                    Jadwal yang telah Anda isi otomatis berpindah ke sini. Data hari-hari sebelumnya dapat dilihat di tab <strong>Arsip Riwayat Jurnal</strong>.
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
                <p style="font-size: 0.75rem;">Jadwal yang telah Anda isi di atas akan otomatis berpindah ke sini.</p>
            </div>
        @endif
    </div>

{{-- ========================================================================= --}}
{{-- TAB 2: ARSIP RIWAYAT JURNAL (HISTORY LENGKAP GURU)                        --}}
{{-- ========================================================================= --}}
@elseif($activeTab === 'history')
    <div class="card" style="margin-bottom: 1.5rem;">
        {{-- Form Filter Tanggal --}}
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
            <div>
                <span style="font-weight: 700; font-size: 1.125rem;">Arsip Riwayat Jurnal Mengajar</span>
                <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                    Daftar seluruh jurnal yang pernah Anda isi beserta catatan materi dan absensi
                </p>
            </div>

            <form method="GET" action="{{ route('journals.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="history">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.6rem; height: auto;">
                <span style="font-size: 0.8rem; color: var(--text-muted);">s/d</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.6rem; height: auto;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->filled('start_date') || request()->filled('end_date'))
                    <a href="{{ route('journals.index', ['tab' => 'history']) }}" class="btn btn-secondary btn-sm">Reset</a>
                @endif
            </form>
        </div>

        @if($allJournals->count())
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>TANGGAL</th>
                            <th>JAM / WAKTU</th>
                            <th>KELAS & RUANG</th>
                            <th>MATA PELAJARAN</th>
                            <th>MATERI PEMBELAJARAN</th>
                            <th>WAKTU INPUT</th>
                            <th style="text-align: right;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allJournals as $journal)
                            <tr>
                                <td>{{ $allJournals->firstItem() + $loop->index }}</td>
                                <td>
                                    <strong>{{ $journal->journal_date ? $journal->journal_date->format('d/m/Y') : '-' }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $journal->schedule?->start_time ? substr($journal->schedule->start_time, 0, 5) : '-' }} - {{ $journal->schedule?->end_time ? substr($journal->schedule->end_time, 0, 5) : '-' }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $journal->schedule?->schoolClass?->name ?? '-' }}</span>
                                    <small style="display: block; color: var(--text-muted); font-size: 0.72rem; margin-top: 1px;">
                                        {{ $journal->schedule?->room?->name ?? '-' }}
                                    </small>
                                </td>
                                <td><strong>{{ $journal->schedule?->subject?->name ?? '-' }}</strong></td>
                                <td style="max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $journal->material }}
                                </td>
                                <td>
                                    <span style="font-size: 0.78rem; color: var(--text-secondary);">
                                        {{ $journal->submitted_at ? $journal->submitted_at->format('H:i') : $journal->created_at->format('H:i') }} WIB
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="{{ route('journals.show', $journal->id) }}" class="btn btn-secondary btn-sm">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.25rem;">
                {{ $allJournals->links() }}
            </div>
        @else
            <div style="padding: 2.5rem; text-align: center; color: var(--text-muted);">
                <p style="font-size: 0.95rem; font-weight: 600;">Belum ada riwayat jurnal yang ditemukan.</p>
                <p style="font-size: 0.8rem; margin-top: 0.25rem;">Jurnal yang Anda buat akan tersimpan permanen di sini.</p>
            </div>
        @endif
    </div>
@endif

<div style="margin-top: 1rem;">
    <a href="{{ route('dashboard.guru') }}" class="btn-back">
        ← Kembali ke Dashboard Guru
    </a>
</div>
@endsection
