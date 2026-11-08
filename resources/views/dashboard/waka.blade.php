@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard Waka Kurikulum</h1>
        <p class="page-subtitle">Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong> &bull; {{ $todayName }}, {{ $today->format('d M Y') }}</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('academic-year.index') }}" class="btn btn-secondary btn-sm">
            Tahun Ajaran: {{ \App\Models\Setting::where('key', 'academic_year')->value('value') ?? '2025/2026' }} ({{ \App\Models\Setting::where('key', 'semester')->value('value') ?? 'Ganjil' }})
        </a>
    </div>
</div>

{{-- 5 Stat Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalTeachers }}</div>
            <div class="stat-label">Guru Pengajar</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalStudents }}</div>
            <div class="stat-label">Siswa Terdaftar</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalMajors }}</div>
            <div class="stat-label">Jurusan Keahlian</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalRooms }}</div>
            <div class="stat-label">Ruangan Belajar</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalSubjects }}</div>
            <div class="stat-label">Mata Pelajaran</div>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    {{-- Siklus Blok Aktif --}}
    <div class="card">
        <h2 class="card-title">
            <span>Siklus Blok Aktif</span>
            @if($activeCycle)
                <span class="badge badge-success">Sedang Berjalan</span>
            @else
                <span class="badge badge-warning">Belum Aktif</span>
            @endif
        </h2>

        @if($activeCycle)
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="font-size: 1.35rem; font-weight: 700; color: var(--accent);">
                    {{ $activeCycle->cycle }}
                </div>
                <div>
                    @if($activeCycle->is_locked)
                        <span class="badge badge-danger">Terkunci</span>
                    @else
                        <span class="badge badge-info">Terbuka</span>
                    @endif
                </div>
            </div>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                Periode Pelaksanaan: <strong>2 Minggu</strong>
            </p>
        @else
            <div style="padding: 1rem; background-color: var(--warning-bg); border-radius: var(--radius-sm); color: var(--warning-text); font-size: 0.875rem; margin-bottom: 1rem;">
                Belum ada siklus blok yang diaktifkan. Silakan aktifkan siklus agar jadwal dan pembelajaran dapat berjalan.
            </div>
        @endif

        <div>
            <a href="{{ route('block-cycles.index') }}" class="btn btn-secondary btn-sm">
                Atur Siklus Blok
            </a>
        </div>
    </div>

    {{-- Ringkasan Operasional Hari Ini --}}
    <div class="card">
        <h2 class="card-title">
            <span>Operasional Hari {{ $todayName }}</span>
            <span class="badge badge-info">{{ $todaySchedulesCount }} Sesi Terjadwal</span>
        </h2>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem; text-align: center;">
            <div style="background-color: var(--bg-surface-hover); padding: 0.75rem; border-radius: var(--radius-sm);">
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--accent);">{{ $todaySchedulesCount }}</div>
                <div style="font-size: 0.75rem; color: var(--text-secondary);">Total Sesi</div>
            </div>
            <div style="background-color: var(--bg-surface-hover); padding: 0.75rem; border-radius: var(--radius-sm);">
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--success);">{{ $todayJournalsCount }}</div>
                <div style="font-size: 0.75rem; color: var(--text-secondary);">Jurnal Terisi</div>
            </div>
            <div style="background-color: var(--bg-surface-hover); padding: 0.75rem; border-radius: var(--radius-sm);">
                <div style="font-size: 1.25rem; font-weight: 700; color: {{ $journalAlerts->count() > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">
                    {{ $journalAlerts->count() }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-secondary);">Terlambat</div>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary btn-sm">Data Guru</a>
            <a href="{{ route('majors.index') }}" class="btn btn-secondary btn-sm">Data Jurusan</a>
            <a href="{{ route('subjects.index') }}" class="btn btn-secondary btn-sm">Mapel</a>
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-sm">Ruangan ({{ $totalRooms }})</a>
            <a href="{{ route('attendances.index') }}" class="btn btn-secondary btn-sm">Rekap Presensi</a>
        </div>
    </div>
</div>

