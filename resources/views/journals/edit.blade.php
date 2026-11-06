@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Jurnal & Presensi Siswa</h1>
        <p class="page-subtitle">Perbarui catatan kegiatan pembelajaran dan status absensi siswa</p>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    {{-- Informasi Jadwal --}}
    <div class="card" style="margin-bottom: 0;">
        <h2 class="card-title">Informasi Pembelajaran</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.875rem;">
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Guru</span>
                <p><strong>{{ $journal->schedule?->teacher?->name ?? '-' }}</strong></p>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Putaran Blok</span>
                <p><span class="badge badge-info">{{ $journal->schedule?->blockCycle?->cycle ?? '-' }}</span></p>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Mata Pelajaran</span>
                <p style="color: var(--accent); font-weight: 600;">{{ $journal->schedule?->subject?->name ?? '-' }}</p>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Kelas & Ruang</span>
                <p>{{ $journal->schedule?->schoolClass?->name ?? '-' }} • {{ $journal->schedule?->room?->name ?? '-' }}</p>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Tanggal Jurnal</span>
                <p><strong>{{ $journal->journal_date?->format('d-m-Y') ?? '-' }}</strong></p>
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

        <div class="form-group">
            <label for="material">Materi Pembelajaran</label>
            <textarea id="material" name="material" rows="3" required>{{ old('material', $journal->material) }}</textarea>
        </div>

        <div class="form-group">
            <label for="activity">Kegiatan Pembelajaran</label>
            <textarea id="activity" name="activity" rows="3" required>{{ old('activity', $journal->activity) }}</textarea>
        </div>

        <div class="form-group">
            <label for="notes">Catatan Tambahan</label>
            <textarea id="notes" name="notes" rows="2">{{ old('notes', $journal->notes) }}</textarea>
        </div>

        {{-- Foto Realtime Kamera Kegiatan Kelas --}}
        <x-realtime-camera-input
            inputName="photo"
            inputId="photo"
            label="Ganti Foto Bukti Pembelajaran"
            helpText="Ambil foto pembaruan kegiatan kelas secara langsung atau unggah berkas baru (Opsional)."
            :required="false"
            accept=".jpg,.jpeg,.png"
            :currentPhoto="$journal->photo_path"
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
                        <th style="width: 60px;">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th style="width: 220px;">Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $attendance = $attendances->get($student->id);
                            $approved = $approvedPermissions->get($student->id);
                            
                            $defaultStatus = $approved ? ($approved->type === 'Sakit' ? 'Sakit' : 'Izin') : ($attendance?->status ?? 'Hadir');
                            $currentStatus = old('attendance.' . $student->id, $attendance?->status ?? $defaultStatus);
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
                                <select name="attendance[{{ $student->id }}]" required style="padding: 0.35rem 0.65rem;">
                                    <option value="Hadir" {{ $currentStatus == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                    <option value="Sakit" {{ $currentStatus == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="Izin" {{ $currentStatus == 'Izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="Alpa" {{ $currentStatus == 'Alpa' ? 'selected' : '' }}>Alpa</option>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                Belum ada siswa pada rombel kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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