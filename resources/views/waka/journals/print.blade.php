<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Jurnal Mengajar Guru - {{ $filterTeacher ? $filterTeacher->name : 'Semua Guru' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
        }

        body {
            background-color: #f1f5f9;
            color: #000;
            padding: 20px;
        }

        .sheet {
            background: #fff;
            max-width: 1050px;
            margin: 0 auto;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Toolbar Layar Browser */
        .no-print-bar {
            max-width: 1050px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1e293b;
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            font-family: Arial, sans-serif;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-print {
            background: #10b981;
            color: white;
        }

        .btn-print:hover {
            background: #059669;
        }

        .btn-close {
            background: #64748b;
            color: white;
        }

        .btn-close:hover {
            background: #475569;
        }

        /* Kop Surat Resmi */
        .kop-header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .kop-instansi {
            font-size: 14pt;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .kop-sekolah {
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            margin: 4px 0;
            text-transform: uppercase;
        }

        .kop-alamat {
            font-size: 9.5pt;
            color: #222;
            line-height: 1.4;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .doc-title h2 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title p {
            font-size: 10pt;
            margin-top: 4px;
        }

        /* Metadata Laporan */
        .meta-table {
            width: 100%;
            margin-bottom: 16px;
            font-size: 10pt;
        }

        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        /* Tabel Data Jurnal */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 20px;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        .data-table th {
            background-color: #f8fafc;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Ringkasan Box */
        .summary-box {
            display: flex;
            justify-content: space-between;
            border: 1px solid #000;
            padding: 8px 14px;
            font-size: 9.5pt;
            margin-bottom: 25px;
            background: #fafafa;
        }

        /* Tanda Tangan */
        .signature-container {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            font-size: 10.5pt;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 20px;
        }

        .signature-space {
            height: 75px;
        }

        .signer-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .signer-nip {
            font-size: 9.5pt;
            margin-top: 2px;
        }

        /* Aturan Print */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .sheet {
                box-shadow: none;
                padding: 10px 0;
                max-width: 100%;
            }

            .no-print-bar {
                display: none !important;
            }

            @page {
                size: A4 landscape;
                margin: 1.2cm;
            }
        }
    </style>
</head>
<body>

    {{-- TOOLBAR ATAS LAYAR --}}
    <div class="no-print-bar">
        <div>
            <strong>Mode Cetak Rekapitulasi Jurnal Mengajar</strong> &bull; Format Lanskap A4
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-print">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Simpan PDF
            </button>
            <a href="{{ route('waka.journals.index') }}" class="btn btn-close">
                ← Kembali
            </a>
        </div>
    </div>

    {{-- LEMBAR KERTAS UTAMA --}}
    <div class="sheet">
        {{-- JUDUL DOKUMEN --}}
        <div class="doc-title">
            <h2>LAPORAN REKAPITULASI JURNAL PEMBELAJARAN GURU</h2>
            <p>
                @if($startDate && $endDate)
                    Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
                @elseif($startDate)
                    Mulai Tanggal: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}
                @else
                    Periode: Rekap Keseluruhan Aktivitas Pembelajaran
                @endif
            </p>
        </div>

        {{-- METADATA --}}
        <table class="meta-table">
            <tr>
                <td style="width: 15%;"><strong>Guru Pengampu</strong></td>
                <td style="width: 2%;">:</td>
                <td style="width: 48%;">{{ $filterTeacher ? $filterTeacher->name : 'Semua Guru Pengajar' }}</td>
                <td style="width: 15%;"><strong>Tanggal Cetak</strong></td>
                <td style="width: 2%;">:</td>
                <td style="width: 18%;">{{ now()->isoFormat('D MMMM Y') }}</td>
            </tr>
            <tr>
                <td><strong>Tingkat / Jurusan</strong></td>
                <td>:</td>
                <td>{{ ($selectedGrade ? 'Kelas ' . $selectedGrade : 'Semua Tingkat') . ($selectedMajor ? ' - ' . $selectedMajor : '') }}</td>
                <td><strong>Dicetak Oleh</strong></td>
                <td>:</td>
                <td>{{ $wakaUser->name ?? 'Waka Kurikulum' }}</td>
            </tr>
        </table>

        {{-- TABEL JURNAL --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 11%;">Hari / Tanggal</th>
                    <th style="width: 16%;">Guru & Mata Pelajaran</th>
                    <th style="width: 10%;">Kelas & Jam</th>
                    <th>Pokok Bahasan / Materi</th>
                    <th>Ringkasan Kegiatan KBM</th>
                    <th style="width: 11%;">Presensi Siswa</th>
                    <th style="width: 12%;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journalsWithStats as $index => $j)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ \Carbon\Carbon::parse($j->journal_date)->isoFormat('dddd') }}</strong><br>
                            <span style="font-size: 8pt; color: #333;">{{ \Carbon\Carbon::parse($j->journal_date)->format('d/m/Y') }}</span>
                        </td>
                        <td>
                            <strong>{{ $j->teacher->name ?? '-' }}</strong><br>
                            <span style="font-size: 8pt; color: #444;">{{ $j->schedule->subject->name ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>{{ $j->schedule->schoolClass->name ?? '-' }}</strong><br>
                            <span style="font-size: 8pt; color: #555;">{{ $j->schedule->start_time }} - {{ $j->schedule->end_time }}</span>
                        </td>
                        <td>{{ $j->material }}</td>
                        <td>{{ $j->activity ?: '-' }}</td>
                        <td class="text-center" style="font-size: 8pt;">
                            <span style="color: #15803d; font-weight: bold;">H: {{ $j->hadir_count }}</span> &bull;
                            <span style="color: #b45309;">S: {{ $j->sakit_count }}</span><br>
                            <span style="color: #1d4ed8;">I: {{ $j->izin_count }}</span> &bull;
                            <span style="color: #b91c1c; font-weight: bold;">A: {{ $j->alpa_count }}</span>
                        </td>
                        <td>{{ $j->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px; color: #64748b;">
                            Tidak ada data jurnal mengajar yang sesuai dengan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- SUMMARY BOX --}}
        <div class="summary-box">
            <div><strong>Total Pembelajaran Terlaksana:</strong> {{ $journalsWithStats->count() }} Sesi Pertemuan KBM</div>
            <div><strong>Status Pengisian:</strong> 100% Terverifikasi Sistem</div>
        </div>

        {{-- KOLOM TANDA TANGAN --}}
        <div class="signature-container">
            <table class="signature-table">
                <tr>
                    <td>
                        Mengetahui,<br>
                        Kepala Sekolah SMK Negeri Contoh
                        <div class="signature-space"></div>
                        <div class="signer-name">Drs. H. Mulyadi, M.M.</div>
                        <div class="signer-nip">NIP. 19680415 199303 1 005</div>
                    </td>
                    <td>
                        Kota Terkait, {{ now()->isoFormat('D MMMM Y') }}<br>
                        Wakil Kepala Sekolah Bidang Kurikulum
                        <div class="signature-space"></div>
                        <div class="signer-name">{{ $wakaUser->name ?? 'Waka Kurikulum, M.Pd' }}</div>
                        <div class="signer-nip">NIP. 19820710 200801 1 012</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
