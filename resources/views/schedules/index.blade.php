@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Jadwal Mengajar</h1>
        <p class="page-subtitle">
            Daftar jadwal pelajaran pada siklus blok aktif
        </p>
    </div>

    {{-- Tombol aksi Waka & Ekspor (Hanya untuk Waka) --}}
    @if(auth()->user()->role === 'waka')
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <form action="{{ route('schedules.destroy-all') }}" method="POST" style="margin: 0;" data-custom-confirm="PERINGATAN: Seluruh data jadwal mengajar (pada putaran yang tidak dikunci) akan dihapus permanen, termasuk jurnal dan absensi yang terkait. Apakah Anda yakin ingin menghapus SEMUA jadwal mengajar?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="background-color: #ef4444; color: white; border: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    Hapus Semua Jadwal
                </button>
            </form>
            @if($activeCycle && $activeCycle->is_locked)
                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    style="opacity: 0.6; cursor: not-allowed; border: 1px dashed var(--border-color);"
                    disabled
                    title="Jadwal sedang dikunci Waka. Buka kunci di Siklus Blok untuk import jadwal."
                >
                    Import Jadwal (Terkunci)
                </button>
                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    style="opacity: 0.6; cursor: not-allowed; border: 1px dashed var(--border-color);"
                    disabled
                    title="Jadwal sedang dikunci Waka. Buka kunci di Siklus Blok untuk menambah jadwal."
                >
                    Tambah Jadwal (Terkunci)
                </button>
            @else
                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    onclick="openModal('modalImportSchedule')"
                    style="font-weight: 600;"
                >
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: -2px; margin-right: 2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Jadwal (PDF / Excel / CSV)
                </button>

                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    onclick="openModal('modalCreateSchedule')"
                >
                    + Tambah Jadwal Mengajar
                </button>
            @endif
        </div>
    @endif
</div>

@if(session('import_warnings') && count(session('import_warnings')) > 0)
    <div class="alert alert-warning" style="margin-bottom: 1.25rem; background-color: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; color: #92400e; border-radius: 6px; padding: 0.85rem 1rem;">
        <div style="font-weight: 700; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
            Catatan Peringatan / Bentrok Jadwal (Data Tetap Disimpan Sesuai Jadwal Kurikulum):
        </div>
        <ul style="margin: 0; padding-left: 1.25rem; max-height: 160px; overflow-y: auto; font-size: 0.82rem; line-height: 1.5;">
            @foreach(session('import_warnings') as $warn)
                <li>{{ $warn }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        <div style="font-weight: 700; margin-bottom: 0.35rem;">
            Daftar Baris yang Dilewati / Tidak Disimpan:
        </div>
        <ul style="margin: 0; padding-left: 1.25rem; max-height: 160px; overflow-y: auto; font-size: 0.82rem; line-height: 1.5;">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Switcher Tab Putaran 1 & Putaran 2 --}}
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
    <div style="display: inline-flex; background: #e2e8f0; padding: 0.35rem; border-radius: 12px; gap: 0.35rem;">
        <a href="{{ route('schedules.index', array_merge(request()->except(['cycle', 'page']), ['cycle' => 1])) }}"
           style="padding: 0.65rem 1.6rem; border-radius: 9px; font-weight: 700; font-size: 0.92rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.55rem; transition: all 0.2s; {{ $selectedCycle == 1 ? 'background: #2563eb; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.3);' : 'background: transparent; color: #475569;' }}">
            <span>Putaran 1</span>
            @if($activeCycleNumber == 1)
                <span class="badge" style="font-size: 0.68rem; background: rgba(255,255,255,0.25); color: #fff; padding: 0.2rem 0.5rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">Aktif</span>
            @endif
        </a>
        <a href="{{ route('schedules.index', array_merge(request()->except(['cycle', 'page']), ['cycle' => 2])) }}"
           style="padding: 0.65rem 1.6rem; border-radius: 9px; font-weight: 700; font-size: 0.92rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.55rem; transition: all 0.2s; {{ $selectedCycle == 2 ? 'background: #2563eb; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.3);' : 'background: transparent; color: #475569;' }}">
            <span>Putaran 2</span>
            @if($activeCycleNumber == 2)
                <span class="badge" style="font-size: 0.68rem; background: rgba(255,255,255,0.25); color: #fff; padding: 0.2rem 0.5rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">Aktif</span>
            @endif
        </a>
    </div>

    {{-- Penanda Putaran Aktif Sistem & Shortcut Agenda --}}
    <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
        <a href="{{ route('student.schedule') }}" class="btn btn-secondary btn-sm" style="font-weight: 700; text-decoration: none;">
            Lihat Agenda Mingguan
        </a>
        <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.88rem; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.55rem 1rem; border-radius: 10px; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);"></span>
            <span>Putaran Aktif: <strong>Putaran {{ $activeCycleNumber }}</strong></span>
        </div>
    </div>
</div>

