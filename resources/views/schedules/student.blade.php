@extends('layouts.app')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h1 class="page-title">Jadwal Pelajaran</h1>
        <p class="page-subtitle">
            Jadwal resmi mata pelajaran mingguan untuk kelas <strong>{{ $schoolClass?->name ?? '-' }}</strong>
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Jadwal</span>
        </button>
    </div>
</div>

{{-- INFORMASI KELAS & PUTARAN RESMI DARI WAKA --}}
<div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid #4f46e5; background: #ffffff;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; align-items: center;">
        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">
                Nama Siswa & Kelas
            </div>
            <div style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">
                {{ $student->name }}
            </div>
            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 2px;">
                Kelas: <span class="badge badge-info" style="font-size: 0.78rem;">{{ $schoolClass?->name ?? 'Belum ada kelas' }}</span>
                @if($student->nis)
                    &bull; NIS: <code>{{ $student->nis }}</code>
                @endif
            </div>
        </div>

        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">
                Wali Kelas
            </div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">
                {{ $schoolClass?->homeroomTeacher?->name ?? 'Belum ditentukan' }}
            </div>
            @if($schoolClass?->homeroomTeacher?->nip)
                <div style="font-size: 0.78rem; color: var(--text-muted);">
                    NIP: {{ $schoolClass->homeroomTeacher->nip }}
                </div>
            @endif
        </div>

        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">
                Putaran Blok & Validasi Waka
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                @if($activeCycle)
                    <span style="font-size: 0.95rem; font-weight: 700; color: #4f46e5;">
                        Putaran {{ $activeCycle->cycle }}
                    </span>
                    @if($activeCycle->is_locked)
                        <span class="badge badge-success" style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.55rem;">
                            🔒 Dikunci & Disahkan Waka
                        </span>
                    @else
                        <span class="badge badge-warning" style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.55rem;">
                            Draft Waka
                        </span>
                    @endif
                @else
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Belum ada putaran blok aktif</span>
                @endif
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                Hari ini: <strong>{{ $todayName }}</strong> ({{ now()->translatedFormat('d F Y') }})
            </div>
        </div>
    </div>
</div>

{{-- NAVIGASI TAB HARI: BISA PILIH HARI TERTENTU ATAU SEMUA HARI (SENIN - JUMAT) --}}
<div style="display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.75rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color);">
    @php
        $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        // Tentukan tab aktif awal: jika hari ini antara Senin-Jumat maka hari ini, selain itu default Senin
        $initialTab = in_array($todayName, $daysList) ? $todayName : 'Senin';
    @endphp

    @foreach($daysList as $day)
        @php
            $count = $schedulesByDay[$day]->count();
            $isToday = ($day === $todayName);
        @endphp
        <button
            type="button"
            class="tab-day-btn btn btn-sm {{ $day === $initialTab ? 'btn-primary' : 'btn-secondary' }}"
            id="tab-btn-{{ strtolower($day) }}"
            onclick="switchScheduleDay('{{ strtolower($day) }}')"
            style="white-space: nowrap; font-size: 0.85rem; padding: 0.45rem 0.9rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.4rem;"
        >
            <span>{{ $day }}</span>
            @if($isToday)
                <span style="background: #ef4444; color: #fff; font-size: 0.65rem; font-weight: 700; padding: 1px 5px; border-radius: 4px; text-transform: uppercase;">
                    Hari Ini
                </span>
            @endif
            <span style="background: rgba(0,0,0,0.08); font-size: 0.72rem; padding: 1px 6px; border-radius: 10px; font-weight: 600;">
                {{ $count }}
            </span>
        </button>
    @endforeach

    <button
        type="button"
        class="tab-day-btn btn btn-sm btn-secondary"
        id="tab-btn-all"
        onclick="switchScheduleDay('all')"
        style="white-space: nowrap; font-size: 0.85rem; padding: 0.45rem 0.9rem; border-radius: 8px;"
    >
        📅 Tampilkan Semua (Senin - Jumat)
    </button>
</div>

{{-- DAFTAR KONTEN JADWAL PER HARI --}}
@foreach($daysList as $day)
    @php
        $daySchedules = $schedulesByDay[$day];
        $isToday = ($day === $todayName);
    @endphp
    <div
        class="day-schedule-pane"
        id="pane-{{ strtolower($day) }}"
        style="{{ $day === $initialTab ? 'display: block;' : 'display: none;' }}"
    >
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <span>Jadwal Hari {{ $day }}</span>
                @if($isToday)
                    <span class="badge badge-danger" style="font-size: 0.72rem;">Sedang Berlangsung Hari Ini</span>
                @endif
            </h3>
            <span style="font-size: 0.8rem; color: var(--text-muted);">
                Total: <strong>{{ $daySchedules->count() }}</strong> mata pelajaran
            </span>
        </div>

        @if($daySchedules->isEmpty())
            <div class="card" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏖️</div>
                <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">
                    Tidak Ada Jadwal Pelajaran
                </h4>
                <p style="font-size: 0.82rem; margin: 0;">
                    Tidak ada kegiatan belajar mengajar atau jadwal khusus pada hari {{ $day }}.
                </p>
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem;">
                @foreach($daySchedules as $sched)
                    @php
                        $startClean = substr((string)$sched->start_time, 0, 5);
                        $endClean = substr((string)$sched->end_time, 0, 5);
                    @endphp
                    <div class="card" style="padding: 1.15rem; border: 1px solid var(--border-color); border-radius: 10px; transition: transform 0.15s ease, box-shadow 0.15s ease; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                            <div>
                                <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.55rem;">
                                    ⏰ {{ $startClean }} - {{ $endClean }}
                                </span>
                            </div>
                            @if($sched->room)
                                <span class="badge badge-secondary" style="font-size: 0.75rem; font-weight: 600;" title="Ruangan Pembelajaran">
                                    🏛️ {{ $sched->room->name }}
                                </span>
                            @endif
                        </div>

                        <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; line-height: 1.35;">
                            {{ $sched->subject?->name ?? 'Mata Pelajaran Tidak Terdaftar' }}
                        </h4>

                        <div style="border-top: 1px dashed var(--border-color); padding-top: 0.75rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                                    👨‍🏫
                                </div>
                                <div>
                                    <div style="font-size: 0.82rem; font-weight: 600; color: var(--text-primary);">
                                        {{ $sched->teacher?->name ?? '-' }}
                                    </div>
                                    @if($sched->teacher?->nip)
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">
                                            NIP: {{ $sched->teacher->nip }}
                                        </div>
                                    @endif
                                </div>
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
        const allButtons = document.querySelectorAll('.tab-day-btn');
        allButtons.forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-secondary');
        });

        if (targetDay === 'all') {
            document.getElementById('tab-btn-all').classList.remove('btn-secondary');
            document.getElementById('tab-btn-all').classList.add('btn-primary');
            days.forEach(d => {
                const pane = document.getElementById('pane-' + d);
                if (pane) pane.style.display = 'block';
            });
        } else {
            const activeBtn = document.getElementById('tab-btn-' + targetDay);
            if (activeBtn) {
                activeBtn.classList.remove('btn-secondary');
                activeBtn.classList.add('btn-primary');
            }
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

