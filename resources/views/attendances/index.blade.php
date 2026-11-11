@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Rekap Presensi Siswa</h1>
        <p class="page-subtitle">
            Ringkasan data kehadiran siswa per kelas berdasarkan status Hadir, Sakit, Izin, dan Alpa
            @if(!empty($startDate) && !empty($endDate))
                &bull; <span class="badge badge-info" style="font-size: 0.78rem; font-weight: 600;">
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    @if(($range ?? '') === 'today') (Hari Ini)
                    @elseif(($range ?? '') === 'this_week') (Minggu Ini)
                    @elseif(in_array(($range ?? ''), ['7_days', '1_week'])) (1 Minggu Terakhir)
                    @elseif(($range ?? '') === 'this_month') (Bulan Ini)
                    @elseif(in_array(($range ?? ''), ['30_days', '1_month'])) (1 Bulan Terakhir)
                    @endif
                </span>
            @elseif(!empty($startDate))
                &bull; <span class="badge badge-info" style="font-size: 0.78rem; font-weight: 600;">Mulai: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</span>
            @elseif(!empty($endDate))
                &bull; <span class="badge badge-info" style="font-size: 0.78rem; font-weight: 600;">Sampai: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
            @endif
        </p>
    </div>

    <div>
        @if(auth()->user()->role === 'waka')
            <a href="{{ route('dashboard.waka') }}" class="btn-back">
                &larr; Kembali ke Dashboard Waka Kurikulum
            </a>
        @else
            <a href="{{ route('home') }}" class="btn-back">
                &larr; Kembali ke Dashboard
            </a>
        @endif
    </div>
</div>

{{-- 4 KARTU STATISTIK RINGKASAN PRESENSI --}}
<div class="grid-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card" style="border-left: 4px solid #10b981;">
        <div>
            <div class="stat-value" style="color: #10b981;">{{ $summary['hadir'] ?? 0 }}</div>
            <div class="stat-label">Total Hadir</div>
        </div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #f59e0b;">
        <div>
            <div class="stat-value" style="color: #f59e0b;">{{ $summary['sakit'] ?? 0 }}</div>
            <div class="stat-label">Total Sakit</div>
        </div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #06b6d4;">
        <div>
            <div class="stat-value" style="color: #06b6d4;">{{ $summary['izin'] ?? 0 }}</div>
            <div class="stat-label">Total Izin</div>
        </div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #ef4444;">
        <div>
            <div class="stat-value" style="color: #ef4444;">{{ $summary['alpa'] ?? 0 }}</div>
            <div class="stat-label">Tanpa Keterangan (Alpa)</div>
        </div>
    </div>
</div>

