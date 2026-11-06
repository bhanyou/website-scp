<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Jurnal Mengajar - {{ $teacher->name }}</title>
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
            font-size: 13pt;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .kop-sekolah {
            font-size: 17pt;
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

        /* Judul Laporan */
        .report-title-box {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-subtitle {
            font-size: 10.5pt;
            margin-top: 4px;
            color: #333;
        }

        /* Metadata Guru & Jadwal */
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10pt;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .meta-table td.label {
            width: 170px;
            font-weight: bold;
        }

        .meta-table td.colon {
            width: 15px;
            text-align: center;
        }

        /* Tabel Rekap KBM */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 20px;
        }

        .table-data th,
        .table-data td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        .table-data th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Summary Box */
        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            border: 1px dashed #666;
            padding: 10px 15px;
            font-size: 9.5pt;
            background-color: #fcfcfc;
        }

        .summary-item {
            font-weight: bold;
        }

        /* Tanda Tangan */
        .signature-section {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 20px;
        }

        .sign-space {
            height: 75px;
        }

        .sign-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Styling Media Print */
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

            .table-data th {
                background-color: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 landscape;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

    {{-- Toolbar Layar Browser (Disembunyikan saat dicetak) --}}
    <div class="no-print-bar">
        <div>
            <strong>Mode Cetak Laporan Jurnal Mengajar Guru</strong> &bull; Silakan gunakan menu cetak browser atau simpan sebagai PDF.
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-print">
                🖨️ Cetak / Simpan PDF
            </button>
            <a href="{{ route('journals.index') }}" class="btn btn-close">
                ← Kembali
            </a>
        </div>
    </div>

    {{-- Lembar Kertas Laporan --}}
    <div class="sheet">

        {{-- Judul Laporan --}}
        <div class="report-title-box">
            <h1 class="report-title">REKAPITULASI JURNAL PEMBELAJARAN GURU</h1>
            <div class="report-subtitle">
                Periode Laporan: 
                @if($startDate && $endDate)
                    <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</strong>
                @elseif($startDate)
                    Mulai <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}</strong>
                @elseif($endDate)
                    Sampai <strong>{{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</strong>
                @else
                    <strong>Seluruh Periode Aktif</strong>
                @endif
            </div>
        </div>

        {{-- Metadata Guru --}}
        <table class="meta-table">
            <tr>
                <td class="label">Nama Tenaga Pendidik</td>
                <td class="colon">:</td>
                <td><strong>{{ $teacher->name }}</strong></td>
                <td class="label">Tahun Ajaran / Semester</td>
                <td class="colon">:</td>
                <td>{{ now()->year }}/{{ now()->year + 1 }} (Ganjil/Genap)</td>
            </tr>
            <tr>
                <td class="label">NIP / NUPTK</td>
                <td class="colon">:</td>
                <td>{{ $teacher->nip ?? '-' }}</td>
                <td class="label">Kelas Binaan / Dituju</td>
                <td class="colon">:</td>
                <td>{{ $selectedClass ? $selectedClass->name : 'Seluruh Kelas Ampuan' }}</td>
            </tr>
        </table>

        {{-- Ringkasan Sesi KBM --}}
        <div class="summary-box">
            <div class="summary-item">Total Sesi KBM Terisi: {{ $journalsWithStats->count() }} Pertemuan</div>
            <div class="summary-item">Total Akumulasi Presensi Hadir: {{ $journalsWithStats->sum('hadir_count') }} Siswa</div>
            <div class="summary-item">Total Sakit: {{ $journalsWithStats->sum('sakit_count') }} | Izin: {{ $journalsWithStats->sum('izin_count') }} | Alpa: {{ $journalsWithStats->sum('alpa_count') }}</div>
        </div>

        {{-- Tabel Rekap Pembelajaran --}}
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 85px;">Hari / Tgl</th>
                    <th style="width: 65px;">Jam Ke-</th>
                    <th style="width: 85px;">Kelas</th>
                    <th style="width: 140px;">Mata Pelajaran</th>
                    <th>Materi / Pokok Bahasan</th>
                    <th>Kegiatan Pembelajaran</th>
                    <th style="width: 90px;">Presensi Siswa<br><small>(H / S / I / A)</small></th>
                    <th style="width: 70px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journalsWithStats as $j)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            {{ $j->journal_date ? $j->journal_date->format('d/m/Y') : '-' }}<br>
                            <small style="color: #444;">{{ $j->schedule?->day ?? '-' }}</small>
                        </td>
                        <td class="text-center">
                            @if($j->schedule?->start_period && $j->schedule?->end_period)
                                {{ $j->schedule->start_period }}-{{ $j->schedule->end_period }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center font-bold">
                            {{ $j->schedule?->schoolClass?->name ?? '-' }}
                        </td>
                        <td>
                            {{ $j->schedule?->subject?->name ?? '-' }}
                        </td>
                        <td>
                            <strong>{{ $j->material }}</strong>
                        </td>
                        <td>
                            {{ $j->activity }}
                            @if($j->notes)
                                <br><small style="color: #666; font-style: italic;">Catatan: {{ $j->notes }}</small>
                            @endif
                        </td>
                        <td class="text-center" style="font-size: 8.5pt;">
                            <span style="color: #059669; font-weight: bold;">{{ $j->hadir_count }}</span> / 
                            <span style="color: #d97706;">{{ $j->sakit_count }}</span> / 
                            <span style="color: #0284c7;">{{ $j->izin_count }}</span> / 
                            <span style="color: #dc2626; font-weight: bold;">{{ $j->alpa_count }}</span>
                        </td>
                        <td class="text-center">
                            <span style="font-size: 8pt; font-weight: bold; color: #059669;">Terlaksana</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 25px; color: #666;">
                            Tidak ada data rekaman jurnal mengajar pada kriteria atau periode yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Lembar Pengesahan dan Tanda Tangan --}}
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td>
                        Mengetahui,<br>
                        <strong>Waka Bidang Kurikulum</strong>
                        <div class="sign-space"></div>
                        <div class="sign-name">Drs. H. Mulyadi, M.Pd.</div>
                        <div>NIP. 19750812 200212 1 002</div>
                    </td>
                    <td>
                        Kota Tercinta, {{ now()->isoFormat('D MMMM Y') }}<br>
                        <strong>Guru Mata Pelajaran</strong>
                        <div class="sign-space"></div>
                        <div class="sign-name">{{ $teacher->name }}</div>
                        <div>NIP. {{ $teacher->nip ?? '....................................' }}</div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>
</html>
