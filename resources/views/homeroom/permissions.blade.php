@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Izin & Sakit Siswa Kelas Binaan</h1>
        <p class="page-subtitle">
            Daftar permohonan izin siswa kelas <strong>{{ $classes->pluck('name')->join(', ') }}</strong>
        </p>
    </div>
    <div>
        <a href="{{ route('dashboard.guru') }}" class="btn btn-secondary">
            &larr; Kembali ke Dashboard
        </a>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid-3" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $requests->count() }}</div>
            <div class="stat-label">Total Riwayat Izin</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">
                {{ $requests->where('status', 'Pending')->count() }}
            </div>
            <div class="stat-label">Menunggu Verifikasi Anda</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">
                {{ $requests->where('status', 'Approved')->count() }}
            </div>
            <div class="stat-label">Izin Telah Disetujui</div>
        </div>
    </div>
</div>

{{-- Tabel Daftar Izin --}}
<div class="card">
    <div class="card-title">
        <span>Daftar Permohonan Izin Siswa Kelas Binaan</span>
        <span class="badge badge-info">{{ $requests->count() }} Data</span>
    </div>

    @if($requests->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Rentang Tanggal</th>
                        <th>Durasi</th>
                        <th>Kategori</th>
                        <th>Alasan</th>
                        <th>Bukti Surat</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right; width: 190px;">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                <strong>{{ $req->student?->name ?? '-' }}</strong>
                                @if($req->student?->nis)
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">NIS: {{ $req->student->nis }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.25rem;">
                                    <span class="badge badge-info">
                                        {{ $req->student?->schoolClass?->name ?? '-' }}
                                    </span>
                                    @if($req->student?->schoolClass?->major)
                                        <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-weight: 600; font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                                            {{ $req->student->schoolClass->major }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($req->end_date && $req->end_date->ne($req->permission_date))
                                    <div><strong>{{ \Carbon\Carbon::parse($req->permission_date)->format('d M Y') }}</strong></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">s.d. {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</div>
                                @else
                                    <strong>{{ \Carbon\Carbon::parse($req->permission_date)->format('d M Y') }}</strong>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-weight: 600;">
                                    {{ $req->duration_days }} Hari
                                </span>
                            </td>
                            <td>
                                @if(strtolower($req->type) === 'sakit')
                                    <span class="badge badge-warning">Sakit</span>
                                @else
                                    <span class="badge badge-primary">Izin</span>
                                @endif
                            </td>
                            <td style="max-width: 250px;">
                                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->reason }}">
                                    {{ $req->reason }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                    Oleh: {{ $req->parent ? 'Orang Tua' : 'Siswa' }}
                                </div>
                            </td>
                            <td>
                                @if($req->document_path)
                                    <button type="button" 
                                        class="btn btn-secondary btn-sm" 
                                        style="padding: 0.25rem 0.6rem; font-size: 0.75rem; cursor: pointer;"
                                        onclick="previewDocument('{{ asset('storage/' . $req->document_path) }}', '{{ addslashes($req->student?->name ?? 'Siswa') }}', '{{ $req->type }}')">
                                        📄 Berkas
                                    </button>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if(strtolower($req->status) === 'approved' || strtolower($req->status) === 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif(strtolower($req->status) === 'rejected' || strtolower($req->status) === 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @else
                                    <span class="badge badge-warning">Menunggu</span>
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem; justify-content: flex-end;">
                                    <a href="{{ route('permission-requests.show', $req->id) }}" class="btn btn-secondary btn-sm" style="padding: 0.35rem 0.55rem; font-size: 0.78rem;">
                                        Detail
                                    </a>

                                    @if ($req->status === 'Pending')
                                        {{-- Approve Langsung oleh Wali Kelas --}}
                                        <form action="{{ route('homeroom.permissions.approve', $req->id) }}" method="POST" style="display: inline; margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.35rem 0.65rem; font-size: 0.78rem; background-color: #10b981; border-color: #10b981;" onclick="return confirm('Setujui pengajuan izin untuk {{ addslashes($req->student?->name ?? 'Siswa') }}?')">
                                                ✓ Setuju
                                            </button>
                                        </form>

                                        {{-- Tolak via Modal In-Page --}}
                                        <button type="button" 
                                            class="btn btn-danger btn-sm"
                                            style="padding: 0.35rem 0.65rem; font-size: 0.78rem;"
                                            onclick="openRejectModal({{ $req->id }}, '{{ addslashes($req->student?->name ?? 'Siswa') }}', '{{ $req->type }}')">
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
        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
            <p style="font-size: 0.95rem; font-weight: 500; margin-bottom: 0.25rem;">Tidak ada permohonan izin dari siswa kelas {{ $classes->pluck('name')->join(', ') }}.</p>
            <p style="font-size: 0.8rem;">Semua siswa tercatat hadir atau belum ada pengajuan baru.</p>
        </div>
    @endif
</div>

{{-- MODAL IN-PAGE: TOLAK PERMOHONAN IZIN (KHUSUS WALI KELAS) --}}
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
                    ⚠️ <strong>Perhatian Wali Kelas:</strong> Menolak pengajuan ini akan mencatat ketidakhadiran siswa pada rentang tanggal tersebut sebagai <strong>ALPA</strong> pada rekap presensi.
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
