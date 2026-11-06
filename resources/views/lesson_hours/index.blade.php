@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Alokasi Waktu Jam Pelajaran</h1>
        <p class="page-subtitle">
            Pengaturan sesi dan alokasi waktu kegiatan belajar mengajar (KBM)
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <form action="{{ route('lesson-hours.reset-defaults') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Kembalikan alokasi jam ke standar baku?')">
            @csrf
            <input type="hidden" name="day" value="{{ $selectedDay }}">
            <button type="submit" class="btn btn-secondary btn-sm">
                Reset ke Standar
            </button>
        </form>
        <a href="{{ route('schedules.index') }}" class="btn btn-primary btn-sm">
            Kembali ke Jadwal Mengajar
        </a>
    </div>
</div>

{{-- Tab Navigasi Hari --}}
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
    @foreach($availableDays as $day)
        <a href="{{ route('lesson-hours.index', ['day' => $day]) }}"
           class="btn btn-sm {{ $selectedDay === $day ? 'btn-primary' : 'btn-secondary' }}"
           style="font-weight: 600; padding: 0.5rem 1.25rem;">
            {{ $day }}
            @if($day === 'Jumat')
                <span class="badge" style="background: rgba(0,0,0,0.12); margin-left: 0.25rem; font-size: 0.7rem; padding: 0.15rem 0.4rem;">
                    8 Jam
                </span>
            @else
                <span class="badge" style="background: rgba(0,0,0,0.12); margin-left: 0.25rem; font-size: 0.7rem; padding: 0.15rem 0.4rem;">
                    10 Jam
                </span>
            @endif
        </a>
    @endforeach
</div>

