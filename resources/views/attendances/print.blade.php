<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Presensi Siswa - {{ $selectedClass->name ?? 'Semua Kelas' }}</title>
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

        /* Lembar Kertas A4 */
        .sheet {
            background: #fff;
            max-width: 860px;
            margin: 0 auto;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        /* Toolbar Layar Browser */
        .no-print-bar {
            max-width: 860px;
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

        /* Judul Dokumen Bersih */
        .doc-title-box {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 8px;
            border-bottom: 2px solid #000;
        }

        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
            color: #000;
        }

        .doc-subtitle {
            font-size: 9.5pt;
            color: #333;
        }

        /* Metadata Laporan */
        .meta-table {
            width: 100%;
            margin-bottom: 16px;
            font-size: 9.5pt;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .meta-table td.label {
            width: 140px;
            font-weight: bold;
        }

        .meta-table td.colon {
            width: 12px;
            text-align: center;
        }

        /* Kotak Konten & Tabel Data */
        .content-box {
            margin-bottom: 16px;
            border: 1px solid #000;
        }

        .box-title {
            background-color: #f1f5f9;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 10px;
            border-bottom: 1px solid #000;
            letter-spacing: 0.5px;
        }

        /* Tabel Presensi Siswa */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 14px;
        }

        .table-data th,
        .table-data td {
            border: 1px solid #000;
            padding: 4px 6px;
            color: #000;
        }

        .table-data th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Tanda Tangan */
        .signature-section {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 15px;
        }

        .signature-space {
            height: 60px;
        }

        .signer-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .signer-nip {
            font-size: 8.5pt;
            margin-top: 2px;
        }

        /* Aturan Media Print */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .sheet {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
                margin: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .table-data th,
            .box-title,
            tfoot tr {
                background-color: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 portrait;
                margin: 1.2cm;
            }
        }
    </style>
</head>
<body>

    {{-- Toolbar Layar Browser (Disembunyikan saat dicetak) --}}
    <div class="no-print-bar">
        <div>
            <strong>Mode Cetak Laporan Rekap Presensi Siswa</strong> &bull; Siap di-print atau diunduh sebagai PDF.
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-print">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Simpan PDF
            </button>
            <a href="{{ route('attendances.index') }}" class="btn btn-close">
                ← Kembali
            </a>
        </div>
    </div>

    {{-- Lembar Kertas Utama --}}
    <div class="sheet">

        {{-- Judul Dokumen Bersih (Tanpa Kop Surat Instansi) --}}
        <div class="doc-title-box">
            <h1 class="doc-title">LAPORAN REKAPITULASI PRESENSI SISWA</h1>
            <div class="doc-subtitle">
                @if($startDate && $endDate)
                    Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</strong>
                @elseif($startDate)
                    Mulai Tanggal: <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}</strong>
                @elseif($endDate)
                    Sampai Tanggal: <strong>{{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</strong>
                @else
                    Periode: <strong>Rekap Keseluruhan Tahun Ajaran Berjalan</strong>
                @endif
                &bull; Dicetak Pada: {{ $today->isoFormat('D MMMM Y') }}
            </div>
        </div>

        {{-- Metadata Kelas & Laporan --}}
        <table class="meta-table">
            <tr>
                <td class="label">Kelas / Rombel</td>
                <td class="colon">:</td>
                <td><strong>{{ $selectedClass ? $selectedClass->name : 'Semua Kelas Binaan' }}</strong></td>
                <td class="label">Tahun Ajaran</td>
                <td class="colon">:</td>
                <td>{{ now()->year }}/{{ now()->year + 1 }} ({{ now()->month >= 7 ? 'Ganjil' : 'Genap' }})</td>
            </tr>
            <tr>
                <td class="label">Tingkat / Jurusan</td>
                <td class="colon">:</td>
                <td>{{ $selectedClass ? ($selectedClass->grade . ' / ' . ($selectedClass->major ?? '-')) : 'Semua Tingkat & Jurusan' }}</td>
                <td class="label">Wali Kelas</td>
                <td class="colon">:</td>
                <td>{{ $selectedClass?->homeroomTeacher?->name ?? 'Wali Kelas Terkait' }}</td>
            </tr>
        </table>

        {{-- Tabel Data Presensi Siswa --}}
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    <th style="width: 95px;">NIS</th>
                    <th>Nama Lengkap Siswa</th>
                    @if(!$selectedClass)
                        <th style="width: 80px;">Kelas</th>
                    @endif
                    <th style="width: 45px;">H</th>
                    <th style="width: 45px;">S</th>
                    <th style="width: 45px;">I</th>
                    <th style="width: 45px;">A</th>
                    <th style="width: 55px;">Total</th>
                    <th style="width: 65px;">% Hadir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentsRecap as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $row->nis }}</td>
                        <td><strong>{{ $row->name }}</strong></td>
                        @if(!$selectedClass)
                            <td class="text-center">{{ $row->class_name }}</td>
                        @endif
                        <td class="text-center">{{ $row->hadir }}</td>
                        <td class="text-center">{{ $row->sakit }}</td>
                        <td class="text-center">{{ $row->izin }}</td>
                        <td class="text-center font-bold" style="{{ $row->alpa > 0 ? 'color: #dc2626;' : '' }}">{{ $row->alpa }}</td>
                        <td class="text-center font-bold">{{ $row->total }}</td>
                        <td class="text-center font-bold">{{ $row->percentage }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $selectedClass ? 9 : 10 }}" class="text-center" style="padding: 15px; color: #64748b;">
                            Tidak ada data presensi yang ditemukan untuk kriteria filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="{{ $selectedClass ? 3 : 4 }}" class="text-center">TOTAL REKAPITULASI KELAS</td>
                    <td class="text-center">{{ $summary['hadir'] }}</td>
                    <td class="text-center">{{ $summary['sakit'] }}</td>
                    <td class="text-center">{{ $summary['izin'] }}</td>
                    <td class="text-center" style="{{ $summary['alpa'] > 0 ? 'color: #dc2626;' : '' }}">{{ $summary['alpa'] }}</td>
                    <td class="text-center">{{ $summary['total'] }}</td>
                    <td class="text-center">{{ $summary['avg_percentage'] }}%</td>
                </tr>
            </tfoot>
        </table>

        {{-- Kotak Keterangan & Statistik --}}
        <div class="content-box">
            <div class="box-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Statistik Kehadiran Siswa</span>
                <span style="font-size: 8.5pt; font-weight: normal; text-transform: none;">
                    Total Siswa: <strong>{{ $summary['total_students'] }}</strong> &bull; Rata-rata Kehadiran: <strong>{{ $summary['avg_percentage'] }}%</strong>
                </span>
            </div>
            <div style="padding: 6px 10px; font-size: 8.5pt; background: #fff;">
                <strong>Keterangan:</strong> 
                <strong>H:</strong> Hadir &bull; 
                <strong>S:</strong> Sakit &bull; 
                <strong>I:</strong> Izin &bull; 
                <strong>A:</strong> Alpa (Tanpa Keterangan) &bull;
                <strong>Total:</strong> Akumulasi Sesi KBM
            </div>
        </div>

        {{-- Lembar Pengesahan / Tanda Tangan --}}
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td>
                        Mengetahui,<br>
                        Wakil Kepala Sekolah Bidang Kurikulum
                        <div class="signature-space"></div>
                        <div class="signer-name">{{ $wakaUser?->name ?? (auth()->user()->role === 'waka' ? auth()->user()->name : 'Wakil Kepala Sekolah Bidang Kurikulum') }}</div>
                        <div class="signer-nip">NIP. {{ $wakaUser?->teacher?->nip ?? ($wakaUser?->nip ?? (auth()->user()->teacher?->nip ?? '-')) }}</div>
                    </td>
                    <td>
                        {{ $today->isoFormat('D MMMM Y') }}<br>
                        Wali Kelas {{ $selectedClass ? $selectedClass->name : '' }}
                        <div class="signature-space"></div>
                        <div class="signer-name">
                            {{ $selectedClass?->homeroomTeacher?->name ?? (auth()->user()->teacher?->name ?? 'Wali Kelas Terkait') }}
                        </div>
                        <div class="signer-nip">
                            NIP. {{ $selectedClass?->homeroomTeacher?->nip ?? (auth()->user()->teacher?->nip ?? '-') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>
</html>
