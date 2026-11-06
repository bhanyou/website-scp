<style>
/* Floating Action Bar: Selalu Melayang Mengikuti Scroll Pengguna */
.floating-bulk-bar {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1050;
    display: none;
    align-items: center;
    animation: floatBarIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes floatBarIn {
    from {
        opacity: 0;
        transform: translate(-50%, 20px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0) scale(1);
    }
}

.floating-bulk-content {
    background: #1e293b;
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 9999px;
    padding: 0.5rem 0.75rem 0.5rem 1.25rem;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25), 0 4px 10px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.floating-bulk-badge {
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
}

.floating-bulk-badge .pulse-dot {
    width: 8px;
    height: 8px;
    background-color: #ef4444;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: pulseRed 1.8s infinite;
}

@keyframes pulseRed {
    0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
    }
}

.floating-bulk-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-bulk-delete {
    background-color: #ef4444;
    color: #ffffff;
    border: none;
    border-radius: 9999px;
    padding: 0.45rem 1rem;
    font-size: 0.825rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: background-color 0.15s ease, transform 0.1s ease;
}

.btn-bulk-delete:hover {
    background-color: #dc2626;
    transform: translateY(-1px);
}

.btn-bulk-cancel {
    background: transparent;
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 9999px;
    padding: 0.45rem 0.85rem;
    font-size: 0.825rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.15s ease, color 0.15s ease;
}

.btn-bulk-cancel:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

/* Tombol Navigasi Halaman */
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

@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        @if(auth()->user()->role === 'bk' || (auth()->user()->teacher && auth()->user()->teacher->isBkTeacher()))
            <h1 class="page-title">Data Siswa Binaan</h1>
            <p class="page-subtitle">Daftar siswa pada kelas binaan Anda: <strong>{{ auth()->user()->teacher?->bkClasses->pluck('name')->join(', ') }}</strong></p>
        @else
            <h1 class="page-title">Data Siswa</h1>
            <p class="page-subtitle">Daftar seluruh siswa terdaftar, kelas, dan informasi akun login</p>
        @endif
    </div>
    @if(auth()->user()->role === 'waka')
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalImportStudent')">
            Import Siswa (Excel / PDF / CSV)
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modalCreateStudent')">
            + Tambah Manual
        </button>
        @if($students->count() > 0)
            <form action="{{ route('students.destroy-all') }}" method="POST" style="margin: 0;" onsubmit="return confirm('PERINGATAN: Seluruh data siswa beserta akun pengguna siswa akan dihapus secara permanen.\n\nApakah Anda yakin ingin menghapus SEMUA data siswa?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="background-color: #ef4444; color: white; border: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <span>🗑️</span> Hapus Semua Siswa
                </button>
            </form>
        @endif
    </div>
    @endif
</div>