{{-- Panel Khusus Hari Senin (Mode Upacara vs Normal) --}}
@if($selectedDay === 'Senin')
    <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem; background: var(--bg-surface);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                    Status KBM Hari Senin Aktif
                </div>
                <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-top: 0.25rem;">
                    {{ $mondayMode === 'upacara' ? 'Mode Upacara / Briefing (Durasi 35 Menit)' : 'Mode Normal Tanpa Upacara (Durasi 40 Menit)' }}
                </div>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0.25rem 0 0 0;">
                    Jadwal pelajaran hari Senin saat ini berjalan menggunakan alokasi waktu <strong>{{ $mondayMode === 'upacara' ? '35 menit (dimulai pukul 08.00 setelah Upacara)' : '40 menit (dimulai pukul 07.15)' }}</strong>.
                </p>
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                @if($mondayMode === 'upacara')
                    <form action="{{ route('lesson-hours.toggle-monday-mode') }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="mode" value="non_upacara">
                        <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Ubah jadwal Senin menjadi Mode Normal (40 Menit)?')">
                            Beralih ke Mode Normal (40 Menit)
                        </button>
                    </form>
                @else
                    <form action="{{ route('lesson-hours.toggle-monday-mode') }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="mode" value="upacara">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Ubah jadwal Senin menjadi Mode Upacara (35 Menit)?')">
                            Beralih ke Mode Upacara (35 Menit)
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Switch Tab untuk Mengedit Sub-Mode Senin --}}
        <div style="display: flex; gap: 0.5rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--border-color); align-items: center;">
            <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">
                Edit Pengaturan:
            </span>
            <a href="{{ route('lesson-hours.index', ['day' => 'Senin', 'day_type' => 'upacara']) }}"
               class="btn btn-sm {{ $selectedDayType === 'upacara' ? 'btn-primary' : 'btn-secondary' }}"
               style="font-size: 0.78rem;">
                Tabel Mode Upacara (35')
            </a>
            <a href="{{ route('lesson-hours.index', ['day' => 'Senin', 'day_type' => 'non_upacara']) }}"
               class="btn btn-sm {{ $selectedDayType === 'non_upacara' ? 'btn-primary' : 'btn-secondary' }}"
               style="font-size: 0.78rem;">
                Tabel Mode Normal (40')
            </a>
        </div>
    </div>
@endif

{{-- Form Edit Jam Pelajaran Hari Terpilih --}}
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                Tabel Sesi Jam Pelajaran Hari {{ $selectedDay }}
                @if($selectedDay === 'Senin')
                    ({{ $selectedDayType === 'upacara' ? 'Mode Upacara - 35 Menit' : 'Mode Normal - 40 Menit' }})
                @endif
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0.25rem 0 0 0;">
                Waka dapat mengubah jam mulai dan selesai jika ada acara/kegiatan sekolah. Format sesi tetap tersimpan dan terjaga.
            </p>
        </div>
        <span class="badge badge-info" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
            {{ $lessonHours->count() }} Sesi Pelajaran
        </span>
    </div>

    {{-- Penanda Kegiatan Khusus Sebelum KBM --}}
    @if($selectedDay === 'Senin' && $selectedDayType === 'upacara')
        <div style="background: rgba(99, 102, 241, 0.06); border-left: 3px solid #6366f1; padding: 0.65rem 1rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
            <span><strong>Kegiatan Awal:</strong> Upacara Bendera / Briefing Guru & Siswa</span>
            <span style="font-weight: 600; color: #4338ca;">07.00 - 08.00 (60 Menit)</span>
        </div>
    @elseif($selectedDay === 'Selasa')
        <div style="background: rgba(99, 102, 241, 0.06); border-left: 3px solid #6366f1; padding: 0.65rem 1rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
            <span><strong>Kegiatan Awal:</strong> Literasi Pagi</span>
            <span style="font-weight: 600; color: #4338ca;">07.00 - 07.30 (30 Menit)</span>
        </div>
    @endif

    <form action="{{ route('lesson-hours.update') }}" method="POST">
        @csrf
        <input type="hidden" name="day" value="{{ $selectedDay }}">
        <input type="hidden" name="day_type" value="{{ $selectedDayType }}">

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 140px;">Sesi Pelajaran</th>
                        <th style="width: 170px;">Jam Mulai</th>
                        <th style="width: 170px;">Jam Selesai</th>
                        <th style="width: 130px; text-align: center;">Durasi KBM</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lessonHours as $hour)
                        @php
                            $startVal = substr((string) $hour->start_time, 0, 5);
                            $endVal = substr((string) $hour->end_time, 0, 5);
                        @endphp

                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                                {{ $hour->period_number }}
                            </td>
                            <td>
                                <strong>{{ $hour->label }}</strong>
                            </td>
                            <td>
                                <input type="time"
                                       name="periods[{{ $hour->period_number }}][start_time]"
                                       id="start_{{ $hour->period_number }}"
                                       value="{{ $startVal }}"
                                       class="form-control"
                                       required
                                       onchange="recalculateDuration({{ $hour->period_number }})"
                                       style="width: 100%; max-width: 140px; font-weight: 600;">
                            </td>
                            <td>
                                <input type="time"
                                       name="periods[{{ $hour->period_number }}][end_time]"
                                       id="end_{{ $hour->period_number }}"
                                       value="{{ $endVal }}"
                                       class="form-control"
                                       required
                                       onchange="recalculateDuration({{ $hour->period_number }})"
                                       style="width: 100%; max-width: 140px; font-weight: 600;">
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-info" id="badge_dur_{{ $hour->period_number }}" style="font-size: 0.78rem;">
                                    {{ $hour->duration_minutes }} Menit
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">
                                Jam Pembelajaran Sesi ke-{{ $hour->period_number }}
                            </td>
                        </tr>

                        {{-- Baris Jeda Istirahat I --}}
                        @if($hour->period_number === 3)
                            <tr style="background-color: rgba(245, 158, 11, 0.05); border-top: 1px dashed #f59e0b; border-bottom: 1px dashed #f59e0b;">
                                <td style="text-align: center; color: #b45309; font-weight: 700;">-</td>
                                <td colspan="3" style="color: #b45309; font-weight: 600; font-size: 0.85rem;">
                                    Istirahat I (Jeda Antar Sesi KBM)
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-warning" style="font-size: 0.75rem;">15 Menit</span>
                                </td>
                                <td style="font-size: 0.8rem; color: #92400e;">
                                    Jeda setelah Jam ke-3 selesai sebelum Jam ke-4
                                </td>
                            </tr>
                        @endif

                        {{-- Baris Jeda Ishoma / Shalat Jumat --}}
                        @if($hour->period_number === 6)
                            <tr style="background-color: rgba(16, 185, 129, 0.05); border-top: 1px dashed #10b981; border-bottom: 1px dashed #10b981;">
                                <td style="text-align: center; color: #047857; font-weight: 700;">-</td>
                                <td colspan="3" style="color: #047857; font-weight: 600; font-size: 0.85rem;">
                                    {{ $selectedDay === 'Jumat' ? 'Shalat Jumat & Istirahat Siang' : 'Istirahat II / Ishoma' }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-success" style="font-size: 0.75rem;">
                                        {{ $selectedDay === 'Jumat' ? '100 Menit' : '60 Menit' }}
                                    </span>
                                </td>
                                <td style="font-size: 0.8rem; color: #065f46;">
                                    {{ $selectedDay === 'Jumat' ? 'Ibadah Shalat Jumat berjamaah dan istirahat' : 'Ibadah Shalat Dzuhur berjamaah dan makan siang' }}
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    {{-- Baris Penutup KBM --}}
                    <tr style="background-color: rgba(148, 163, 184, 0.08); border-top: 1px dashed var(--border-color);">
                        <td style="text-align: center; color: var(--text-muted); font-weight: 700;">-</td>
                        <td colspan="3" style="color: var(--text-secondary); font-weight: 600; font-size: 0.85rem;">
                            {{ $selectedDay === 'Jumat' ? 'Kepulangan / Kegiatan Ekstrakurikuler' : 'Shalat Ashar / Piket Kelas' }}
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-info" style="font-size: 0.75rem;">
                                {{ $selectedDay === 'Jumat' ? 'Selesai KBM' : '20 Menit' }}
                            </span>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ $selectedDay === 'Jumat' ? 'KBM hari Jumat berakhir setelah Jam ke-8' : 'Pukul 15.10 - 15.30 WIB' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-weight: 600;">
                Simpan Perubahan Alokasi Jam Hari {{ $selectedDay }}
            </button>
        </div>
    </form>
</div>

<script>
function recalculateDuration(periodNumber) {
    const startInput = document.getElementById('start_' + periodNumber);
    const endInput = document.getElementById('end_' + periodNumber);
    const badge = document.getElementById('badge_dur_' + periodNumber);

    if (startInput && endInput && badge && startInput.value && endInput.value) {
        const startParts = startInput.value.split(':');
        const endParts = endInput.value.split(':');

        const startMin = parseInt(startParts[0], 10) * 60 + parseInt(startParts[1], 10);
        const endMin = parseInt(endParts[0], 10) * 60 + parseInt(endParts[1], 10);

        let duration = endMin - startMin;
        if (duration < 0) duration = 0;

        badge.textContent = duration + ' Menit';
    }
}
</script>
@endsection
