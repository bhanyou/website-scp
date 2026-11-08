@extends('layouts.app')

@section('content')
<style>
    /* ==========================================================================
       AGENDA JADWAL PELAJARAN MODERN (TABBED TIMELINE)
       Desain bersih, teratur, tanpa icon/emoji, aksen border warna dinamis
       ========================================================================== */
    .agenda-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .agenda-page-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.35rem 0;
        letter-spacing: -0.02em;
    }
    .agenda-page-subtitle {
        font-size: 0.9rem;
        color: #64748b;
        margin: 0;
    }

    /* Kartu Informasi Metadata */
    .agenda-info-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 5px solid #4f46e5;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .agenda-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        align-items: center;
    }
    .agenda-info-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 0.25rem;
    }
    .agenda-info-value {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }
    .agenda-info-sub {
        font-size: 0.82rem;
        color: #64748b;
        margin-top: 2px;
    }

    /* Tab Filter Hari Modern */
    .agenda-tabs-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.65rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        scrollbar-width: thin;
    }
    .agenda-tab-btn {
        white-space: nowrap;
        font-size: 0.88rem;
        font-weight: 600;
        padding: 0.55rem 1.15rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.18s ease;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-family: inherit;
        text-decoration: none;
    }
    .agenda-tab-btn:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #1e293b;
    }
    .agenda-tab-btn.is-active {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        font-weight: 700;
    }
    .agenda-tab-btn.is-active .agenda-tab-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .agenda-tab-count {
        background: #f1f5f9;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 9999px;
        line-height: 1.2;
    }
    .agenda-tab-today {
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

    /* Sub-header Hari Aktif */
    .agenda-pane-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0 0.25rem;
    }
    .agenda-pane-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .agenda-pane-count {
        font-size: 0.82rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Format Kartu Baris Horizontal (Agenda Timeline) */
    .agenda-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .agenda-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left-width: 6px;
        border-left-style: solid;
        border-radius: 14px;
        padding: 1.25rem 1.6rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
        box-sizing: border-box;
    }
    .agenda-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
    }

    /* Sisi Kiri: Waktu (Jam Mulai - Selesai) & Sub-keterangan jam ke- */
    .agenda-time-col {
        min-width: 145px;
        max-width: 165px;
        flex-shrink: 0;
        border-right: 1px dashed #e2e8f0;
        padding-right: 1.25rem;
    }
    .agenda-time-range {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        letter-spacing: -0.01em;
    }
    .agenda-time-period {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* Sisi Tengah: Judul Mapel, Tipe, Avatar Guru + Nama + NIP */
    .agenda-content-col {
        flex: 1;
        min-width: 0;
    }
    .agenda-subject-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 0.55rem;
    }
    .agenda-subject-title {
        font-size: 1.08rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.35;
    }
    .agenda-type-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }
    .agenda-code-badge {
        font-size: 0.72rem;
        font-weight: 600;
        background: #f1f5f9;
        color: #475569;
        padding: 2px 7px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        white-space: nowrap;
    }
    .agenda-class-badge {
        font-size: 0.72rem;
        font-weight: 700;
        background: #e0e7ff;
        color: #4338ca;
        padding: 2px 8px;
        border-radius: 6px;
        white-space: nowrap;
    }

    /* Avatar Inisial & Info Guru */
    .agenda-teacher-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .agenda-avatar-initials {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        flex-shrink: 0;
    }
    .agenda-teacher-info {
        min-width: 0;
    }
    .agenda-teacher-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
    }
    .agenda-teacher-nip {
        font-size: 0.74rem;
        color: #64748b;
        margin-top: 1px;
    }

    /* Sisi Kanan: Badge Nama Ruangan Menonjol */
    .agenda-room-col {
        flex-shrink: 0;
        text-align: right;
        min-width: 110px;
    }
    .agenda-room-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.06em;
        color: #94a3b8;
        margin-bottom: 4px;
    }
    .agenda-room-badge {
        font-size: 0.95rem;
        font-weight: 800;
        padding: 0.55rem 1.05rem;
        border-radius: 10px;
        display: inline-block;
        text-align: center;
        min-width: 80px;
        letter-spacing: 0.02em;
        box-sizing: border-box;
    }

    /* Card Kosong / Tanpa Jadwal */
    .agenda-empty-card {
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        padding: 3.5rem 1.5rem;
        text-align: center;
    }
    .agenda-empty-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.35rem;
    }
    .agenda-empty-subtitle {
        font-size: 0.84rem;
        color: #64748b;
        margin: 0;
    }

    /* Responsif Layar Kecil */
    @media (max-width: 720px) {
        .agenda-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.15rem;
        }
        .agenda-time-col {
            border-right: none;
            border-bottom: 1px dashed #e2e8f0;
            padding-right: 0;
            padding-bottom: 0.65rem;
            width: 100%;
            max-width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .agenda-room-col {
            text-align: left;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px dashed #e2e8f0;
            padding-top: 0.65rem;
        }
    }
