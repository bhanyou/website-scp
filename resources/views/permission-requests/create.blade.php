@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Ajukan Izin / Sakit</h1>
        <p class="page-subtitle">Isi data pengajuan dispensasi atau surat keterangan sakit siswa</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <form action="{{ route('permission-requests.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Siswa --}}
        <div class="form-group">
            <label for="student_id">Siswa</label>
            @isset($student)
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="text" value="{{ $student->name }}" readonly style="background-color: var(--bg-surface-hover);" class="form-control">
            @else
                <select name="student_id" id="student_id" required class="form-control">
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($students as $item)
                        <option value="{{ $item->id }}" {{ old('student_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} @if($item->nis) {{ $item->nis }} @endif
                        </option>
                    @endforeach
                </select>
            @endisset
        </div>

        {{-- Cakupan Waktu Izin: Seharian Penuh vs Memotong Jam --}}
        <div class="form-group" style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 700; margin-bottom: 0.5rem;">Cakupan Waktu Izin</label>
            <div style="display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 0.45rem; font-weight: normal; cursor: pointer;">
                    <input type="radio" name="time_scope" value="full_day" {{ old('time_scope', 'full_day') === 'full_day' ? 'checked' : '' }} onchange="toggleTimeScope()">
                    <span>Seharian Penuh (1 Hari atau Lebih)</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 0.45rem; font-weight: normal; cursor: pointer;">
                    <input type="radio" name="time_scope" value="partial_day" {{ old('time_scope') === 'partial_day' ? 'checked' : '' }} onchange="toggleTimeScope()">
                    <span>Sebagian Jam Pelajaran (Memotong Jam)</span>
                </label>
            </div>
        </div>

        {{-- Tanggal Mulai & Tanggal Selesai Izin --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" id="date_inputs_wrapper">
            <div class="form-group">
                <label for="permission_date" id="permission_date_label">Tanggal Izin <span style="color: var(--danger, #ef4444); font-size: 0.8rem;">*</span></label>
                <input type="date" name="permission_date" id="permission_date" value="{{ old('permission_date', now()->toDateString()) }}" required class="form-control">
                @error('permission_date')
                    <span style="color: var(--danger, #ef4444); font-size: 0.75rem;">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group" id="end_date_group">
                <label for="end_date">Tanggal Selesai Izin <span style="color: var(--danger, #ef4444); font-size: 0.8rem;">*</span></label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', now()->toDateString()) }}" class="form-control">
                @error('end_date')
                    <span style="color: var(--danger, #ef4444); font-size: 0.75rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Pilihan Jam Pelajaran (Khusus jika memotong jam) --}}
        <div id="partial_hour_box" style="display: {{ old('time_scope') === 'partial_day' ? 'grid' : 'none' }}; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; padding: 1rem; background: var(--bg-surface-hover); border-radius: 8px; border: 1px solid var(--border-color);">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="start_hour">Dari Jam Pelajaran Ke</label>
                <select name="start_hour" id="start_hour" class="form-control" style="width: 100%;">
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ old('start_hour', 1) == $i ? 'selected' : '' }}>Jam ke-{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="end_hour">Sampai Jam Pelajaran Ke</label>
                <select name="end_hour" id="end_hour" class="form-control" style="width: 100%;">
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ old('end_hour', 5) == $i ? 'selected' : '' }}>Jam ke-{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div style="grid-column: 1 / -1; font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">
                Catatan: Izin memotong jam pelajaran hanya berlaku pada tanggal yang dipilih. Jam pelajaran sebelum atau sesudahnya tetap tercatat masuk kelas.
            </div>
        </div>

        <div id="duration_box" style="margin-top: -0.35rem; margin-bottom: 1.25rem; padding: 0.65rem 0.85rem; background: rgba(99, 102, 241, 0.08); border-left: 3px solid #6366f1; border-radius: 6px; font-size: 0.85rem; display: flex; align-items: center; justify-content: space-between;">
            <span style="color: var(--text-muted);">
                Durasi Ketidakhadiran:
            </span>
            <strong id="duration_label" style="color: #6366f1; font-size: 0.9rem;">1 Hari</strong>
        </div>

        {{-- Jenis --}}
        <div class="form-group">
            <label for="type">Jenis Pengajuan</label>
            <select name="type" id="type" required class="form-control">
                <option value="">-- Pilih Jenis --</option>
                <option value="Izin" {{ old('type', 'Izin') === 'Izin' ? 'selected' : '' }}>Izin</option>
                <option value="Sakit" {{ old('type') === 'Sakit' ? 'selected' : '' }}>Sakit</option>
            </select>
        </div>

        {{-- Alasan --}}
        <div class="form-group">
            <label for="reason">Alasan Lengkap</label>
            <textarea name="reason" id="reason" rows="4" placeholder="Tuliskan alasan izin/sakit dengan jelas (contoh: Rapat koordinasi OSIS di ruang pertemuan)..." required class="form-control">{{ old('reason') }}</textarea>
        </div>

        {{-- Unggah Surat Dokter / Bukti Pendukung --}}
        <x-realtime-camera-input
            inputName="document"
            inputId="document"
            label="Unggah Surat Dokter / Bukti Pendukung"
            helpText="Ambil foto surat dokter/keterangan langsung melalui kamera HP/laptop secara realtime (Opsional)."
            :required="false"
            accept=".jpg,.jpeg,.png,.pdf"
        />

        {{-- Tombol --}}
        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Kirim Pengajuan
            </button>
            <a href="{{ route('permission-requests.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function toggleTimeScope() {
        const isPartial = document.querySelector('input[name="time_scope"]:checked').value === 'partial_day';
        const partialBox = document.getElementById('partial_hour_box');
        const endDateGroup = document.getElementById('end_date_group');
        const dateWrapper = document.getElementById('date_inputs_wrapper');
        const permDateLabel = document.getElementById('permission_date_label');
        const startDateInput = document.getElementById('permission_date');
        const endDateInput = document.getElementById('end_date');
        const startHour = document.getElementById('start_hour');
        const endHour = document.getElementById('end_hour');
        const durationLabel = document.getElementById('duration_label');

        if (isPartial) {
            partialBox.style.display = 'grid';
            endDateGroup.style.display = 'none';
            dateWrapper.style.gridTemplateColumns = '1fr';
            permDateLabel.innerHTML = 'Tanggal Pelaksanaan Izin <span style="color: var(--danger, #ef4444); font-size: 0.8rem;">*</span>';
            endDateInput.value = startDateInput.value;
            durationLabel.innerHTML = '<span style="color: #6366f1; font-weight: 600;">Jam ke-' + startHour.value + ' s.d. Jam ke-' + endHour.value + '</span>';
        } else {
            partialBox.style.display = 'none';
            endDateGroup.style.display = 'block';
            dateWrapper.style.gridTemplateColumns = '1fr 1fr';
            permDateLabel.innerHTML = 'Tanggal Mulai Izin <span style="color: var(--danger, #ef4444); font-size: 0.8rem;">*</span>';
            updateFullDayDuration();
        }
    }

    function updateFullDayDuration() {
        const startDateInput = document.getElementById('permission_date');
        const endDateInput = document.getElementById('end_date');
        const durationLabel = document.getElementById('duration_label');

        if (!startDateInput.value) return;

        endDateInput.min = startDateInput.value;
        if (!endDateInput.value) {
            endDateInput.value = startDateInput.value;
        }

        const start = new Date(startDateInput.value + 'T00:00:00');
        const end = new Date(endDateInput.value + 'T00:00:00');

        if (isNaN(start.getTime()) || isNaN(end.getTime())) {
            durationLabel.textContent = '-';
            return;
        }

        const diffTime = end.getTime() - start.getTime();
        const diffDays = Math.round(diffTime / (1000 * 3600 * 24)) + 1;

        if (diffDays < 1) {
            durationLabel.innerHTML = '<span style="color: var(--danger, #ef4444); font-weight: 600;">Tanggal selesai tidak boleh lebih awal dari tanggal mulai</span>';
        } else if (diffDays === 1) {
            durationLabel.innerHTML = '<span style="color: #6366f1; font-weight: 600;">1 Hari</span>';
        } else {
            durationLabel.innerHTML = '<span style="color: #6366f1; font-weight: 600;">' + diffDays + ' Hari</span>';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const startDateInput = document.getElementById('permission_date');
        const endDateInput = document.getElementById('end_date');
        const startHour = document.getElementById('start_hour');
        const endHour = document.getElementById('end_hour');

        startDateInput.addEventListener('change', function () {
            const isPartial = document.querySelector('input[name="time_scope"]:checked').value === 'partial_day';
            if (isPartial) {
                endDateInput.value = startDateInput.value;
            } else {
                if (!endDateInput.value || endDateInput.value < startDateInput.value) {
                    endDateInput.value = startDateInput.value;
                }
                updateFullDayDuration();
            }
        });

        endDateInput.addEventListener('change', updateFullDayDuration);
        endDateInput.addEventListener('input', updateFullDayDuration);

        startHour.addEventListener('change', function () {
            if (parseInt(endHour.value) < parseInt(startHour.value)) {
                endHour.value = startHour.value;
            }
            toggleTimeScope();
        });

        endHour.addEventListener('change', function () {
            if (parseInt(endHour.value) < parseInt(startHour.value)) {
                startHour.value = endHour.value;
            }
            toggleTimeScope();
        });

        toggleTimeScope();
    });
</script>
@endpush
@endsection
