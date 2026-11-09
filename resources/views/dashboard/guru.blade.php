@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard Guru Pengajar</h1>
        <p class="page-subtitle">Selamat datang, <strong>{{ $teacher->name ?? auth()->user()->name }}</strong> &bull; {{ $todayName }}, {{ $today->format('d M Y') }}</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('journals.index', ['tab' => 'weekly']) }}" class="btn btn-secondary btn-sm">
            Jadwal Saya
        </a>
        <a href="{{ route('journals.create') }}" class="btn btn-primary btn-sm">
            Tulis Jurnal
        </a>
    </div>
</div>

@if($teacher && $teacher->homeroomClasses->isNotEmpty())
    @foreach($teacher->homeroomClasses as $hClass)
        <div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(139, 92, 246, 0.05) 100%); border-left: 4px solid var(--primary, #4f46e5);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <span class="badge badge-primary">Tugas Tambahan</span>
                        <span style="font-weight: 700; font-size: 1.15rem; color: var(--text-primary);">Wali Kelas {{ $hClass->name }}</span>
                    </div>
                    <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0;">
                        Tingkat {{ $hClass->grade }} &bull; Jurusan {{ $hClass->major }} &bull; Total <strong>{{ $hClass->students->count() }}</strong> Siswa Terdaftar
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="{{ route('attendances.index') }}" class="btn btn-primary btn-sm">
                        Rekap Presensi Kelas
                    </a>
                    <a href="{{ route('homeroom.permissions.index') }}" class="btn btn-secondary btn-sm">
                        Izin Siswa Kelas
                    </a>
                </div>
            </div>
        </div>
    @endforeach
@endif

@if($teacher && $teacher->bkClasses->isNotEmpty())
    <div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(99, 102, 241, 0.04) 100%); border-left: 4px solid #4f46e5;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <span class="badge" style="background: rgba(79, 70, 229, 0.15); color: #4338ca; font-weight: 700;">Guru Bimbingan & Konseling</span>
                    <span style="font-weight: 700; font-size: 1.15rem; color: var(--text-primary);">{{ $teacher->bkClasses->count() }} Kelas Binaan</span>
                </div>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0;">
                    Kelas Binaan: <strong>{{ $teacher->bkClasses->pluck('name')->join(', ') }}</strong>
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a href="{{ route('students.index') }}" class="btn btn-primary btn-sm">
                    Data Siswa Binaan
                </a>
                <a href="{{ route('attendances.index') }}" class="btn btn-secondary btn-sm">
                    Rekap Presensi Siswa
                </a>
                <a href="{{ route('permission-requests.verification') }}" class="btn btn-secondary btn-sm">
                    Monitoring Izin Siswa
                </a>
            </div>
        </div>
    </div>
@endif

{{-- Stat Cards --}}
<div class="grid-3" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalMySchedules }}</div>
            <div class="stat-label">Total Jadwal Mengajar</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $schedules->count() }}</div>
            <div class="stat-label">Jadwal Hari Ini ({{ $todayName }})</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalMyJournals }}</div>
            <div class="stat-label">Total Jurnal Terisi</div>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    {{-- Informasi Siklus Aktif --}}
    <div class="card">
        <h2 class="card-title">
            <span>Siklus Pembelajaran Aktif</span>
            @if($activeCycle)
                <span class="badge badge-success">Berjalan</span>
            @else
                <span class="badge badge-warning">Belum Ada</span>
            @endif
        </h2>

        @if($activeCycle)
            <div style="font-size: 1.35rem; font-weight: 700; color: var(--accent); margin-bottom: 0.5rem;">
                {{ $activeCycle->cycle }}
            </div>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.75rem;">
                Periode Pelaksanaan: <strong>2 Minggu</strong>
            </p>
            <p style="color: var(--text-muted); font-size: 0.8rem;">
                Pastikan seluruh kegiatan belajar dan presensi siswa dicatat sesuai rentang siklus yang berlaku.
            </p>
        @else
            <p style="color: var(--text-muted); font-size: 0.875rem;">
                Saat ini belum ada siklus blok yang sedang diaktifkan oleh Waka Kurikulum.
            </p>
        @endif
    </div>

    {{-- Akses Menu Cepat Guru --}}
    <div class="card">
        <h2 class="card-title">Navigasi Mengajar</h2>
        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 1rem;">
            Akses cepat fitur pendukung kegiatan pembelajaran kelas Anda:
        </p>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('journals.index', ['tab' => 'weekly']) }}" class="btn btn-secondary btn-sm">Jadwal Mengajar Lengkap</a>
            <a href="{{ route('journals.index') }}" class="btn btn-secondary btn-sm">Daftar Riwayat Jurnal</a>
            <a href="{{ route('attendances.index') }}" class="btn btn-secondary btn-sm">Presensi Siswa</a>
            <a href="{{ route('journals.create') }}" class="btn btn-primary btn-sm">Input Jurnal Sekarang</a>
        </div>
    </div>
