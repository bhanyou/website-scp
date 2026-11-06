@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Input Jurnal & Presensi Siswa</h1>
        <p class="page-subtitle">Pencatatan materi kegiatan belajar mengajar dan absensi kelas</p>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    {{-- Informasi Jadwal --}}
    <div class="card" style="margin-bottom: 0;">
        <h2 class="card-title">Informasi Pembelajaran</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.875rem;">
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Guru</span>
                <p><strong>{{ $schedule->teacher?->name ?? '-' }}</strong></p>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Putaran Blok</span>
                <p><span class="badge badge-info">{{ $schedule->blockCycle?->cycle ?? '-' }}</span></p>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Mata Pelajaran</span>
                <p style="color: var(--accent); font-weight: 600;">{{ $schedule->subject?->name ?? '-' }}</p>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Kelas & Ruangan</span>
                <p>{{ $schedule->schoolClass?->name ?? '-' }} • {{ $schedule->room?->name ?? '-' }}</p>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Waktu</span>
                <p>{{ $schedule->day }}, {{ $schedule->session_label }}</p>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('journals.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

    {{-- Form Isi Jurnal --}}
    <div class="card">
        <h2 class="card-title">Rincian Jurnal Guru</h2>

        <div class="form-group" style="max-width: 300px;">
            <label for="journal_date">Tanggal Jurnal (Hari Ini)</label>
            <input type="date" id="journal_date" name="journal_date" value="{{ date('Y-m-d') }}" readonly style="background-color: var(--bg-surface-hover); cursor: not-allowed;" required>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Jurnal hanya dapat diisi pada hari jadwal berlangsung (hari ini).</small>
        </div>

        <div class="form-group">
            <label for="material">Materi Pembelajaran</label>
            <textarea id="material" name="material" rows="3" placeholder="Tuliskan pokok materi yang diajarkan hari ini..." required>{{ old('material') }}</textarea>
        </div>

        <div class="form-group">
            <label for="activity">Kegiatan Pembelajaran</label>
            <textarea id="activity" name="activity" rows="3" placeholder="Deskripsikan proses dan aktivitas siswa di kelas..." required>{{ old('activity') }}</textarea>
        </div>

        <div class="form-group">
            <label for="notes">Catatan Tambahan (Opsional)</label>
            <textarea id="notes" name="notes" rows="2" placeholder="Catatan evaluasi atau kendala di kelas...">{{ old('notes') }}</textarea>
        </div>

        {{-- Foto Realtime Kamera Kegiatan Kelas --}}
        <x-realtime-camera-input
            inputName="photo"
            inputId="photo"
            label="Foto Bukti Kegiatan Kelas"
            helpText="Ambil foto dokumentasi kegiatan kelas langsung melalui kamera secara realtime (Opsional)."
            :required="false"
            accept=".jpg,.jpeg,.png"
        />
    </div>

    {{-- Presensi Siswa --}}
    <div class="card">
        <div class="card-title">
            <span>Presensi Kehadiran Siswa</span>
            <span class="badge badge-info">{{ $students->count() }} Siswa</span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 120px;">NIS</th>
                        <th>Nama Siswa</th>
                        <th style="width: 160px;">Status Kehadiran</th>
                        <th style="width: 280px;">Catatan Izin / Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $approved = $approvedPermissions->get($student->id);
                            $defaultStatus = $approved ? ($approved->type === 'Sakit' ? 'Sakit' : 'Izin') : 'Hadir';
                            $currentStatus = old('attendance.' . $student->id, $defaultStatus);
                            $defaultNote = $approved ? 'Izin disetujui: ' . ($approved->reason ?? $approved->type) : '';
                            $currentNote = old('attendance_notes.' . $student->id, $defaultNote);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $student->nis }}</td>
                            <td>
                                <strong>{{ $student->name }}</strong>
                                @if($approved)
                                    <span class="badge badge-warning" style="margin-left: 0.5rem; font-size: 0.75rem;" title="Alasan: {{ $approved->reason }}">
                                        Izin Resmi: {{ $approved->type }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <select name="attendance[{{ $student->id }}]" id="status_{{ $student->id }}" onchange="handleStatusChange('{{ $student->id }}')" required style="padding: 0.35rem 0.65rem; width: 100%;">
                                    <option value="Hadir" {{ $currentStatus == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                    <option value="Sakit" {{ $currentStatus == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="Izin" {{ $currentStatus == 'Izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="Alpa" {{ $currentStatus == 'Alpa' ? 'selected' : '' }}>Alpa</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" 
                                    name="attendance_notes[{{ $student->id }}]" 
                                    id="note_{{ $student->id }}" 
                                    value="{{ $currentNote }}" 
                                    placeholder="{{ $currentStatus == 'Izin' ? 'Keterangan izin karena apa...' : 'Catatan opsional...' }}" 
                                    style="padding: 0.35rem 0.65rem; font-size: 0.85rem; width: 100%; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background-color: {{ $currentStatus == 'Izin' ? '#f0fdf4' : 'var(--bg-surface)' }};">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                Belum ada siswa pada rombel kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <script>
            function handleStatusChange(studentId) {
                const select = document.getElementById('status_' + studentId);
                const noteInput = document.getElementById('note_' + studentId);
                if (!select || !noteInput) return;

                if (select.value === 'Izin') {
                    noteInput.placeholder = 'Keterangan izin karena apa (mis: acara keluarga, dsb)...';
                    noteInput.style.backgroundColor = '#f0fdf4';
                    noteInput.style.borderColor = '#86efac';
                    noteInput.focus();
                } else if (select.value === 'Sakit') {
                    noteInput.placeholder = 'Keterangan sakit (mis: demam, flu, rawat jalan)...';
                    noteInput.style.backgroundColor = '#fffbeb';
                    noteInput.style.borderColor = '#fde68a';
                } else {
                    noteInput.placeholder = 'Catatan opsional...';
                    noteInput.style.backgroundColor = 'var(--bg-surface)';
                    noteInput.style.borderColor = 'var(--border-color)';
                }
            }
        </script>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Jurnal & Presensi
            </button>
            <a href="{{ route('journals.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </div>
</form>
@endsection