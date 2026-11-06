
@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Jadwal Mengajar</h1>
        <p class="page-subtitle">
            Putaran: <strong>Putaran {{ $targetCycle?->cycle ?? $schedule->blockCycle?->cycle ?? '-' }}</strong>
            ({{ $targetCycle?->start_date?->format('d-m-Y') ?? $schedule->blockCycle?->start_date?->format('d-m-Y') ?? '-' }}
            s.d. {{ $targetCycle?->end_date?->format('d-m-Y') ?? $schedule->blockCycle?->end_date?->format('d-m-Y') ?? '-' }})
        </p>
    </div>
</div>

<div class="card" style="max-width: 640px;">
    <form action="{{ route('schedules.update', $schedule->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="teacher_id">Guru Pengajar</label>
            <select name="teacher_id" id="teacher_id" required>
                <option value="">-- Pilih Guru Pengajar --</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}"
                        {{ old('teacher_id', $schedule->teacher_id) == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>
            @error('teacher_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="subject_id">Mata Pelajaran</label>
            <select name="subject_id" id="subject_id" required>
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}"
                        {{ old('subject_id', $schedule->subject_id) == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
            @error('subject_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="class_id">Kelas</label>
            <select name="class_id" id="class_id" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}"
                        {{ old('class_id', $schedule->class_id) == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
            @error('class_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="room_id">Ruangan</label>
            <select name="room_id" id="room_id" required>
                <option value="">-- Pilih Ruangan --</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}"
                        {{ old('room_id', $schedule->room_id) == $room->id ? 'selected' : '' }}>
                        {{ $room->name }}
                    </option>
                @endforeach
            </select>
            @error('room_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="day">Hari</label>
            <select name="day" id="day" required>
                <option value="">-- Pilih Hari --</option>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    <option value="{{ $day }}"
                        {{ old('day', $schedule->day) == $day ? 'selected' : '' }}>
                        {{ $day }}
                    </option>
                @endforeach
            </select>
            @error('day')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="start_period">Mulai dari Jam Pelajaran <span style="color: var(--danger);">*</span></label>
                <select name="start_period" id="start_period" required>
                    <option value="">-- Pilih Jam Mulai --</option>
                    @foreach($periods as $num => $p)
                        <option value="{{ $num }}" data-start="{{ $p['start'] }}" data-end="{{ $p['end'] }}"
                            {{ old('start_period', $schedule->start_period ?? 1) == $num ? 'selected' : '' }}>
                            {{ $p['label'] }} ({{ $p['start'] }})
                        </option>
                    @endforeach
                </select>
                @error('start_period')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="end_period">Sampai Jam Pelajaran <span style="color: var(--danger);">*</span></label>
                <select name="end_period" id="end_period" required>
                    <option value="">-- Pilih Jam Selesai --</option>
                    @foreach($periods as $num => $p)
                        <option value="{{ $num }}" data-start="{{ $p['start'] }}" data-end="{{ $p['end'] }}"
                            {{ old('end_period', $schedule->end_period ?? 2) == $num ? 'selected' : '' }}>
                            {{ $p['label'] }} ({{ $p['end'] }})
                        </option>
                    @endforeach
                </select>
                @error('end_period')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        {{-- Preview Jam Sesi Pelajaran --}}
        <div id="period-preview" style="background: rgba(37, 99, 235, 0.08); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #2563eb; letter-spacing: 0.5px;">Estimasi Rentang Waktu</span>
                    <div id="preview-text" style="font-weight: 600; font-size: 0.95rem; color: #1e293b; margin-top: 2px;">
                        {{ $schedule->session_label }}
                    </div>
                </div>
                <span id="preview-badge" class="badge badge-info" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                    {{ ($schedule->start_period && $schedule->end_period) ? (($schedule->end_period - $schedule->start_period + 1) . ' Jam Pelajaran') : '-' }}
                </span>
            </div>
        </div>

        @error('schedule')
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @enderror

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>

            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
window.allLessonPeriods = @json($allLessonPeriods);

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
        const optStart = document.createElement('option');
        optStart.value = num;
        optStart.setAttribute('data-start', p.start);
        optStart.setAttribute('data-end', p.end);
        optStart.textContent = `${p.label} (${p.start})`;
        if (num === Math.min(currentStartVal, maxPeriod)) {
            optStart.selected = true;
        }
        startSelect.appendChild(optStart);

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

document.addEventListener('DOMContentLoaded', function () {
    const daySelect = document.getElementById('day');
    const startSelect = document.getElementById('start_period');
    const endSelect = document.getElementById('end_period');
    const previewText = document.getElementById('preview-text');
    const previewBadge = document.getElementById('preview-badge');
    const previewBox = document.getElementById('period-preview');

    function updatePreview() {
        const startOpt = startSelect.options[startSelect.selectedIndex];
        const endOpt = endSelect.options[endSelect.selectedIndex];

        if (!startOpt || !endOpt || !startOpt.value || !endOpt.value) {
            previewText.textContent = 'Silakan pilih jam mulai dan jam selesai.';
            previewBadge.textContent = '-';
            return;
        }

        const startPeriod = parseInt(startOpt.value, 10);
        const endPeriod = parseInt(endOpt.value, 10);
        const startTime = startOpt.getAttribute('data-start');
        const endTime = endOpt.getAttribute('data-end');

        if (endPeriod < startPeriod) {
            previewBox.style.background = 'rgba(239, 68, 68, 0.08)';
            previewBox.style.borderColor = 'rgba(239, 68, 68, 0.25)';
            previewText.textContent = 'Peringatan: Jam selesai harus sama atau setelah jam mulai!';
            previewBadge.className = 'badge badge-danger';
            previewBadge.textContent = 'Tidak Valid';
            return;
        }

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

    if (daySelect) {
        daySelect.addEventListener('change', function() {
            const day = this.value || 'Senin';
            populatePeriods(day);
            updatePreview();
        });

        const initDay = daySelect.value || 'Senin';
        populatePeriods(initDay, {{ old('start_period', $schedule->start_period ?? 1) }}, {{ old('end_period', $schedule->end_period ?? 2) }});
    }

    startSelect.addEventListener('change', function () {
        if (parseInt(endSelect.value, 10) < parseInt(startSelect.value, 10)) {
            endSelect.value = startSelect.value;
        }
        updatePreview();
    });

    endSelect.addEventListener('change', updatePreview);
    updatePreview();
});
</script>
@endsection