</div>

{{-- Alert Peringatan Jurnal Belum Terisi --}}
@if($journalAlerts->count())
    <div class="card" style="border-left: 4px solid var(--danger); margin-bottom: 1.5rem;">
        <h2 class="card-title" style="color: var(--danger-text);">
            <span>Perhatian: Jadwal Memerlukan Pengisian Jurnal</span>
            <span class="badge badge-danger">{{ $journalAlerts->count() }} Jadwal Lewat Waktu</span>
        </h2>
        <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
            Waktu pembelajaran jadwal di bawah ini telah usai lebih dari 30 menit. Mohon segera input jurnal materi dan aktivitas siswa:
        </p>

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach($journalAlerts as $alert)
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; padding: 0.85rem 1rem; background-color: var(--danger-bg); border-radius: var(--radius-sm); border: 1px solid #fecaca;">
                    <div>
                        <div style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">
                            {{ $alert['schedule']->subject->name }} &bull; Kelas {{ $alert['schedule']->schoolClass->name }}
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.2rem;">
                            Ruang: <strong>{{ $alert['schedule']->room->name }}</strong> &bull;
                            Pukul: <strong>{{ substr($alert['schedule']->start_time, 0, 5) }} - {{ substr($alert['schedule']->end_time, 0, 5) }}</strong> &bull;
                            Terlambat: <strong style="color: var(--danger);">{{ number_format($alert['minutes_late'], 0) }} menit</strong>
                        </div>
                    </div>
                    <a href="{{ route('journals.create', ['schedule_id' => $alert['schedule']->id]) }}" class="btn btn-primary btn-sm">
                        Isi Jurnal Sekarang
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Jadwal Mengajar Hari Ini --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <h2 class="card-title">
        <span>Jadwal Mengajar Hari Ini ({{ $todayName }})</span>
        <span class="badge badge-info">{{ $schedules->count() }} Sesi Terjadwal</span>
    </h2>

    @if($schedules->count())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Ruangan</th>
                        <th>Jam Pelajaran</th>
                        <th>Aksi Pembelajaran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $schedule->subject->name }}</strong></td>
                            <td><span class="badge badge-info">{{ $schedule->schoolClass->name }}</span></td>
                            <td>{{ $schedule->room->name }}</td>
                            <td>
                                <strong>{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</strong>
                            </td>
                            <td>
                                @php
                                    $todayJournal = \App\Models\Journal::where('schedule_id', $schedule->id)
                                        ->whereDate('journal_date', \Carbon\Carbon::today())
                                        ->first();
                                @endphp
                                @if($todayJournal)
                                    <span class="badge badge-success" style="margin-right: 0.35rem; background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                                        ✓ Sudah Diisi
                                    </span>
                                    <a href="{{ route('journals.show', $todayJournal->id) }}" class="btn btn-secondary btn-sm">
                                        Lihat Jurnal
                                    </a>
                                @else
                                    <a href="{{ route('journals.create', ['schedule_id' => $schedule->id]) }}" class="btn btn-primary btn-sm">
                                        Buka Kelas / Isi Jurnal
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="padding: 1.25rem; text-align: center; color: var(--text-secondary); background-color: var(--bg-surface-hover); border-radius: var(--radius-sm);">
            <p style="font-size: 0.95rem; margin-bottom: 0.25rem;"><strong>Tidak ada jadwal mengajar pada hari {{ $todayName }}.</strong></p>
            <p style="font-size: 0.8rem; color: var(--text-muted);">Gunakan waktu luang untuk evaluasi materi atau meninjau jurnal yang telah diselesaikan.</p>
        </div>
    @endif
</div>

{{-- Riwayat Jurnal Terakhir Saya --}}
<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
        <h2 class="card-title" style="margin-bottom: 0;">
            <span>Riwayat Jurnal Mengajar Saya</span>
            @if($recentMyJournals->count() > 0)
                <span class="badge badge-info">{{ $recentMyJournals->count() }} Data Terbaru</span>
            @endif
        </h2>
        <a href="{{ route('journals.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
    </div>

    @if($recentMyJournals->count())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Materi Pembelajaran</th>
                        <th>Waktu Catat</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentMyJournals as $journal)
                        <tr>
                            <td>{{ $journal->journal_date ? $journal->journal_date->format('d/m/Y') : '-' }}</td>
                            <td><strong>{{ $journal->schedule->subject->name ?? '-' }}</strong></td>
                            <td><span class="badge badge-info">{{ $journal->schedule->schoolClass->name ?? '-' }}</span></td>
                            <td style="max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $journal->material }}
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $journal->submitted_at ? $journal->submitted_at->format('H:i') : $journal->created_at->format('H:i') }} WIB
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('journals.show', $journal->id) }}" class="btn btn-secondary btn-sm">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 0.875rem;">
            Anda belum mengisi jurnal mengajar. Mulai buat jurnal setelah sesi pembelajaran selesai.
        </p>
    @endif
</div>
@endsection