{{-- Filter Data Siswa --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('students.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1.5; min-width: 180px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Cari Siswa
            </label>
            <input type="text" name="search" class="form-control" placeholder="Ketik nama atau NIS siswa..." value="{{ request('search') }}" style="width: 100%;">
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Jurusan
            </label>
            <select name="major" class="form-control" style="width: 100%;">
                <option value="">Semua Jurusan</option>
                @foreach($majors as $m)
                    @php
                        $code = is_object($m) ? $m->code : $m;
                        $label = is_object($m) && isset($m->name) && $m->name !== $m->code ? "{$m->code} - {$m->name}" : $code;
                    @endphp
                    <option value="{{ $code }}" {{ request('major') == $code ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 130px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Tingkat
            </label>
            <select name="grade" class="form-control" style="width: 100%;">
                <option value="">Semua Tingkat</option>
                <option value="10" {{ request('grade') == '10' ? 'selected' : '' }}>Kelas 10 (X)</option>
                <option value="11" {{ request('grade') == '11' ? 'selected' : '' }}>Kelas 11 (XI)</option>
                <option value="12" {{ request('grade') == '12' ? 'selected' : '' }}>Kelas 12 (XII)</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 130px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Rombel
            </label>
            <select name="rombel" class="form-control" style="width: 100%;">
                <option value="">Semua Rombel</option>
                <option value="1" {{ request('rombel') == '1' ? 'selected' : '' }}>Rombel 1</option>
                <option value="2" {{ request('rombel') == '2' ? 'selected' : '' }}>Rombel 2</option>
                <option value="3" {{ request('rombel') == '3' ? 'selected' : '' }}>Rombel 3</option>
                <option value="4" {{ request('rombel') == '4' ? 'selected' : '' }}>Rombel 4</option>
            </select>
        </div>

        <div style="flex: 1.2; min-width: 160px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Pilih Kelas Langsung
            </label>
            <select name="class_id" class="form-control" style="width: 100%;">
                <option value="">Semua Kelas</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.25rem;">
                Filter
            </button>
            @if(request()->filled('search') || request()->filled('major') || request()->filled('grade') || request()->filled('rombel') || request()->filled('class_id'))
                <a href="{{ route('students.index') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

@if($activeFilteredClass)
    <div style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">
                Informasi Rombongan Belajar
            </div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--accent); margin-top: 0.2rem;">
                Kelas {{ $activeFilteredClass->name }} (Jurusan {{ $activeFilteredClass->major }})
            </div>
            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                Wali Kelas: <strong>{{ $activeFilteredClass->homeroomTeacher?->name ?? 'Belum Ditentukan' }}</strong>
            </div>
        </div>
        <div>
            <span class="badge badge-info" style="font-size: 0.9rem; padding: 0.45rem 0.85rem;">
                {{ $students->count() }} Siswa di Kelas Ini
            </span>
        </div>
    </div>
@endif

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">
                Daftar Siswa Terdaftar
            </span>
            <span class="badge badge-info" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                {{ $students->total() }} Siswa Ditemukan
            </span>
        </div>

    </div>

    {{-- Floating Bulk Action Bar (Mengikuti Scroll Layar di Bawah) --}}
    <div id="bulkActionBar" class="floating-bulk-bar">
        <div class="floating-bulk-content">
            <div class="floating-bulk-badge">
                <span class="pulse-dot"></span>
                <span><strong id="selectedCountLabel">0</strong> siswa dipilih</span>
            </div>
            <div class="floating-bulk-actions">
                <button type="button" class="btn-bulk-delete" onclick="submitBulkDestroy()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Hapus Siswa Terpilih</span>
                </button>
                <button type="button" class="btn-bulk-cancel" onclick="clearSelection()" title="Batalkan pilihan">
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- Form tersembunyi untuk proses Bulk Delete --}}
    <form id="formBulkDestroy" action="{{ route('students.bulk-destroy') }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- Tabel Responsif: Diringkas menjadi 8 kolom fleksibel tanpa scroll horizontal berlebih --}}
    <div class="table-container" style="margin-top: 0.5rem; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 36px; text-align: center;">
                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" title="Pilih Semua Siswa di Halaman Ini" style="cursor: pointer; width: 16px; height: 16px;">
                    </th>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th style="width: 135px;">NIS & Akun</th>
                    <th>Nama Siswa & Kontak</th>
                    <th style="width: 110px;">Kelas</th>
                    <th style="width: 180px;">Orang Tua / Wali</th>
                    <th style="max-width: 200px;">Alamat</th>
                    <th style="width: 115px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr id="student-row-{{ $student->id }}" style="transition: background-color 0.15s ease;">
                        <td style="text-align: center;">
                            <input type="checkbox" class="student-select-cb" value="{{ $student->id }}" onchange="updateSelectedCount()" style="cursor: pointer; width: 16px; height: 16px;">
                        </td>
                        <td style="text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                            {{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem;">{{ $student->nis }}</div>
                            <small style="display: inline-block; margin-top: 2px;">
                                <code style="color: var(--accent); font-weight: 600; font-size: 0.78rem; background: rgba(37, 99, 235, 0.08); padding: 1px 6px; border-radius: 4px;">{{ $student->username ?? $student->user?->username ?? '-' }}</code>
                            </small>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-primary); font-size: 0.885rem;">{{ $student->name }}</div>
                            @if(!empty($student->phone) || !empty($student->user?->email))
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
                                    @if(!empty($student->phone))
                                        <span><span style="font-weight: 600; text-transform: uppercase;">Telp:</span> {{ $student->phone }}</span>
                                    @endif
                                    @if(!empty($student->user?->email))
                                        <span><span style="font-weight: 600; text-transform: uppercase;">Email:</span> {{ $student->user->email }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info" style="display: inline-block; padding: 0.3rem 0.6rem; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.3px;">
                                {{ $student->schoolClass?->name ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 500; font-size: 0.85rem; color: var(--text-primary);">{{ $student->parent?->name ?? ('Orang Tua ' . $student->name) }}</div>
                            <small style="display: inline-block; margin-top: 2px;">
                                <code style="color: #16a34a; background: rgba(22, 163, 74, 0.08); padding: 1px 6px; border-radius: 3px; font-weight: 600; font-size: 0.75rem;" title="Username Login Orang Tua">
                                    {{ $student->parent?->user?->username ?? ('p' . ($student->username ?? $student->user?->username ?? $student->nis)) }}
                                </code>
                            </small>
                        </td>
                        <td style="max-width: 200px;">
                            <div style="font-size: 0.8rem; color: var(--text-secondary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35;" title="{{ $student->address }}">
                                @if(!empty($student->address))
                                    <span style="color: var(--text-muted); font-size: 0.74rem; font-weight: 600; text-transform: uppercase;">Alamat:</span> {{ $student->address }}
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            @if(auth()->user()->role === 'waka')
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm"
                                    style="padding: 0.3rem 0.6rem; font-size: 0.78rem;"
                                    onclick="openEditStudentModal({{ $student->id }}, '{{ addslashes($student->nis) }}', '{{ addslashes($student->username ?? $student->user?->username ?? '') }}', '{{ addslashes($student->name) }}', {{ $student->class_id ?? 'null' }}, {{ $student->parent_id ?? 'null' }}, '{{ addslashes($student->user?->email ?? '') }}', '{{ addslashes($student->phone ?? '') }}', '{{ addslashes($student->address ?? '') }}')"
                                >
                                    Edit
                                </button>
                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem;" onclick="return confirm('Yakin ingin menghapus data siswa ini?')">
                                        Hapus
                                    </button>
                                </form>
                            @else
                                <span class="badge badge-secondary" style="font-size: 0.75rem;">Siswa Binaan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            Belum ada data siswa yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Navigasi Halaman (Pagination) --}}
    @if ($students->hasPages() || $students->total() > 10)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color, #e2e8f0); flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.85rem; font-size: 0.85rem; color: var(--text-secondary);">
                <span>
                    Menampilkan <strong>{{ $students->firstItem() ?? 0 }}</strong> - <strong>{{ $students->lastItem() ?? 0 }}</strong> dari <strong>{{ $students->total() }}</strong> siswa
                </span>
                <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <label for="per_page_select_student" style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Baris per hal:</label>
                    <select id="per_page_select_student" onchange="changePerPage(this.value)" style="padding: 0.2rem 0.4rem; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--border-color, #cbd5e1); background: #fff; cursor: pointer;">
                        @foreach([10, 15, 25, 50, 100] as $opt)
                            <option value="{{ $opt }}" {{ $students->perPage() == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 0.35rem; align-items: center;">
                {{-- Tombol Pertama --}}
                @if ($students->currentPage() > 2)
                    <a href="{{ $students->url(1) }}" class="btn-page" title="Halaman Pertama">&laquo;&laquo;</a>
                @endif

                {{-- Tombol Sebelumnya --}}
                @if ($students->onFirstPage())
                    <span class="btn-page disabled">&laquo; Prev</span>
                @else
                    <a href="{{ $students->previousPageUrl() }}" class="btn-page">&laquo; Prev</a>
                @endif

                {{-- Link Nomor Halaman --}}
                @php
                    $startPage = max(1, $students->currentPage() - 2);
                    $endPage = min($students->lastPage(), $students->currentPage() + 2);
                @endphp
                @for ($p = $startPage; $p <= $endPage; $p++)
                    @if ($p == $students->currentPage())
                        <span class="btn-page active">{{ $p }}</span>
                    @else
                        <a href="{{ $students->url($p) }}" class="btn-page">{{ $p }}</a>
                    @endif
                @endfor

                {{-- Tombol Selanjutnya --}}
                @if ($students->hasMorePages())
                    <a href="{{ $students->nextPageUrl() }}" class="btn-page">Next &raquo;</a>
                @else
                    <span class="btn-page disabled">Next &raquo;</span>
                @endif

                {{-- Tombol Terakhir --}}
                @if ($students->currentPage() < $students->lastPage() - 1)
                    <a href="{{ $students->url($students->lastPage()) }}" class="btn-page" title="Halaman Terakhir">&raquo;&raquo;</a>
                @endif
            </div>
        </div>
    @endif

    <div style="margin-top: 1.25rem;">
        <a href="{{ route('dashboard.waka') }}" class="btn-back">
            Kembali ke Dashboard Waka
        </a>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: IMPORT DATA SISWA DARI EXCEL / CSV         --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalImportStudent">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Import Data Siswa</h3>
                <p class="modal-subtitle">Unggah data siswa dari file Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), atau CSV (.csv) dari penyimpanan komputer lokal</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalImportStudent')">&times;</button>
        </div>

        <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body" style="padding: 1.5rem;">
                <div style="background-color: var(--bg-surface-hover, #f8fafc); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; text-align: center; margin-bottom: 1.25rem;">
                    <label for="import_file" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary); cursor: pointer;">
                        Pilih File Excel / Dokumen (.xlsx, .xls, .csv, .docx, .pdf)
                    </label>
                    <input type="file" name="file" id="import_file" accept=".xlsx, .xls, .csv, .docx, .pdf, .txt" required style="width: 100%; max-width: 320px; margin: 0 auto; display: block; font-size: 0.85rem;">
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.5rem;">
                        Maksimal ukuran file 5MB. Format didukung: Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), CSV (.csv)
                    </small>
                </div>

                <div style="background: rgba(99, 102, 241, 0.05); border-left: 3px solid #6366f1; padding: 0.85rem 1rem; border-radius: 4px; font-size: 0.8rem; line-height: 1.5; color: var(--text-secondary);">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.35rem;">Petunjuk Format Kolom File:</strong>
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        <li>Kolom wajib: <strong>nis</strong>, <strong>nama</strong>, dan <strong>kelas</strong> (kolom opsional: <strong>username</strong>, <strong>email</strong>, <strong>password</strong>, dll). (contoh: <em>XII RPL 2</em>, <em>X RPL 1</em>).</li>
                        <li>Kolom opsional: <strong>email</strong>, <strong>password</strong>, <strong>no_telepon</strong>, dan <strong>alamat</strong>.</li>
                        <li>Jika email kosong, sistem otomatis membuatkan email dari NIS siswa.</li>
                        <li>Jika password kosong, sistem memberikan password default <em>12345678</em>.</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background-color: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalImportStudent')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Mulai Import Data
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: TAMBAH DATA & AKUN SISWA                   --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalCreateStudent">
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Tambah Data & Akun Siswa</h3>
                <p class="modal-subtitle">Daftarkan identitas siswa dan buatkan akun login baru</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalCreateStudent')">&times;</button>
        </div>

        <form action="{{ route('students.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body" style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1; min-height: 0;">
                <div class="grid-2">
                    <div class="form-group">
                        <label for="create_student_nis">Nomor Induk Siswa (NIS) <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nis" id="create_student_nis" placeholder="Contoh: 20241001" required>
                    </div>

                    <div class="form-group">
                        <label for="create_student_username">Username Siswa <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Opsional, default: siswa1, siswa2, dst.)</span></label>
                        <input type="text" name="username" id="create_student_username" placeholder="Contoh: siswa1">
                    </div>

                    <div class="form-group">
                        <label for="create_student_name">Nama Lengkap Siswa <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="name" id="create_student_name" placeholder="Nama lengkap siswa" required>
                    </div>

                    <div class="form-group">
                        <label for="create_student_class_id">Kelas <span style="color: var(--danger);">*</span></label>
                        <select name="class_id" id="create_student_class_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->major }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_student_parent_id">Orang Tua / Wali (Opsional)</label>
                        <select name="parent_id" id="create_student_parent_id">
                            <option value="">-- Belum Ditentukan / Tidak Ada --</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_student_email">Email Akun Login Siswa <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Opsional)</span></label>
                        <input type="email" name="email" id="create_student_email" placeholder="Kosongkan jika belum ada">
                    </div>

                    <div class="form-group">
                        <label for="create_student_password">Password Akun Login Siswa <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Opsional, default: 12345678)</span></label>
                        <input type="password" name="password" id="create_student_password" placeholder="Default: 12345678">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="create_student_phone">Nomor Telepon / WhatsApp Siswa (Opsional)</label>
                        <input type="text" name="phone" id="create_student_phone" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="create_student_address">Alamat Domisili Siswa (Opsional)</label>
                        <textarea name="address" id="create_student_address" rows="2" placeholder="Masukkan alamat lengkap domisili siswa..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background-color: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateStudent')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: EDIT DATA SISWA                            --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalEditStudent">
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Data Siswa</h3>
                <p class="modal-subtitle">Perbarui identitas, kelas, informasi akun, dan alamat siswa</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalEditStudent')">&times;</button>
        </div>

        <form id="formEditStudent" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            @method('PUT')
            <div class="modal-body" style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1; min-height: 0;">
                <div class="grid-2">
                    <div class="form-group">
                        <label for="edit_student_nis">Nomor Induk Siswa (NIS) <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nis" id="edit_student_nis" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_student_username">Username Akun Siswa</label>
                        <input type="text" name="username" id="edit_student_username" placeholder="Username login siswa">
                    </div>

                    <div class="form-group">
                        <label for="edit_student_name">Nama Lengkap Siswa <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="name" id="edit_student_name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_student_class_id">Kelas <span style="color: var(--danger);">*</span></label>
                        <select name="class_id" id="edit_student_class_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->major }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_student_parent_id">Orang Tua / Wali (Opsional)</label>
                        <select name="parent_id" id="edit_student_parent_id">
                            <option value="">-- Belum Ditentukan / Tidak Ada --</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_student_email">Email Akun Login <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Opsional - masukkan email jika siswa sudah memberikan)</span></label>
                        <input type="email" name="email" id="edit_student_email" placeholder="Contoh: siswa@gmail.com (opsional)">
                    </div>

                    <div class="form-group">
                        <label for="edit_student_password">Password Baru <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" id="edit_student_password" placeholder="Minimal 6 karakter jika ingin diubah">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="edit_student_phone">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="edit_student_phone">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="edit_student_address">Alamat Domisili Siswa</label>
                        <textarea name="address" id="edit_student_address" rows="2" placeholder="Masukkan alamat lengkap siswa"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background-color: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditStudent')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.student-select-cb');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.student-select-cb:checked');
    const count = checked.length;
    const bar = document.getElementById('bulkActionBar');
    const countLabel = document.getElementById('selectedCountLabel');
    const master = document.getElementById('selectAllCheckbox');
    const allCbs = document.querySelectorAll('.student-select-cb');

    if (countLabel) countLabel.textContent = count;
    if (bar) {
        bar.style.display = count > 0 ? 'flex' : 'none';
    }
    if (master && allCbs.length > 0) {
        master.checked = (count === allCbs.length);
        master.indeterminate = (count > 0 && count < allCbs.length);
    }

    // Highlight visual baris yang dipilih
    allCbs.forEach(cb => {
        const tr = cb.closest('tr');
        if (tr) {
            tr.style.backgroundColor = cb.checked ? 'rgba(239, 68, 68, 0.05)' : '';
        }
    });
}

