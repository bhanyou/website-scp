@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Jurnal dan Presensi Siswa</h1>
        <p class="page-subtitle">Perbarui materi kegiatan pembelajaran atau perbaiki status absensi kehadiran siswa kelas</p>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        {{ session('error') }}
    </div>
@endif

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
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Kelas dan Ruangan</span>
                <p>{{ $schedule->schoolClass?->name ?? '-' }} - {{ $schedule->room?->name ?? '-' }}</p>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Waktu</span>
                <p>{{ $schedule->day }}, {{ $journal->journal_date ? $journal->journal_date->format('d/m/Y') : '-' }} ({{ $schedule->start_time ? substr($schedule->start_time, 0, 5) : '-' }} - {{ $schedule->end_time ? substr($schedule->end_time, 0, 5) : '-' }})</p>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('journals.update', $journal->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Form Isi Jurnal --}}
    <div class="card">
        <h2 class="card-title">Rincian Jurnal Guru</h2>

        <div class="form-group" style="max-width: 300px;">
            <label for="journal_date">Tanggal Jurnal</label>
            <input type="text" id="journal_date" value="{{ $journal->journal_date ? $journal->journal_date->format('d/m/Y') : '-' }}" readonly style="background-color: var(--bg-surface-hover); cursor: not-allowed;" class="form-control">
        </div>

        <div class="form-group">
            <label for="material">Materi Pembelajaran</label>
            <textarea id="material" name="material" rows="3" placeholder="Tuliskan pokok materi yang diajarkan..." required class="form-control">{{ old('material', $journal->material) }}</textarea>
        </div>

        <div class="form-group">
            <label for="activity">Kegiatan Pembelajaran</label>
            <textarea id="activity" name="activity" rows="3" placeholder="Deskripsikan proses dan aktivitas siswa di kelas..." required class="form-control">{{ old('activity', $journal->activity) }}</textarea>
        </div>

        <div class="form-group">
            <label for="notes">Catatan Tambahan (Opsional)</label>
            <textarea id="notes" name="notes" rows="2" placeholder="Catatan evaluasi, kejadian memotong jam pelajaran, izin dispensasi, dsb..." class="form-control">{{ old('notes', $journal->notes) }}</textarea>
        </div>

        @if($journal->photo_path)
            <div class="form-group">
                <label>Foto Dokumentasi Saat Ini</label>
                <div style="margin-top: 0.5rem; margin-bottom: 0.75rem;">
                    <img src="{{ asset('storage/' . $journal->photo_path) }}" alt="Foto Pembelajaran" style="max-height: 180px; border-radius: 8px; border: 1px solid var(--border-color);">
                </div>
            </div>
        @endif

        <div class="form-group">
            <label for="photo">Ganti Foto Bukti Pembelajaran (Opsional)</label>
            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png" class="form-control">
            <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                Format JPG atau PNG, maksimal 2MB. Kosongkan jika tidak ingin mengganti foto.
            </small>
        </div>
    </div>

    {{-- Presensi Siswa --}}
    <div class="card">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
            <span>Presensi Kehadiran Siswa</span>
            <span class="badge badge-info">{{ $students->count() }} Siswa</span>
        </div>

        <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 1rem;">
            Perbarui status kehadiran siswa di bawah ini jika terdapat perubahan (misal siswa datang terlambat, izin dispensasi memotong jam pelajaran, atau sakit).
        </p>

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
                            $attendance = $attendances->get($student->id);
                            $approved = $approvedPermissions->get($student->id);
                            $savedStatus = $attendance ? $attendance->status : ($approved ? ($approved->type === 'Sakit' ? 'Sakit' : 'Izin') : 'Hadir');
                            $currentStatus = old('attendance.' . $student->id, $savedStatus);
                            $savedNote = $attendance ? $attendance->note : ($approved ? 'Izin disetujui: ' . ($approved->reason ?? $approved->type) : '');
                            $currentNote = old('attendance_notes.' . $student->id, $savedNote);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $student->nis }}</td>
                            <td>
                                <strong>{{ $student->name }}</strong>
                                @if($approved)
                                    <span class="badge badge-warning" style="margin-left: 0.5rem; font-size: 0.75rem;">
                                        Izin Resmi: {{ $approved->type }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <select name="attendance[{{ $student->id }}]" id="status_{{ $student->id }}" onchange="handleStatusChange('{{ $student->id }}')" required style="padding: 0.35rem 0.65rem; width: 100%; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
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
                                    placeholder="{{ $currentStatus == 'Izin' ? 'Keterangan izin...' : 'Catatan opsional...' }}" 
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
                    noteInput.placeholder = 'Keterangan izin (misal: tugas OSIS, keperluan keluarga)...';
                    noteInput.style.backgroundColor = '#f0fdf4';
                    noteInput.style.borderColor = '#86efac';
                } else if (select.value === 'Sakit') {
                    noteInput.placeholder = 'Keterangan sakit (misal: UKS, demam, pulang sakit)...';
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
                Simpan Perubahan
            </button>
            <a href="{{ route('journals.show', $journal->id) }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </div>
</form>
@endsection
