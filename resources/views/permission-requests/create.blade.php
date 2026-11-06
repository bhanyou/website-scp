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
                <input type="text" value="{{ $student->name }}" readonly style="background-color: var(--bg-surface-hover);">
            @else
                <select name="student_id" id="student_id" required>
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($students as $item)
                        <option value="{{ $item->id }}" {{ old('student_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} @if($item->nis) {{ $item->nis }} @endif
                        </option>
                    @endforeach
                </select>
            @endisset
        </div>

        {{-- Tanggal Mulai & Tanggal Selesai Izin --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="permission_date">Tanggal Mulai Izin <span style="color: var(--danger, #ef4444); font-size: 0.8rem;">*</span></label>
                <input type="date" name="permission_date" id="permission_date" value="{{ old('permission_date', now()->toDateString()) }}" required>
                @error('permission_date')
                    <span style="color: var(--danger, #ef4444); font-size: 0.75rem;">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="end_date">Tanggal Selesai Izin <span style="color: var(--danger, #ef4444); font-size: 0.8rem;">*</span></label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', now()->toDateString()) }}" required>
                @error('end_date')
                    <span style="color: var(--danger, #ef4444); font-size: 0.75rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div id="duration_box" style="margin-top: -0.35rem; margin-bottom: 1.25rem; padding: 0.65rem 0.85rem; background: rgba(99, 102, 241, 0.08); border-left: 3px solid #6366f1; border-radius: 6px; font-size: 0.85rem; display: flex; align-items: center; justify-content: space-between;">
            <span style="color: var(--text-muted);">
                <i class="fas fa-calendar-alt" style="margin-right: 0.35rem; color: #6366f1;"></i>
                Durasi Ketidakhadiran:
            </span>
            <strong id="duration_label" style="color: #6366f1; font-size: 0.9rem;">1 Hari</strong>
        </div>

        {{-- Jenis --}}
        <div class="form-group">
            <label for="type">Jenis Pengajuan</label>
            <select name="type" id="type" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="Izin" {{ old('type') === 'Izin' ? 'selected' : '' }}>Izin</option>
                <option value="Sakit" {{ old('type') === 'Sakit' ? 'selected' : '' }}>Sakit</option>
            </select>
        </div>

        {{-- Alasan --}}
        <div class="form-group">
            <label for="reason">Alasan Lengkap</label>
            <textarea name="reason" id="reason" rows="4" placeholder="Tuliskan alasan izin/sakit dengan jelas..." required>{{ old('reason') }}</textarea>
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
    document.addEventListener('DOMContentLoaded', function () {
        const startDateInput = document.getElementById('permission_date');
        const endDateInput = document.getElementById('end_date');
        const durationLabel = document.getElementById('duration_label');

        function updateDuration() {
            if (!startDateInput.value) return;

            // Pastikan tanggal selesai tidak boleh sebelum tanggal mulai
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

        startDateInput.addEventListener('change', function () {
            if (!endDateInput.value || endDateInput.value < startDateInput.value) {
                endDateInput.value = startDateInput.value;
            }
            updateDuration();
        });

        endDateInput.addEventListener('change', updateDuration);
        endDateInput.addEventListener('input', updateDuration);

        updateDuration();
    });
</script>
@endpush
@endsection