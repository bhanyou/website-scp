@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Rekap Presensi</h1>
        <p class="page-subtitle">Informasi riwayat kehadiran siswa per mata pelajaran</p>
    </div>

    <div>
        <a href="{{ route('home') }}" class="btn-back">
            ← Kembali ke Dashboard
        </a>
    </div>
</div>

{{-- Informasi Siswa --}}
@isset($student)
    <div class="card">
        <h2 class="card-title">Profil Siswa</h2>
        <div class="grid-2">
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Nama Lengkap</span>
                <div style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-top: 0.2rem;">
                    {{ $student->name }}
                </div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">NIS & Kelas</span>
                <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-secondary); margin-top: 0.2rem;">
                    NIS: {{ $student->nis ?? '-' }} • <span class="badge badge-info">{{ $student->schoolClass?->name ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
@endisset

{{-- Informasi Orang Tua jika ada --}}
@isset($students)
    <div class="card">
        <h2 class="card-title">Data Anak Terhubung</h2>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
            @forelse ($students as $studentItem)
                <div style="background: var(--bg-surface-hover); border: 1px solid var(--border-color); padding: 0.5rem 0.85rem; border-radius: var(--radius-sm); font-size: 0.85rem;">
                    <strong>{{ $studentItem->name }}</strong>
                    <span style="color: var(--text-muted);"> (NIS: {{ $studentItem->nis ?? '-' }} • {{ $studentItem->schoolClass?->name ?? '-' }})</span>
                </div>
            @empty
                <p style="color: var(--text-muted);">Belum ada data anak yang terhubung.</p>
            @endforelse
        </div>
    </div>
@endisset

{{-- Tabel Data Kehadiran --}}
<div class="card">
    <div class="card-title">
        <span>Catatan Presensi</span>
        <span class="badge badge-info">{{ $attendances->count() }} Pertemuan</span>
    </div>

    @if ($attendances->count())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        @isset($students)
                            <th>Siswa</th>
                        @endisset
                        <th>Tanggal</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Jam</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            @isset($students)
                                <td><strong>{{ $attendance->student->name }}</strong></td>
                            @endisset

                            <td>{{ $attendance->date ? $attendance->date->format('d-m-Y') : '-' }}</td>

                            <td>{{ $attendance->schedule?->subject?->name ?? '-' }}</td>

                            <td>
                                <span class="badge badge-info">
                                    {{ $attendance->schedule?->schoolClass?->name ?? '-' }}
                                </span>
                            </td>

                            <td>
                                @if ($attendance->schedule)
                                    {{ substr($attendance->schedule->start_time, 0, 5) }} - {{ substr($attendance->schedule->end_time, 0, 5) }}
                                @else
                                    -
                                @endif
                            </td>

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

                            <td>{{ $attendance->check_in ?? '-' }}</td>

                            <td>{{ $attendance->note ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
            <div style="margin-bottom: 0.5rem; display: flex; justify-content: center;">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--text-muted); opacity: 0.6;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 style="font-size: 1rem; color: var(--text-primary); margin-bottom: 0.25rem;">Belum Ada Catatan Kehadiran</h3>
            <p style="font-size: 0.85rem;">Data presensi belum tersedia untuk periode ini.</p>
        </div>
    @endif

    <div style="margin-top: 1rem;">
        <a href="{{ route('attendances.index') }}" class="btn-back">
            Kembali ke Rekap Presensi
        </a>
    </div>
</div>
@endsection