</style>

<div class="agenda-page-header">
    <div>
        <h1 class="agenda-page-title">Jadwal Pelajaran</h1>
        <p class="agenda-page-subtitle">
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
<div class="agenda-info-card">
    <div class="agenda-info-grid">
        @if(isset($student) && $student)
            <div>
                <div class="agenda-info-label">Nama Siswa & Kelas</div>
                <div class="agenda-info-value">{{ $student->name }}</div>
                <div class="agenda-info-sub">
                    Kelas: <strong>{{ $schoolClass?->name ?? 'Belum ada kelas' }}</strong>
                    @if($student->nis)
                        &bull; NIS: {{ $student->nis }}
                    @endif
                </div>
            </div>

            <div>
                <div class="agenda-info-label">Wali Kelas</div>
                <div class="agenda-info-value">
                    {{ $schoolClass?->homeroomTeacher?->name ?? 'Belum ditentukan' }}
                </div>
                @if($schoolClass?->homeroomTeacher?->nip)
                    <div class="agenda-info-sub">
                        NIP: {{ $schoolClass->homeroomTeacher->nip }}
                    </div>
                @endif
            </div>
        @else
            <div>
                <div class="agenda-info-label">Tenaga Pengajar</div>
                <div class="agenda-info-value">{{ $teacher?->name ?? auth()->user()->name }}</div>
                <div class="agenda-info-sub">
                    NIP: {{ $teacher?->nip ?? '-' }}
                </div>
            </div>

            <div>
                <div class="agenda-info-label">Kelas / Rombel</div>
                <div class="agenda-info-value">
                    {{ $schoolClass?->name ?? 'Semua Kelas Binaan' }}
                </div>
                <div class="agenda-info-sub">
                    Jurusan: {{ $schoolClass?->major?->name ?? 'Reguler' }}
                </div>
            </div>
        @endif

        <div>
            <div class="agenda-info-label">Putaran Blok & Status</div>
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
            <div class="agenda-info-sub" style="margin-top: 4px;">
                Hari ini: <strong>{{ $todayName }}</strong> ({{ now()->translatedFormat('d F Y') }})
            </div>
        </div>
    </div>
</div>

{{-- 1. TAB FILTER HARI (SENIN S/D JUMAT) MODERN --}}
@php
    $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    // Otomatis memilih hari aktif saat ini jika hari kerja (Senin s/d Jumat), selain itu default Senin
    $initialTab = in_array($todayName, $daysList) ? $todayName : 'Senin';
@endphp

