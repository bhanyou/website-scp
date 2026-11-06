@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Monitoring Izin & Sakit Siswa</h1>
        <p class="page-subtitle">Pantau seluruh permohonan izin siswa sekolah yang diverifikasi oleh masing-masing Wali Kelas</p>
    </div>
    <div>
        <a href="{{ auth()->user()->role === 'waka' ? route('dashboard.waka') : route('dashboard.guru') }}" class="btn-back">
            ← Kembali ke Dashboard
        </a>
    </div>
</div>

<style>
    .monitoring-table {
        width: 100%;
        table-layout: auto;
    }
    .monitoring-table th {
        padding: 0.65rem 0.75rem;
        font-size: 0.75rem;
    }
    .monitoring-table td {
        padding: 0.65rem 0.75rem;
    }
</style>

{{-- Form Filter Monitoring --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.15rem 1.25rem;">
    <form action="{{ route('permission-requests.verification') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        {{-- Status Verifikasi --}}
        <div style="flex: 1; min-width: 140px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Status
            </label>
            <select name="status" class="form-control" style="width: 100%;">
                <option value="">Semua Status</option>
                <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        {{-- Jenis Pengajuan --}}
        <div style="flex: 1; min-width: 120px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Kategori
            </label>
            <select name="type" class="form-control" style="width: 100%;">
                <option value="">Semua</option>
                <option value="Sakit" {{ request('type') === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                <option value="Izin" {{ request('type') === 'Izin' ? 'selected' : '' }}>Izin</option>
            </select>
        </div>

        {{-- Tingkat Kelas --}}
        <div style="flex: 1; min-width: 130px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Tingkat
            </label>
            <select name="grade" class="form-control" style="width: 100%;">
                <option value="">Semua Tingkat</option>
                <option value="X" {{ strtoupper(request('grade')) === 'X' || request('grade') === '10' ? 'selected' : '' }}>Kelas X (10)</option>
                <option value="XI" {{ strtoupper(request('grade')) === 'XI' || request('grade') === '11' ? 'selected' : '' }}>Kelas XI (11)</option>
                <option value="XII" {{ strtoupper(request('grade')) === 'XII' || request('grade') === '12' ? 'selected' : '' }}>Kelas XII (12)</option>
            </select>
        </div>

        {{-- Jurusan --}}
        <div style="flex: 1; min-width: 130px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Jurusan
            </label>
            <select name="major" class="form-control" style="width: 100%;">
                <option value="">Semua Jurusan</option>
                @foreach($majors as $m)
                    <option value="{{ $m }}" {{ request('major') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        {{-- Cari Siswa --}}
        <div style="flex: 1.5; min-width: 170px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Cari Siswa
            </label>
            <input type="text" name="search" class="form-control" placeholder="Nama atau NIS..." value="{{ request('search') }}" style="width: 100%;">
        </div>

        {{-- Tombol Aksi --}}
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.25rem;">
                Filter
            </button>
            @if(request()->filled('status') || request()->filled('type') || request()->filled('grade') || request()->filled('major') || request()->filled('search'))
                <a href="{{ route('permission-requests.verification') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Tabel Monitoring --}}
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
        <span style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">
            Rekap Data Izin & Sakit Siswa
        </span>
        <span class="badge badge-info" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
            {{ $requests->count() }} Data Ditemukan
        </span>
    </div>

    @if ($requests->count())
        <div class="table-container" style="overflow-x: auto;">
            <table class="monitoring-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th>Siswa</th>
                        <th>Kelas & Jurusan</th>
                        <th>Tanggal & Durasi</th>
                        <th>Jenis</th>
                        <th>Alasan & Dokumen</th>
                        <th style="text-align: center;">Status</th>
                        <th>Diverifikasi Oleh</th>
                        <th style="text-align: right; width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                <strong>{{ $request->student?->name ?? '-' }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">NIS: {{ $request->student?->nis ?? '-' }}</div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.25rem;">
                                    <span class="badge badge-secondary" style="background-color: var(--bg-surface-hover); color: var(--text-primary); font-weight: 600;">
                                        {{ $request->student?->schoolClass?->name ?? '-' }}
                                    </span>
                                    @if($request->student?->schoolClass?->major)
                                        <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-weight: 600; font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                                            {{ $request->student->schoolClass->major }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="white-space: nowrap;">
                                <div style="font-weight: 600; color: var(--text-primary);">
                                    @if($request->end_date && $request->end_date->ne($request->permission_date))
                                        {{ $request->permission_date->format('d/m/Y') }} &ndash; {{ $request->end_date->format('d/m/Y') }}
                                    @else
                                        {{ $request->permission_date ? $request->permission_date->format('d/m/Y') : '-' }}
                                    @endif
                                </div>
                                <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-weight: 600; font-size: 0.72rem; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-block; margin-top: 0.2rem;">
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
                            <td style="max-width: 250px;">
                                <div style="font-size: 0.85rem; color: var(--text-primary); line-height: 1.35; word-break: break-word;">
                                    {{ $request->reason }}
                                </div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.15rem;">
                                    Diajukan oleh: {{ $request->parent ? 'Wali Murid' : 'Siswa' }}
                                </div>
                                @if($request->document_path)
                                    <div style="margin-top: 0.35rem;">
                                        <button type="button" 
                                            class="btn btn-secondary btn-sm" 
                                            style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.72rem; padding: 0.18rem 0.5rem; border-radius: 4px; cursor: pointer;"
                                            onclick="previewDocument('{{ asset('storage/' . $request->document_path) }}', '{{ addslashes($request->student?->name ?? 'Siswa') }}', '{{ $request->type }}')">
                                            📄 Bukti Surat
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if(strtolower($request->status) === 'approved' || strtolower($request->status) === 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif(strtolower($request->status) === 'rejected' || strtolower($request->status) === 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @else
                                    <span class="badge badge-warning">Menunggu</span>
                                @endif
                            </td>
                            <td>
                                @if($request->status === 'Pending')
                                    <span style="font-size: 0.78rem; color: #d97706; font-style: italic;">
                                        Menunggu Wali Kelas
                                    </span>
                                @else
                                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-primary);">
                                        {{ $request->verifiedBy?->name ?? 'Wali Kelas' }}
                                    </div>
                                    @if($request->verified_at)
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">
                                            {{ $request->verified_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                    @if($request->verification_note)
                                        <div style="font-size: 0.72rem; color: var(--danger-text); margin-top: 0.15rem;" title="{{ $request->verification_note }}">
                                            Catatan: {{ Str::limit($request->verification_note, 30) }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem; justify-content: flex-end;">
                                    <a href="{{ route('permission-requests.show', $request->id) }}" class="btn btn-secondary btn-sm" style="padding: 0.35rem 0.65rem; font-size: 0.78rem;">
                                        Detail
                                    </a>

                                    @if ($request->status === 'Pending')
                                        {{-- Approve / Setuju Langsung --}}
                                        <form action="{{ route('homeroom.permissions.approve', $request->id) }}" method="POST" style="display: inline; margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.35rem 0.65rem; font-size: 0.78rem; background-color: #10b981; border-color: #10b981;" onclick="return confirm('Setujui pengajuan izin untuk {{ addslashes($request->student?->name ?? 'Siswa') }}?')">
                                                ✓ Setuju
                                            </button>
                                        </form>

                                        {{-- Reject / Tolak via Modal In-Page --}}
                                        <button type="button" 
                                            class="btn btn-danger btn-sm"
                                            style="padding: 0.35rem 0.65rem; font-size: 0.78rem;"
                                            onclick="openRejectModal({{ $request->id }}, '{{ addslashes($request->student?->name ?? 'Siswa') }}', '{{ $request->type }}')">
                                            ✕ Tolak
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 0.875rem; text-align: center; padding: 2.5rem;">
            Tidak ada data permohonan izin atau sakit yang sesuai dengan filter.
        </p>
    @endif
</div>

{{-- MODAL IN-PAGE: TOLAK PERMOHONAN IZIN --}}
<div class="modal-overlay" id="modalRejectPerm">
    <div class="modal-card modal-md" style="max-width: 480px; width: 90%;">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon" style="background-color: var(--danger-bg); color: var(--danger-text);">✕</div>
                <div>
                    <h3 class="modal-title">Tolak Permohonan Izin</h3>
                    <p class="modal-subtitle" id="rejectStudentInfo">Konfirmasi penolakan izin siswa</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalRejectPerm')">&times;</button>
        </div>
        <form id="formRejectPerm" method="POST" action="">
            @csrf
            <div class="modal-body" style="padding: 1.25rem;">
                <div class="alert alert-warning" style="margin-bottom: 1rem; font-size: 0.8rem; line-height: 1.4;">
                    ⚠️ <strong>Perhatian:</strong> Menolak pengajuan ini akan mencatat ketidakhadiran siswa pada rentang tanggal tersebut sebagai <strong>ALPA</strong> pada rekap presensi.
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="reject_note" style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 0.4rem;">
                        Alasan Penolakan <span style="color: var(--text-muted); font-size: 0.75rem;">(Opsional)</span>
                    </label>
                    <textarea name="verification_note" id="reject_note" class="form-control" rows="3" placeholder="Contoh: Bukti surat tidak valid, atau siswa tidak memberikan keterangan jelas..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--border-color);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalRejectPerm')">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL IN-PAGE: PRATINJAU DOKUMEN BUKTI SURAT (1 TAB) --}}
<div class="modal-overlay" id="modalPreviewDoc">
    <div class="modal-card" style="max-width: 780px; width: 95%; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon" style="background-color: rgba(99, 102, 241, 0.1); color: #6366f1;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h3 class="modal-title">Dokumen Bukti Surat</h3>
                    <p class="modal-subtitle" id="previewStudentInfo">Memuat berkas siswa...</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalPreviewDoc')">&times;</button>
        </div>

        <div class="modal-body" style="padding: 1rem; overflow-y: auto; text-align: center; background: #f8fafc; min-height: 380px; display: flex; align-items: center; justify-content: center;">
            {{-- Pratinjau Gambar --}}
            <div id="previewImageWrapper" style="display: none; width: 100%; text-align: center;">
                <img id="previewImage" src="" alt="Bukti Surat Izin" style="max-width: 100%; max-height: 65vh; border-radius: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.08); object-fit: contain;">
            </div>

            {{-- Pratinjau PDF --}}
            <div id="previewPdfWrapper" style="display: none; width: 100%; height: 65vh;">
                <iframe id="previewPdf" src="" style="width: 100%; height: 100%; border: none; border-radius: 8px;"></iframe>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
            <a id="downloadDocLink" href="#" target="_blank" download class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh File Asli</span>
            </a>
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalPreviewDoc')">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openRejectModal(requestId, studentName, type) {
        document.getElementById('rejectStudentInfo').innerText = 'Siswa: ' + studentName + ' (' + type + ')';
        document.getElementById('formRejectPerm').action = '/homeroom/permission-requests/' + requestId + '/reject';
        document.getElementById('reject_note').value = '';
        openModal('modalRejectPerm');
    }

    function previewDocument(url, studentName, type) {
        document.getElementById('previewStudentInfo').textContent = `${type} - ${studentName}`;
        document.getElementById('downloadDocLink').href = url;

        const imgWrapper = document.getElementById('previewImageWrapper');
        const pdfWrapper = document.getElementById('previewPdfWrapper');
        const imgElem = document.getElementById('previewImage');
        const pdfElem = document.getElementById('previewPdf');

        const isPdf = url.toLowerCase().split('?')[0].endsWith('.pdf');

        if (isPdf) {
            imgWrapper.style.display = 'none';
            imgElem.src = '';

            pdfElem.src = url;
            pdfWrapper.style.display = 'block';
        } else {
            pdfWrapper.style.display = 'none';
            pdfElem.src = '';

            imgElem.src = url;
            imgWrapper.style.display = 'block';
        }

        openModal('modalPreviewDoc');
    }
</script>
@endsection