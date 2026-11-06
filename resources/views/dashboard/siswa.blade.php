@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ auth()->user()->role === 'ortu' ? 'Dashboard Monitoring Orang Tua' : 'Dashboard Siswa' }}</h1>
        <p class="page-subtitle">
            @if(auth()->user()->role === 'ortu')
                Halo, <strong>{{ auth()->user()->name }}</strong> (Memantau Siswa: <strong>{{ $student->name ?? '-' }}</strong>) &bull; {{ $todayName }}, {{ $today->format('d M Y') }}
            @else
                Halo, <strong>{{ $student->name ?? auth()->user()->name }}</strong> &bull; {{ $todayName }}, {{ $today->format('d M Y') }}
            @endif
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('permission-requests.create') }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
            <span>📝</span> {{ auth()->user()->role === 'ortu' ? 'Ajukan Surat Izin Anak' : 'Ajukan Surat Izin' }}
        </a>
    </div>
</div>

{{-- 4 Stat Cards --}}
<div class="grid-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $attendanceStats['percent'] }}%</div>
            <div class="stat-label">Kehadiran ({{ $attendanceStats['hadir'] }} Hadir)</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $attendanceStats['sakit'] }}</div>
            <div class="stat-label">Sakit Tercatat</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $attendanceStats['izin'] }}</div>
            <div class="stat-label">Izin Tercatat</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value" style="{{ $attendanceStats['alpa'] > 0 ? 'color: #dc2626;' : '' }}">
                {{ $attendanceStats['alpa'] }}
            </div>
            <div class="stat-label">Tanpa Keterangan (Alpa)</div>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    {{-- Profil Akademik Siswa --}}
    <div class="card">
        <h2 class="card-title">
            <span>Profil Siswa</span>
            @if($student?->schoolClass)
                <span class="badge badge-info">{{ $student->schoolClass->name }}</span>
            @endif
        </h2>

        <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.875rem;">
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-secondary);">Nama Lengkap:</span>
                <strong>{{ $student->name ?? auth()->user()->name }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-secondary);">Nomor Induk (NIS):</span>
                <strong>{{ $student->nis ?? '-' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-secondary);">Kelas:</span>
                <strong>{{ $student->schoolClass->name ?? '-' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-secondary);">Orang Tua / Wali:</span>
                <strong>{{ $student->parent->name ?? '-' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-secondary);">Siklus Aktif:</span>
                <span class="badge badge-success">{{ $activeCycle->cycle ?? 'Tidak Ada' }}</span>
            </div>
        </div>
    </div>

    {{-- Layanan & Tindakan Cepat (Tombol Buat Izin Dihapus) --}}
    <div class="card">
        <h2 class="card-title">Layanan & Bantuan Siswa</h2>
        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 1rem;">
            Jika Anda berhalangan hadir dikarenakan sakit atau urusan penting keluarga, silakan tinjau riwayat izin atau catatan presensi Anda.
        </p>

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('permission-requests.index') }}" class="btn btn-secondary btn-sm" style="flex: 1; padding: 0.6rem;">
                    Riwayat Pengajuan Izin
                </a>
                <a href="{{ route('attendances.index') }}" class="btn btn-secondary btn-sm" style="flex: 1; padding: 0.6rem;">
                    Detail Catatan Presensi
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Jadwal Pelajaran Kelas Hari Ini --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <h2 class="card-title">
        <span>Jadwal Pelajaran Kelas Hari Ini ({{ $todayName }})</span>
        <span class="badge badge-info">{{ $todaySchedules->count() }} Mata Pelajaran</span>
    </h2>

    @if($todaySchedules->count())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Jam Pelajaran</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengajar</th>
                        <th>Ruangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todaySchedules as $sch)
                        <tr>
                            <td>
                                <strong>{{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}</strong>
                            </td>
                            <td><strong>{{ $sch->subject->name ?? '-' }}</strong></td>
                            <td>{{ $sch->teacher->name ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ $sch->room->name ?? '-' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="padding: 1.25rem; text-align: center; color: var(--text-secondary); background-color: var(--bg-surface-hover); border-radius: var(--radius-sm);">
            <p style="font-size: 0.95rem; margin-bottom: 0.25rem;"><strong>Tidak ada jadwal kegiatan belajar untuk kelas Anda hari ini.</strong></p>
            <p style="font-size: 0.8rem; color: var(--text-muted);">Selamat beristirahat atau belajar mandiri!</p>
        </div>
    @endif
</div>

{{-- Riwayat Pengajuan Izin Terakhir --}}
<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
        <h2 class="card-title" style="margin-bottom: 0;">
            <span>Riwayat Pengajuan Izin Saya</span>
            @if($recentPermissions->count() > 0)
                <span class="badge badge-info">{{ $recentPermissions->count() }} Data Terbaru</span>
            @endif
        </h2>
        <a href="{{ route('permission-requests.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
    </div>

    @if($recentPermissions->count())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Rentang Tanggal</th>
                        <th>Durasi</th>
                        <th>Jenis</th>
                        <th>Alasan</th>
                        <th>Status Verifikasi</th>
                        <th>Catatan Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPermissions as $perm)
                        <tr>
                            <td>
                                @if($perm->end_date && $perm->end_date->ne($perm->permission_date))
                                    <span>{{ $perm->permission_date->format('d/m/Y') }} &ndash; {{ $perm->end_date->format('d/m/Y') }}</span>
                                @else
                                    <span>{{ $perm->permission_date ? $perm->permission_date->format('d/m/Y') : '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-weight: 600;">
                                    {{ $perm->duration_days }} Hari
                                </span>
                            </td>
                            <td>
                                @if($perm->type === 'Sakit')
                                    <span class="badge badge-warning">Sakit</span>
                                @else
                                    <span class="badge badge-info">Izin</span>
                                @endif
                            </td>
                            <td style="max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $perm->reason }}
                            </td>
                            <td>
                                @if($perm->status === 'Approved')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($perm->status === 'Rejected')
                                    <span class="badge badge-danger">Ditolak</span>
                                @else
                                    <span class="badge badge-warning">Menunggu</span>
                                @endif
                            </td>
                            <td style="color: var(--text-secondary); font-size: 0.85rem;">
                                {{ $perm->verification_note ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 0.875rem;">
            Anda belum pernah membuat permohonan izin.
        </p>
    @endif
</div>
@endsection