<div class="agenda-tabs-wrapper">
    @foreach($daysList as $day)
        @php
            $count = $schedulesByDay[$day]->count();
            $isToday = ($day === $todayName);
            $isActive = ($day === $initialTab);
        @endphp
        <button
            type="button"
            class="agenda-tab-btn {{ $isActive ? 'is-active' : '' }}"
            id="tab-btn-{{ strtolower($day) }}"
            onclick="switchScheduleDay('{{ strtolower($day) }}')"
        >
            <span>{{ $day }}</span>
            @if($isToday)
                <span class="agenda-tab-today">Hari Ini</span>
            @endif
            <span class="agenda-tab-count">{{ $count }}</span>
        </button>
    @endforeach

    <button
        type="button"
        class="agenda-tab-btn"
        id="tab-btn-all"
        onclick="switchScheduleDay('all')"
    >
        <span>Tampilkan Semua (Senin - Jumat)</span>
    </button>
</div>

{{-- 2. KONTEN AGENDA TIMELINE PER HARI (FORMAT KARTU BARIS HORIZONTAL) --}}
@foreach($daysList as $day)
    @php
        $daySchedules = $schedulesByDay[$day];
        $isToday = ($day === $todayName);
    @endphp
    <div
        class="agenda-day-pane"
        id="pane-{{ strtolower($day) }}"
        style="{{ $day === $initialTab ? 'display: block;' : 'display: none;' }}"
    >
        <div class="agenda-pane-header">
            <h3 class="agenda-pane-title">
                <span>Jadwal Hari {{ $day }}</span>
                @if($isToday)
                    <span class="badge badge-danger" style="font-size: 0.72rem; font-weight: 700;">
                        Sedang Berlangsung Hari Ini
                    </span>
                @endif
            </h3>
            <span class="agenda-pane-count">
                Total: <strong>{{ $daySchedules->count() }}</strong> mata pelajaran
            </span>
        </div>

        @if($daySchedules->isEmpty())
            <div class="agenda-empty-card">
                <h4 class="agenda-empty-title">Tidak Ada Jadwal Pelajaran</h4>
                <p class="agenda-empty-subtitle">
                    Tidak ada kegiatan belajar mengajar atau jadwal khusus pada hari {{ $day }}.
                </p>
            </div>
        @else
            <div class="agenda-list">
                @foreach($daySchedules as $sched)
                    @php
                        $roomName = trim($sched->room?->name ?? '');
                        $subjName = strtolower($sched->subject?->name ?? '');

                        // Tentukan tipe ruangan & aksen warna border kiri
                        $isLab = preg_match('/^L\./i', $roomName) || str_contains($subjName, 'praktik') || str_contains($subjName, 'lab');
                        $isSport = preg_match('/^(OR|HALL)/i', $roomName) || str_contains($subjName, 'pjok') || str_contains($subjName, 'olahraga') || str_contains($subjName, 'penjas');

                        if ($isLab) {
                            $tipeLabel = 'Praktik Lab';
                            $accentColor = '#2563eb'; // Biru solid untuk Lab
                            $tipeBadgeStyle = 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;';
                            $roomBadgeStyle = 'background: #2563eb; color: #ffffff; font-weight: 800; border: none; box-shadow: 0 4px 10px rgba(37,99,235,0.25);';
                            $avatarBg = '#eff6ff';
                            $avatarColor = '#1d4ed8';
                        } elseif ($isSport) {
                            $tipeLabel = 'Olahraga';
                            $accentColor = '#f97316'; // Oranye untuk Olahraga
                            $tipeBadgeStyle = 'background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa;';
                            $roomBadgeStyle = 'background: #ea580c; color: #ffffff; font-weight: 800; border: none; box-shadow: 0 4px 10px rgba(234,88,12,0.25);';
                            $avatarBg = '#fff7ed';
                            $avatarColor = '#c2410c';
                        } else {
                            $tipeLabel = 'Teori';
                            $accentColor = '#10b981'; // Hijau untuk Teori
                            $tipeBadgeStyle = 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;';
                            $roomBadgeStyle = 'background: #f8fafc; color: #0f172a; border: 1.5px solid #cbd5e1; font-weight: 800;';
                            $avatarBg = '#f1f5f9';
                            $avatarColor = '#334155';
                        }

                        $startClean = substr((string)$sched->start_time, 0, 5);
                        $endClean = substr((string)$sched->end_time, 0, 5);

                        $startPeriod = $sched->start_period;
                        $endPeriod = $sched->end_period;
                        if ($startPeriod && $endPeriod) {
                            $periodLabel = ($startPeriod == $endPeriod) ? "Jam ke-{$startPeriod}" : "Jam ke-{$startPeriod} s.d. {$endPeriod}";
                        } elseif ($startPeriod) {
                            $periodLabel = "Jam ke-{$startPeriod}";
                        } else {
                            $periodLabel = "Sesi Terjadwal";
                        }

                        $teacherName = $sched->teacher?->name ?? 'Guru Pengajar';

                        // Inisial avatar: 1 - 2 huruf kapital bersih tanpa gelar akademik
                        $words = preg_split('/[\s,\.]+/', trim($teacherName));
                        $initials = '';
                        $ignoredTitles = ['spd', 'spdsi', 'skom', 'st', 'mpd', 'mkom', 'dra', 'dr', 'drs', 'ir', 'se', 'sh', 'ssos'];
                        foreach ($words as $w) {
                            $w = trim($w);
                            if ($w !== '' && !in_array(strtolower($w), $ignoredTitles)) {
                                $initials .= strtoupper($w[0]);
                                if (strlen($initials) >= 2) break;
                            }
                        }
                        if (empty($initials)) {
                            $initials = strtoupper(substr($teacherName, 0, 2));
                        }
                    @endphp

                    {{-- Format Kartu Baris Horizontal (Agenda Timeline) --}}
                    <div class="agenda-card" style="border-left-color: {{ $accentColor }};">
                        {{-- Sisi Kiri: Waktu (Jam Mulai - Selesai) & Sub-keterangan jam ke- --}}
                        <div class="agenda-time-col">
                            <div class="agenda-time-range">{{ $startClean }} - {{ $endClean }}</div>
                            <div class="agenda-time-period">{{ $periodLabel }}</div>
                        </div>

                        {{-- Sisi Tengah: Judul Mapel, Badge Tipe, Guru Avatar + Nama + NIP --}}
                        <div class="agenda-content-col">
                            <div class="agenda-subject-header">
                                <h4 class="agenda-subject-title">
                                    {{ $sched->subject?->name ?? 'Mata Pelajaran Tidak Terdaftar' }}
                                </h4>
                                <span class="agenda-type-badge" style="{{ $tipeBadgeStyle }}">
                                    {{ $tipeLabel }}
                                </span>
                                @if($sched->subject?->code)
                                    <span class="agenda-code-badge">
                                        Kode: {{ $sched->subject->code }}
                                    </span>
                                @endif
                                @if($sched->schoolClass && (auth()->user()->role === 'guru' || auth()->user()->role === 'waka'))
                                    <span class="agenda-class-badge">
                                        Kelas: {{ $sched->schoolClass->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="agenda-teacher-row">
                                <div class="agenda-avatar-initials" style="background: {{ $avatarBg }}; color: {{ $avatarColor }};">
                                    {{ $initials }}
                                </div>
                                <div class="agenda-teacher-info">
                                    <div class="agenda-teacher-name">{{ $teacherName }}</div>
                                    @if($sched->teacher?->nip)
                                        <div class="agenda-teacher-nip">NIP: {{ $sched->teacher->nip }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Sisi Kanan: Badge Nama Ruangan yang Menonjol --}}
                        <div class="agenda-room-col">
                            <div class="agenda-room-label">Ruangan</div>
                            <div class="agenda-room-badge" style="{{ $roomBadgeStyle }}">
                                {{ $roomName ?: 'Tanpa Ruang' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endforeach

<script>
    function switchScheduleDay(targetDay) {
        const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        const allButtons = document.querySelectorAll('.agenda-tab-btn');
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
