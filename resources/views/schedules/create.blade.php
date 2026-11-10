
{{-- 
    ==========================================================================
    HALAMAN: TAMBAH JADWAL MENGAJAR (CREATE SCHEDULE)
    FUNGSI: Form input untuk mendaftarkan jadwal pelajaran baru bagi guru,
            dilengkapi pemilihan siklus/putaran blok, jam pelajaran dinamis,
            serta preview rentang waktu otomatis secara real-time.
    ==========================================================================
--}}

@extends('layouts.app')

@section('content')
{{-- 1. HEADER HALAMAN: Menampilkan judul dan periode siklus blok yang sedang aktif --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Jadwal Mengajar</h1>
        <p class="page-subtitle">
            Siklus Aktif: <strong>{{ $activeCycle->cycle }}</strong>
            ({{ $activeCycle->start_date?->format('d-m-Y') ?? '-' }}
            s.d. {{ $activeCycle->end_date?->format('d-m-Y') ?? '-' }})
        </p>
    </div>
</div>

{{-- 2. KARTU FORMULIR UTAMA --}}
<div class="card" style="max-width: 640px;">
    {{-- Form mengarah ke route 'schedules.store' dengan method POST untuk menyimpan data ke database --}}
    <form action="{{ route('schedules.store') }}" method="POST">
        @csrf {{-- Token keamanan CSRF Laravel untuk mencegah serangan pemalsuan permintaan --}}

        {{-- FIELD 1: PILIHAN PUTARAN BLOK (Putaran 1 atau Putaran 2) --}}
        <div class="form-group">
            <label for="block_cycle_id">Pilih Putaran Blok <span style="color: var(--danger);">*</span></label>
            <select name="block_cycle_id" id="block_cycle_id" required>
                @foreach($blockCycles as $cycle)
                    <option value="{{ $cycle->id }}" {{ old('block_cycle_id') == $cycle->id ? 'selected' : '' }}>
                        Putaran {{ $cycle->cycle }}
                    </option>
                @endforeach
            </select>
            @error('block_cycle_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- FIELD 2: PILIHAN GURU PENGAJAR --}}
        <div class="form-group">
            <label for="teacher_id">Guru Pengajar <span style="color: var(--danger);">*</span></label>
            <select name="teacher_id" id="teacher_id" required>
                <option value="">-- Pilih Guru Pengajar --</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}"
                        {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>
            @error('teacher_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- FIELD 3: PILIHAN MATA PELAJARAN --}}
        <div class="form-group">
            <label for="subject_id">Mata Pelajaran <span style="color: var(--danger);">*</span></label>
            <select name="subject_id" id="subject_id" required>
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}"
                        {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
            @error('subject_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- FIELD 4: PILIHAN KELAS TARGET PEMBELAJARAN --}}
        <div class="form-group">
            <label for="class_id">Kelas <span style="color: var(--danger);">*</span></label>
            <select name="class_id" id="class_id" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}"
                        {{ old('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
            @error('class_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- FIELD 5: PILIHAN RUANGAN / LAB / BENGKEL --}}
        <div class="form-group">
            <label for="room_id">Ruangan <span style="color: var(--danger);">*</span></label>
            <select name="room_id" id="room_id" required>
                <option value="">-- Pilih Ruangan --</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}"
                        {{ old('room_id') == $room->id ? 'selected' : '' }}>
                        {{ $room->name }}
                    </option>
                @endforeach
            </select>
            @error('room_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- FIELD 6: PILIHAN HARI PELAJARAN (Senin s.d. Jumat) --}}
        <div class="form-group">
            <label for="day">Hari <span style="color: var(--danger);">*</span></label>
            <select name="day" id="day" required>
                <option value="">-- Pilih Hari --</option>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    <option value="{{ $day }}"
                        {{ old('day') == $day ? 'selected' : '' }}>
                        {{ $day }}
                    </option>
                @endforeach
            </select>
            @error('day')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- FIELD 7: RENTANG JAM PELAJARAN (JAM MULAI & JAM SELESAI) --}}
        <div class="grid-2">
            {{-- Jam Mulai: Pilihan awal jam ke berapa sesi dimulai --}}
            <div class="form-group">
                <label for="start_period">Mulai dari Jam Pelajaran <span style="color: var(--danger);">*</span></label>
                <select name="start_period" id="start_period" required>
                    <option value="">-- Pilih Jam Mulai --</option>
                    @foreach($periods as $num => $p)
                        <option value="{{ $num }}" data-start="{{ $p['start'] }}" data-end="{{ $p['end'] }}"
                            {{ old('start_period', 1) == $num ? 'selected' : '' }}>
                            {{ $p['label'] }} ({{ $p['start'] }})
                        </option>
                    @endforeach
                </select>
                @error('start_period')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Jam Selesai: Pilihan akhir jam ke berapa sesi selesai --}}
            <div class="form-group">
                <label for="end_period">Sampai Jam Pelajaran <span style="color: var(--danger);">*</span></label>
                <select name="end_period" id="end_period" required>
                    <option value="">-- Pilih Jam Selesai --</option>
                    @foreach($periods as $num => $p)
                        <option value="{{ $num }}" data-start="{{ $p['start'] }}" data-end="{{ $p['end'] }}"
                            {{ old('end_period', 2) == $num ? 'selected' : '' }}>
                            {{ $p['label'] }} ({{ $p['end'] }})
                        </option>
                    @endforeach
                </select>
                @error('end_period')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        {{-- 3. KOTAK PREVIEW INTERAKTIF: Menghitung total jam pelajaran & konversi waktu WIB secara dinamis --}}
        <div id="period-preview" style="background: rgba(37, 99, 235, 0.08); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #2563eb; letter-spacing: 0.5px;">Estimasi Rentang Waktu</span>
                    <div id="preview-text" style="font-weight: 600; font-size: 0.95rem; color: #1e293b; margin-top: 2px;">
                        Jam ke-1 s.d. 2 (07:00 - 08:30 WIB)
                    </div>
                </div>
                <span id="preview-badge" class="badge badge-info" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                    2 Jam Pelajaran
                </span>
            </div>
        </div>

        {{-- Pesan error jika jadwal bentrok (guru/ruangan/kelas sudah terpakai di jam yang sama) --}}
        @error('schedule')
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @enderror

        {{-- 4. TOMBOL AKSI: Simpan Data ke Database atau Batal kembali ke daftar --}}
        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Jadwal
            </button>

            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>

{{-- 
    ==========================================================================
    LOGIKA JAVASCRIPT: Menangani sinkronisasi jam pelajaran antar hari,
                       memperbarui opsi jam saat hari dipilih, serta
                       memperbarui kotak estimasi waktu secara interaktif.
    ==========================================================================
--}}
<script>
// Mengambil master konfigurasi jam pelajaran (Senin-Kamis vs Jumat) dari backend
window.allLessonPeriods = @json($allLessonPeriods);

/**
 * Mengisi ulang opsi dropdown jam pelajaran sesuai hari yang dipilih
 * (Misalnya: hari Jumat memiliki alokasi jam istirahat sholat Jumat yang berbeda).
 */
function populatePeriods(day, defaultStart, defaultEnd) {
    const periods = window.allLessonPeriods[day] || window.allLessonPeriods['Senin'] || {};
    const startSelect = document.getElementById('start_period');
    const endSelect = document.getElementById('end_period');
    if (!startSelect || !endSelect) return;

    const currentStartVal = defaultStart !== undefined ? parseInt(defaultStart, 10) : parseInt(startSelect.value, 10) || 1;
    const currentEndVal = defaultEnd !== undefined ? parseInt(defaultEnd, 10) : parseInt(endSelect.value, 10) || 2;

    startSelect.innerHTML = '<option value="">-- Pilih Jam Mulai --</option>';
    endSelect.innerHTML = '<option value="">-- Pilih Jam Selesai --</option>';

    const keys = Object.keys(periods).map(k => parseInt(k, 10)).sort((a, b) => a - b);
    const maxPeriod = keys.length ? Math.max(...keys) : 10;

    keys.forEach(num => {
        const p = periods[num];
        // Buat opsi jam mulai
        const optStart = document.createElement('option');
        optStart.value = num;
        optStart.setAttribute('data-start', p.start);
        optStart.setAttribute('data-end', p.end);
        optStart.textContent = `${p.label} (${p.start})`;
        if (num === Math.min(currentStartVal, maxPeriod)) {
            optStart.selected = true;
        }
        startSelect.appendChild(optStart);

        // Buat opsi jam selesai
        const optEnd = document.createElement('option');
        optEnd.value = num;
        optEnd.setAttribute('data-start', p.start);
        optEnd.setAttribute('data-end', p.end);
        optEnd.textContent = `${p.label} (${p.end})`;
        if (num === Math.min(currentEndVal, maxPeriod)) {
            optEnd.selected = true;
        }
        endSelect.appendChild(optEnd);
    });
}

// Inisialisasi event listener setelah seluruh DOM halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function () {
    const daySelect = document.getElementById('day');
    const startSelect = document.getElementById('start_period');
    const endSelect = document.getElementById('end_period');
    const previewText = document.getElementById('preview-text');
    const previewBadge = document.getElementById('preview-badge');
    const previewBox = document.getElementById('period-preview');

    /**
     * Memperbarui kotak preview: Menghitung total jam pelajaran dan format jam WIB
     */
    function updatePreview() {
        const startOpt = startSelect.options[startSelect.selectedIndex];
        const endOpt = endSelect.options[endSelect.selectedIndex];

        // Validasi jika jam belum dipilih
        if (!startOpt || !endOpt || !startOpt.value || !endOpt.value) {
            previewText.textContent = 'Silakan pilih jam mulai dan jam selesai.';
            previewBadge.textContent = '-';
            return;
        }

        const startPeriod = parseInt(startOpt.value, 10);
        const endPeriod = parseInt(endOpt.value, 10);
        const startTime = startOpt.getAttribute('data-start');
        const endTime = endOpt.getAttribute('data-end');

        // Validasi: Jam selesai tidak boleh lebih awal dari jam mulai
        if (endPeriod < startPeriod) {
            previewBox.style.background = 'rgba(239, 68, 68, 0.08)';
            previewBox.style.borderColor = 'rgba(239, 68, 68, 0.25)';
            previewText.textContent = 'Peringatan: Jam selesai harus sama atau setelah jam mulai!';
            previewBadge.className = 'badge badge-danger';
            previewBadge.textContent = 'Tidak Valid';
            return;
        }

        // Tampilan normal jika valid
        previewBox.style.background = 'rgba(37, 99, 235, 0.08)';
        previewBox.style.borderColor = 'rgba(37, 99, 235, 0.2)';
        previewBadge.className = 'badge badge-info';

        const totalHours = (endPeriod - startPeriod) + 1;
        previewBadge.textContent = totalHours + ' Jam Pelajaran';

        if (startPeriod === endPeriod) {
            previewText.textContent = `Jam ke-${startPeriod} (${startTime} - ${endTime} WIB)`;
        } else {
            previewText.textContent = `Jam ke-${startPeriod} s.d. ${endPeriod} (${startTime} - ${endTime} WIB)`;
        }
    }

    // Event listener: Saat pilihan hari berubah, perbarui jam yang tersedia (Jumat vs Senin-Kamis)
    if (daySelect) {
        daySelect.addEventListener('change', function() {
            const day = this.value || 'Senin';
            populatePeriods(day);
            updatePreview();
        });

        const initDay = daySelect.value || 'Senin';
        populatePeriods(initDay, {{ old('start_period', 1) }}, {{ old('end_period', 2) }});
    }

    // Event listener: Jika jam mulai digeser melampaui jam selesai, otomatis sesuaikan jam selesai
    startSelect.addEventListener('change', function () {
        if (parseInt(endSelect.value, 10) < parseInt(startSelect.value, 10)) {
            endSelect.value = startSelect.value;
        }
        updatePreview();
    });

    // Event listener: Perbarui preview saat jam selesai diganti
    endSelect.addEventListener('change', updatePreview);
    updatePreview();
});
</script>
@endsection
