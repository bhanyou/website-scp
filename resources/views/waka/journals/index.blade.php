@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Monitoring Jurnal Mengajar - {{ $todayDateFormatted }}</h1>
        <p class="page-subtitle">Pantau aktivitas mengajar guru dan status pengisian jurnal harian secara real-time</p>
    </div>
    <div>
        <a href="{{ route('dashboard.waka') }}" class="btn-back">
            ← Kembali ke Dashboard
        </a>
    </div>
</div>

{{-- Kartu Ringkasan Statistik Hari Ini --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Total Jadwal Hari Ini</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--text-primary); line-height: 1.2;">{{ $totalSchedulesToday }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Sudah Mengisi</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #059669; line-height: 1.2;">{{ $totalFilledToday }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Belum Mengisi</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #dc2626; line-height: 1.2;">{{ $totalPendingToday }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Tingkat Keterisian</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #7c3aed; line-height: 1.2;">{{ $completionPercentage }}%</div>
        </div>
    </div>
</div>

{{-- Panel Filter & Pencarian --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('waka.journals.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        
        {{-- Pencarian Cepat --}}
        <div style="flex: 1.5; min-width: 200px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Cari Jadwal / Guru
            </label>
            <input type="text" name="search" class="form-control" placeholder="Nama guru, mapel, kelas, atau ruang..." value="{{ request('search') }}" style="width: 100%;">
        </div>

        {{-- Filter Guru Pengajar --}}
        <div style="flex: 1.2; min-width: 170px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Guru Pengajar
            </label>
            <select name="teacher_id" class="form-control" style="width: 100%;">
                <option value="">Semua Guru</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filter Status Pengisian --}}
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Status Jurnal
            </label>
            <select name="status" class="form-control" style="width: 100%;">
                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="sudah" {{ request('status') === 'sudah' ? 'selected' : '' }}>Sudah Mengisi</option>
                <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Belum Mengisi</option>
            </select>
        </div>

        {{-- Filter Tingkat Kelas --}}
        <div style="flex: 0.9; min-width: 130px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Tingkat Kelas
            </label>
            <select name="grade" class="form-control" style="width: 100%;">
                <option value="">Semua Tingkat</option>
                <option value="X" {{ strtoupper(request('grade')) === 'X' || request('grade') === '10' ? 'selected' : '' }}>Kelas X</option>
                <option value="XI" {{ strtoupper(request('grade')) === 'XI' || request('grade') === '11' ? 'selected' : '' }}>Kelas XI</option>
                <option value="XII" {{ strtoupper(request('grade')) === 'XII' || request('grade') === '12' ? 'selected' : '' }}>Kelas XII</option>
            </select>
        </div>

        {{-- Filter Jurusan --}}
        <div style="flex: 1; min-width: 140px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Jurusan
            </label>
            <select name="major" class="form-control" style="width: 100%;">
                <option value="">Semua Jurusan</option>
                @foreach($majors as $m)
                    <option value="{{ $m }}" {{ request('major') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tombol Filter & Reset --}}
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.25rem;">
                Filter
            </button>
            @if(request()->filled('search') || request()->filled('teacher_id') || request()->filled('status') && request('status') !== 'all' || request()->filled('grade') || request()->filled('major'))
                <a href="{{ route('waka.journals.index') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Tabel Monitoring Jurnal Hari Ini --}}
<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <span style="font-weight: 700; font-size: 1.1rem; color: var(--text-primary);">
            Daftar Jadwal Mengajar Hari Ini ({{ $todayDayName }})
        </span>
        <span class="badge badge-info">{{ $schedules->count() }} Jadwal Terjadwal</span>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Jam / Sesi</th>
                    <th>Guru Pengajar</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th>Ruangan</th>
                    <th style="text-align: center;">Status Jurnal</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                    @php
                        $filledJournal = $schedule->journals->first();
                    @endphp
                    <tr>
                        <td style="text-align: center; color: var(--text-muted);">{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $schedule->session_label }}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">
                                {{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }} WIB
                            </div>
                        </td>
                        <td>
                            <strong>{{ $schedule->teacher?->name ?? '-' }}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">
                                {{ $schedule->teacher?->nip ? 'NIP: ' . $schedule->teacher->nip : '-' }}
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--accent);">{{ $schedule->subject?->name ?? '-' }}</strong>
                            @if($schedule->subject?->code)
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">
                                    Kode: {{ $schedule->subject->code }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="background-color: var(--bg-surface-hover); color: var(--text-primary); font-weight: 600;">
                                {{ $schedule->schoolClass?->name ?? '-' }}
                            </span>
                            @if($schedule->schoolClass?->major)
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.2rem;">
                                    {{ $schedule->schoolClass->major }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight: 500;">{{ $schedule->room?->name ?? '-' }}</span>
                        </td>
                        <td style="text-align: center;">
                            @if($filledJournal)
                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Sudah Mengisi
                                </span>
                            @else
                                <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Belum Mengisi
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            @if($filledJournal)
                                <a href="{{ route('waka.journals.show', $filledJournal->id) }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                    Detail Jurnal →
                                </a>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                            Tidak ada jadwal mengajar pada hari {{ $todayDayName }} yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        <a href="{{ route('dashboard.waka') }}" class="btn-back">
            ← Kembali ke Dashboard Waka Kurikulum
        </a>
    </div>
</div>
@endsection
