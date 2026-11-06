@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Monitoring Jurnal & Presensi</h1>
        <p class="page-subtitle">Informasi materi ajar guru dan rekap kehadiran siswa kelas</p>
    </div>
    <div>
        <a href="{{ route('waka.journals.index') }}" class="btn-back">
            ← Kembali ke Monitoring Jurnal
        </a>
    </div>
</div>

{{-- Header Khusus Dokumen Cetak Lembar Bukti KBM (Hanya tampil saat dicetak) --}}
<div class="print-doc-header">
    <h1 class="print-doc-title">LEMBAR BUKTI KEGIATAN BELAJAR MENGAJAR (KBM)</h1>
    <div class="print-doc-subtitle">
        Tanggal Pelaksanaan: <strong>{{ $journal->journal_date ? $journal->journal_date->isoFormat('dddd, D MMMM Y') : '-' }}</strong> 
        &bull; Waktu Input: {{ $journal->submitted_at ? $journal->submitted_at->format('H:i') . ' WIB' : ($journal->created_at ? $journal->created_at->format('H:i') . ' WIB' : '-') }}
    </div>
</div>

<div class="grid-2">
    {{-- Informasi Jurnal --}}
    <div class="card">
        <h2 class="card-title">Informasi Pembelajaran</h2>
        
        <div class="detail-item">
            <strong>Guru Pengajar</strong>
            <p style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0.25rem 0;">
                {{ $journal->teacher?->name ?? '-' }}
                @if($journal->teacher?->nip)
                    <span style="font-size: 0.85rem; font-weight: normal; color: var(--text-muted);">
                        (NIP: {{ $journal->teacher->nip }})
                    </span>
                @endif
            </p>
        </div>

        <div class="detail-item">
            <strong>Putaran Blok</strong>
            <p style="margin: 0.25rem 0;"><span class="badge badge-info">{{ $journal->schedule?->blockCycle?->cycle ?? '-' }}</span></p>
        </div>

        <div class="detail-item">
            <strong>Mata Pelajaran & Kelas</strong>
            <p style="margin: 0.25rem 0;">
                <strong style="color: var(--accent); font-size: 1.05rem;">{{ $journal->schedule?->subject?->name ?? '-' }}</strong> 
                • <span class="badge badge-secondary" style="background-color: var(--bg-surface-hover); color: var(--text-primary);">{{ $journal->schedule?->schoolClass?->name ?? '-' }}</span>
                @if($journal->schedule?->room)
                    <span style="color: var(--text-muted); font-size: 0.875rem;">({{ $journal->schedule->room->name }})</span>
                @endif
            </p>
        </div>

        <div class="detail-item">
            <strong>Hari & Tanggal</strong>
            <p style="margin: 0.25rem 0;">{{ $journal->schedule?->day ?? '-' }}, {{ $journal->journal_date ? $journal->journal_date->format('d/m/Y') : '-' }} ({{ $journal->schedule?->start_time ? substr($journal->schedule->start_time, 0, 5) : '-' }} - {{ $journal->schedule?->end_time ? substr($journal->schedule->end_time, 0, 5) : '-' }})</p>
        </div>

        <div class="detail-item">
            <strong>Waktu Submit Guru</strong>
            <p style="color: var(--text-muted); margin: 0.25rem 0;">
                {{ $journal->submitted_at ? $journal->submitted_at->format('d/m/Y H:i') : ($journal->created_at ? $journal->created_at->format('d/m/Y H:i') : '-') }}
            </p>
        </div>

        <div class="detail-item">
            <strong>Materi Pembelajaran</strong>
            <p style="line-height: 1.6; margin: 0.25rem 0;">{{ $journal->material }}</p>
        </div>

        <div class="detail-item">
            <strong>Kegiatan Kelas</strong>
            <p style="line-height: 1.6; margin: 0.25rem 0;">{{ $journal->activity }}</p>
        </div>

        <div class="detail-item">
            <strong>Catatan Tambahan</strong>
            <p style="line-height: 1.6; margin: 0.25rem 0;">{{ $journal->notes ?: '-' }}</p>
        </div>
    </div>

    {{-- Foto Dokumentasi --}}
    <div class="card">
        <h2 class="card-title">Foto Dokumentasi Kegiatan</h2>
        @if($journal->photo_path)
            <img src="{{ asset('storage/' . $journal->photo_path) }}" alt="Foto kegiatan pembelajaran" style="width: 100%; border-radius: var(--radius-sm); border: 1px solid var(--border-color); object-fit: cover; max-height: 420px;">
        @else
            <div style="background-color: var(--bg-surface-hover); border: 1px dashed var(--border-color); border-radius: var(--radius-sm); padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
                <p style="margin: 0; font-size: 0.9rem;">Guru tidak mengunggah foto kegiatan pembelajaran.</p>
            </div>
        @endif
    </div>