{{-- Panel Filter Jadwal Mengajar --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('schedules.index') }}" method="GET" style="display: flex; gap: 0.85rem; flex-wrap: wrap; align-items: flex-end;">
        <input type="hidden" name="cycle" value="{{ $selectedCycle }}">
        <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">

        {{-- Filter Ruangan / Lab --}}
        <div style="flex: 1.3; min-width: 170px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Pilih Ruangan / Lab
            </label>
            <select name="room_id" class="form-control" style="width: 100%;">
                <option value="">Semua Ruangan / Lab</option>
                @foreach($rooms as $r)
                    <option value="{{ $r->id }}" {{ request('room_id') == $r->id ? 'selected' : '' }}>
                        {{ $r->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filter Hari --}}
        <div style="flex: 1; min-width: 120px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Hari
            </label>
            <select name="day" class="form-control" style="width: 100%;">
                <option value="">Semua Hari</option>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $d)
                    <option value="{{ $d }}" {{ request('day') == $d ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filter Kelas --}}
        <div style="flex: 1.1; min-width: 130px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Kelas
            </label>
            <select name="class_group_id" class="form-control" style="width: 100%;">
                <option value="">Semua Kelas</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ (request('class_group_id') == $c->id || request('class_id') == $c->id) ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Cari Mapel --}}
        <div style="flex: 1.3; min-width: 160px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Cari Mata Pelajaran
            </label>
            <input type="text" name="search" class="form-control" placeholder="Ketik nama mapel..." value="{{ request('search') }}" style="width: 100%;">
        </div>

        @if(auth()->user()->role === 'waka' || auth()->user()->role === 'bk' || (auth()->user()->teacher && auth()->user()->teacher->isBkTeacher()))
            <div style="flex: 1.3; min-width: 160px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                    Guru Pengajar
                </label>
                <select name="teacher_id" class="form-control" style="width: 100%;">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.25rem;">
                Terapkan
            </button>
            @if(request()->filled('day') || request()->filled('class_group_id') || request()->filled('class_id') || request()->filled('room_id') || request()->filled('teacher_id') || request()->filled('search'))
                <a href="{{ route('schedules.index', ['cycle' => $selectedCycle, 'per_page' => request('per_page', 15)]) }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center;">
                    Reset Filter
                </a>
            @endif
        </div>
    </form>
</div>

