@extends('layouts.app')

@section('content')
<style>
    /* ==========================================================================
       MODERN COMPACT DATA TABLE WITH VISUAL TAGS (CLEAN ENTERPRISE TABLE)
       Tampilan Jadwal Pelajaran Elegan, Rapi, & Responsif
       ========================================================================== */
    .enterprise-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .enterprise-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.35rem 0;
        letter-spacing: -0.02em;
    }
    .enterprise-subtitle {
        font-size: 0.88rem;
        color: #64748b;
        margin: 0;
    }

    /* Kartu Info Metadata Akademik */
    .meta-summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 5px solid #2563eb;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
    }
    .meta-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        align-items: center;
    }
    .meta-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 0.25rem;
    }
    .meta-value {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }
    .meta-sub {
        font-size: 0.82rem;
        color: #64748b;
        margin-top: 2px;
    }

    /* Tab Filter Hari Modern */
    .tab-nav-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        scrollbar-width: thin;
    }
    .day-tab-btn {
        white-space: nowrap;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.5rem 1.15rem;
        border-radius: 0.625rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.15s ease;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-family: inherit;
        text-decoration: none;
    }
    .day-tab-btn:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #1e293b;
    }
    .day-tab-btn.is-active {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
        font-weight: 700;
    }
    .day-tab-btn.is-active .day-tab-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .day-tab-count {
        background: #f1f5f9;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 9999px;
        line-height: 1.2;
    }
    .day-tab-today-tag {
        background: #ef4444;
        color: #ffffff;
        font-size: 0.62rem;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        line-height: 1.2;
    }

    /* Blok Header Hari */
    .day-block-section {
        margin-bottom: 2rem;
    }
    .day-block-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.85rem;
        padding: 0 0.25rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .day-badge-title {
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #1e293b;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        padding: 0.4rem 0.85rem;
        border-radius: 0.5rem;
        display: inline-block;
    }
    .day-today-indicator {
        background: #ef4444;
        color: #ffffff;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.25rem 0.55rem;
        border-radius: 0.375rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-block;
    }
    .day-count-label {
        font-size: 0.82rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Container Card Tabel Enterprise (rounded-2xl, border border-gray-200/80, overflow-hidden shadow-sm) */
    .enterprise-table-card {
        background: #ffffff;
        border-radius: 1rem; /* rounded-2xl */
        border: 1px solid rgba(229, 231, 235, 0.8); /* border border-gray-200/80 */
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04); /* shadow-sm */
        overflow: hidden;
    }
    .enterprise-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .enterprise-table th {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.875rem 1.5rem; /* py-3.5 px-6 */
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }
    .enterprise-table td {
        padding: 0.875rem 1.5rem; /* py-3.5 px-6 */
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .enterprise-table tbody tr {
        transition: background-color 0.15s ease;
    }
    .enterprise-table tbody tr:hover {
        background-color: rgba(248, 250, 252, 0.7); /* hover:bg-slate-50/70 */
    }
    .enterprise-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Kolom 1: Waktu & Jam */
    .time-range-text {
        font-size: 0.95rem;
        font-weight: 800;
        color: #111827; /* text-gray-900 */
        line-height: 1.25;
        letter-spacing: -0.01em;
        white-space: nowrap;
    }
    .period-subtext {
        font-size: 0.75rem; /* text-xs */
        color: #9ca3af;     /* text-gray-400 */
        font-weight: 600;
        margin-top: 2px;
        white-space: nowrap;
    }

    /* Kolom 2: Mata Pelajaran & Visual Tags */
    .subject-title-wrap {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        flex-wrap: wrap;
    }
    .subject-title-text {
        font-weight: 800;
        color: #111827; /* font-bold text-gray-900 */
        font-size: 0.92rem;
        line-height: 1.35;
    }

    /* Badge Visual Tipe Mapel */
    .badge-type-lab {
        background-color: #dbeafe; /* bg-blue-100 */
        color: #1d4ed8;            /* text-blue-700 */
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }
    .badge-type-teori {
        background-color: #ecfdf5; /* bg-emerald-50 */
        color: #047857;            /* text-emerald-700 */
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }
    .badge-type-sport {
        background-color: #fffbeb; /* bg-amber-50 */
        color: #b45309;            /* text-amber-700 */
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }
    .badge-type-special {
        background-color: #f3e8ff; /* bg-purple-100 */
        color: #7e22ce;            /* text-purple-700 */
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }
    .badge-code-pill {
        font-size: 0.7rem;
        font-weight: 600;
        background: #f1f5f9;
        color: #64748b;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .badge-class-pill {
        font-size: 0.7rem;
        font-weight: 700;
        background: #e0e7ff;
        color: #4338ca;
        padding: 0.15rem 0.55rem;
        border-radius: 4px;
        white-space: nowrap;
    }

    /* Kolom 3: Guru Pengampu */
    .teacher-name-text {
        font-weight: 600;
        color: #1f2937; /* font-medium text-gray-800 */
        font-size: 0.88rem;
        line-height: 1.3;
    }
    .teacher-nip-subtext {
        font-size: 0.75rem; /* text-xs */
        color: #9ca3af;     /* text-gray-400 */
        margin-top: 2px;
    }

    /* Kolom 4: Ruangan (Rata Kanan) */
    .room-badge-wrap {
        text-align: right;
        white-space: nowrap;
    }
    .badge-room-lab {
        background-color: #2563eb; /* bg-blue-600 */
        color: #ffffff;            /* text-white */
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
        font-weight: 800;          /* font-bold */
        font-size: 0.85rem;
        padding: 0.35rem 0.85rem;
        border-radius: 0.5rem;
        display: inline-block;
        letter-spacing: 0.02em;
    }
    .badge-room-teori {
        background-color: #f3f4f6; /* bg-gray-100 */
        color: #374151;            /* text-gray-700 */
        border: 1px solid #e5e7eb; /* border border-gray-200 */
        font-weight: 800;          /* font-bold */
        font-size: 0.85rem;
        padding: 0.35rem 0.85rem;
        border-radius: 0.5rem;
        display: inline-block;
        letter-spacing: 0.02em;
    }
    .badge-room-sport {
        background-color: #ea580c; /* bg-orange-600 */
        color: #ffffff;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        font-weight: 800;
        font-size: 0.85rem;
        padding: 0.35rem 0.85rem;
        border-radius: 0.5rem;
        display: inline-block;
        letter-spacing: 0.02em;
    }
    .badge-room-special {
        background-color: #7e22ce; /* bg-purple-700 */
        color: #ffffff;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        font-weight: 800;
        font-size: 0.85rem;
        padding: 0.35rem 0.85rem;
        border-radius: 0.5rem;
        display: inline-block;
        letter-spacing: 0.02em;
    }

    /* Baris Kosong Rapi */
    .empty-schedule-row {
        text-align: center;
        padding: 2.25rem 1.5rem !important;
        color: #94a3b8;
        font-size: 0.88rem;
        font-style: italic;
    }

    /* Pembungkus overflow-x-auto agar responsif */
    .table-responsive-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>

<div class="enterprise-header">
    <div>
        <h1 class="enterprise-title">Jadwal Pelajaran</h1>
        <p class="enterprise-subtitle">
            @if(isset($student) && $student)
                Jadwal resmi pembelajaran mingguan untuk kelas <strong>{{ $schoolClass?->name ?? '-' }}</strong>
            @elseif(isset($teacher) && $teacher)
                Jadwal resmi mengajar mingguan untuk <strong>{{ $teacher->name }}</strong>
            @else
                Jadwal resmi pembelajaran mingguan kelas <strong>{{ $schoolClass?->name ?? '-' }}</strong>
            @endif
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        @if(isset($allClasses) && $allClasses->isNotEmpty() && (auth()->user()->role === 'waka' || auth()->user()->role === 'guru'))
            <form action="{{ route('student.schedule') }}" method="GET" style="margin: 0; display: inline-flex; align-items: center; gap: 0.35rem;">
                <select name="class_id" class="form-control" onchange="this.form.submit()" style="font-size: 0.85rem; padding: 0.45rem 0.75rem; border-radius: 8px;">
                    @foreach($allClasses as $c)
                        <option value="{{ $c->id }}" {{ $schoolClass?->id == $c->id ? 'selected' : '' }}>
                            Kelas {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
        <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()" style="font-weight: 600;">
            Cetak Jadwal
        </button>
    </div>
</div>

{{-- INFORMASI KELAS, PENGAJAR & SIKLUS BLOK AKTIF --}}
<div class="meta-summary-card">
    <div class="meta-summary-grid">
        @if(isset($student) && $student)
            <div>
                <div class="meta-label">Nama Siswa & Kelas</div>
                <div class="meta-value">{{ $student->name }}</div>
                <div class="meta-sub">
                    Kelas: <strong>{{ $schoolClass?->name ?? 'Belum ada kelas' }}</strong>
                    @if($student->nis)
                        &bull; NIS: {{ $student->nis }}
                    @endif
                </div>
            </div>

            <div>
                <div class="meta-label">Wali Kelas</div>
                <div class="meta-value">
                    {{ $schoolClass?->homeroomTeacher?->name ?? 'Belum ditentukan' }}
                </div>
                @if($schoolClass?->homeroomTeacher?->nip)
                    <div class="meta-sub">
                        NIP: {{ $schoolClass->homeroomTeacher->nip }}
                    </div>
                @endif
            </div>
        @else
            <div>
                <div class="meta-label">Tenaga Pengajar</div>
                <div class="meta-value">{{ $teacher?->name ?? auth()->user()->name }}</div>
                <div class="meta-sub">
                    NIP: {{ $teacher?->nip ?? '-' }}
                </div>
            </div>

            <div>
                <div class="meta-label">Kelas / Rombel</div>
                <div class="meta-value">
                    {{ $schoolClass?->name ?? 'Semua Kelas Binaan' }}
                </div>
                <div class="meta-sub">
                    Jurusan: {{ $schoolClass?->major?->name ?? 'Reguler' }}
                </div>
            </div>
        @endif

        <div>
            <div class="meta-label">Putaran Blok & Status</div>
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                @if($activeCycle)
                    <span style="font-size: 1rem; font-weight: 800; color: #2563eb;">
                        Putaran {{ $activeCycle->cycle }}
                    </span>
                    @if($activeCycle->is_locked)
                        <span class="badge badge-success" style="font-size: 0.74rem; font-weight: 700; padding: 0.25rem 0.55rem;">
                            Dikunci & Disahkan Waka
                        </span>
                    @else
                        <span class="badge badge-warning" style="font-size: 0.74rem; font-weight: 700; padding: 0.25rem 0.55rem;">
                            Draft Waka
                        </span>
                    @endif
                @else
                    <span style="color: #64748b; font-size: 0.85rem;">Belum ada putaran blok aktif</span>
                @endif
            </div>
            <div class="meta-sub" style="margin-top: 4px;">
                Hari ini: <strong>{{ $todayName }}</strong> ({{ now()->translatedFormat('d F Y') }})
            </div>
        </div>
    </div>
</div>

{{-- 1. TAB FILTER HARI (SENIN S/D JUMAT) --}}
@php
    $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    // Otomatis memilih hari aktif saat ini jika hari kerja (Senin s/d Jumat), selain itu default Senin
    $initialTab = in_array($todayName, $daysList) ? $todayName : 'Senin';
@endphp

<div class="tab-nav-wrapper">
    @foreach($daysList as $day)
        @php
            $count = $schedulesByDay[$day]->count();
            $isToday = ($day === $todayName);
            $isActive = ($day === $initialTab);
        @endphp
        <button
            type="button"
            class="day-tab-btn {{ $isActive ? 'is-active' : '' }}"
            id="tab-btn-{{ strtolower($day) }}"
            onclick="switchEnterpriseScheduleDay('{{ strtolower($day) }}')"
        >
            <span>{{ $day }}</span>
            @if($isToday)
                <span class="day-tab-today-tag">Hari Ini</span>
            @endif
            <span class="day-tab-count">{{ $count }}</span>
        </button>
    @endforeach

    <button
        type="button"
        class="day-tab-btn"
        id="tab-btn-all"
        onclick="switchEnterpriseScheduleDay('all')"
    >
        <span>Tampilkan Semua (Senin - Jumat)</span>
    </button>
</div>

{{-- 2. KONTEN TABEL DATA MODERN PER BLOK HARI (SENIN S.D. JUMAT) --}}
@foreach($daysList as $day)
    @php
        $daySchedules = $schedulesByDay[$day];
        $isToday = ($day === $todayName);
    @endphp

    <div
        class="day-block-section"
        id="pane-{{ strtolower($day) }}"
        style="{{ $day === $initialTab ? 'display: block;' : 'display: none;' }}"
    >
        {{-- Header Blok Hari --}}
        <div class="day-block-header">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <span class="day-badge-title">HARI {{ strtoupper($day) }}</span>
                @if($isToday)
                    <span class="day-today-indicator">HARI INI</span>
                @endif
            </div>
            <div class="day-count-label">
                Total: <strong>{{ $daySchedules->count() }}</strong> Mata Pelajaran Terjadwal
            </div>
        </div>

        {{-- Kontainer Tabel Data Modern (Enterprise Table) --}}
        <div class="enterprise-table-card">
            <div class="table-responsive-wrapper">
                <table class="enterprise-table">
                    <thead>
                        <tr>
                            <th style="width: 200px;">WAKTU & JAM</th>
                            <th>MATA PELAJARAN</th>
                            <th style="width: 280px;">GURU PENGAMPU</th>
                            <th style="text-align: right; width: 180px;">RUANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daySchedules as $sched)
                            @php
                                $roomName = trim($sched->room?->name ?? '');
                                $subjName = strtolower($sched->subject?->name ?? '');

                                // Klasifikasi Tipe Ruangan & Visual Tags
                                $isLab = preg_match('/^L\./i', $roomName) && strcasecmp($roomName, 'L.IPA') !== 0;
                                $isIpa = (strcasecmp($roomName, 'L.IPA') === 0);
                                $isSport = preg_match('/^(OR|HALL)/i', $roomName) || str_contains($subjName, 'pjok') || str_contains($subjName, 'olahraga') || str_contains($subjName, 'penjas');

                                // Kolom 2: Badge Tipe Mapel
                                if ($isLab || str_contains($subjName, 'praktik') || str_contains($subjName, 'lab')) {
                                    $tipeLabel = 'Lab Komputer / Praktik';
                                    $tipeBadgeClass = 'badge-type-lab';
                                } elseif ($isSport) {
                                    $tipeLabel = 'Olahraga';
                                    $tipeBadgeClass = 'badge-type-sport';
                                } elseif ($isIpa) {
                                    $tipeLabel = 'Praktik IPA';
                                    $tipeBadgeClass = 'badge-type-special';
                                } else {
                                    $tipeLabel = 'Teori';
                                    $tipeBadgeClass = 'badge-type-teori';
                                }

                                // Kolom 4: Badge Ruangan (Rata Kanan)
                                if ($isLab) {
                                    $roomBadgeClass = 'badge-room-lab';
                                } elseif ($isSport) {
                                    $roomBadgeClass = 'badge-room-sport';
                                } elseif ($isIpa) {
                                    $roomBadgeClass = 'badge-room-special';
                                } else {
                                    $roomBadgeClass = 'badge-room-teori';
                                }

                                // Waktu & Jam Pelajaran
                                $startClean = substr((string)$sched->start_time, 0, 5);
                                $endClean = substr((string)$sched->end_time, 0, 5);

                                $startPeriod = $sched->start_period;
                                $endPeriod = $sched->end_period;
                                if ($startPeriod && $endPeriod) {
                                    $periodLabel = ($startPeriod == $endPeriod) ? "Jam ke-{$startPeriod}" : "Jam ke {$startPeriod} - {$endPeriod}";
                                } elseif ($startPeriod) {
                                    $periodLabel = "Jam ke-{$startPeriod}";
                                } else {
                                    $periodLabel = "Sesi Terjadwal";
                                }

                                $teacherName = $sched->teacher?->name ?? 'Guru Pengajar';
                                $teacherNip = $sched->teacher?->nip;
                            @endphp

                            <tr>
                                {{-- Kolom 1 (WAKTU & JAM) --}}
                                <td>
                                    <div class="time-range-text">{{ $startClean }} - {{ $endClean }}</div>
                                    <div class="period-subtext">{{ $periodLabel }}</div>
                                </td>

                                {{-- Kolom 2 (MATA PELAJARAN) --}}
                                <td>
                                    <div class="subject-title-wrap">
                                        <span class="subject-title-text">
                                            {{ $sched->subject?->name ?? 'Mata Pelajaran Tidak Terdaftar' }}
                                        </span>
                                        <span class="{{ $tipeBadgeClass }}">
                                            {{ $tipeLabel }}
                                        </span>
                                        @if($sched->subject?->code)
                                            <span class="badge-code-pill">
                                                {{ $sched->subject->code }}
                                            </span>
                                        @endif
                                        @if($sched->schoolClass && (auth()->user()->role === 'guru' || auth()->user()->role === 'waka'))
                                            <span class="badge-class-pill">
                                                Kelas {{ $sched->schoolClass->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Kolom 3 (GURU PENGAMPU) --}}
                                <td>
                                    <div class="teacher-name-text">{{ $teacherName }}</div>
                                    <div class="teacher-nip-subtext">
                                        {{ $teacherNip ? 'NIP: ' . $teacherNip : 'NIP: -' }}
                                    </div>
                                </td>

                                {{-- Kolom 4 (RUANGAN - Rata Kanan) --}}
                                <td style="text-align: right;">
                                    <div class="room-badge-wrap">
                                        <span class="{{ $roomBadgeClass }}">
                                            {{ $roomName ?: 'Tanpa Ruang' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-schedule-row">
                                    Tidak ada jadwal KBM pada hari ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endforeach

<script>
    function switchEnterpriseScheduleDay(targetDay) {
        const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        const allButtons = document.querySelectorAll('.day-tab-btn');
        allButtons.forEach(btn => {
            btn.classList.remove('is-active');
        });

        if (targetDay === 'all') {
            const allBtn = document.getElementById('tab-btn-all');
            if (allBtn) allBtn.classList.add('is-active');
            days.forEach(d => {
                const pane = document.getElementById('pane-' + d);
                if (pane) pane.style.display = 'block';
            });
        } else {
            const activeBtn = document.getElementById('tab-btn-' + targetDay);
            if (activeBtn) activeBtn.classList.add('is-active');
            days.forEach(d => {
                const pane = document.getElementById('pane-' + d);
                if (pane) {
                    pane.style.display = (d === targetDay) ? 'block' : 'none';
                }
            });
        }
    }
</script>
@endsection