</div>

{{-- Presensi Siswa --}}
<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <span style="font-weight: 700; font-size: 1.125rem;">Presensi Kehadiran Siswa Kelas</span>
        <span class="badge badge-info">{{ $attendances->count() }} Total Siswa</span>
    </div>

    {{-- Keterangan Rekap Hadir, Sakit, Izin, Alpa --}}
    @php
        $hadirCount = $attendances->where('status', 'Hadir')->count();
        $sakitCount = $attendances->where('status', 'Sakit')->count();
        $izinCount  = $attendances->where('status', 'Izin')->count();
        $alpaCount  = $attendances->where('status', 'Alpa')->count();
    @endphp
    <div style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
        <div style="background-color: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
            <strong style="color: #10b981;">Hadir:</strong> 
            <span style="font-weight: 700; color: #10b981;">{{ $hadirCount }}</span> Siswa
        </div>
        <div style="background-color: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
            <strong style="color: #f59e0b;">Sakit:</strong> 
            <span style="font-weight: 700; color: #f59e0b;">{{ $sakitCount }}</span> Siswa
        </div>
        <div style="background-color: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.35); padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
            <strong style="color: #3b82f6;">Izin:</strong> 
            <span style="font-weight: 700; color: #3b82f6;">{{ $izinCount }}</span> Siswa
        </div>
        <div style="background-color: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
            <strong style="color: #ef4444;">Alpa:</strong> 
            <span style="font-weight: 700; color: #ef4444;">{{ $alpaCount }}</span> Siswa
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Status Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $attendance->student?->nis ?? '-' }}</td>
                        <td><strong>{{ $attendance->student?->name ?? '-' }}</strong></td>
                        <td>
                            @if($attendance->status === 'Hadir')
                                <span class="badge badge-success">Hadir</span>
                            @elseif($attendance->status === 'Sakit')
                                <span class="badge badge-warning">Sakit</span>
                            @elseif($attendance->status === 'Izin')
                                <span class="badge badge-info">Izin</span>
                            @elseif($attendance->status === 'Alpa')
                                <span class="badge badge-danger">Alpa</span>
                            @else
                                <span class="badge badge-secondary">{{ $attendance->status }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Belum ada data presensi yang tercatat untuk jurnal ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="no-print" style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
        <button type="button" onclick="window.print()" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; background: #059669; border-color: #059669; color: white;">
            🖨️ Cetak Lembar Bukti KBM Ini
        </button>
        <a href="{{ route('waka.journals.index') }}" class="btn btn-secondary">
            ← Kembali ke Monitoring Jurnal
        </a>
    </div>
</div>

{{-- Kolom Pengesahan Tanda Tangan Cetak --}}
@php
    $wakaUser = \App\Models\User::where('role', 'waka')->with('teacher')->first();
@endphp
<div class="print-signature-block">
    <table class="print-sign-table">
        <tr>
            <td>
                Mengetahui,<br>
                Wakil Kepala Sekolah Bidang Kurikulum
                <div class="print-sign-space"></div>
                <div class="print-signer-name">{{ $wakaUser?->name ?? 'Wakil Kepala Sekolah Bidang Kurikulum' }}</div>
                <div class="print-signer-nip">NIP. {{ $wakaUser?->teacher?->nip ?? ($wakaUser?->nip ?? '-') }}</div>
            </td>
            <td>
                {{ now()->isoFormat('D MMMM Y') }}<br>
                Guru Pengampu Mata Pelajaran
                <div class="print-sign-space"></div>
                <div class="print-signer-name">{{ $journal->teacher?->name }}</div>
                <div class="print-signer-nip">NIP. {{ $journal->teacher?->nip ?? '-' }}</div>
            </td>
        </tr>
    </table>
</div>

<style>
/* Sembunyikan elemen dokumen cetak di tampilan layar browser */
.print-doc-header,
.print-signature-block {
    display: none;
}

@media print {
    /* Sembunyikan elemen interface website */
    .sidebar, .navbar, .page-header, .no-print, .btn-back {
        display: none !important;
    }

    /* Reset background dan typography cetak ke standar kedinasan A4 */
    html, body, .main-content {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
        font-family: 'Times New Roman', Times, serif !important;
        color: #000 !important;
    }

    /* Judul Dokumen Bersih */
    .print-doc-header {
        display: block !important;
        text-align: center;
        margin-bottom: 18px;
        padding-bottom: 8px;
        border-bottom: 2px solid #000;
    }

    .print-doc-title {
        font-size: 14pt !important;
        font-weight: bold !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px;
        margin: 0 0 4px 0 !important;
        color: #000 !important;
    }

    .print-doc-subtitle {
        font-size: 9.5pt !important;
        color: #333 !important;
    }

    /* Layout Card agar rapi dan tidak tumpang tindih */
    .grid-2 {
        display: flex !important;
        flex-direction: row !important;
        gap: 15px !important;
        margin-bottom: 12px !important;
    }

    .grid-2 > .card:first-child {
        flex: 1.4 !important;
    }

    .grid-2 > .card:last-child {
        flex: 1 !important;
        text-align: center !important;
    }

    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 10px 12px !important;
        margin-bottom: 12px !important;
        background: #fff !important;
        page-break-inside: avoid;
    }

    .card-title {
        font-size: 9.5pt !important;
        font-weight: bold !important;
        text-transform: uppercase !important;
        border-bottom: 1px solid #000 !important;
        padding-bottom: 4px !important;
        margin-bottom: 8px !important;
        color: #000 !important;
    }

    .detail-item {
        margin-bottom: 5px !important;
        font-size: 9pt !important;
    }

    .detail-item strong {
        font-size: 8.5pt !important;
        color: #000 !important;
        display: block !important;
    }

    .detail-item p {
        margin: 1px 0 0 0 !important;
        font-size: 9pt !important;
        color: #000 !important;
        line-height: 1.4 !important;
    }

    /* Foto Dokumentasi dibuat pas dan proporsional (tidak merusak 1 halaman penuh) */
    .card img {
        max-height: 180px !important;
        max-width: 100% !important;
        object-fit: contain !important;
        border: 1px solid #000 !important;
        border-radius: 0 !important;
        display: block !important;
        margin: 0 auto !important;
    }

    /* Tabel Presensi Siswa */
    .table-container {
        overflow: visible !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 8.5pt !important;
    }

    table tr {
        page-break-inside: avoid !important;
    }

    table th,
    table td {
        border: 1px solid #000 !important;
        padding: 4px 6px !important;
        color: #000 !important;
    }

    table th {
        background-color: #f1f5f9 !important;
        font-weight: bold !important;
        text-transform: uppercase !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .badge {
        border: 1px solid #000 !important;
        background: transparent !important;
        color: #000 !important;
        font-weight: bold !important;
        padding: 1px 3px !important;
    }

    /* Tanda Tangan Cetak */
    .print-signature-block {
        display: block !important;
        width: 100% !important;
        margin-top: 15px !important;
        page-break-inside: avoid !important;
    }

    .print-sign-table {
        width: 100% !important;
        border: none !important;
    }

    .print-sign-table td {
        border: none !important;
        width: 50% !important;
        text-align: center !important;
        vertical-align: top !important;
        font-size: 9.5pt !important;
        padding: 0 15px !important;
    }

    .print-sign-space {
        height: 50px !important;
    }

    .print-signer-name {
        font-weight: bold !important;
        text-decoration: underline !important;
    }

    .print-signer-nip {
        font-size: 8.5pt !important;
        margin-top: 2px !important;
    }

    @page {
        size: A4 portrait;
        margin: 1cm;
    }
}
</style>
@endsection