<div class="card">

    {{-- Informasi siklus aktif --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                Putaran / Siklus Ditampilkan
            </div>

            <div style="font-size: 1.15rem; font-weight: 700; color: var(--accent);">
                Putaran {{ $selectedCycle }}
                @if($activeCycleNumber == $selectedCycle)
                    <span class="badge badge-success" style="font-size: 0.75rem; vertical-align: middle; margin-left: 0.35rem;">Aktif Saat Ini</span>
                @endif
            </div>

            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                Periode Pelaksanaan: <strong>2 Minggu</strong>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            @if($currentViewCycle && $currentViewCycle->is_locked)
                <span class="badge badge-danger">
                    Jadwal Sedang Dikunci Waka
                </span>
                <a href="{{ route('block-cycles.index') }}" class="btn btn-secondary btn-sm" style="font-size: 0.78rem;">
                    Kelola di Siklus Blok
                </a>
            @else
                <span class="badge badge-success">
                    Jadwal Tidak Dikunci
                </span>
            @endif
        </div>
    </div>

    {{-- Ringkasan Informasi jika Filter Ruangan Aktif --}}
    @if($selectedRoom)
        <div style="margin-bottom: 1.25rem; background: #eff6ff; border: 1px solid #bfdbfe; border-left: 5px solid #2563eb; color: #1e3a8a; border-radius: 8px; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #2563eb; margin-bottom: 0.15rem;">
                    Filter Khusus Pemakaian Ruangan
                </div>
                <div style="font-size: 1.2rem; font-weight: 800; color: #1e3a8a; line-height: 1.2;">
                    Jadwal Penggunaan Ruang: {{ $selectedRoom->name }} - Putaran {{ $selectedCycle }}
                </div>
                <div style="font-size: 0.85rem; color: #475569; margin-top: 0.35rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <span>Kategori: <strong>{{ $selectedRoom->type ?? 'Ruang Belajar' }}</strong></span>
                    <span>&bull;</span>
                    <span>Kapasitas: <strong>{{ $selectedRoom->capacity ?? 36 }} Siswa</strong></span>
                    <span>&bull;</span>
                    <span>Total Penggunaan: <strong style="color: #2563eb;">{{ $schedules->total() }} Sesi Mengajar</strong></span>
                </div>
            </div>
            <a href="{{ route('schedules.index', array_merge(request()->except('room_id'), ['cycle' => $selectedCycle, 'page' => 1])) }}" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; background: white; border: 1px solid #cbd5e1; color: #334155; font-weight: 600;">
                ✕ Hapus Filter Ruangan
            </a>
        </div>
    @endif

    {{-- Tabel jadwal --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    @if($selectedRoom)
                        <th>Hari</th>
                        <th>Jam Pelajaran</th>
                        <th>Kelas yang Memakai</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengajar</th>
                    @else
                        <th>Guru Pengajar</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Ruangan</th>
                        <th>Hari</th>
                        <th>Jam Pelajaran</th>
                    @endif
                    @if(auth()->user()->role === 'waka')
                        <th style="text-align: center;">Aksi</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse($schedules as $schedule)
                    <tr>
                        <td>{{ ($schedules->currentPage() - 1) * $schedules->perPage() + $loop->iteration }}</td>

                        @if($selectedRoom)
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #0f172a; font-weight: 700; border: 1px solid #cbd5e1; font-size: 0.82rem;">
                                    {{ $schedule->day }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #1e293b;">
                                    @if($schedule->start_period && $schedule->end_period)
                                        @if($schedule->start_period == $schedule->end_period)
                                            Jam ke-{{ $schedule->start_period }}
                                        @else
                                            Jam ke-{{ $schedule->start_period }} s.d. {{ $schedule->end_period }}
                                        @endif
                                    @else
                                        Sesi Khusus
                                    @endif
                                </div>
                                <small style="color: var(--text-muted); font-size: 0.75rem;">
                                    {{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.35rem 0.65rem; font-weight: 700;">
                                    {{ $schedule->schoolClass?->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a;">
                                    {{ $schedule->subject?->name ?? '-' }}
                                </div>
                                @if($schedule->subject?->code)
                                    <small style="color: var(--text-muted); font-size: 0.75rem;">Kode: {{ $schedule->subject->code }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $schedule->teacher?->name ?? '-' }}</strong>
                            </td>
                        @else
                            <td>
                                <strong>
                                    {{ $schedule->teacher?->name ?? '-' }}
                                </strong>
                            </td>

                            <td>
                                {{ $schedule->subject?->name ?? '-' }}
                            </td>

                            <td>
                                <span class="badge badge-info">
                                    {{ $schedule->schoolClass?->name ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <span style="font-weight: 600;">
                                    {{ $schedule->room?->name ?? '-' }}
                                </span>
                            </td>

                            <td>{{ $schedule->day }}</td>

                            <td>
                                <div style="font-weight: 600; color: #1e293b;">
                                    @if($schedule->start_period && $schedule->end_period)
                                        @if($schedule->start_period == $schedule->end_period)
                                            Jam ke-{{ $schedule->start_period }}
                                        @else
                                            Jam ke-{{ $schedule->start_period }} s.d. {{ $schedule->end_period }}
                                        @endif
                                    @else
                                        Sesi Khusus
                                    @endif
                                </div>
                                <small style="color: var(--text-muted); font-size: 0.75rem;">
                                    {{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}
                                </small>
                            </td>
                        @endif

                        @if(auth()->user()->role === 'waka')
                            <td style="text-align: center; white-space: nowrap;">
                                @if(!$schedule->blockCycle?->is_locked)
                                    {{-- Tombol Edit Modal (Tidak Berpindah Halaman) --}}
                                    <button
                                        type="button"
                                        class="btn btn-secondary btn-sm"
                                        onclick="openEditScheduleModal({{ $schedule->id }}, {{ $schedule->teacher_id }}, {{ $schedule->subject_id }}, {{ $schedule->class_id }}, {{ $schedule->room_id }}, '{{ $schedule->day }}', {{ $schedule->start_period ?? 1 }}, {{ $schedule->end_period ?? 2 }}, '{{ addslashes($schedule->blockCycle ? (str_starts_with(strtolower($schedule->blockCycle->cycle), 'putaran') ? $schedule->blockCycle->cycle : 'Putaran ' . $schedule->blockCycle->cycle) : '-') }}')"
                                    >
                                        Edit
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    <form
                                        action="{{ route('schedules.destroy', $schedule->id) }}"
                                        method="POST"
                                        style="display: inline;"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus jadwal ini?')"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                @else
                                    {{-- Jika Siklus Terkunci: Hanya Bisa Lihat --}}
                                    <a
                                        href="{{ route('schedules.show', $schedule->id) }}"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        Lihat
                                    </a>

                                    <span
                                        class="badge badge-warning"
                                        style="font-size: 0.75rem;"
                                    >
                                        Terkunci
                                    </span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="{{ auth()->user()->role === 'waka' ? ($selectedRoom ? 7 : 8) : ($selectedRoom ? 6 : 7) }}"
                            style="text-align: center; color: var(--text-muted); padding: 2.5rem;"
                        >
                            @if($selectedRoom)
                                Belum ada jadwal pemakaian untuk ruangan <strong>{{ $selectedRoom->name }}</strong> pada putaran ini.
                            @else
                                Belum ada jadwal mengajar pada putaran aktif saat ini.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Navigasi Halaman (Pagination) --}}
    @if ($schedules->hasPages() || $schedules->total() > 10)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color, #e2e8f0); flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.85rem; font-size: 0.85rem; color: var(--text-secondary);">
                <span>
                    Menampilkan <strong>{{ $schedules->firstItem() ?? 0 }}</strong> - <strong>{{ $schedules->lastItem() ?? 0 }}</strong> dari <strong>{{ $schedules->total() }}</strong> jadwal
                </span>
                <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <label for="per_page_select_schedule" style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Baris per hal:</label>
                    <select id="per_page_select_schedule" onchange="changePerPage(this.value)" style="padding: 0.2rem 0.4rem; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--border-color, #cbd5e1); background: #fff; cursor: pointer;">
                        @foreach([10, 15, 25, 50] as $opt)
                            <option value="{{ $opt }}" {{ $schedules->perPage() == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 0.35rem; align-items: center;">
                {{-- Tombol Pertama --}}
                @if ($schedules->currentPage() > 2)
                    <a href="{{ $schedules->url(1) }}" class="btn-page" title="Halaman Pertama">&laquo;&laquo;</a>
                @endif

                {{-- Tombol Sebelumnya --}}
                @if ($schedules->onFirstPage())
                    <span class="btn-page disabled">&laquo; Prev</span>
                @else
                    <a href="{{ $schedules->previousPageUrl() }}" class="btn-page">&laquo; Prev</a>
                @endif

                {{-- Link Nomor Halaman --}}
                @php
                    $startPage = max(1, $schedules->currentPage() - 2);
                    $endPage = min($schedules->lastPage(), $schedules->currentPage() + 2);
                @endphp
                @for ($p = $startPage; $p <= $endPage; $p++)
                    @if ($p == $schedules->currentPage())
                        <span class="btn-page active">{{ $p }}</span>
                    @else
                        <a href="{{ $schedules->url($p) }}" class="btn-page">{{ $p }}</a>
                    @endif
                @endfor

                {{-- Tombol Selanjutnya --}}
                @if ($schedules->hasMorePages())
                    <a href="{{ $schedules->nextPageUrl() }}" class="btn-page">Next &raquo;</a>
                @else
                    <span class="btn-page disabled">Next &raquo;</span>
                @endif

                {{-- Tombol Terakhir --}}
                @if ($schedules->currentPage() < $schedules->lastPage() - 1)
                    <a href="{{ $schedules->url($schedules->lastPage()) }}" class="btn-page" title="Halaman Terakhir">&raquo;&raquo;</a>
                @endif
            </div>
        </div>
    @endif

    {{-- Tombol kembali --}}
    <div style="margin-top: 1rem;">
        @if(auth()->user()->role === 'waka')
            <a href="{{ route('dashboard.waka') }}" class="btn-back">
                Kembali ke Dashboard Waka
            </a>
        @elseif(auth()->user()->role === 'guru')
            <a href="{{ route('dashboard.guru') }}" class="btn-back">
                Kembali ke Dashboard Guru
            </a>
        @else
            <a href="{{ url('/') }}" class="btn-back">
                Kembali
            </a>
        @endif
    </div>
</div>

{{-- Modal tambah jadwal hanya untuk Waka --}}
@if(auth()->user()->role === 'waka')
    <div class="modal-overlay" id="modalCreateSchedule">
        <div class="modal-card modal-lg">

            <div class="modal-header">
                <div class="modal-header-info">
                    <div class="modal-header-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <div>
                        <h3 class="modal-title">
                            Tambah Jadwal Mengajar
                        </h3>

                        <p class="modal-subtitle">
                            Tentukan putaran, guru, mata pelajaran, kelas, ruangan, dan jam
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="modal-close-btn"
                    onclick="closeModal('modalCreateSchedule')"
                >
                    &times;
                </button>
            </div>

            <form action="{{ route('schedules.store') }}" method="POST" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
                @csrf

                <div class="modal-body" style="max-height: 50vh; overflow-y: auto; padding: 1.25rem 1.5rem;">
                    <div class="grid-2">

                        {{-- Pilihan Putaran Blok (Hanya Putaran 1 dan Putaran 2) --}}
                        <div class="form-group" style="grid-column: span 2;">
                            <label for="modal_block_cycle_id">
                                Putaran Blok <span style="color: var(--danger);">*</span>
                            </label>
                            <select name="block_cycle_id" id="modal_block_cycle_id" class="form-control" required>
                                @foreach($blockCycles as $cycle)
                                    @php
                                        $namaPutaran = str_starts_with(strtolower($cycle->cycle), 'putaran') ? $cycle->cycle : 'Putaran ' . $cycle->cycle;
                                    @endphp
                                    <option value="{{ $cycle->id }}" {{ (old('block_cycle_id', $selectedCycleId ?? $activeCycle?->id) == $cycle->id) ? 'selected' : '' }}>
                                        {{ $namaPutaran }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Guru pengajar --}}
                        <div class="form-group">
                            <label for="teacher_id">
                                Guru Pengajar
                                <span style="color: var(--danger);">*</span>
                            </label>

                            <select name="teacher_id" id="teacher_id" required>
                                <option value="">-- Pilih Guru Pengajar --</option>

                                @foreach($teachers as $teacher)
                                    <option
                                        value="{{ $teacher->id }}"
                                        {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}
                                    >
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Mata pelajaran --}}
                        <div class="form-group">
                            <label for="subject_id">
                                Mata Pelajaran
                                <span style="color: var(--danger);">*</span>
                            </label>

                            <select name="subject_id" id="subject_id" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>

                                @foreach($subjects as $subject)
                                    <option
                                        value="{{ $subject->id }}"
                                        {{ old('subject_id') == $subject->id ? 'selected' : '' }}
                                    >
                                        {{ $subject->name }} ({{ $subject->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kelas --}}
                        <div class="form-group">
                            <label for="class_id">
                                Kelas
                                <span style="color: var(--danger);">*</span>
                            </label>

                            <select name="class_id" id="class_id" required>
                                <option value="">-- Pilih Kelas --</option>

                                @foreach($classes as $class)
                                    <option
                                        value="{{ $class->id }}"
                                        {{ old('class_id') == $class->id ? 'selected' : '' }}
                                    >
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Ruangan --}}
                        <div class="form-group">
                            <label for="room_id">
                                Ruangan
                                <span style="color: var(--danger);">*</span>
                            </label>

                            <select name="room_id" id="room_id" required>
                                <option value="">-- Pilih Ruangan --</option>

                                @foreach($rooms as $room)
                                    <option
                                        value="{{ $room->id }}"
                                        {{ old('room_id') == $room->id ? 'selected' : '' }}
                                    >
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Hari --}}
                        <div class="form-group">
                            <label for="day">
                                Hari
                                <span style="color: var(--danger);">*</span>
                            </label>

                            <select name="day" id="day" required>
                                <option value="">-- Pilih Hari --</option>

                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                                    <option
                                        value="{{ $day }}"
                                        {{ old('day') == $day ? 'selected' : '' }}
                                    >
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sesi Jam Pelajaran --}}
                        <div class="form-group">
                            <label>
                                Sesi Jam Pelajaran
                                <span style="color: var(--danger);">*</span>
                            </label>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                <div>
                                    <select name="start_period" id="modal_start_period" required style="width: 100%;">
                                        <option value="">-- Jam Mulai --</option>
                                        @foreach($periods as $num => $p)
                                            <option value="{{ $num }}" data-start="{{ $p['start'] }}" data-end="{{ $p['end'] }}"
                                                {{ old('start_period', 1) == $num ? 'selected' : '' }}>
                                                {{ $p['label'] }} ({{ $p['start'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small style="color: var(--text-muted); font-size: 0.75rem;">
                                        Mulai dari
                                    </small>
                                </div>

                                <div>
                                    <select name="end_period" id="modal_end_period" required style="width: 100%;">
                                        <option value="">-- Jam Selesai --</option>
                                        @foreach($periods as $num => $p)
                                            <option value="{{ $num }}" data-start="{{ $p['start'] }}" data-end="{{ $p['end'] }}"
                                                {{ old('end_period', 2) == $num ? 'selected' : '' }}>
                                                {{ $p['label'] }} ({{ $p['end'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small style="color: var(--text-muted); font-size: 0.75rem;">
                                        Sampai dengan
                                    </small>
                                </div>
                            </div>

                            <div id="modal-period-preview" style="background: rgba(37, 99, 235, 0.08); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 6px; padding: 0.5rem 0.75rem; margin-top: 0.5rem; font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
                                <span id="modal-preview-text" style="font-weight: 500; color: #1e293b;">
                                    Jam ke-1 s.d. 2 (07:00 - 08:30 WIB)
                                </span>
                                <span id="modal-preview-badge" class="badge badge-info" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                    2 Jam Pelajaran
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer" style="padding: 1rem 1.5rem; background-color: #ffffff; border-top: 1px solid var(--border-color); flex-shrink: 0;">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="closeModal('modalCreateSchedule')"
                    >
                        Batal
                    </button>

                    <button type="submit" class="btn btn-primary">
                        ✓ Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- MODAL IN-PAGE: EDIT JADWAL (TIDAK BERPINDAH HALAMAN)       --}}
    {{-- ========================================================= --}}
    <div class="modal-overlay" id="modalEditSchedule">
        <div class="modal-card modal-lg">
            <div class="modal-header">
                <div class="modal-header-info">
                    <div class="modal-header-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="modal-title">Edit Jadwal Mengajar</h3>
                        <p class="modal-subtitle">Perbarui guru, mata pelajaran, kelas, ruangan, dan sesi jam pelajaran</p>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalEditSchedule')">&times;</button>
            </div>

            <form id="formEditSchedule" method="POST" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
                @csrf
                @method('PUT')

                <div class="modal-body" style="max-height: 50vh; overflow-y: auto; padding: 1.25rem 1.5rem;">
                    <div class="grid-2">

                        {{-- Info Putaran Blok (Read Only) --}}
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Putaran Blok</label>
                            <input type="text" id="edit_schedule_cycle_display" class="form-control" readonly style="background-color: var(--bg-hover); cursor: not-allowed; font-weight: 600; color: var(--text-primary);">
                        </div>

                        {{-- Guru pengajar --}}
                        <div class="form-group">
                            <label for="edit_schedule_teacher_id">
                                Guru Pengajar <span style="color: var(--danger);">*</span>
                            </label>
                            <select name="teacher_id" id="edit_schedule_teacher_id" required>
                                <option value="">-- Pilih Guru Pengajar --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Mata pelajaran --}}
                        <div class="form-group">
                            <label for="edit_schedule_subject_id">
                                Mata Pelajaran <span style="color: var(--danger);">*</span>
                            </label>
                            <select name="subject_id" id="edit_schedule_subject_id" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kelas --}}
                        <div class="form-group">
                            <label for="edit_schedule_class_id">
                                Kelas <span style="color: var(--danger);">*</span>
                            </label>
                            <select name="class_id" id="edit_schedule_class_id" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Ruangan --}}
                        <div class="form-group">
                            <label for="edit_schedule_room_id">
                                Ruangan <span style="color: var(--danger);">*</span>
                            </label>
                            <select name="room_id" id="edit_schedule_room_id" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Hari --}}
                        <div class="form-group">
                            <label for="edit_schedule_day">
                                Hari <span style="color: var(--danger);">*</span>
                            </label>
                            <select name="day" id="edit_schedule_day" required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sesi Jam Pelajaran --}}
                        <div class="form-group">
                            <label>
                                Sesi Jam Pelajaran <span style="color: var(--danger);">*</span>
                            </label>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                <div>
                                    <select name="start_period" id="edit_modal_start_period" required style="width: 100%;">
                                        <option value="">-- Jam Mulai --</option>
                                        @foreach($periods as $num => $p)
                                            <option value="{{ $num }}" data-start="{{ $p['start'] }}" data-end="{{ $p['end'] }}">
                                                {{ $p['label'] }} ({{ $p['start'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small style="color: var(--text-muted); font-size: 0.75rem;">Mulai dari</small>
                                </div>

                                <div>
                                    <select name="end_period" id="edit_modal_end_period" required style="width: 100%;">
                                        <option value="">-- Jam Selesai --</option>
                                        @foreach($periods as $num => $p)
                                            <option value="{{ $num }}" data-start="{{ $p['start'] }}" data-end="{{ $p['end'] }}">
                                                {{ $p['label'] }} ({{ $p['end'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small style="color: var(--text-muted); font-size: 0.75rem;">Sampai dengan</small>
                                </div>
                            </div>

                            <div id="edit-modal-period-preview" style="background: rgba(37, 99, 235, 0.08); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 6px; padding: 0.5rem 0.75rem; margin-top: 0.5rem; font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
                                <span id="edit-modal-preview-text" style="font-weight: 500; color: #1e293b;">
                                    Jam ke-1 s.d. 2 (07:00 - 08:30 WIB)
                                </span>
                                <span id="edit-modal-preview-badge" class="badge badge-info" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                    2 Jam Pelajaran
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer" style="padding: 1rem 1.5rem; background-color: #ffffff; border-top: 1px solid var(--border-color); flex-shrink: 0;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSchedule')">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        ✓ Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- MODAL IMPORT CSV / EXCEL JADWAL PELAJARAN                  --}}
    {{-- ========================================================= --}}
    <div class="modal-overlay" id="modalImportSchedule">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-header-info">
                    <div class="modal-header-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--accent);">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="modal-title">Import Jadwal Mengajar</h3>
                        <p class="modal-subtitle">Unggah berkas PDF (.pdf), Excel (.xlsx, .xls), atau CSV (.csv)</p>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalImportSchedule')">&times;</button>
            </div>

            <form action="{{ route('schedules.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
                @csrf
                <div class="modal-body" style="padding: 1.5rem; max-height: 60vh; overflow-y: auto;">
                    <div style="background-color: var(--bg-surface-hover, #f8fafc); border: 2px dashed var(--border-color); border-radius: var(--radius-md, 8px); padding: 1.5rem; text-align: center; margin-bottom: 1.25rem;">
                        <label for="import_schedule_file" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary); cursor: pointer;">
                            Pilih Berkas PDF / Excel / CSV (.pdf, .xlsx, .xls, .csv, .docx)
                        </label>
                        <input type="file" name="file" id="import_schedule_file" accept=".pdf, .xlsx, .xls, .csv, .docx, .txt" required style="width: 100%; max-width: 320px; margin: 0 auto; display: block; font-size: 0.85rem;">
                        <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.5rem;">
                            Maksimal ukuran file 20MB. Format didukung: PDF (.pdf), Excel (.xlsx, .xls), CSV (.csv), Word (.docx)
                        </small>
                    </div>

                    {{-- Pilihan Putaran Jadwal --}}
                    <div class="form-group" style="text-align: left; margin-bottom: 1.25rem;">
                        <label style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.5rem; display: block;">
                            Pilih Putaran Jadwal:
                        </label>
                        <div style="display: flex; gap: 1.5rem; align-items: center; background: #f8fafc; padding: 0.75rem 1.25rem; border-radius: 8px; border: 1px solid var(--border-color);">
                            <label style="display: inline-flex; align-items: center; gap: 0.5rem; margin: 0; cursor: pointer; font-weight: 700; font-size: 0.9rem; color: #1e293b;">
                                <input type="radio" name="block_cycle" value="1" {{ ($selectedCycle ?? 1) == 1 ? 'checked' : '' }} style="cursor: pointer; width: 18px; height: 18px;">
                                <span>Putaran 1</span>
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 0.5rem; margin: 0; cursor: pointer; font-weight: 700; font-size: 0.9rem; color: #1e293b;">
                                <input type="radio" name="block_cycle" value="2" {{ ($selectedCycle ?? 1) == 2 ? 'checked' : '' }} style="cursor: pointer; width: 18px; height: 18px;">
                                <span>Putaran 2</span>
                            </label>
                        </div>
                        <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.35rem;">
                            Seluruh jadwal yang diimpor dari berkas akan disimpan ke putaran yang dipilih.
                        </small>
                    </div>

                    <div class="form-group" style="text-align: left; margin-bottom: 1.25rem; background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px; padding: 0.75rem 1rem;">
                        <label style="display: flex; align-items: flex-start; gap: 0.6rem; cursor: pointer; margin: 0; font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">
                            <input type="checkbox" name="bypass_conflict" value="1" checked style="margin-top: 0.2rem; cursor: pointer; width: 16px; height: 16px;">
                            <div>
                                <span>Bypass Pengecekan Bentrok (Disarankan untuk Berkas Jadwal Kurikulum)</span>
                                <small style="display: block; font-weight: normal; color: var(--text-muted); font-size: 0.76rem; margin-top: 0.2rem; line-height: 1.4;">
                                    Jika dicentang, seluruh data jadwal dari file kurikulum akan tetap disimpan ke basis data dan tidak dibatalkan saat terjadi potensi jadwal ganda/bentrok. Rincian bentrok akan ditampilkan sebagai catatan peringatan.
                                </small>
                            </div>
                        </label>
                    </div>

                    <div class="form-group" style="text-align: left; margin-bottom: 1.25rem;">
                        <label for="import_default_day" style="font-weight: 600; font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.35rem; display: block;">
                            Pilihan Hari (Opsional jika berkas tidak memiliki kolom 'Hari')
                        </label>
                        <select name="default_day" id="import_default_day" class="form-control" style="width: 100%;">
                            <option value="">Otomatis (Baca dari kolom 'Hari' di berkas CSV/PDF)</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                        </select>
                        <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.35rem;">
                            Jika berkas tidak memuat kolom 'Hari', seluruh jadwal pada berkas akan otomatis dialokasikan ke hari yang dipilih di atas (default: Senin).
                        </small>
                    </div>

                    <div style="background: rgba(99, 102, 241, 0.05); border-left: 3px solid #6366f1; padding: 0.85rem 1rem; border-radius: 4px; font-size: 0.8rem; line-height: 1.5; color: var(--text-secondary);">
                        <strong style="color: var(--text-primary); display: block; margin-bottom: 0.35rem;">Petunjuk Format Berkas Jadwal:</strong>
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            <li><strong>Jadwal Kelas (FET):</strong> Mengimpor seluruh mata pelajaran, guru pengajar, rombel kelas, dan jam pelajaran.</li>
                            <li><strong>Jadwal Ruangan (Matriks PDF Resmi):</strong> Mengunggah berkas denah ruangan (contoh: <em>JADWAL RUANGAN PUTARAN 1 / 2</em>) akan otomatis menyinkronkan seluruh rombel kelas ke ruangan fisik aslinya (R. 1 s/d R. 48, L.1 s/d L.22, HALL, OR).</li>
                            <li>Mendukung file dokumen <strong>PDF (.pdf)</strong>, <strong>Excel (.xlsx, .xls)</strong>, dan <strong>CSV (.csv)</strong> secara langsung.</li>
                            <li><strong>Pilihan Putaran:</strong> Pastikan memilih Putaran 1 atau Putaran 2 sesuai berkas yang diunggah.</li>
                            <li>Sistem otomatis mengenali nama guru meskipun terdapat gelar akademik (Drs, S.Pd, M.Kom, dll.) dan memvalidasi alokasi jam.</li>
                        </ul>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 1rem 1.5rem; background-color: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalImportSchedule')">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Mulai Import Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
window.allLessonPeriods = @json($allLessonPeriods);

function populatePeriodsForDay(day, startSelectId, endSelectId, defaultStart, defaultEnd) {
    const periods = window.allLessonPeriods[day] || window.allLessonPeriods['Senin'] || {};
    const startSelect = document.getElementById(startSelectId);
    const endSelect = document.getElementById(endSelectId);
    if (!startSelect || !endSelect) return;

    const currentStartVal = defaultStart !== undefined ? parseInt(defaultStart, 10) : parseInt(startSelect.value, 10) || 1;
    const currentEndVal = defaultEnd !== undefined ? parseInt(defaultEnd, 10) : parseInt(endSelect.value, 10) || 2;

    startSelect.innerHTML = '<option value="">-- Jam Mulai --</option>';
    endSelect.innerHTML = '<option value="">-- Jam Selesai --</option>';

    const keys = Object.keys(periods).map(k => parseInt(k, 10)).sort((a, b) => a - b);
    const maxPeriod = keys.length ? Math.max(...keys) : 10;

    keys.forEach(num => {
        const p = periods[num];
        const optStart = document.createElement('option');
        optStart.value = num;
        optStart.setAttribute('data-start', p.start);
        optStart.setAttribute('data-end', p.end);
        optStart.textContent = `${p.label} (${p.start})`;
        if (num === Math.min(currentStartVal, maxPeriod)) {
            optStart.selected = true;
        }
        startSelect.appendChild(optStart);

        const optEnd = document.createElement('option');
        optEnd.value = num;
        optEnd.setAttribute('data-start', p.start);
        optEnd.setAttribute('data-end', p.end);
        optEnd.textContent = `${p.label} (${p.end})`;
        if (num === Math.min(currentEndVal, maxPeriod)) {
            optEnd.selected = true;
        }
        endSelect.appendChild(optEnd);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Handler Modal Create
    const createDaySelect = document.getElementById('day');
    const startSelect = document.getElementById('modal_start_period');
    const endSelect = document.getElementById('modal_end_period');
    const previewText = document.getElementById('modal-preview-text');
    const previewBadge = document.getElementById('modal-preview-badge');
    const previewBox = document.getElementById('modal-period-preview');

    window.updateModalPreview = function() {
        if (!startSelect || !endSelect || !previewText || !previewBadge || !previewBox) return;

        const startOpt = startSelect.options[startSelect.selectedIndex];
        const endOpt = endSelect.options[endSelect.selectedIndex];

        if (!startOpt || !endOpt || !startOpt.value || !endOpt.value) {
            previewText.textContent = 'Pilih jam mulai dan selesai.';
            previewBadge.textContent = '-';
            return;
        }

        const startPeriod = parseInt(startOpt.value, 10);
        const endPeriod = parseInt(endOpt.value, 10);
        const startTime = startOpt.getAttribute('data-start');
        const endTime = endOpt.getAttribute('data-end');

        if (endPeriod < startPeriod) {
            previewBox.style.background = 'rgba(239, 68, 68, 0.08)';
            previewBox.style.borderColor = 'rgba(239, 68, 68, 0.25)';
            previewText.textContent = 'Peringatan: Jam selesai harus sama atau setelah jam mulai!';
            previewBadge.className = 'badge badge-danger';
            previewBadge.textContent = 'Tidak Valid';
            return;
        }

        previewBox.style.background = 'rgba(37, 99, 235, 0.08)';
        previewBox.style.borderColor = 'rgba(37, 99, 235, 0.2)';
        previewBadge.className = 'badge badge-info';

        const totalHours = (endPeriod - startPeriod) + 1;
        previewBadge.textContent = totalHours + ' Jam Pelajaran';

        if (startPeriod === endPeriod) {
            previewText.textContent = `Jam ke-${startPeriod} (${startTime} - ${endTime} WIB)`;
        } else {
            previewText.textContent = `Jam ke-${startPeriod} s.d. ${endPeriod} (${startTime} - ${endTime} WIB)`;
        }
    };

    if (createDaySelect) {
        createDaySelect.addEventListener('change', function() {
            const day = this.value || 'Senin';
            populatePeriodsForDay(day, 'modal_start_period', 'modal_end_period', 1, 2);
            updateModalPreview();
        });

        const initDay = createDaySelect.value || 'Senin';
        populatePeriodsForDay(initDay, 'modal_start_period', 'modal_end_period', 1, 2);
    }

    if (startSelect && endSelect) {
        startSelect.addEventListener('change', function () {
            if (parseInt(endSelect.value, 10) < parseInt(startSelect.value, 10)) {
                endSelect.value = startSelect.value;
            }
            updateModalPreview();
        });

        endSelect.addEventListener('change', updateModalPreview);
        updateModalPreview();
    }

    // Handler Modal Edit
    const editDaySelect = document.getElementById('edit_schedule_day');
    const editStartSelect = document.getElementById('edit_modal_start_period');
    const editEndSelect = document.getElementById('edit_modal_end_period');
    const editPreviewText = document.getElementById('edit-modal-preview-text');
    const editPreviewBadge = document.getElementById('edit-modal-preview-badge');
    const editPreviewBox = document.getElementById('edit-modal-period-preview');

    window.updateEditModalPreview = function() {
        if (!editStartSelect || !editEndSelect || !editPreviewText || !editPreviewBadge || !editPreviewBox) return;

        const startOpt = editStartSelect.options[editStartSelect.selectedIndex];
        const endOpt = editEndSelect.options[editEndSelect.selectedIndex];

        if (!startOpt || !endOpt || !startOpt.value || !endOpt.value) {
            editPreviewText.textContent = 'Pilih jam mulai dan selesai.';
            editPreviewBadge.textContent = '-';
            return;
        }

        const startPeriod = parseInt(startOpt.value, 10);
        const endPeriod = parseInt(endOpt.value, 10);
        const startTime = startOpt.getAttribute('data-start');
        const endTime = endOpt.getAttribute('data-end');

        if (endPeriod < startPeriod) {
            editPreviewBox.style.background = 'rgba(239, 68, 68, 0.08)';
            editPreviewBox.style.borderColor = 'rgba(239, 68, 68, 0.25)';
            editPreviewText.textContent = 'Peringatan: Jam selesai harus sama atau setelah jam mulai!';
            editPreviewBadge.className = 'badge badge-danger';
            editPreviewBadge.textContent = 'Tidak Valid';
            return;
        }

        editPreviewBox.style.background = 'rgba(37, 99, 235, 0.08)';
        editPreviewBox.style.borderColor = 'rgba(37, 99, 235, 0.2)';
        editPreviewBadge.className = 'badge badge-info';

        const totalHours = (endPeriod - startPeriod) + 1;
        editPreviewBadge.textContent = totalHours + ' Jam Pelajaran';

        if (startPeriod === endPeriod) {
            editPreviewText.textContent = `Jam ke-${startPeriod} (${startTime} - ${endTime} WIB)`;
        } else {
            editPreviewText.textContent = `Jam ke-${startPeriod} s.d. ${endPeriod} (${startTime} - ${endTime} WIB)`;
        }
    };

    if (editDaySelect) {
        editDaySelect.addEventListener('change', function() {
            const day = this.value || 'Senin';
            const currStart = editStartSelect.value || 1;
            const currEnd = editEndSelect.value || 2;
            populatePeriodsForDay(day, 'edit_modal_start_period', 'edit_modal_end_period', currStart, currEnd);
            updateEditModalPreview();
        });
    }

    if (editStartSelect && editEndSelect) {
        editStartSelect.addEventListener('change', function () {
            if (parseInt(editEndSelect.value, 10) < parseInt(editStartSelect.value, 10)) {
                editEndSelect.value = editStartSelect.value;
            }
            updateEditModalPreview();
        });

        editEndSelect.addEventListener('change', updateEditModalPreview);
    }
});

function openEditScheduleModal(id, teacherId, subjectId, classId, roomId, day, startPeriod, endPeriod, cycleName) {
    const form = document.getElementById('formEditSchedule');
    form.action = `/schedules/${id}`;

    const cycleDisplay = document.getElementById('edit_schedule_cycle_display');
    if (cycleDisplay && cycleName) {
        cycleDisplay.value = cycleName;
    }

    document.getElementById('edit_schedule_teacher_id').value = teacherId;
    document.getElementById('edit_schedule_subject_id').value = subjectId;
    document.getElementById('edit_schedule_class_id').value = classId;
    document.getElementById('edit_schedule_room_id').value = roomId;
    document.getElementById('edit_schedule_day').value = day;

    populatePeriodsForDay(day, 'edit_modal_start_period', 'edit_modal_end_period', startPeriod, endPeriod);

    if (window.updateEditModalPreview) {
        window.updateEditModalPreview();
    }
    openModal('modalEditSchedule');
}
</script>
@endif

<style>
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
</style>

<script>
function changePerPage(limit) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', limit);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}
</script>
@endsection