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

{{-- 6 Stat Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
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
            <div class="stat-value" style="color: #10b981;">{{ $todayHadir }}</div>
            <div class="stat-label">Siswa Hadir Hari Ini</div>
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

{{-- Monitoring Kehadiran Siswa Hari Ini --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #10b981;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Monitoring Kehadiran Siswa (Hari Ini)</span>
            </h2>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0.2rem 0 0 0;">
                Ringkasan kehadiran siswa kelas pada hari <strong>{{ $todayName }}, {{ $today->format('d M Y') }}</strong>
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('attendances.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                Lihat Rekap Presensi Lengkap &rarr;
            </a>
        </div>
    </div>

    {{-- Ringkasan 5 Kotak Status Presensi Hari Ini --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 0.85rem 1rem; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 800; color: #059669;">{{ $todayHadir }}</div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.04em;">Hadir</div>
        </div>
        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 0.85rem 1rem; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 800; color: #d97706;">{{ $todaySakit }}</div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.04em;">Sakit</div>
        </div>
        <div style="background: #ecfeff; border: 1px solid #a5f3fc; border-radius: 12px; padding: 0.85rem 1rem; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 800; color: #0891b2;">{{ $todayIzin }}</div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #0e7490; text-transform: uppercase; letter-spacing: 0.04em;">Izin</div>
        </div>
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 0.85rem 1rem; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 800; color: #dc2626;">{{ $todayAlpa }}</div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.04em;">Alpa</div>
        </div>
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 0.85rem 1rem; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 800; color: #2563eb;">{{ $todayAttendanceRate }}%</div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.04em;">Tingkat Hadir</div>
        </div>
    </div>

    {{-- Detail Presensi per Kelas yang Sudah Mengisi Hari Ini --}}
    @if($todayClassAttendances->isNotEmpty())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Rombel / Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengajar</th>
                        <th style="text-align: center; width: 85px;">Hadir</th>
                        <th style="text-align: center; width: 75px;">Sakit</th>
                        <th style="text-align: center; width: 75px;">Izin</th>
                        <th style="text-align: center; width: 75px;">Alpa</th>
                        <th style="text-align: center; width: 95px;">% Hadir</th>
                        <th style="text-align: right; width: 120px;">Daftar Siswa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayClassAttendances as $classAtt)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $classAtt->class_name }}</strong>
                            </td>
                            <td>{{ $classAtt->subject_name }}</td>
                            <td><span style="font-size: 0.85rem; color: var(--text-secondary);">{{ $classAtt->teacher_name }}</span></td>
                            <td style="text-align: center;">
                                <button type="button" 
                                    class="badge" 
                                    style="background: #ecfdf5; color: #065f46; font-weight: 700; font-size: 0.82rem; border: 1px solid #a7f3d0; cursor: pointer; padding: 0.25rem 0.6rem; border-radius: 9999px; transition: transform 0.15s ease;"
                                    title="Klik untuk lihat daftar siswa Hadir"
                                    onclick="openAttendanceModal({{ $loop->index }}, 'Hadir')">
                                    {{ $classAtt->hadir }}
                                </button>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" 
                                    class="badge" 
                                    style="background: {{ $classAtt->sakit > 0 ? '#fffbeb' : '#f8fafc' }}; color: {{ $classAtt->sakit > 0 ? '#b45309' : 'var(--text-muted)' }}; font-size: 0.82rem; border: 1px solid {{ $classAtt->sakit > 0 ? '#fde68a' : '#e2e8f0' }}; cursor: {{ $classAtt->sakit > 0 ? 'pointer' : 'default' }}; padding: 0.25rem 0.6rem; border-radius: 9999px; transition: transform 0.15s ease;"
                                    title="{{ $classAtt->sakit > 0 ? 'Klik untuk lihat daftar siswa Sakit' : 'Tidak ada siswa sakit' }}"
                                    onclick="{{ $classAtt->sakit > 0 ? 'openAttendanceModal(' . $loop->index . ', \'Sakit\')' : '' }}">
                                    {{ $classAtt->sakit }}
                                </button>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" 
                                    class="badge" 
                                    style="background: {{ $classAtt->izin > 0 ? '#ecfeff' : '#f8fafc' }}; color: {{ $classAtt->izin > 0 ? '#0e7490' : 'var(--text-muted)' }}; font-size: 0.82rem; border: 1px solid {{ $classAtt->izin > 0 ? '#a5f3fc' : '#e2e8f0' }}; cursor: {{ $classAtt->izin > 0 ? 'pointer' : 'default' }}; padding: 0.25rem 0.6rem; border-radius: 9999px; transition: transform 0.15s ease;"
                                    title="{{ $classAtt->izin > 0 ? 'Klik untuk lihat daftar siswa Izin' : 'Tidak ada siswa izin' }}"
                                    onclick="{{ $classAtt->izin > 0 ? 'openAttendanceModal(' . $loop->index . ', \'Izin\')' : '' }}">
                                    {{ $classAtt->izin }}
                                </button>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" 
                                    class="badge" 
                                    style="background: {{ $classAtt->alpa > 0 ? '#fef2f2' : '#f8fafc' }}; color: {{ $classAtt->alpa > 0 ? '#b91c1c' : 'var(--text-muted)' }}; font-size: 0.82rem; border: 1px solid {{ $classAtt->alpa > 0 ? '#fecaca' : '#e2e8f0' }}; cursor: {{ $classAtt->alpa > 0 ? 'pointer' : 'default' }}; padding: 0.25rem 0.6rem; border-radius: 9999px; transition: transform 0.15s ease;"
                                    title="{{ $classAtt->alpa > 0 ? 'Klik untuk lihat daftar siswa Alpa' : 'Tidak ada siswa alpa' }}"
                                    onclick="{{ $classAtt->alpa > 0 ? 'openAttendanceModal(' . $loop->index . ', \'Alpa\')' : '' }}">
                                    {{ $classAtt->alpa }}
                                </button>
                            </td>
                            <td style="text-align: center;">
                                <span style="font-weight: 800; font-size: 0.88rem; color: {{ $classAtt->rate >= 90 ? '#059669' : ($classAtt->rate >= 75 ? '#d97706' : '#dc2626') }};">
                                    {{ $classAtt->rate }}%
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <button type="button" 
                                    class="btn btn-primary btn-sm" 
                                    style="padding: 0.35rem 0.75rem; font-size: 0.78rem; font-weight: 600;" 
                                    onclick="openAttendanceModal({{ $loop->index }}, 'all')">
                                    Lihat Siswa
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="padding: 1.25rem; background: var(--bg-surface-hover); border-radius: var(--radius-sm); text-align: center; color: var(--text-muted); font-size: 0.85rem;">
            Belum ada guru yang menginput presensi siswa untuk hari ini. Data presensi akan otomatis terupdate di sini setelah guru mengisi jurnal mengajar.
            <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                Total akumulasi riwayat presensi tersimpan: <strong>{{ $allTimeHadir }} Hadir</strong> dari <strong>{{ $allTimeTotal }} data</strong> ({{ $allTimeRate }}% kehadiran).
            </div>
        </div>
    @endif
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

{{-- Monitoring Keterlambatan Jurnal Guru (Dengan Paginasi Per Halaman) --}}
<div class="card" style="margin-bottom: 1.5rem;" id="cardMonitoringKeterlambatan">
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
            Berikut daftar jadwal yang telah usai lebih dari 30 menit namun jurnal mengajar belum diisi pada hari ini.
        </p>

        {{-- Filter & Per Page Controls --}}
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.85rem; padding: 0.65rem 0.85rem; background: var(--bg-hover, #f8fafc); border-radius: var(--radius-sm, 8px); border: 1px solid var(--border-color, #e2e8f0);">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-secondary);">
                <span>Tampilkan</span>
                <select id="lateAlertPerPage" onchange="changeLateAlertPageSize(this.value)" style="padding: 0.3rem 0.6rem; border-radius: var(--radius-sm, 6px); border: 1px solid var(--border-color, #cbd5e1); font-size: 0.85rem; background: var(--bg-surface, #ffffff); color: var(--text-primary); cursor: pointer;">
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="all">Semua</option>
                </select>
                <span>per halaman</span>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="text" id="lateAlertSearch" onkeyup="filterLateAlerts()" placeholder="Cari guru, kelas, mapel..." style="padding: 0.35rem 0.75rem; border-radius: var(--radius-sm, 6px); border: 1px solid var(--border-color, #cbd5e1); font-size: 0.85rem; min-width: 220px; background: var(--bg-surface, #ffffff); color: var(--text-primary);">
            </div>
        </div>

        <div class="table-container">
            <table id="tableLateAlerts">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Nama Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Ruangan</th>
                        <th>Jam Selesai</th>
                        <th style="text-align: center;">Keterlambatan</th>
                    </tr>
                </thead>
                <tbody id="lateAlertsBody">
                    @foreach($journalAlerts as $alert)
                        <tr class="late-alert-row" data-search="{{ strtolower(($alert['schedule']->teacher->name ?? '') . ' ' . ($alert['schedule']->subject->name ?? '') . ' ' . ($alert['schedule']->schoolClass->name ?? '') . ' ' . ($alert['schedule']->room->name ?? '')) }}">
                            <td class="row-num" style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">{{ $loop->iteration }}</td>
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

        {{-- Navigasi Paginasi Bawah --}}
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0.85rem; background: var(--bg-hover, #f8fafc); border-radius: var(--radius-sm, 6px); margin-top: 0.75rem; flex-wrap: wrap; gap: 0.75rem; border-top: 1px solid var(--border-color, #e2e8f0);">
            <div id="lateAlertInfo" style="font-size: 0.82rem; color: var(--text-secondary);">
                Menampilkan <strong id="lateAlertShownRange">1 - 10</strong> dari <strong id="lateAlertTotalCount">{{ $journalAlerts->count() }}</strong> keterlambatan hari ini.
            </div>

            <div id="lateAlertPagination" style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                {{-- Diisi secara dinamis oleh JavaScript --}}
            </div>
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

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: DETAIL KEHADIRAN SISWA KELAS HARI INI     --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalClassAttendance" onclick="if(event.target === this) closeAttendanceModal()">
    <div class="modal-card modal-lg" style="max-height: 90vh; display: flex; flex-direction: column;">
        
        {{-- Modal Header --}}
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: flex-start; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <div>
                <h3 class="modal-title" id="attModalTitle" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Detail Kehadiran Siswa
                </h3>
                <p class="modal-subtitle" id="attModalSubtitle" style="font-size: 0.82rem; color: #64748b; margin: 0.3rem 0 0 0;">
                    Memuat rincian siswa...
                </p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeAttendanceModal()" style="background: transparent; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1; padding: 0.25rem;">&times;</button>
        </div>

        {{-- Filter Tabs & Pencarian Siswa --}}
        <div style="padding: 0.85rem 1.5rem; background: #ffffff; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;" id="attFilterTabs">
                <button type="button" class="btn-filter-status active" data-filter="all" onclick="filterModalStudents('all', this)">
                    Semua (<span id="countAll">0</span>)
                </button>
                <button type="button" class="btn-filter-status" data-filter="Hadir" onclick="filterModalStudents('Hadir', this)" style="--active-bg: #10b981;">
                    Hadir (<span id="countHadir">0</span>)
                </button>
                <button type="button" class="btn-filter-status" data-filter="Sakit" onclick="filterModalStudents('Sakit', this)" style="--active-bg: #f59e0b;">
                    Sakit (<span id="countSakit">0</span>)
                </button>
                <button type="button" class="btn-filter-status" data-filter="Izin" onclick="filterModalStudents('Izin', this)" style="--active-bg: #06b6d4;">
                    Izin (<span id="countIzin">0</span>)
                </button>
                <button type="button" class="btn-filter-status" data-filter="Alpa" onclick="filterModalStudents('Alpa', this)" style="--active-bg: #ef4444;">
                    Alpa (<span id="countAlpa">0</span>)
                </button>
            </div>

            <div style="min-width: 220px; flex: 0.6;">
                <input type="text" id="attSearchInput" placeholder="Cari nama atau NIS siswa..." oninput="searchModalStudents(this.value)" style="padding: 0.45rem 0.75rem; font-size: 0.82rem; border: 1px solid #cbd5e1; border-radius: 8px; width: 100%;">
            </div>
        </div>

        {{-- Modal Body: Tabel Siswa --}}
        <div style="padding: 0 1.5rem; overflow-y: auto; flex: 1; max-height: 52vh;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; margin: 0.75rem 0;">
                <thead style="position: sticky; top: 0; background: #ffffff; z-index: 5;">
                    <tr style="border-bottom: 2px solid #e2e8f0; text-align: left;">
                        <th style="padding: 0.65rem 0.5rem; width: 45px; color: #64748b; font-weight: 700;">No</th>
                        <th style="padding: 0.65rem 0.5rem; width: 100px; color: #64748b; font-weight: 700;">NIS</th>
                        <th style="padding: 0.65rem 0.5rem; color: #64748b; font-weight: 700;">Nama Siswa</th>
                        <th style="padding: 0.65rem 0.5rem; width: 110px; text-align: center; color: #64748b; font-weight: 700;">Status</th>
                        <th style="padding: 0.65rem 0.5rem; color: #64748b; font-weight: 700;">Keterangan / Catatan</th>
                    </tr>
                </thead>
                <tbody id="attModalTableBody">
                    {{-- Diisi secara dinamis via JavaScript --}}
                </tbody>
            </table>
            <div id="attEmptyMessage" style="display: none; padding: 2.5rem; text-align: center; color: #94a3b8; font-size: 0.88rem;">
                Tidak ada data siswa yang cocok dengan filter atau kata kunci ini.
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            <a href="#" id="attDirectRecapLink" class="btn btn-secondary btn-sm" style="font-weight: 600; text-decoration: none;">
                Buka Rekap Presensi Lengkap Kelas Ini &rarr;
            </a>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeAttendanceModal()">
                Tutup
            </button>
        </div>
    </div>
</div>

<style>
.btn-filter-status {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.35rem 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-filter-status:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
.btn-filter-status.active {
    background: var(--active-bg, var(--accent, #4f46e5));
    border-color: var(--active-bg, var(--accent, #4f46e5));
    color: #ffffff;
}
</style>
@endsection

@push('scripts')
<script>
    (function() {
        let currentPage = 1;
        let pageSize = 10;
        let visibleRows = [];

        function initLateAlertsPagination() {
            const allRows = Array.from(document.querySelectorAll('#lateAlertsBody .late-alert-row'));
            if (!allRows.length) return;
            visibleRows = allRows;
            renderLateAlerts();
        }

        window.changeLateAlertPageSize = function(val) {
            pageSize = val === 'all' ? 999999 : parseInt(val, 10);
            currentPage = 1;
            renderLateAlerts();
        };

        window.filterLateAlerts = function() {
            const query = (document.getElementById('lateAlertSearch')?.value || '').toLowerCase().trim();
            const allRows = Array.from(document.querySelectorAll('#lateAlertsBody .late-alert-row'));
            
            visibleRows = allRows.filter(row => {
                const searchStr = row.getAttribute('data-search') || '';
                return searchStr.includes(query);
            });
            
            currentPage = 1;
            renderLateAlerts();
        };

        window.goToLateAlertPage = function(page) {
            currentPage = page;
            renderLateAlerts();
            const card = document.getElementById('cardMonitoringKeterlambatan');
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        function renderLateAlerts() {
            const allRows = Array.from(document.querySelectorAll('#lateAlertsBody .late-alert-row'));
            const total = visibleRows.length;
            const totalPages = Math.ceil(total / pageSize) || 1;
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIdx = (currentPage - 1) * pageSize;
            const endIdx = startIdx + pageSize;

            // Sembunyikan semua dulu
            allRows.forEach(r => r.style.display = 'none');

            // Tampilkan yang sesuai halaman dan perbarui nomor urut
            visibleRows.slice(startIdx, endIdx).forEach((r, idx) => {
                r.style.display = '';
                const numCell = r.querySelector('.row-num');
                if (numCell) numCell.textContent = startIdx + idx + 1;
            });

            // Update teks info
            const rangeEl = document.getElementById('lateAlertShownRange');
            const totalEl = document.getElementById('lateAlertTotalCount');
            if (rangeEl && totalEl) {
                totalEl.textContent = total;
                if (total === 0) {
                    rangeEl.textContent = '0';
                } else {
                    const actualEnd = Math.min(endIdx, total);
                    rangeEl.textContent = `${startIdx + 1} - ${actualEnd}`;
                }
            }

            // Render tombol paginasi
            renderPaginationButtons(totalPages);
        }

        function renderPaginationButtons(totalPages) {
            const container = document.getElementById('lateAlertPagination');
            if (!container) return;
            container.innerHTML = '';

            if (totalPages <= 1) return;

            const createBtn = (text, page, disabled = false, active = false) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerHTML = text;
                btn.style.cssText = `
                    padding: 0.35rem 0.65rem;
                    font-size: 0.8rem;
                    font-weight: 600;
                    border: 1px solid ${active ? 'var(--accent, #4f46e5)' : 'var(--border-color, #cbd5e1)'};
                    background: ${active ? 'var(--accent, #4f46e5)' : 'var(--bg-surface, #ffffff)'};
                    color: ${active ? '#ffffff' : 'var(--text-primary, #1e293b)'};
                    border-radius: 6px;
                    cursor: ${disabled ? 'not-allowed' : 'pointer'};
                    opacity: ${disabled ? '0.5' : '1'};
                    transition: all 0.15s ease;
                `;
                if (!disabled && !active) {
                    btn.onclick = () => goToLateAlertPage(page);
                }
                return btn;
            };

            // Tombol Prev
            container.appendChild(createBtn('&laquo; Prev', currentPage - 1, currentPage === 1));

            // Daftar Halaman dengan Windowing cerdas
            let pages = [];
            if (totalPages <= 7) {
                pages = Array.from({length: totalPages}, (_, i) => i + 1);
            } else {
                if (currentPage <= 4) {
                    pages = [1, 2, 3, 4, 5, '...', totalPages];
                } else if (currentPage >= totalPages - 3) {
                    pages = [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
                } else {
                    pages = [1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages];
                }
            }

            pages.forEach(p => {
                if (p === '...') {
                    const dots = document.createElement('span');
                    dots.textContent = '...';
                    dots.style.cssText = 'padding: 0.35rem 0.5rem; font-size: 0.8rem; color: var(--text-muted);';
                    container.appendChild(dots);
                } else {
                    container.appendChild(createBtn(p, p, false, p === currentPage));
                }
            });

            // Tombol Next
            container.appendChild(createBtn('Next &raquo;', currentPage + 1, currentPage === totalPages));
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLateAlertsPagination);
        } else {
            initLateAlertsPagination();
        }
    })();
</script>

{{-- Script Modal Detail Kehadiran Siswa Hari Ini --}}
<script>
    const classAttendancesData = @json($todayClassAttendances);
    let currentClassIndex = null;
    let currentStatusFilter = 'all';
    let currentSearchQuery = '';

    window.openAttendanceModal = function(index, defaultFilter = 'all') {
        const classData = classAttendancesData[index];
        if (!classData) return;

        currentClassIndex = index;
        currentStatusFilter = defaultFilter;
        currentSearchQuery = '';
        const searchInput = document.getElementById('attSearchInput');
        if (searchInput) searchInput.value = '';

        // Update Title & Subtitle
        document.getElementById('attModalTitle').textContent = 'Detail Kehadiran: Kelas ' + classData.class_name;
        document.getElementById('attModalSubtitle').innerHTML = 
            'Mapel: <strong>' + classData.subject_name + '</strong> &bull; ' +
            'Pengajar: <strong>' + classData.teacher_name + '</strong> &bull; ' +
            'Total: <strong>' + classData.total + ' Siswa</strong> (' + classData.rate + '% Hadir)';

        // Update Direct Recap Link
        const directLink = document.getElementById('attDirectRecapLink');
        if (directLink) {
            directLink.href = '{{ route("attendances.index") }}?class_id=' + classData.class_id + '&range=today';
        }

        // Update Counter Badges
        document.getElementById('countAll').textContent = classData.total;
        document.getElementById('countHadir').textContent = classData.hadir;
        document.getElementById('countSakit').textContent = classData.sakit;
        document.getElementById('countIzin').textContent = classData.izin;
        document.getElementById('countAlpa').textContent = classData.alpa;

        // Set Active Filter Button
        document.querySelectorAll('#attFilterTabs .btn-filter-status').forEach(btn => {
            if (btn.getAttribute('data-filter') === defaultFilter) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        renderModalStudents();

        const modal = document.getElementById('modalClassAttendance');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeAttendanceModal = function() {
        const modal = document.getElementById('modalClassAttendance');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    window.filterModalStudents = function(status, btn) {
        currentStatusFilter = status;
        document.querySelectorAll('#attFilterTabs .btn-filter-status').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        renderModalStudents();
    };

    window.searchModalStudents = function(query) {
        currentSearchQuery = query.toLowerCase().trim();
        renderModalStudents();
    };

    function renderModalStudents() {
        if (currentClassIndex === null) return;
        const classData = classAttendancesData[currentClassIndex];
        if (!classData || !classData.students) return;

        const tbody = document.getElementById('attModalTableBody');
        const emptyMsg = document.getElementById('attEmptyMessage');
        if (!tbody || !emptyMsg) return;

        tbody.innerHTML = '';

        const filtered = classData.students.filter(std => {
            const matchesStatus = (currentStatusFilter === 'all' || std.status === currentStatusFilter);
            const matchesSearch = !currentSearchQuery || 
                std.name.toLowerCase().includes(currentSearchQuery) || 
                (std.nis && std.nis.toLowerCase().includes(currentSearchQuery));
            return matchesStatus && matchesSearch;
        });

        if (filtered.length === 0) {
            emptyMsg.style.display = 'block';
            return;
        }

        emptyMsg.style.display = 'none';

        filtered.forEach((std, idx) => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';

            let badgeStyle = 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;';
            if (std.status === 'Sakit') badgeStyle = 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;';
            else if (std.status === 'Izin') badgeStyle = 'background: #ecfeff; color: #0e7490; border: 1px solid #a5f3fc;';
            else if (std.status === 'Alpa') badgeStyle = 'background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;';

            tr.innerHTML = `
                <td style="padding: 0.65rem 0.5rem; color: #94a3b8;">${idx + 1}</td>
                <td style="padding: 0.65rem 0.5rem; font-family: monospace; font-size: 0.8rem; color: #64748b;">${std.nis}</td>
                <td style="padding: 0.65rem 0.5rem;"><strong>${std.name}</strong></td>
                <td style="padding: 0.65rem 0.5rem; text-align: center;">
                    <span class="badge" style="font-weight: 700; font-size: 0.78rem; padding: 0.2rem 0.6rem; border-radius: 9999px; ${badgeStyle}">
                        ${std.status}
                    </span>
                </td>
                <td style="padding: 0.65rem 0.5rem; color: #64748b; font-size: 0.82rem;">${std.note ? std.note : '-'}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Tutup modal jika klik di luar box
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modalClassAttendance');
        if (e.target === modal) {
            closeAttendanceModal();
        }
    });

    // Tutup modal dengan tombol Escape
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAttendanceModal();
        }
    });
</script>
@endpush