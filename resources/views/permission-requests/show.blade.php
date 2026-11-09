@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Pengajuan Izin / Sakit</h1>
        <p class="page-subtitle">Informasi lengkap status dan berkas pengajuan siswa</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    {{-- Siswa --}}
    <div class="detail-item">
        <strong>Nama Siswa</strong>
        <p>{{ $permissionRequest->student->name }}</p>
    </div>

    {{-- NIS --}}
    @if($permissionRequest->student->nis)
        <div class="detail-item">
            <strong>NIS</strong>
            <p>{{ $permissionRequest->student->nis }}</p>
        </div>
    @endif

    {{-- Tanggal & Durasi --}}
    <div class="detail-item">
        <strong>Rentang Tanggal Izin</strong>
        <p>
            @if($permissionRequest->end_date && $permissionRequest->end_date->ne($permissionRequest->permission_date))
                {{ $permissionRequest->permission_date->format('d-m-Y') }} s.d. {{ $permissionRequest->end_date->format('d-m-Y') }}
            @else
                {{ $permissionRequest->permission_date->format('d-m-Y') }}
            @endif
            <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-weight: 600; margin-left: 0.5rem;">
                {{ $permissionRequest->duration_days }} Hari
            </span>
        </p>
    </div>

    {{-- Jenis --}}
    <div class="detail-item">
        <strong>Jenis Pengajuan</strong>
        <p>{{ $permissionRequest->type }}</p>
    </div>

    {{-- Alasan --}}
    <div class="detail-item">
        <strong>Alasan Izin</strong>
        <p>{{ $permissionRequest->reason }}</p>
    </div>

    {{-- Pengaju --}}
    <div class="detail-item">
        <strong>Diajukan Oleh</strong>
        <p>{{ $permissionRequest->parent ? 'Orang Tua' : 'Siswa' }}</p>
    </div>

    {{-- Status --}}
    <div class="detail-item">
        <strong>Status Permohonan</strong>
        <p>
            @if(strtolower($permissionRequest->status) === 'approved' || strtolower($permissionRequest->status) === 'disetujui')
                <span class="badge badge-success">Disetujui</span>
            @elseif(strtolower($permissionRequest->status) === 'rejected' || strtolower($permissionRequest->status) === 'ditolak')
                <span class="badge badge-danger">Ditolak</span>
            @else
                <span class="badge badge-warning">Menunggu</span>
            @endif
        </p>
    </div>

    {{-- Dokumen --}}
    <div class="detail-item">
        <strong>Dokumen Bukti</strong>
        <p>
            @if($permissionRequest->document_path)
                <button type="button" 
                    class="btn btn-secondary btn-sm" 
                    style="display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer;"
                    onclick="previewDocument('{{ asset('storage/' . $permissionRequest->document_path) }}', '{{ addslashes($permissionRequest->student->name) }}', '{{ $permissionRequest->type }}')">
                    📄 Pratinjau Dokumen
                </button>
            @else
                <span style="color: var(--text-muted);">Tidak ada berkas diunggah.</span>
            @endif
        </p>
    </div>

    {{-- Waktu Verifikasi --}}
    @if($permissionRequest->verified_at)
        <div class="detail-item">
            <strong>Diverifikasi Oleh</strong>
            <p>{{ $permissionRequest->verifiedBy?->name ?? 'Wali Kelas' }} &bull; {{ $permissionRequest->verified_at->format('d-m-Y H:i') }}</p>
        </div>
    @endif

    {{-- Catatan Verifikasi --}}
    @if($permissionRequest->verification_note)
        <div class="detail-item">
            <strong>Catatan Verifikasi</strong>
            <p style="color: var(--danger-text);">{{ $permissionRequest->verification_note }}</p>
        </div>
    @endif

    @if($permissionRequest->status === 'Pending' && ($canVerify ?? false))
        <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color); display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary); margin-right: 0.5rem;">Aksi Verifikasi:</span>
            {{-- Tombol Setujui Izin --}}
            <form action="{{ route('homeroom.permissions.approve', $permissionRequest->id) }}" method="POST" style="margin: 0; display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary" style="background-color: #10b981; border-color: #10b981; display: inline-flex; align-items: center; gap: 0.4rem;" onclick="return confirm('Apakah Anda yakin ingin MENYETUJUI permohonan izin untuk {{ addslashes($permissionRequest->student->name) }}?')">
                    <span>✓</span> Setujui Izin (Accept)
                </button>
            </form>

            {{-- Tombol Tolak Izin --}}
            <button type="button" class="btn btn-danger" style="display: inline-flex; align-items: center; gap: 0.4rem;" onclick="openRejectModal({{ $permissionRequest->id }}, '{{ addslashes($permissionRequest->student->name) }}', '{{ $permissionRequest->type }}')">
                <span>✕</span> Tolak Izin (Reject)
            </button>
        </div>
    @endif

    <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; align-items: center;">
        @if(in_array(auth()->user()->role, ['waka', 'bk', 'kesiswaan'], true))
            <a href="{{ route('permission-requests.verification') }}" class="btn btn-secondary">
                &larr; Kembali ke Monitoring Izin
            </a>
        @elseif(auth()->user()->role === 'guru')
            <a href="{{ route('homeroom.permissions.index') }}" class="btn btn-secondary">
                &larr; Kembali ke Verifikasi Izin Siswa
            </a>
        @else
            <a href="{{ route('permission-requests.index') }}" class="btn btn-secondary">
                &larr; Kembali ke Daftar Izin
            </a>
        @endif
    </div>
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

{{-- MODAL PRATINJAU DOKUMEN --}}
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
            <div id="previewImageWrapper" style="display: none; width: 100%; text-align: center;">
                <img id="previewImage" src="" alt="Bukti Surat Izin" style="max-width: 100%; max-height: 65vh; border-radius: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.08); object-fit: contain;">
            </div>

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