{{-- Rekapitulasi Rombel & Siswa per Jurusan --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <div>
            <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0;">Rekapitulasi Rombel & Siswa per Jurusan</h2>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0.2rem 0 0 0;">Distribusi rombongan belajar dan total peserta didik aktif per kompetensi keahlian</p>
        </div>
        <span class="badge badge-info">{{ $majorStats->count() }} Jurusan Terdata</span>
    </div>

    @if($majorStats->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode</th>
                        <th>Nama Jurusan</th>
                        <th>Jumlah Rombel</th>
                        <th>Total Siswa Aktif</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($majorStats as $stat)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="badge badge-info">{{ $stat->code }}</span></td>
                            <td><strong>{{ $stat->name }}</strong></td>
                            <td>
                                <span class="badge badge-secondary">{{ $stat->rombel_count }} Rombel / Tingkat</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.2rem;">Total {{ $stat->total_classes }} Kelas</span>
                            </td>
                            <td><span class="badge badge-info">{{ $stat->total_students }} Siswa</span></td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="{{ route('students.index', ['major' => $stat->code]) }}" class="btn btn-secondary btn-sm">
                                    Data Siswa
                                </a>
                                <a href="{{ route('schedules.index', ['major' => $stat->code]) }}" class="btn btn-secondary btn-sm">
                                    Lihat Jadwal
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 0.875rem;">
            Belum ada data jurusan atau kelas yang terdaftar.
        </p>
    @endif
</div>

{{-- Monitoring Keterlambatan Jurnal Guru (Secukupnya di Dashboard) --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
        <h2 class="card-title" style="margin: 0;">
            <span>Monitoring Keterlambatan Pengisian Jurnal</span>
            @if($journalAlerts->count())
                <span class="badge badge-danger">{{ $journalAlerts->count() }} Tertunda</span>
            @else
                <span class="badge badge-success">Semua Tertib</span>
            @endif
        </h2>
    </div>

    @if($journalAlerts->count())
        <p style="color: var(--danger-text); font-size: 0.82rem; margin-bottom: 0.75rem;">
            Berikut ringkasan jadwal yang telah usai lebih dari 30 menit namun jurnal belum diisi.
        </p>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th>Nama Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Ruangan</th>
                        <th>Jam Selesai</th>
                        <th style="text-align: center;">Keterlambatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journalAlerts->take(5) as $alert)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">{{ $loop->iteration }}</td>
                            <td><strong>{{ $alert['schedule']->teacher->name ?? '-' }}</strong></td>
                            <td>{{ $alert['schedule']->subject->name ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ $alert['schedule']->schoolClass->name ?? '-' }}</span></td>
                            <td>{{ $alert['schedule']->room->name ?? '-' }}</td>
                            <td>{{ substr($alert['schedule']->start_time, 0, 5) }} - {{ substr($alert['schedule']->end_time, 0, 5) }}</td>
                            <td style="text-align: center;"><span class="badge badge-danger">{{ number_format($alert['minutes_late'], 0) }} menit</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.65rem 0.85rem; background: var(--bg-hover, #f8fafc); border-radius: var(--radius-sm, 6px); margin-top: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
            <div style="font-size: 0.8rem; color: var(--text-muted);">
                Menampilkan <strong>{{ min(5, $journalAlerts->count()) }}</strong> dari <strong>{{ $journalAlerts->count() }}</strong> keterlambatan hari ini.
            </div>
            <a href="{{ route('waka.journals.index', ['status' => 'belum']) }}" class="btn btn-danger btn-sm" style="font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                Lihat di Monitoring Jurnal ({{ $journalAlerts->count() }}) &rarr;
            </a>
        </div>
    @else
        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem; background-color: var(--success-bg); border-radius: var(--radius-sm); color: var(--success-text);">
            <div>
                <strong>Kondisi Ideal:</strong> Tidak ada keterlambatan pengisian jurnal hari ini. Seluruh KBM berjalan tertib sesuai kurikulum.
            </div>
        </div>
    @endif
</div>

{{-- Log Jurnal Pembelajaran Terbaru --}}
<div class="card">
    <h2 class="card-title">
        <span>Aktivitas Jurnal Mengajar Terbaru</span>
        @if($recentJournals->count() > 0)
            <span class="badge badge-info">{{ $recentJournals->count() }} Data Terbaru</span>
        @endif
    </h2>

    @if($recentJournals->count())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Guru Pengajar</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Materi / Pembahasan</th>
                        <th>Waktu Submit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentJournals as $journal)
                        <tr>
                            <td>{{ $journal->journal_date ? $journal->journal_date->format('d/m/Y') : '-' }}</td>
                            <td><strong>{{ $journal->teacher->name ?? '-' }}</strong></td>
                            <td>{{ $journal->schedule->subject->name ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ $journal->schedule->schoolClass->name ?? '-' }}</span></td>
                            <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $journal->material }}
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $journal->submitted_at ? $journal->submitted_at->format('H:i') : $journal->created_at->format('H:i') }} WIB
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 0.875rem;">
            Belum ada data jurnal mengajar yang tersimpan di sistem.
        </p>
    @endif
</div>
@endsection