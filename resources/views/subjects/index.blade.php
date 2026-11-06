@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Mata Pelajaran</h1>
        <p class="page-subtitle">Daftar kurikulum, kategori keahlian, dan alokasi beban jam pelajaran</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('subjects.template') }}" class="btn btn-secondary btn-sm">
            Unduh Format CSV
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalImportSubject')">
            Import Mapel (Excel / PDF / CSV)
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modalCreateSubject')">
            + Tambah Manual
        </button>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">NO</th>
                    <th style="width: 140px;">KODE MAPEL</th>
                    <th>NAMA MATA PELAJARAN</th>
                    <th style="width: 150px;">KATEGORI</th>
                    <th style="width: 130px; text-align: center;">BEBAN (JP)</th>
                    <th style="width: 140px; text-align: right;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subjects as $subject)
                    @php
                        $cat = $subject->category ?? 'Umum';
                    @endphp
                    <tr>
                        <td>{{ ($subjects->currentPage() - 1) * $subjects->perPage() + $loop->iteration }}</td>
                        <td>
                            <span class="badge" style="background-color: var(--bg-surface-hover, #f1f5f9); color: var(--text-primary, #0f172a); border: 1px solid var(--border-color, #cbd5e1); font-family: monospace; font-size: 0.8rem; font-weight: 700;">
                                {{ $subject->code }}
                            </span>
                        </td>
                        <td><strong>{{ $subject->name }}</strong></td>
                        <td>
                            @if($cat === 'Kejuruan')
                                <span class="badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span style="font-size: 0.8rem;">💻</span> Kejuruan
                                </span>
                            @elseif($cat === 'Olahraga')
                                <span class="badge" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span style="font-size: 0.8rem;">⚽</span> Olahraga
                                </span>
                            @else
                                <span class="badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span style="font-size: 0.8rem;">📖</span> Umum
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: 700; color: var(--text-primary);">
                                {{ $subject->total_hours ?? 2 }} JP
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" 
                                class="btn btn-secondary btn-sm" 
                                onclick="openEditSubjectModal({{ $subject->id }}, '{{ addslashes($subject->code) }}', '{{ addslashes($subject->name) }}', '{{ addslashes($cat) }}', {{ $subject->total_hours ?? 2 }})">
                                Edit
                            </button>
                            <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus mata pelajaran ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            Belum ada mata pelajaran yang ditambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Navigasi Halaman (Pagination) --}}
    @if ($subjects->hasPages() || $subjects->total() > 10)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color, #e2e8f0); flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.85rem; font-size: 0.85rem; color: var(--text-secondary);">
                <span>
                    Menampilkan <strong>{{ $subjects->firstItem() ?? 0 }}</strong> - <strong>{{ $subjects->lastItem() ?? 0 }}</strong> dari <strong>{{ $subjects->total() }}</strong> mata pelajaran
                </span>
                <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <label for="per_page_select_subject" style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Baris per hal:</label>
                    <select id="per_page_select_subject" onchange="changePerPage(this.value)" style="padding: 0.2rem 0.4rem; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--border-color, #cbd5e1); background: #fff; cursor: pointer;">
                        @foreach([10, 15, 25, 50] as $opt)
                            <option value="{{ $opt }}" {{ $subjects->perPage() == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 0.35rem; align-items: center;">
                {{-- Tombol Pertama --}}
                @if ($subjects->currentPage() > 2)
                    <a href="{{ $subjects->url(1) }}" class="btn-page" title="Halaman Pertama">&laquo;&laquo;</a>
                @endif

                {{-- Tombol Sebelumnya --}}
                @if ($subjects->onFirstPage())
                    <span class="btn-page disabled">&laquo; Prev</span>
                @else
                    <a href="{{ $subjects->previousPageUrl() }}" class="btn-page">&laquo; Prev</a>
                @endif

                {{-- Link Nomor Halaman --}}
                @php
                    $startPage = max(1, $subjects->currentPage() - 2);
                    $endPage = min($subjects->lastPage(), $subjects->currentPage() + 2);
                @endphp
                @for ($p = $startPage; $p <= $endPage; $p++)
                    @if ($p == $subjects->currentPage())
                        <span class="btn-page active">{{ $p }}</span>
                    @else
                        <a href="{{ $subjects->url($p) }}" class="btn-page">{{ $p }}</a>
                    @endif
                @endfor

                {{-- Tombol Selanjutnya --}}
                @if ($subjects->hasMorePages())
                    <a href="{{ $subjects->nextPageUrl() }}" class="btn-page">Next &raquo;</a>
                @else
                    <span class="btn-page disabled">Next &raquo;</span>
                @endif

                {{-- Tombol Terakhir --}}
                @if ($subjects->currentPage() < $subjects->lastPage() - 1)
                    <a href="{{ $subjects->url($subjects->lastPage()) }}" class="btn-page" title="Halaman Terakhir">&raquo;&raquo;</a>
                @endif
            </div>
        </div>
    @endif

    <div style="margin-top: 1rem;">
        <a href="{{ route('dashboard.waka') }}" class="btn-back">
            Kembali ke Dashboard Waka
        </a>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: IMPORT MATA PELAJARAN DARI EXCEL / CSV     --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalImportSubject">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Import Mata Pelajaran</h3>
                <p class="modal-subtitle">Unggah data mata pelajaran dari file Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), atau CSV (.csv) dari penyimpanan komputer lokal</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalImportSubject')">&times;</button>
        </div>

        <form action="{{ route('subjects.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body" style="padding: 1.5rem;">
                <div style="background-color: var(--bg-surface-hover, #f8fafc); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; text-align: center; margin-bottom: 1.25rem;">
                    <label for="import_subject_file" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary); cursor: pointer;">
                        Pilih File Excel / Dokumen (.xlsx, .xls, .csv, .docx, .pdf)
                    </label>
                    <input type="file" name="file" id="import_subject_file" accept=".xlsx, .xls, .csv, .docx, .pdf, .txt" required style="width: 100%; max-width: 320px; margin: 0 auto; display: block; font-size: 0.85rem;">
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.5rem;">
                        Maksimal ukuran file 5MB. Format didukung: Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), CSV (.csv)
                    </small>
                </div>

                <div style="background: rgba(99, 102, 241, 0.05); border-left: 3px solid #6366f1; padding: 0.85rem 1rem; border-radius: 4px; font-size: 0.8rem; line-height: 1.5; color: var(--text-secondary);">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.35rem;">Petunjuk Format Kolom File:</strong>
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        <li>Kolom wajib: <strong>kode_mapel</strong> (contoh: <em>MP-001</em>) dan <strong>nama_mapel</strong> (contoh: <em>Pemrograman Web</em>).</li>
                        <li>Kolom opsional: <strong>kategori</strong> (<em>Kejuruan</em>, <em>Umum</em>, atau <em>Olahraga</em> - default: <em>Umum</em>).</li>
                        <li>Kolom opsional: <strong>beban_jp</strong> (alokasi jam pelajaran, default: <em>2</em>).</li>
                        <li>Jika kode mata pelajaran sudah ada di database, data kategori dan beban JP akan diperbarui otomatis tanpa membuat data ganda.</li>
                    </ul>
                    <div style="margin-top: 0.75rem;">
                        <a href="{{ route('subjects.template') }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                            Unduh Contoh Format File CSV (.csv)
                        </a>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background-color: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalImportSubject')">
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
{{-- MODAL IN-PAGE: TAMBAH MATA PELAJARAN (TIDAK BERPINDAH)   --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalCreateSubject">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Tambah Mata Pelajaran</h3>
                <p class="modal-subtitle">Tambahkan mata pelajaran baru ke kurikulum</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalCreateSubject')">&times;</button>
        </div>

        <form action="{{ route('subjects.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_code">Kode Mata Pelajaran <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="code" id="create_code" placeholder="Contoh: MP-001 / RPL-01" required>
                </div>

                <div class="form-group">
                    <label for="create_name">Nama Mata Pelajaran <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" id="create_name" placeholder="Contoh: Pemrograman Web" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="create_category">Kategori Mapel</label>
                        <select name="category" id="create_category">
                            <option value="Umum" selected>Umum</option>
                            <option value="Kejuruan">Kejuruan</option>
                            <option value="Olahraga">Olahraga</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_total_hours">Beban Jam Pelajaran (JP)</label>
                        <input type="number" name="total_hours" id="create_total_hours" value="2" min="1" max="20" placeholder="2">
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateSubject')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan Mata Pelajaran
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: EDIT MATA PELAJARAN (TIDAK BERPINDAH)     --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalEditSubject">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Mata Pelajaran</h3>
                <p class="modal-subtitle">Perbarui data kurikulum mata pelajaran</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalEditSubject')">&times;</button>
        </div>

        <form id="formEditSubject" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_code">Kode Mata Pelajaran <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="code" id="edit_code" required>
                </div>

                <div class="form-group">
                    <label for="edit_name">Nama Mata Pelajaran <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" id="edit_name" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="edit_category">Kategori Mapel</label>
                        <select name="category" id="edit_category">
                            <option value="Umum">Umum</option>
                            <option value="Kejuruan">Kejuruan</option>
                            <option value="Olahraga">Olahraga</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_total_hours">Beban Jam Pelajaran (JP)</label>
                        <input type="number" name="total_hours" id="edit_total_hours" min="1" max="20" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSubject')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

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

    function openEditSubjectModal(id, code, name, category, totalHours) {
        const form = document.getElementById('formEditSubject');
        form.action = `/subjects/${id}`;
        document.getElementById('edit_code').value = code;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_category').value = category || 'Umum';
        document.getElementById('edit_total_hours').value = totalHours || 2;
        openModal('modalEditSubject');
    }
</script>
@endsection
