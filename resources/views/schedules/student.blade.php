@extends('layouts.app')

@section('content')
<style>
    /* ==========================================================================
       ACCORDION COLLAPSE PER-HARI (MINIMALIS & INTERAKTIF)
       Clean Enterprise Accordion dengan Tailwind styling & transisi halus
       ========================================================================== */
    .schedule-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .schedule-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.35rem 0;
        letter-spacing: -0.02em;
    }
    .schedule-subtitle {
        font-size: 0.88rem;
        color: #64748b;
        margin: 0;
    }

    /* Kartu Info Metadata Akademik */
    .meta-summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 5px solid #4f46e5;
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

    /* Kontrol Toolbar Accordion */
    .accordion-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0 0.25rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .accordion-toolbar-info {
        font-size: 0.84rem;
        color: #64748b;
        font-weight: 500;
    }
    .accordion-toggle-btns {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .btn-toggle-accordion {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-toggle-accordion:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    /* Container Accordion per Hari */
    .day-accordion-card {
        background: #ffffff; /* bg-white */
        border: 1px solid #e5e7eb; /* border border-gray-200 */
        border-radius: 1rem; /* rounded-2xl */
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
        overflow: hidden; /* overflow-hidden */
        margin-bottom: 0.85rem; /* mb-3 */
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .day-accordion-card[open] {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    }

    /* Header Accordion (<summary>) */
    .day-accordion-summary {
        list-style: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.1rem 1.4rem;
        cursor: pointer;
        user-select: none;
        transition: background-color 0.15s ease;
        background: #ffffff;
    }
    .day-accordion-summary::-webkit-details-marker {
        display: none;
    }
    .day-accordion-summary:hover {
        background-color: rgba(249, 250, 251, 0.85); /* hover:bg-gray-50/80 */
    }
    .day-accordion-card[open] > .day-accordion-summary {
        border-bottom: 1px solid #f1f5f9;
        background-color: #fafafa;
    }

    /* Header Sisi Kiri */
    .summary-left {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .day-abbr-badge {
        width: 44px;
        height: 44px;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    .day-abbr-today {
        background: #4f46e5; /* bg-indigo-600 */
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);
    }
    .day-abbr-regular {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .summary-day-title {
        font-size: 0.95rem; /* text-sm / 0.95rem */
        font-weight: 800;  /* font-bold */
        color: #111827;    /* text-gray-900 */
        line-height: 1.25;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .summary-day-subtitle {
        font-size: 0.75rem; /* text-xs */
        color: #9ca3af;     /* text-gray-400 */
        margin-top: 3px;
        font-weight: 500;
    }
    .tag-today-pill {
        background: #ef4444;
        color: #ffffff;
        font-size: 0.62rem;
        font-weight: 800;
        padding: 0.15rem 0.45rem;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Header Sisi Kanan */
    .summary-right {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }
    .operating-hours-text {
        font-size: 0.75rem; /* text-xs */
        color: #6b7280;     /* text-gray-500 */
        font-weight: 600;
        white-space: nowrap;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.35rem 0.75rem;
        border-radius: 0.5rem;
    }
    .accordion-chevron-icon {
        color: #94a3b8;
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), color 0.15s ease;
        flex-shrink: 0;
    }
    .day-accordion-card[open] .accordion-chevron-icon {
        transform: rotate(180deg);
        color: #4f46e5;
    }

    /* Body Accordion (List Baris Horizontal divide-y divide-gray-100) */
    .accordion-body-list {
        background: #ffffff;
    }
    .accordion-schedule-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.1rem 1.4rem;
        border-top: 1px solid #f3f4f6; /* divide-y divide-gray-100 */
        gap: 1.25rem;
        transition: background-color 0.15s ease;
    }
    .accordion-schedule-row:first-child {
        border-top: none;
    }
    .accordion-schedule-row:hover {
        background-color: #fafafa;
    }

    /* Sisi Kiri: Waktu & Sesi */
    .schedule-time-box {
        min-width: 140px;
        flex-shrink: 0;
    }
    .time-range-val {
        font-size: 0.95rem;
        font-weight: 800;
        color: #111827; /* text-gray-900 */
        line-height: 1.2;
        letter-spacing: -0.01em;
        white-space: nowrap;
    }
    .time-period-label {
        font-size: 0.75rem; /* text-xs */
        font-weight: 600;  /* font-semibold */
        color: #374151;    /* text-gray-700 */
        margin-top: 2px;
        white-space: nowrap;
    }

    /* Sisi Tengah: Info Mapel & Pengampu */
    .schedule-info-box {
        flex: 1;
        min-width: 0;
    }
    .subject-header-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 0.35rem;
    }
    .subject-name-strong {
        font-size: 0.92rem;
        font-weight: 800; /* font-bold */
        color: #111827;   /* text-gray-900 */
        line-height: 1.35;
    }

    /* Badge Tipe Mapel (Praktik Lab / Teori / Olahraga) */
    .type-pill-lab {
        background-color: #dbeafe; /* bg-blue-100 */
        color: #1d4ed8;            /* text-blue-700 */
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.15rem 0.55rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .type-pill-teori {
        background-color: #ecfdf5; /* bg-emerald-50 */
        color: #047857;            /* text-emerald-700 */
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.15rem 0.55rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .type-pill-sport {
        background-color: #fffbeb; /* bg-amber-50 */
        color: #b45309;            /* text-amber-700 */
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.15rem 0.55rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .type-pill-special {
        background-color: #f3e8ff; /* bg-purple-100 */
        color: #7e22ce;            /* text-purple-700 */
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.15rem 0.55rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .code-pill-tag {
        font-size: 0.68rem;
        font-weight: 600;
        background: #f1f5f9;
        color: #64748b;
        padding: 0.1rem 0.45rem;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .class-pill-tag {
        font-size: 0.68rem;
        font-weight: 700;
        background: #e0e7ff;
        color: #4338ca;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        white-space: nowrap;
    }

    .teacher-details-text {
        font-size: 0.78rem; /* text-xs */
        color: #6b7280;     /* text-gray-500 */
        line-height: 1.35;
    }
    .teacher-details-text strong {
        color: #374151; /* text-gray-700 */
        font-weight: 600;
    }

    /* Sisi Kanan: Lokasi Ruangan */
    .schedule-room-box {
        text-align: right;
        flex-shrink: 0;
    }
    .badge-room-lab-solid {
        background-color: #2563eb; /* bg-blue-600 */
        color: #ffffff;            /* text-white */
        font-weight: 700;          /* font-bold */
        padding: 0.25rem 0.75rem;  /* px-3 py-1 */
        border-radius: 0.5rem;     /* rounded-lg */
        font-size: 0.75rem;        /* text-xs */
        display: inline-block;
        white-space: nowrap;
        letter-spacing: 0.02em;
    }
    .badge-room-teori-neutral {
        background-color: #f3f4f6; /* bg-gray-100 */
        color: #374151;            /* text-gray-700 */
        font-weight: 700;          /* font-bold */
        padding: 0.25rem 0.75rem;  /* px-3 py-1 */
        border-radius: 0.5rem;     /* rounded-lg */
        font-size: 0.75rem;        /* text-xs */
        border: 1px solid #e5e7eb; /* border border-gray-200 */
        display: inline-block;
        white-space: nowrap;
        letter-spacing: 0.02em;
    }
    .badge-room-sport-solid {
        background-color: #ea580c; /* oranye tegas */
        color: #ffffff;
        font-weight: 700;
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        display: inline-block;
        white-space: nowrap;
        letter-spacing: 0.02em;
    }
    .badge-room-special-solid {
        background-color: #7e22ce; /* ungu tegas */
        color: #ffffff;
        font-weight: 700;
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        display: inline-block;
        white-space: nowrap;
        letter-spacing: 0.02em;
    }

    /* Panel Kosong Tanpa Jadwal */
    .empty-day-panel {
        padding: 2.25rem 1.5rem;
        text-align: center;
        color: #9ca3af; /* text-gray-400 */
        font-size: 0.85rem;
        font-style: italic;
        background: #fafafa;
    }

    /* Responsivitas Layar Smartphone */
    @media (max-width: 680px) {
        .day-accordion-summary {
            padding: 0.95rem 1.15rem;
        }
        .operating-hours-text {
            display: none; /* Sembunyikan di mobile sangat sempit agar tetap lega */
        }
        .accordion-schedule-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.95rem 1.15rem;
        }
        .schedule-time-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            border-bottom: 1px dashed #f1f5f9;
            padding-bottom: 0.4rem;
        }
        .schedule-room-box {
            text-align: left;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px dashed #f1f5f9;
            padding-top: 0.4rem;
        }
    }
</style>

<div class="schedule-header">
    <div>
        <h1 class="schedule-title">Jadwal Pelajaran</h1>
        <p class="schedule-subtitle">
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
                    <span style="font-size: 1rem; font-weight: 800; color: #4f46e5;">
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

{{-- TOOLBAR KONTROL ACCORDION --}}
@php
    $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    $dayAbbrs = [
        'Senin' => 'SEN',
        'Selasa' => 'SEL',
        'Rabu' => 'RAB',
        'Kamis' => 'KAM',
        'Jumat' => 'JUM'
    ];
    $totalWeeklySchedules = $schedules->count();
@endphp

<div class="accordion-toolbar">
    <div class="accordion-toolbar-info">
        Total <strong>{{ $totalWeeklySchedules }}</strong> sesi pembelajaran aktif dalam sepekan
    </div>
    <div class="accordion-toggle-btns">
        <button type="button" class="btn-toggle-accordion" onclick="toggleAllAccordions(true)">
            Buka Semua
        </button>
        <button type="button" class="btn-toggle-accordion" onclick="toggleAllAccordions(false)">
            Tutup Semua
        </button>
    </div>
</div>

{{-- 1. STRUKTUR UTAMA LAYOUT (ACCORDION PER-HARI MENGGUNAKAN <details>) --}}
@foreach($daysList as $day)
    @php
        $daySchedules = $schedulesByDay[$day];
        $isToday = ($day === $todayName);
        $abbr = $dayAbbrs[$day] ?? strtoupper(substr($day, 0, 3));

        // Hitung rentang waktu operasional hari tersebut
        $operatingHours = '-';
        if ($daySchedules->isNotEmpty()) {
            $firstStart = substr((string)$daySchedules->first()->start_time, 0, 5);
            $lastEnd = substr((string)$daySchedules->last()->end_time, 0, 5);
            $operatingHours = "{$firstStart} - {$lastEnd} WIB";
        }
    @endphp

    {{-- Logika Default Open: Hari yang sama dengan hari ini otomatis terbuka (open) --}}
    <details class="day-accordion-card" {{ $isToday ? 'open' : '' }}>
        {{-- 2. HEADER ACCORDION (<summary>) --}}
        <summary class="day-accordion-summary">
            {{-- Sisi Kiri Header --}}
            <div class="summary-left">
                {{-- Badge singkatan hari (SEN, SEL, RAB, KAM, JUM) --}}
                <div class="day-abbr-badge {{ $isToday ? 'day-abbr-today' : 'day-abbr-regular' }}">
                    {{ $abbr }}
                </div>
                <div>
                    <div class="summary-day-title">
                        <span>Hari {{ $day }}</span>
                        @if($isToday)
                            <span class="tag-today-pill">Hari Ini</span>
                        @endif
                    </div>
                    <div class="summary-day-subtitle">
                        {{ $daySchedules->count() }} Sesi Pembelajaran
                    </div>
                </div>
            </div>

            {{-- Sisi Kanan Header --}}
            <div class="summary-right">
                @if($daySchedules->isNotEmpty())
                    <span class="operating-hours-text">
                        {{ $operatingHours }}
                    </span>
                @endif
                {{-- Ikon panah (chevron) berotasi halus 180 derajat saat status accordion dibuka --}}
                <svg class="accordion-chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>
        </summary>

        {{-- 3. ISI KONTEN JADWAL (BODY ACCORDION: divide-y divide-gray-100) --}}
        <div class="accordion-body-list">
            @forelse($daySchedules as $sched)
                @php
                    $roomName = trim($sched->room?->name ?? '');
                    $subjName = strtolower($sched->subject?->name ?? '');

                    // Klasifikasi Tipe Ruangan & Visual Tags
                    $isLab = preg_match('/^L\./i', $roomName) && strcasecmp($roomName, 'L.IPA') !== 0;
                    $isIpa = (strcasecmp($roomName, 'L.IPA') === 0);
                    $isSport = preg_match('/^(OR|HALL)/i', $roomName) || str_contains($subjName, 'pjok') || str_contains($subjName, 'olahraga') || str_contains($subjName, 'penjas');

                    // Badge Tipe Mapel
                    if ($isLab || str_contains($subjName, 'praktik') || str_contains($subjName, 'lab')) {
                        $tipeLabel = 'Praktik Lab';
                        $tipeBadgeClass = 'type-pill-lab';
                    } elseif ($isSport) {
                        $tipeLabel = 'Olahraga';
                        $tipeBadgeClass = 'type-pill-sport';
                    } elseif ($isIpa) {
                        $tipeLabel = 'Praktik IPA';
                        $tipeBadgeClass = 'type-pill-special';
                    } else {
                        $tipeLabel = 'Teori';
                        $tipeBadgeClass = 'type-pill-teori';
                    }

                    // Badge Ruangan Sisi Kanan
                    if ($isLab) {
                        $roomBadgeClass = 'badge-room-lab-solid';
                    } elseif ($isSport) {
                        $roomBadgeClass = 'badge-room-sport-solid';
                    } elseif ($isIpa) {
                        $roomBadgeClass = 'badge-room-special-solid';
                    } else {
                        $roomBadgeClass = 'badge-room-teori-neutral';
                    }

                    // Waktu & Keterangan Jam Ke-
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

                <div class="accordion-schedule-row">
                    {{-- Waktu & Sesi --}}
                    <div class="schedule-time-box">
                        <div class="time-range-val">{{ $startClean }} - {{ $endClean }}</div>
                        <div class="time-period-label">{{ $periodLabel }}</div>
                    </div>

                    {{-- Info Mapel & Pengampu --}}
                    <div class="schedule-info-box">
                        <div class="subject-header-row">
                            <span class="subject-name-strong">
                                {{ $sched->subject?->name ?? 'Mata Pelajaran Tidak Terdaftar' }}
                            </span>
                            <span class="{{ $tipeBadgeClass }}">
                                {{ $tipeLabel }}
                            </span>
                            @if($sched->subject?->code)
                                <span class="code-pill-tag">
                                    {{ $sched->subject->code }}
                                </span>
                            @endif
                            @if($sched->schoolClass && (auth()->user()->role === 'guru' || auth()->user()->role === 'waka'))
                                <span class="class-pill-tag">
                                    Kelas {{ $sched->schoolClass->name }}
                                </span>
                            @endif
                        </div>
                        <div class="teacher-details-text">
                            <strong>{{ $teacherName }}</strong> &bull; {{ $teacherNip ? 'NIP: ' . $teacherNip : 'NIP: -' }}
                        </div>
                    </div>

                    {{-- Lokasi Ruangan (Kanan) --}}
                    <div class="schedule-room-box">
                        <span class="{{ $roomBadgeClass }}">
                            {{ $roomName ?: 'Tanpa Ruang' }}
                        </span>
                    </div>
                </div>
            @empty
                {{-- Teks Halus jika Hari tersebut tidak memiliki jadwal KBM --}}
                <div class="empty-day-panel">
                    Tidak ada jadwal KBM terjadwal untuk hari ini.
                </div>
            @endforelse
        </div>
    </details>
@endforeach

<script>
    function toggleAllAccordions(open) {
        document.querySelectorAll('.day-accordion-card').forEach(details => {
            details.open = open;
        });
    }
</script>
@endsection