{{-- JIKA ROLE SISWA SENDIRI ATAU ORANG TUA --}}
@if(isset($student))
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 class="card-title">
            {{ auth()->user()->role === 'ortu' ? 'Informasi Presensi Siswa (Dipantau Orang Tua)' : 'Informasi Siswa' }}
        </h2>
        <div class="grid-2">
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Nama Lengkap Siswa</span>
                <div style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-top: 0.2rem;">
                    {{ $student->name }}
                    @if(auth()->user()->role === 'ortu')
                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-weight: 600; font-size: 0.75rem; vertical-align: middle; margin-left: 0.5rem;">
                            Dipantau: {{ auth()->user()->name }}
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">NIS & Kelas</span>
                <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-secondary); margin-top: 0.2rem;">
                    NIS: {{ $student->nis ?? '-' }} &bull; <span class="badge badge-info">{{ $student->schoolClass?->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Filter Periode Dropdown untuk Siswa --}}
        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <form id="studentFilterForm" action="{{ route('attendances.index') }}" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
                <div style="min-width: 200px;">
                    <label style="display: block; font-size: 0.775rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                        Periode Waktu
                    </label>
                    <select name="range" id="studentRangeSelect" class="form-control" style="width: 100%;" onchange="onStudentRangeChange(this)">
                        <option value="all" {{ ($range ?? 'all') === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                        <option value="today" {{ ($range ?? '') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="7_days" {{ in_array(($range ?? ''), ['7_days', '1_week']) ? 'selected' : '' }}>1 Minggu Terakhir</option>
                        <option value="this_week" {{ ($range ?? '') === 'this_week' ? 'selected' : '' }}>Minggu Ini (Sen-Min)</option>
                        <option value="30_days" {{ in_array(($range ?? ''), ['30_days', '1_month']) ? 'selected' : '' }}>1 Bulan Terakhir</option>
                        <option value="this_month" {{ ($range ?? '') === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="custom" {{ ($range ?? '') === 'custom' ? 'selected' : '' }}>Pilih Tanggal Sendiri...</option>
                    </select>
                </div>

                {{-- Custom Date Inputs (hidden by default) --}}
                <div id="studentCustomDates" style="display: {{ ($range ?? '') === 'custom' ? 'flex' : 'none' }}; gap: 0.75rem; align-items: flex-end;">
                    <div style="min-width: 140px;">
                        <label style="display: block; font-size: 0.775rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate ?? '' }}" style="width: 100%;">
                    </div>
                    <div style="min-width: 140px;">
                        <label style="display: block; font-size: 0.775rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate ?? '' }}" style="width: 100%;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.25rem;">Filter</button>
                </div>

                @if(($range ?? 'all') !== 'all')
                    <a href="{{ route('attendances.index') }}" class="btn btn-secondary btn-sm" style="height: 36px; display: inline-flex; align-items: center;">Reset</a>
                @endif
            </form>
        </div>
    </div>

{{-- JIKA WAKA, BK, ATAU WALI KELAS --}}
@elseif(isset($availableClasses))
    {{-- BARIS FILTER MULTI PARAMETER --}}
    <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <form id="attendanceFilterForm" action="{{ route('attendances.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">

            {{-- Filter Kelas --}}
            <div style="flex: 1; min-width: 170px;">
                <label style="display: block; font-size: 0.775rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                    Filter Kelas
                </label>
                <select name="class_id" class="form-control" style="width: 100%;">
                    <option value="">Semua Kelas</option>
                    @foreach($availableClasses as $cls)
                        <option value="{{ $cls->id }}" {{ (string) request('class_id') === (string) $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Periode Dropdown --}}
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 0.775rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                    Periode Waktu
                </label>
                <select name="range" id="rangeSelect" class="form-control" style="width: 100%;" onchange="onRangeSelectChange(this)">
                    <option value="all" {{ ($range ?? 'all') === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                    <option value="today" {{ ($range ?? '') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="7_days" {{ in_array(($range ?? ''), ['7_days', '1_week']) ? 'selected' : '' }}>1 Minggu Terakhir</option>
                    <option value="this_week" {{ ($range ?? '') === 'this_week' ? 'selected' : '' }}>Minggu Ini (Sen-Min)</option>
                    <option value="30_days" {{ in_array(($range ?? ''), ['30_days', '1_month']) ? 'selected' : '' }}>1 Bulan Terakhir</option>
                    <option value="this_month" {{ ($range ?? '') === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ ($range ?? '') === 'custom' ? 'selected' : '' }}>Pilih Tanggal Sendiri...</option>
                </select>
            </div>

            {{-- Custom Date Inputs (tersembunyi kecuali mode kustom) --}}
            <div id="customDateFields" style="display: {{ ($range ?? '') === 'custom' ? 'contents' : 'none' }};">
                <div style="flex: 0.8; min-width: 140px;">
                    <label style="display: block; font-size: 0.775rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                        Dari Tanggal
                    </label>
                    <input type="date" name="start_date" id="startDateInput" class="form-control" value="{{ $startDate ?? '' }}" style="width: 100%;">
                </div>
                <div style="flex: 0.8; min-width: 140px;">
                    <label style="display: block; font-size: 0.775rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                        Sampai Tanggal
                    </label>
                    <input type="date" name="end_date" id="endDateInput" class="form-control" value="{{ $endDate ?? '' }}" style="width: 100%;">
                </div>
            </div>

            {{-- Cari Siswa --}}
            <div style="flex: 1; min-width: 170px;">
                <label style="display: block; font-size: 0.775rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                    Cari Nama / NIS
                </label>
                <input type="text" name="search" class="form-control" placeholder="Ketik nama atau NIS..." value="{{ request('search') }}" style="width: 100%;">
            </div>

            {{-- Tombol Aksi --}}
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.25rem;">
                    Filter
                </button>
                @if(request()->filled('class_id') || request()->filled('start_date') || request()->filled('end_date') || request()->filled('search') || (request()->filled('range') && request('range') !== 'all'))
                    <a href="{{ route('attendances.index') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center;">
                        Reset
                    </a>
                @endif
                @if(auth()->user()->role !== 'waka')
                    <button type="button" onclick="window.print()" class="btn btn-success" style="height: 42px; display: inline-flex; align-items: center; gap: 0.4rem; background: #059669; border-color: #059669; color: white; cursor: pointer;">
                        <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak Laporan Presensi (PDF)
                    </button>
                @endif
            </div>
        </form>
    </div>
@endif

{{-- KELOMPOK TAB NAVIGASI STATUS (SEMUA, HADIR, SAKIT, IZIN, ALPA, LOG DETAIL) --}}
@if(isset($studentsRecap))
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
        <button type="button" class="tab-btn active" onclick="switchFilter('all', this)">
            Semua Siswa
            <span class="tab-badge" id="badge-all">{{ $studentsRecap->total() }}</span>
        </button>

        <button type="button" class="tab-btn" onclick="switchFilter('hadir', this)" style="--active-color: #10b981;">
            Hadir
            <span class="tab-badge" id="badge-hadir">{{ $studentsRecap->where('hadir', '>', 0)->count() }}</span>
        </button>

        <button type="button" class="tab-btn" onclick="switchFilter('sakit', this)" style="--active-color: #f59e0b;">
            Sakit
            <span class="tab-badge" id="badge-sakit">{{ $studentsRecap->where('sakit', '>', 0)->count() }}</span>
        </button>

        <button type="button" class="tab-btn" onclick="switchFilter('izin', this)" style="--active-color: #06b6d4;">
            Izin
            <span class="tab-badge" id="badge-izin">{{ $studentsRecap->where('izin', '>', 0)->count() }}</span>
        </button>

        <button type="button" class="tab-btn" onclick="switchFilter('alpa', this)" style="--active-color: #ef4444;">
            Alpa
            <span class="tab-badge" id="badge-alpa">{{ $studentsRecap->where('alpa', '>', 0)->count() }}</span>
        </button>

        <button type="button" class="tab-btn" onclick="switchViewMode('log', this)" style="margin-left: auto;">
            Log Riwayat Harian
            <span class="tab-badge">{{ $attendances->count() }}</span>
        </button>
    </div>
@endif

{{-- ========================================================================= --}}
{{-- 1. TABEL UTAMA: REKAPITULASI PER SISWA (HADIR, SAKIT, IZIN, ALPA)        --}}
{{-- ========================================================================= --}}
@if(isset($studentsRecap))
    {{-- Header Khusus Dokumen Cetak (Hanya tampil saat dicetak/PDF) --}}
    <div class="print-doc-header">
        <h1 class="print-doc-title">LAPORAN REKAPITULASI PRESENSI SISWA</h1>
        <div class="print-doc-subtitle">
            @if(!empty($startDate) && !empty($endDate))
                Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</strong>
            @elseif(!empty($startDate))
                Mulai Tanggal: <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}</strong>
            @elseif(!empty($endDate))
                Sampai Tanggal: <strong>{{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</strong>
            @else
                Periode: <strong>Rekap Keseluruhan Tahun Ajaran Berjalan</strong>
            @endif
            &bull; Dicetak Pada: {{ now()->isoFormat('D MMMM Y') }}
        </div>
    </div>

    {{-- Metadata Kelas Khusus Dokumen Cetak --}}
    <div class="print-meta-box">
        <table class="print-meta-table">
            <tr>
                <td style="width: 16%; font-weight: bold;">Kelas / Rombel</td>
                <td style="width: 2%;">:</td>
                <td style="width: 35%;"><strong>{{ $selectedClass ? $selectedClass->name : 'Semua Kelas Binaan' }}</strong></td>
                <td style="width: 16%; font-weight: bold;">Tahun Ajaran</td>
                <td style="width: 2%;">:</td>
                <td style="width: 29%;">{{ now()->year }}/{{ now()->year + 1 }} ({{ now()->month >= 7 ? 'Ganjil' : 'Genap' }})</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tingkat / Jurusan</td>
                <td>:</td>
                <td>{{ $selectedClass ? ($selectedClass->grade . ' / ' . ($selectedClass->major ?? '-')) : 'Semua Tingkat & Jurusan' }}</td>
                <td style="font-weight: bold;">Wali Kelas</td>
                <td>:</td>
                <td>{{ $selectedClass?->homeroomTeacher?->name ?? (auth()->user()->teacher?->name ?? 'Wali Kelas Terkait') }}</td>
            </tr>
        </table>
    </div>

    <div class="card" id="recapTableCard">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
            <span id="recapTableTitle">Daftar Rekap Presensi Siswa</span>
            <span class="badge badge-info" id="recapCountLabel">{{ $studentsRecap->total() }} Siswa</span>
        </div>

        <div class="table-container">
            <table id="tableRecap">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th style="text-align: center; width: 110px;">Hadir</th>
                        <th style="text-align: center; width: 110px;">Sakit</th>
                        <th style="text-align: center; width: 110px;">Izin</th>
                        <th style="text-align: center; width: 110px;">Alpa</th>
                        <th style="text-align: center; width: 130px;">Total Presensi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentsRecap as $recap)
                        @php
                            $rowIndex = $loop->iteration + ($studentsRecap->currentPage() - 1) * $studentsRecap->perPage();
                        @endphp
                        <tr class="recap-row" 
                            data-index="{{ $rowIndex }}"
                            data-hadir="{{ $recap->hadir }}" 
                            data-sakit="{{ $recap->sakit }}" 
                            data-izin="{{ $recap->izin }}" 
                            data-alpa="{{ $recap->alpa }}">
                            <td style="text-align: center; color: var(--text-muted); font-size: 0.8rem;" class="row-number">
                                {{ $rowIndex }}
                            </td>
                            <td>
                                <strong>{{ $recap->name }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">NIS: {{ $recap->nis ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $recap->class_name }}
                                </span>
                            </td>
                            {{-- Hadir --}}
                            <td style="text-align: center;">
                                @if($recap->hadir > 0)
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-weight: 700; font-size: 0.85rem; padding: 0.25rem 0.65rem;">
                                        {{ $recap->hadir }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">0</span>
                                @endif
                            </td>
                            {{-- Sakit --}}
                            <td style="text-align: center;">
                                @if($recap->sakit > 0)
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.12); color: #d97706; font-weight: 700; font-size: 0.85rem; padding: 0.25rem 0.65rem;">
                                        {{ $recap->sakit }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">0</span>
                                @endif
                            </td>
                            {{-- Izin --}}
                            <td style="text-align: center;">
                                @if($recap->izin > 0)
                                    <span class="badge" style="background: rgba(6, 182, 212, 0.12); color: #0891b2; font-weight: 700; font-size: 0.85rem; padding: 0.25rem 0.65rem;">
                                        {{ $recap->izin }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">0</span>
                                @endif
                            </td>
                            {{-- Alpa --}}
                            <td style="text-align: center;">
                                @if($recap->alpa > 0)
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; font-weight: 700; font-size: 0.85rem; padding: 0.25rem 0.65rem;">
                                        {{ $recap->alpa }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">0</span>
                                @endif
                            </td>
                            {{-- Total Kehadiran --}}
                            <td style="text-align: center; font-weight: 600; color: var(--text-primary);">
                                {{ $recap->total }} Pertemuan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                Tidak ada data siswa yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="3" style="text-align: center;">TOTAL REKAPITULASI KELAS</td>
                        <td style="text-align: center;">{{ $summary['hadir'] ?? 0 }}</td>
                        <td style="text-align: center;">{{ $summary['sakit'] ?? 0 }}</td>
                        <td style="text-align: center;">{{ $summary['izin'] ?? 0 }}</td>
                        <td style="text-align: center; {{ ($summary['alpa'] ?? 0) > 0 ? 'color: #dc2626;' : '' }}">{{ $summary['alpa'] ?? 0 }}</td>
                        <td style="text-align: center;">{{ $summary['total'] ?? 0 }} Data</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Navigasi Halaman (Pagination Rekap Presensi) --}}
        @if ($studentsRecap->hasPages() || $studentsRecap->total() > 10)
            <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color, #e2e8f0); flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.85rem; font-size: 0.85rem; color: var(--text-secondary);">
                    <span>
                        Menampilkan <strong>{{ $studentsRecap->firstItem() ?? 0 }}</strong> - <strong>{{ $studentsRecap->lastItem() ?? 0 }}</strong> dari <strong>{{ $studentsRecap->total() }}</strong> siswa
                    </span>
                    <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <label for="per_page_select_attendance" style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Baris per hal:</label>
                        <select id="per_page_select_attendance" onchange="changePerPage(this.value)" style="padding: 0.2rem 0.4rem; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--border-color, #cbd5e1); background: #fff; cursor: pointer;">
                            @foreach([10, 15, 25, 50, 100] as $opt)
                                <option value="{{ $opt }}" {{ $studentsRecap->perPage() == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 0.35rem; align-items: center;">
                    {{-- Tombol Pertama --}}
                    @if ($studentsRecap->currentPage() > 2)
                        <a href="{{ $studentsRecap->url(1) }}" class="btn-page" title="Halaman Pertama">&laquo;&laquo;</a>
                    @endif

                    {{-- Tombol Sebelumnya --}}
                    @if ($studentsRecap->onFirstPage())
                        <span class="btn-page disabled">&laquo; Prev</span>
                    @else
                        <a href="{{ $studentsRecap->previousPageUrl() }}" class="btn-page">&laquo; Prev</a>
                    @endif

                    {{-- Link Nomor Halaman --}}
                    @php
                        $startPage = max(1, $studentsRecap->currentPage() - 2);
                        $endPage = min($studentsRecap->lastPage(), $studentsRecap->currentPage() + 2);
                    @endphp
                    @for ($p = $startPage; $p <= $endPage; $p++)
                        @if ($p == $studentsRecap->currentPage())
                            <span class="btn-page active">{{ $p }}</span>
                        @else
                            <a href="{{ $studentsRecap->url($p) }}" class="btn-page">{{ $p }}</a>
                        @endif
                    @endfor

                    {{-- Tombol Selanjutnya --}}
                    @if ($studentsRecap->hasMorePages())
                        <a href="{{ $studentsRecap->nextPageUrl() }}" class="btn-page">Next &raquo;</a>
                    @else
                        <span class="btn-page disabled">Next &raquo;</span>
                    @endif

                    {{-- Tombol Terakhir --}}
                    @if ($studentsRecap->currentPage() < $studentsRecap->lastPage() - 1)
                        <a href="{{ $studentsRecap->url($studentsRecap->lastPage()) }}" class="btn-page" title="Halaman Terakhir">&raquo;&raquo;</a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Kotak Ringkasan & Keterangan Mode Cetak --}}
        <div class="print-summary-box">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Keterangan:</strong> 
                    <strong>H:</strong> Hadir &bull; 
                    <strong>S:</strong> Sakit &bull; 
                    <strong>I:</strong> Izin &bull; 
                    <strong>A:</strong> Alpa (Tanpa Keterangan)
                </div>
                <div>
                    Total Siswa: <strong>{{ $studentsRecap->total() }}</strong> Siswa &bull; 
                    Akumulasi Presensi: <strong>{{ $summary['total'] ?? 0 }}</strong> Pertemuan
                </div>
            </div>
        </div>

        {{-- Kolom Pengesahan Tanda Tangan Cetak --}}
        @php
            $wakaSigner = \App\Models\User::where('role', 'waka')->first();
        @endphp
        <div class="print-signature-block">
            <table class="print-sign-table">
                <tr>
                    <td>
                        Mengetahui,<br>
                        Wakil Kepala Sekolah Bidang Kurikulum
                        <div class="print-sign-space"></div>
                        <div class="print-signer-name">{{ $wakaSigner?->name ?? (auth()->user()->role === 'waka' ? auth()->user()->name : 'Wakil Kepala Sekolah Bidang Kurikulum') }}</div>
                        <div class="print-signer-nip">NIP. {{ $wakaSigner?->teacher?->nip ?? ($wakaSigner?->nip ?? (auth()->user()->teacher?->nip ?? '-')) }}</div>
                    </td>
                    <td>
                        {{ now()->isoFormat('D MMMM Y') }}<br>
                        Wali Kelas {{ $selectedClass ? $selectedClass->name : '' }}
                        <div class="print-sign-space"></div>
                        <div class="print-signer-name">
                            {{ $selectedClass?->homeroomTeacher?->name ?? (auth()->user()->teacher?->name ?? 'Wali Kelas Terkait') }}
                        </div>
                        <div class="print-signer-nip">
                            NIP. {{ $selectedClass?->homeroomTeacher?->nip ?? (auth()->user()->teacher?->nip ?? '-') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endif

{{-- ========================================================================= --}}
{{-- 2. TABEL LOG RIWAYAT HARIAN (DETAIL PER MAPEL & TANGGAL)                 --}}
{{-- ========================================================================= --}}
<div class="card" id="logTableCard" style="{{ isset($studentsRecap) ? 'display: none;' : '' }}">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
        <span>Log Riwayat Kehadiran Lengkap</span>
        <span class="badge badge-info">{{ $attendances->count() }} Data Tercatat</span>
    </div>

    @if($attendances->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        @if(isset($students) || isset($studentsRecap))
                            <th>Siswa</th>
                        @endif
                        <th>Tanggal</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Jam Pelajaran</th>
                        <th style="text-align: center;">Status Kehadiran</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                                {{ $loop->iteration }}
                            </td>

                            @if(isset($students) || isset($studentsRecap))
                                <td>
                                    <strong>{{ $attendance->student?->name ?? '-' }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">NIS: {{ $attendance->student?->nis ?? '-' }}</div>
                                </td>
                            @endif

                            <td style="white-space: nowrap;">
                                {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('d-m-Y') : '-' }}
                            </td>

                            <td>{{ $attendance->schedule?->subject?->name ?? '-' }}</td>

                            <td>
                                <span class="badge badge-info">
                                    {{ $attendance->student?->schoolClass?->name ?? $attendance->schedule?->schoolClass?->name ?? '-' }}
                                </span>
                            </td>

                            <td>
                                @if($attendance->schedule)
                                    {{ \Carbon\Carbon::parse($attendance->schedule->start_time)->format('H:i') }}
                                    -
                                    {{ \Carbon\Carbon::parse($attendance->schedule->end_time)->format('H:i') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td style="text-align: center;">
                                @if($attendance->status === 'Hadir')
                                    <span class="badge badge-success">Hadir</span>
                                @elseif($attendance->status === 'Sakit')
                                    <span class="badge badge-warning">Sakit</span>
                                @elseif($attendance->status === 'Izin')
                                    <span class="badge badge-info">Izin</span>
                                @else
                                    <span class="badge badge-danger">Alpa</span>
                                @endif
                            </td>

                            <td style="font-size: 0.8rem; color: var(--text-muted);">
                                {{ $attendance->note ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); text-align: center; padding: 2.5rem;">
            Belum ada riwayat catatan presensi yang ditemukan.
        </p>
    @endif
</div>

<style>
    .tab-btn {
        background: #fff;
        border: 1px solid var(--border-color);
        padding: 0.55rem 1rem;
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .tab-btn:hover {
        background: var(--bg-surface-hover);
        color: var(--text-primary);
    }
    .tab-btn.active {
        background: var(--active-color, #4f46e5);
        color: #fff;
        border-color: var(--active-color, #4f46e5);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }
    .tab-badge {
        background: rgba(0, 0, 0, 0.08);
        padding: 0.1rem 0.45rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .tab-btn.active .tab-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }

    /* Styling Navigasi Pagination */
    .btn-page {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 0.5rem;
        font-size: 0.825rem;
        font-weight: 500;
        color: var(--text-primary, #1e293b);
        background: #ffffff;
        border: 1px solid var(--border-color, #cbd5e1);
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .btn-page:hover:not(.disabled):not(.active) {
        background-color: #f1f5f9;
        border-color: #94a3b8;
    }
    .btn-page.active {
        background-color: var(--accent, #4f46e5);
        color: #ffffff;
        border-color: var(--accent, #4f46e5);
        font-weight: 700;
    }
    .btn-page.disabled {
        color: #94a3b8;
        background-color: #f8fafc;
        border-color: #e2e8f0;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Sembunyikan elemen cetak di layar monitor */
    .print-doc-header,
    .print-meta-box,
    .print-summary-box,
    .print-signature-block {
        display: none;
    }

    @media print {
        /* Sembunyikan navigasi & UI web dashboard */
        .sidebar, .navbar, .page-header, .btn-back, .grid-4, form, .card:has(form), .tab-btn, .no-print,
        #logTableCard, .card-title, #recapTableCard .card-title {
            display: none !important;
        }

        /* Reset background dan typography kertas A4 kedinasan */
        html, body, .main-content {
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
            font-family: 'Times New Roman', Times, serif !important;
            color: #000 !important;
        }

        #recapTableCard {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
        }

        /* Tampilkan judul dokumen */
        .print-doc-header {
            display: block !important;
            text-align: center;
            margin-bottom: 16px;
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

        /* Tampilkan metadata kelas */
        .print-meta-box {
            display: block !important;
            margin-bottom: 14px !important;
        }

        .print-meta-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9.5pt !important;
        }

        .print-meta-table td {
            padding: 3px 6px !important;
            border: none !important;
            vertical-align: top !important;
        }

        /* Format tabel cetak */
        .table-container {
            overflow: visible !important;
        }

        #tableRecap {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 8.5pt !important;
        }

        #tableRecap th,
        #tableRecap td {
            border: 1px solid #000 !important;
            padding: 4px 6px !important;
            color: #000 !important;
        }

        #tableRecap th {
            background-color: #f1f5f9 !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #tableRecap .badge {
            background: transparent !important;
            color: #000 !important;
            border: none !important;
            padding: 0 !important;
            font-weight: bold !important;
        }

        /* Kotak Ringkasan Statistik */
        .print-summary-box {
            display: block !important;
            border: 1px solid #000 !important;
            padding: 6px 10px !important;
            font-size: 8.5pt !important;
            margin-top: 10px !important;
            background-color: #f1f5f9 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Kolom Tanda Tangan */
        .print-signature-block {
            display: block !important;
            width: 100% !important;
            margin-top: 25px !important;
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
            height: 60px !important;
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
            margin: 1.2cm;
        }
    }
</style>

@push('scripts')
<script>
    let currentFilter = 'all';

    /**
     * Handler untuk dropdown Periode di view Waka / BK / Wali Kelas.
     * Jika bukan "custom", langsung submit form.
     * Jika "custom", tampilkan input tanggal manual.
     */
    function onRangeSelectChange(selectElem) {
        const customFields = document.getElementById('customDateFields');
        if (selectElem.value === 'custom') {
            if (customFields) customFields.style.display = 'contents';
            const startInput = document.getElementById('startDateInput');
            if (startInput) startInput.focus();
        } else {
            // Kosongkan input tanggal custom agar tidak override range
            const startInput = document.getElementById('startDateInput');
            const endInput = document.getElementById('endDateInput');
            if (startInput) startInput.value = '';
            if (endInput) endInput.value = '';
            if (customFields) customFields.style.display = 'none';
            // Auto-submit
            document.getElementById('attendanceFilterForm').submit();
        }
    }

    /**
     * Handler untuk dropdown Periode di view Siswa.
     * Langsung submit kecuali mode kustom.
     */
    function onStudentRangeChange(selectElem) {
        const customDates = document.getElementById('studentCustomDates');
        if (selectElem.value === 'custom') {
            if (customDates) customDates.style.display = 'flex';
        } else {
            if (customDates) customDates.style.display = 'none';
            document.getElementById('studentFilterForm').submit();
        }
    }

    function switchFilter(status, btnElem) {
        // Tampilkan kartu tabel rekap, sembunyikan log riwayat harian
        document.getElementById('recapTableCard').style.display = 'block';
        document.getElementById('logTableCard').style.display = 'none';

        // Update status tombol aktif
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        if (btnElem) btnElem.classList.add('active');

        currentFilter = status;
        const rows = document.querySelectorAll('.recap-row');
        let visibleCount = 0;

        const titleElem = document.getElementById('recapTableTitle');
        const countElem = document.getElementById('recapCountLabel');

        const titleMap = {
            'all': 'Daftar Rekap Presensi Siswa (Semua)',
            'hadir': 'Daftar Siswa dengan Catatan Hadir',
            'sakit': 'Daftar Siswa dengan Catatan Sakit',
            'izin': 'Daftar Siswa dengan Catatan Izin',
            'alpa': 'Daftar Siswa dengan Catatan Alpa (Tanpa Keterangan)'
        };

        if (titleElem && titleMap[status]) {
            titleElem.textContent = titleMap[status];
        }

        rows.forEach(row => {
            const hadir = parseInt(row.getAttribute('data-hadir') || '0', 10);
            const sakit = parseInt(row.getAttribute('data-sakit') || '0', 10);
            const izin = parseInt(row.getAttribute('data-izin') || '0', 10);
            const alpa = parseInt(row.getAttribute('data-alpa') || '0', 10);

            let isVisible = false;

            if (status === 'all') {
                isVisible = true;
            } else if (status === 'hadir' && hadir > 0) {
                isVisible = true;
            } else if (status === 'sakit' && sakit > 0) {
                isVisible = true;
            } else if (status === 'izin' && izin > 0) {
                isVisible = true;
            } else if (status === 'alpa' && alpa > 0) {
                isVisible = true;
            }

            if (isVisible) {
                row.style.display = '';
                visibleCount++;
                const rowNum = row.querySelector('.row-number');
                if (rowNum) {
                    rowNum.textContent = status === 'all' ? (row.getAttribute('data-index') || visibleCount) : visibleCount;
                }
            } else {
                row.style.display = 'none';
            }
        });

        if (countElem) {
            countElem.textContent = (status === 'all' ? '{{ $studentsRecap->total() }}' : visibleCount) + ' Siswa';
        }
    }

    function changePerPage(limit) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', limit);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    function switchViewMode(mode, btnElem) {
        if (mode === 'log') {
            document.getElementById('recapTableCard').style.display = 'none';
            document.getElementById('logTableCard').style.display = 'block';

            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            if (btnElem) btnElem.classList.add('active');
        }
    }
</script>
@endpush
@endsection