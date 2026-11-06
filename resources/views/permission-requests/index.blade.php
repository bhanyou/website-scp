@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Riwayat Izin / Sakit Siswa</h1>
        <p class="page-subtitle">Daftar permohonan dispensasi dan surat izin siswa</p>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Rentang Tanggal</th>
                    <th>Durasi</th>
                    <th>Jenis</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $request->student?->name ?? '-' }}</strong></td>
                        <td>
                            @if($request->end_date && $request->end_date->ne($request->permission_date))
                                <span>{{ $request->permission_date?->format('d/m/Y') }} &ndash; {{ $request->end_date?->format('d/m/Y') }}</span>
                            @else
                                <span>{{ $request->permission_date?->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-weight: 600;">
                                {{ $request->duration_days }} Hari
                            </span>
                        </td>
                        <td>
                            @if($request->type === 'Sakit')
                                <span class="badge badge-warning">Sakit</span>
                            @else
                                <span class="badge badge-info">Izin</span>
                            @endif
                        </td>
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $request->reason }}
                        </td>
                        <td>
                            @if(strtolower($request->status) === 'approved' || strtolower($request->status) === 'disetujui')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif(strtolower($request->status) === 'rejected' || strtolower($request->status) === 'ditolak')
                                <span class="badge badge-danger">Ditolak</span>
                            @else
                                <span class="badge badge-warning">Menunggu</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('permission-requests.show', $request->id) }}" class="btn btn-secondary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            Belum ada riwayat pengajuan izin yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1rem;">
        <a href="{{ route('dashboard.siswa') }}" class="btn-back">
            &larr; Kembali ke Dashboard Siswa
        </a>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: PENGAJUAN IZIN (TIDAK BERPINDAH)          --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalCreatePermission">
    <div class="modal-card">
        <div class="modal-header">
            <div class="modal-header-info">
                <div>
                    <h3 class="modal-title">Formulir Pengajuan Izin Siswa</h3>
                    <p class="modal-subtitle">Isi data permohonan izin atau surat sakit secara daring</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalCreatePermission')">&times;</button>
        </div>

        <form action="{{ route('permission-requests.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            @if(isset($student))
                <input type="hidden" name="student_id" value="{{ $student->id }}">
            @elseif(auth()->user()->student)
                <input type="hidden" name="student_id" value="{{ auth()->user()->student->id }}">
            @endif

            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label for="perm_date">Tanggal Mulai <span style="color: var(--danger);">*</span></label>
                        <input type="date" name="permission_date" id="perm_date" value="{{ old('permission_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="perm_end_date">Tanggal Selesai <span style="color: var(--danger);">*</span></label>
                        <input type="date" name="end_date" id="perm_end_date" value="{{ old('end_date', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <div id="modal_duration_box" style="margin-top: -0.25rem; margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: rgba(99, 102, 241, 0.08); border-left: 3px solid #6366f1; border-radius: 4px; font-size: 0.825rem; display: flex; align-items: center; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Durasi Ketidakhadiran:</span>
                    <strong id="modal_duration_label" style="color: #6366f1;">1 Hari</strong>
                </div>

                <div class="form-group">
                    <label for="perm_type">Jenis Keterangan <span style="color: var(--danger);">*</span></label>
                    <select name="type" id="perm_type" required>
                        <option value="Izin" {{ old('type') == 'Izin' ? 'selected' : '' }}>Izin (Urusan Keluarga/Dinas)</option>
                        <option value="Sakit" {{ old('type') == 'Sakit' ? 'selected' : '' }}>Sakit (Kondisi Kesehatan)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="perm_reason">Alasan Lengkap <span style="color: var(--danger);">*</span></label>
                    <textarea name="reason" id="perm_reason" placeholder="Jelaskan alasan izin secara jelas dan sopan..." required>{{ old('reason') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="perm_document">Unggah Surat / Bukti Foto <span style="color: var(--danger);">*</span></label>
                    <input type="file" name="document" id="perm_document" accept=".jpg,.jpeg,.png,.pdf" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                        Format: JPG, PNG, atau PDF (Surat dokter / surat dari orang tua)
                    </small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreatePermission')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Kirim Pengajuan Izin
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mStart = document.getElementById('perm_date');
        const mEnd = document.getElementById('perm_end_date');
        const mLabel = document.getElementById('modal_duration_label');

        if (mStart && mEnd && mLabel) {
            function updateModalDuration() {
                if (!mStart.value) return;
                mEnd.min = mStart.value;

                if (!mEnd.value) {
                    mEnd.value = mStart.value;
                }

                const s = new Date(mStart.value + 'T00:00:00');
                const e = new Date(mEnd.value + 'T00:00:00');

                if (isNaN(s.getTime()) || isNaN(e.getTime())) {
                    mLabel.textContent = '-';
                    return;
                }

                const diff = Math.round((e.getTime() - s.getTime()) / (1000 * 3600 * 24)) + 1;
                if (diff < 1) {
                    mLabel.innerHTML = '<span style="color: var(--danger, #ef4444);">Tanggal tidak valid</span>';
                } else {
                    mLabel.textContent = diff + ' Hari';
                }
            }

            mStart.addEventListener('change', function () {
                if (!mEnd.value || mEnd.value < mStart.value) {
                    mEnd.value = mStart.value;
                }
                updateModalDuration();
            });

            mEnd.addEventListener('change', updateModalDuration);
            mEnd.addEventListener('input', updateModalDuration);

            updateModalDuration();
        }
    });
</script>
@endpush
@endsection