function submitBulkDestroy() {
    const checked = document.querySelectorAll('.student-select-cb:checked');
    if (checked.length === 0) {
        alert('Silakan pilih minimal satu siswa yang ingin dihapus.');
        return;
    }
    if (!confirm(`PERINGATAN:\nApakah Anda yakin ingin menghapus ${checked.length} data siswa terpilih beserta akun loginnya secara permanen?`)) {
        return;
    }
    const form = document.getElementById('formBulkDestroy');
    form.innerHTML = `
        @csrf
        @method('DELETE')
    `;
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'student_ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });
    form.submit();
}

function clearSelection() {
    document.querySelectorAll('.student-select-cb').forEach(cb => cb.checked = false);
    const master = document.getElementById('selectAllCheckbox');
    if (master) {
        master.checked = false;
        master.indeterminate = false;
    }
    updateSelectedCount();
}


function changePerPage(limit) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', limit);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}

function openEditStudentModal(id, nis, username, name, classId, parentId, email, phone, address) {
    const form = document.getElementById('formEditStudent');
    form.action = `/students/${id}`;

    document.getElementById('edit_student_nis').value = nis;
    document.getElementById('edit_student_username').value = username || '';
    document.getElementById('edit_student_name').value = name;
    document.getElementById('edit_student_class_id').value = classId || '';
    document.getElementById('edit_student_parent_id').value = parentId || '';
    document.getElementById('edit_student_email').value = email || '';
    document.getElementById('edit_student_phone').value = phone || '';
    document.getElementById('edit_student_address').value = address || '';
    document.getElementById('edit_student_password').value = '';

    openModal('modalEditStudent');
}
</script>
@endsection