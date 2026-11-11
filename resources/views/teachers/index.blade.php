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
        <h1 class="page-title">Data Guru</h1>
        <p class="page-subtitle">Daftar tenaga pendidik dan pengajar sekolah</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalImportTeacher')">
            Import Guru (Excel / PDF / CSV)
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modalCreateTeacher')">
            + Tambah Manual
        </button>
        @if($teachers->count() > 0)
            <form action="{{ route('teachers.destroy-all') }}" method="POST" style="margin: 0;" onsubmit="return confirm('PERINGATAN: Seluruh data guru (kecuali akun Waka) beserta akun pengguna guru akan dihapus secara permanen.\n\nApakah Anda yakin ingin menghapus SEMUA data guru?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="background-color: #ef4444; color: white; border: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    Hapus Semua Guru
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Filter Data Guru --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('teachers.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1.5; min-width: 200px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Cari Guru
            </label>
            <input type="text" name="search" class="form-control" placeholder="Ketik nama, NIP, username, atau email..." value="{{ request('search') }}" style="width: 100%;">
        </div>

        <div style="flex: 1; min-width: 160px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Status Penugasan
            </label>
            <select name="homeroom_status" class="form-control" style="width: 100%;">
                <option value="">Semua Guru</option>
                <option value="is_homeroom" {{ request('homeroom_status') == 'is_homeroom' ? 'selected' : '' }}>Wali Kelas Saja</option>
                <option value="not_homeroom" {{ request('homeroom_status') == 'not_homeroom' ? 'selected' : '' }}>Bukan Wali Kelas</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 160px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Kelas Wali
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
            @if(request()->filled('search') || request()->filled('homeroom_status') || request()->filled('class_id'))
                <a href="{{ route('teachers.index') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">
                Daftar Guru Terdaftar
            </span>
            <span class="badge badge-info" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                {{ $teachers->total() }} Guru Ditemukan
            </span>
        </div>

    </div>

    {{-- Floating Bulk Action Bar (Mengikuti Scroll Layar di Bawah) --}}
    <div id="bulkActionBar" class="floating-bulk-bar">
        <div class="floating-bulk-content">
            <div class="floating-bulk-badge">
                <span class="pulse-dot"></span>
                <span><strong id="selectedCountLabel">0</strong> guru dipilih</span>
            </div>
            <div class="floating-bulk-actions">
                <button type="button" class="btn-bulk-delete" onclick="submitBulkDestroy()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Hapus Guru Terpilih</span>
                </button>
                <button type="button" class="btn-bulk-cancel" onclick="clearSelection()" title="Batalkan pilihan">
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- Form tersembunyi untuk proses Bulk Delete --}}
    <form id="formBulkDestroy" action="{{ route('teachers.bulk-destroy') }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- Tabel Responsif: Diringkas menjadi 7 kolom fleksibel tanpa scroll horizontal berlebih --}}
    <div class="table-container" style="margin-top: 0.5rem; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 36px; text-align: center;">
                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" title="Pilih Semua Guru di Halaman Ini" style="cursor: pointer; width: 16px; height: 16px;">
                    </th>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th style="width: 140px;">NIP</th>
                    <th>Nama Lengkap Guru</th>
                    <th style="width: 115px;">Username</th>
                    <th style="width: 160px;">Mata Pelajaran</th>
                    <th style="width: 110px;">Wali Kelas</th>
                    <th style="width: 200px;">Kontak & Alamat</th>
                    <th style="width: 115px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $teacher)
                    <tr id="teacher-row-{{ $teacher->id }}" style="transition: background-color 0.15s ease;">
                        <td style="text-align: center;">
                            <input type="checkbox" class="teacher-select-cb" value="{{ $teacher->id }}" onchange="updateSelectedCount()" style="cursor: pointer; width: 16px; height: 16px;">
                        </td>
                        <td style="text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                            {{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}
                        </td>
                        <td>
                            <strong style="font-size: 0.85rem; color: var(--text-primary);">{{ $teacher->nip ?? '-' }}</strong>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.885rem;">{{ $teacher->name }}</div>
                        </td>
                        <td>
                            <code style="color: #4f46e5; background: rgba(79, 70, 229, 0.08); padding: 2px 8px; border-radius: 4px; font-size: 0.78rem; font-weight: 600;">
                                {{ $teacher->username ?? ($teacher->user->username ?? '-') }}
                            </code>
                        </td>
                        <td>
                            @php
                                $schedSubjects = $teacher->schedules ? $teacher->schedules->pluck('subject.name')->unique()->filter()->values() : collect();
                            @endphp
                            @if(!empty($teacher->mapel))
                                <span class="badge badge-info" style="font-size: 0.78rem; font-weight: 600; padding: 0.3rem 0.6rem; display: inline-block; line-height: 1.3;">
                                    {{ $teacher->mapel }}
                                </span>
                            @elseif($schedSubjects->isNotEmpty())
                                <span class="badge badge-info" style="font-size: 0.78rem; font-weight: 600; padding: 0.3rem 0.6rem; display: inline-block; line-height: 1.3;" title="{{ $schedSubjects->implode(', ') }}">
                                    {{ $schedSubjects->take(2)->implode(', ') }}{{ $schedSubjects->count() > 2 ? ' (+' . ($schedSubjects->count() - 2) . ')' : '' }}
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.82rem;">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($teacher->homeroomClasses->isNotEmpty())
                                @foreach($teacher->homeroomClasses as $hClass)
                                    <span class="badge badge-success" style="font-size: 0.78rem; padding: 0.3rem 0.6rem; display: inline-block;">
                                        {{ $hClass->name }}
                                    </span>
                                @endforeach
                            @else
                                <span style="color: var(--text-muted); font-size: 0.8rem; font-style: italic;">Bukan Wali Kelas</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 4px; font-size: 0.82rem;">
                                @if(!empty($teacher->phone))
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-size: 0.68rem; font-weight: 700; color: #475569; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid #e2e8f0;">Telp</span>
                                        <span style="color: var(--text-primary); font-weight: 500;">{{ $teacher->phone }}</span>
                                    </div>
                                @endif
                                @if(!empty($teacher->user?->email))
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-size: 0.68rem; font-weight: 700; color: #475569; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid #e2e8f0;">Email</span>
                                        <span style="color: var(--text-secondary); font-size: 0.78rem;">{{ $teacher->user->email }}</span>
                                    </div>
                                @endif
                                @if(!empty($teacher->address))
                                    <div style="display: flex; align-items: flex-start; gap: 6px; margin-top: 1px;">
                                        <span style="font-size: 0.68rem; font-weight: 700; color: #475569; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid #e2e8f0; flex-shrink: 0;">Alamat</span>
                                        <span style="color: var(--text-secondary); font-size: 0.78rem; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $teacher->address }}">{{ $teacher->address }}</span>
                                    </div>
                                @endif
                                @if(empty($teacher->phone) && empty($teacher->user?->email) && empty($teacher->address))
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button
                                type="button"
                                class="btn btn-secondary btn-sm"
                                style="padding: 0.3rem 0.6rem; font-size: 0.78rem;"
                                onclick="openEditTeacherModal({{ $teacher->id }}, '{{ addslashes($teacher->nip ?? '') }}', '{{ addslashes($teacher->username ?? ($teacher->user->username ?? '')) }}', '{{ addslashes($teacher->name) }}', '{{ addslashes($teacher->mapel ?? '') }}', '{{ addslashes($teacher->user->email ?? '') }}', '{{ addslashes($teacher->phone ?? '') }}', '{{ addslashes($teacher->address ?? '') }}', '{{ $teacher->homeroomClasses->first()?->id ?? '' }}')"
                            >
                                Edit
                            </button>
                            <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.3rem 0.55rem; font-size: 0.78rem;" onclick="return confirm('Yakin ingin menghapus guru ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            Belum ada data guru yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Navigasi Halaman (Pagination) --}}
    @if ($teachers->hasPages() || $teachers->total() > 10)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color, #e2e8f0); flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.85rem; font-size: 0.85rem; color: var(--text-secondary);">
                <span>
                    Menampilkan <strong>{{ $teachers->firstItem() ?? 0 }}</strong> - <strong>{{ $teachers->lastItem() ?? 0 }}</strong> dari <strong>{{ $teachers->total() }}</strong> guru
                </span>
                <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <label for="per_page_select_teacher" style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Baris per hal:</label>
                    <select id="per_page_select_teacher" onchange="changePerPage(this.value)" style="padding: 0.2rem 0.4rem; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--border-color, #cbd5e1); background: #fff; cursor: pointer;">
                        @foreach([10, 15, 25, 50, 100] as $opt)
                            <option value="{{ $opt }}" {{ $teachers->perPage() == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 0.35rem; align-items: center;">
                {{-- Tombol Pertama --}}
                @if ($teachers->currentPage() > 2)
                    <a href="{{ $teachers->url(1) }}" class="btn-page" title="Halaman Pertama">&laquo;&laquo;</a>
                @endif

                {{-- Tombol Sebelumnya --}}
                @if ($teachers->onFirstPage())
                    <span class="btn-page disabled">&laquo; Prev</span>
                @else
                    <a href="{{ $teachers->previousPageUrl() }}" class="btn-page">&laquo; Prev</a>
                @endif

                {{-- Link Nomor Halaman --}}
                @php
                    $startPage = max(1, $teachers->currentPage() - 2);
                    $endPage = min($teachers->lastPage(), $teachers->currentPage() + 2);
                @endphp
                @for ($p = $startPage; $p <= $endPage; $p++)
                    @if ($p == $teachers->currentPage())
                        <span class="btn-page active">{{ $p }}</span>
                    @else
                        <a href="{{ $teachers->url($p) }}" class="btn-page">{{ $p }}</a>
                    @endif
                @endfor

                {{-- Tombol Selanjutnya --}}
                @if ($teachers->hasMorePages())
                    <a href="{{ $teachers->nextPageUrl() }}" class="btn-page">Next &raquo;</a>
                @else
                    <span class="btn-page disabled">Next &raquo;</span>
                @endif

                {{-- Tombol Terakhir --}}
                @if ($teachers->currentPage() < $teachers->lastPage() - 1)
                    <a href="{{ $teachers->url($teachers->lastPage()) }}" class="btn-page" title="Halaman Terakhir">&raquo;&raquo;</a>
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
{{-- MODAL IN-PAGE: IMPORT DATA GURU DARI EXCEL / CSV          --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalImportTeacher">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Import Data Guru</h3>
                <p class="modal-subtitle">Unggah data guru dari file Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), atau CSV (.csv) dari penyimpanan komputer lokal</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalImportTeacher')">&times;</button>
        </div>

        <form action="{{ route('teachers.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body" style="padding: 1.5rem;">
                <div style="background-color: var(--bg-surface-hover, #f8fafc); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; text-align: center; margin-bottom: 1.25rem;">
                    <label for="import_guru_file" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary); cursor: pointer;">
                        Pilih File Excel / Dokumen (.xlsx, .xls, .csv, .docx, .pdf)
                    </label>
                    <input type="file" name="file" id="import_guru_file" accept=".xlsx, .xls, .csv, .docx, .pdf, .txt" required style="width: 100%; max-width: 320px; margin: 0 auto; display: block; font-size: 0.85rem;">
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.5rem;">
                        Maksimal ukuran file 5MB. Format didukung: Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), CSV (.csv)
                    </small>
                </div>

                <div style="background: rgba(99, 102, 241, 0.05); border-left: 3px solid #6366f1; padding: 0.85rem 1rem; border-radius: 4px; font-size: 0.8rem; line-height: 1.5; color: var(--text-secondary);">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.35rem;">Mendukung 2 Jenis Berkas Import:</strong>
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        <li><strong>1. Berkas Daftar Wali Kelas & Guru BK:</strong> Jika berkas memuat kolom <em>Kelas</em>, <em>Nama Wali Kelas</em>, dan opsional <em>Nama Guru BK</em> (meskipun <strong>tanpa NIP</strong>), sistem secara cerdas akan mencocokkan guru berdasarkan kesesuaian nama dan menugaskannya langsung sebagai wali kelas!</li>
                        <li><strong>2. Berkas Master Data Guru:</strong> Kolom utama <strong>NIP</strong> dan <strong>Nama Guru</strong>. Filter cerdas otomatis menyaring agar hanya guru pengajar yang tersimpan (melewati TU, Outsourcing, Sheet 1, Satpam, dll).</li>
                        <li><strong>Otomatisasi Akun:</strong> Seluruh akun guru baru otomatis dibuatkan akun login dengan password <code>password</code>.</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background-color: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalImportTeacher')">
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
{{-- MODAL IN-PAGE: TAMBAH DATA GURU (TIDAK BERPINDAH)        --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalCreateTeacher">
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <div class="modal-header-info">
                <div>
                    <h3 class="modal-title">Tambah Data Guru</h3>
                    <p class="modal-subtitle">Daftarkan akun dan profil tenaga pendidik baru</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalCreateTeacher')">&times;</button>
        </div>

        <form action="{{ route('teachers.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label for="create_teacher_nip">NIP Guru <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nip" id="create_teacher_nip" placeholder="Contoh: 198501152010011002" maxlength="18" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        <small style="color: var(--text-muted); font-size: 0.72rem;">Hanya angka, 8-18 digit.</small>
                    </div>

                    <div class="form-group">
                        <label for="create_teacher_username">Username Login (Opsional)</label>
                        <input type="text" name="username" id="create_teacher_username" placeholder="Contoh: guru1 (otomatis jika dikosongkan)">
                        <small style="color: var(--text-muted); font-size: 0.72rem;">Jika kosong, otomatis berurutan (guru1, guru2, dst).</small>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="create_teacher_name">Nama Lengkap & Gelar <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="name" id="create_teacher_name" placeholder="Contoh: Budi Santoso, S.Kom" required>
                    </div>

                    <div class="form-group">
                        <label for="create_teacher_mapel">Mata Pelajaran (Mapel)</label>
                        <select name="mapel" id="create_teacher_mapel" class="form-control" style="width: 100%;">
                            <option value="">-- Pilih Mata Pelajaran (Opsional) --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->name }}">
                                    {{ $subject->name }} ({{ $subject->code }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: var(--text-muted); font-size: 0.72rem;">Diambil langsung dari master data Mapel.</small>
                    </div>

                    <div class="form-group">
                        <label for="create_teacher_email">Email Akun Login <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Opsional)</span></label>
                        <input type="email" name="email" id="create_teacher_email" placeholder="budi@sekolah.sch.id (boleh dikosongkan)">
                    </div>

                    <div class="form-group">
                        <label for="create_teacher_password">Password Login <span style="color: var(--danger);">*</span></label>
                        <input type="password" name="password" id="create_teacher_password" placeholder="Minimal 8 karakter" required>
                    </div>

                    <div class="form-group">
                        <label for="create_teacher_phone">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="create_teacher_phone" placeholder="081234567890">
                    </div>

                    <div class="form-group">
                        <label for="create_teacher_address">Alamat Domisili</label>
                        <input type="text" name="address" id="create_teacher_address" placeholder="Jl. Merdeka No. 10">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="create_teacher_homeroom">Tugaskan Sebagai Wali Kelas (Opsional)</label>
                        <select name="homeroom_class_id" id="create_teacher_homeroom">
                            <option value="">-- Bukan Wali Kelas (Guru Pengajar Saja) --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">
                                    Wali Kelas {{ $class->name }} (Tingkat {{ $class->grade }} - {{ $class->major }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: var(--text-muted); font-size: 0.75rem;">
                            Pilih kelas jika guru baru ini sekaligus ditugaskan sebagai wali kelas.
                        </small>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateTeacher')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan Data Guru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: EDIT DATA GURU (TIDAK BERPINDAH HALAMAN)   --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalEditTeacher">
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <div class="modal-header-info">
                <div>
                    <h3 class="modal-title">Edit Data Guru</h3>
                    <p class="modal-subtitle">Perbarui data profil dan informasi akun guru</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalEditTeacher')">&times;</button>
        </div>

        <form id="formEditTeacher" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label for="edit_teacher_nip">NIP Guru <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nip" id="edit_teacher_nip" maxlength="18" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        <small style="color: var(--text-muted); font-size: 0.72rem;">Hanya angka, 8-18 digit.</small>
                    </div>

                    <div class="form-group">
                        <label for="edit_teacher_username">Username Login</label>
                        <input type="text" name="username" id="edit_teacher_username" placeholder="Contoh: guru1">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="edit_teacher_name">Nama Lengkap & Gelar <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="name" id="edit_teacher_name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_teacher_mapel">Mata Pelajaran (Mapel)</label>
                        <select name="mapel" id="edit_teacher_mapel" class="form-control" style="width: 100%;">
                            <option value="">-- Pilih Mata Pelajaran (Opsional) --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->name }}">
                                    {{ $subject->name }} ({{ $subject->code }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: var(--text-muted); font-size: 0.72rem;">Diambil langsung dari master data Mapel.</small>
                    </div>

                    <div class="form-group">
                        <label for="edit_teacher_email">Email Akun Login <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Opsional)</span></label>
                        <input type="email" name="email" id="edit_teacher_email" placeholder="budi@sekolah.sch.id">
                    </div>

                    <div class="form-group">
                        <label for="edit_teacher_password">Password Baru <span style="font-weight: normal; color: var(--text-muted); font-size: 0.8rem;">(Kosongkan jika tidak diganti)</span></label>
                        <input type="password" name="password" id="edit_teacher_password" placeholder="Minimal 8 karakter jika ingin diubah">
                    </div>

                    <div class="form-group">
                        <label for="edit_teacher_phone">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="edit_teacher_phone">
                    </div>

                    <div class="form-group">
                        <label for="edit_teacher_address">Alamat Domisili</label>
                        <input type="text" name="address" id="edit_teacher_address">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="edit_teacher_homeroom">Tugaskan Sebagai Wali Kelas (Opsional)</label>
                        <select name="homeroom_class_id" id="edit_teacher_homeroom">
                            <option value="">-- Bukan Wali Kelas (Guru Pengajar Saja) --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">
                                    Wali Kelas {{ $class->name }} (Tingkat {{ $class->grade }} - {{ $class->major }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: var(--text-muted); font-size: 0.75rem;">
                            Pilih kelas jika guru ini sekaligus ditugaskan sebagai wali kelas.
                        </small>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditTeacher')">
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
    const checkboxes = document.querySelectorAll('.teacher-select-cb');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.teacher-select-cb:checked');
    const count = checked.length;
    const bar = document.getElementById('bulkActionBar');
    const countLabel = document.getElementById('selectedCountLabel');
    const master = document.getElementById('selectAllCheckbox');
    const allCbs = document.querySelectorAll('.teacher-select-cb');

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
    const checked = document.querySelectorAll('.teacher-select-cb:checked');
    if (checked.length === 0) {
        window.showCustomAlert('Silakan pilih minimal satu guru yang ingin dihapus.');
        return;
    }
    const msg = `PERINGATAN:\nApakah Anda yakin ingin menghapus ${checked.length} data guru terpilih beserta akun loginnya secara permanen?`;
    window.showCustomConfirm(msg, function(confirmed) {
        if (!confirmed) return;
        const form = document.getElementById('formBulkDestroy');
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'teacher_ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });
        form.submit();
    });
}

function clearSelection() {
    document.querySelectorAll('.teacher-select-cb').forEach(cb => cb.checked = false);
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

function openEditTeacherModal(id, nip, username, name, mapel, email, phone, address, homeroomClassId) {
    const form = document.getElementById('formEditTeacher');
    form.action = `/teachers/${id}`;

    document.getElementById('edit_teacher_nip').value = nip;
    document.getElementById('edit_teacher_username').value = username || '';
    document.getElementById('edit_teacher_name').value = name;
    document.getElementById('edit_teacher_mapel').value = mapel || '';
    document.getElementById('edit_teacher_email').value = email || '';
    document.getElementById('edit_teacher_phone').value = phone || '';
    document.getElementById('edit_teacher_address').value = address || '';
    document.getElementById('edit_teacher_password').value = '';
    document.getElementById('edit_teacher_homeroom').value = homeroomClassId || '';

    openModal('modalEditTeacher');
}
</script>
@endsection