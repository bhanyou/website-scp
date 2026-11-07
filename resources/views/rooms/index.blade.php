@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Data Ruangan</h1>
        <p class="page-subtitle">Daftar ruangan kelas, laboratorium, tipe, dan kapasitas siswa</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalImportRoom')">
            Import Ruangan (Excel / PDF / CSV)
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modalCreateRoom')">
            + Tambah Manual
        </button>
        @if($rooms->count() > 0)
            <form action="{{ route('rooms.destroy-all') }}" method="POST" style="margin: 0;" onsubmit="return confirm('PERINGATAN: Seluruh data ruangan akan dihapus secara permanen.\n\nApakah Anda yakin ingin menghapus SEMUA data ruangan?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="background-color: #ef4444; color: white; border: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <span>🗑️</span> Hapus Semua Ruangan
                </button>
            </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">NO</th>
                    <th>NAMA RUANGAN</th>
                    <th style="width: 150px;">TIPE</th>
                    <th style="width: 160px; text-align: center;">KAPASITAS</th>
                    <th style="width: 140px; text-align: right;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rooms as $room)
                    @php
                        $type = $room->type_label;
                    @endphp
                    <tr>
                        <td>{{ ($rooms->currentPage() - 1) * $rooms->perPage() + $loop->iteration }}</td>
                        <td><strong>{{ $room->name }}</strong></td>
                        <td>
                            @if($type === 'Praktik')
                                <span class="badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">
                                    Praktik
                                </span>
                            @elseif($type === 'Olahraga')
                                <span class="badge" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">
                                    Olahraga
                                </span>
                            @else
                                <span class="badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">
                                    Teori
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if(!empty($room->capacity))
                                <span>{{ $room->capacity }} Siswa</span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" 
                                class="btn btn-secondary btn-sm" 
                                onclick="openEditRoomModal({{ $room->id }}, '{{ addslashes($room->name) }}', '{{ addslashes($room->type ?? $type) }}', '{{ $room->capacity ?? '' }}')">
                                Edit
                            </button>
                            <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus ruangan ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            Belum ada data ruangan yang ditambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Navigasi Halaman (Pagination) --}}
    @if ($rooms->hasPages() || $rooms->total() > 10)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color, #e2e8f0); flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.85rem; font-size: 0.85rem; color: var(--text-secondary);">
                <span>
                    Menampilkan <strong>{{ $rooms->firstItem() ?? 0 }}</strong> - <strong>{{ $rooms->lastItem() ?? 0 }}</strong> dari <strong>{{ $rooms->total() }}</strong> ruangan
                </span>
                <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <label for="per_page_select_room" style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Baris per hal:</label>
                    <select id="per_page_select_room" onchange="changePerPage(this.value)" style="padding: 0.2rem 0.4rem; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--border-color, #cbd5e1); background: #fff; cursor: pointer;">
                        @foreach([10, 15, 25, 50] as $opt)
                            <option value="{{ $opt }}" {{ $rooms->perPage() == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 0.35rem; align-items: center;">
                {{-- Tombol Pertama --}}
                @if ($rooms->currentPage() > 2)
                    <a href="{{ $rooms->url(1) }}" class="btn-page" title="Halaman Pertama">&laquo;&laquo;</a>
                @endif

                {{-- Tombol Sebelumnya --}}
                @if ($rooms->onFirstPage())
                    <span class="btn-page disabled">&laquo; Prev</span>
                @else
                    <a href="{{ $rooms->previousPageUrl() }}" class="btn-page">&laquo; Prev</a>
                @endif

                {{-- Link Nomor Halaman --}}
                @php
                    $startPage = max(1, $rooms->currentPage() - 2);
                    $endPage = min($rooms->lastPage(), $rooms->currentPage() + 2);
                @endphp
                @for ($p = $startPage; $p <= $endPage; $p++)
                    @if ($p == $rooms->currentPage())
                        <span class="btn-page active">{{ $p }}</span>
                    @else
                        <a href="{{ $rooms->url($p) }}" class="btn-page">{{ $p }}</a>
                    @endif
                @endfor

                {{-- Tombol Selanjutnya --}}
                @if ($rooms->hasMorePages())
                    <a href="{{ $rooms->nextPageUrl() }}" class="btn-page">Next &raquo;</a>
                @else
                    <span class="btn-page disabled">Next &raquo;</span>
                @endif

                {{-- Tombol Terakhir --}}
                @if ($rooms->currentPage() < $rooms->lastPage() - 1)
                    <a href="{{ $rooms->url($rooms->lastPage()) }}" class="btn-page" title="Halaman Terakhir">&raquo;&raquo;</a>
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
{{-- MODAL IN-PAGE: IMPORT DATA RUANGAN DARI EXCEL / CSV       --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalImportRoom">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Import Data Ruangan</h3>
                <p class="modal-subtitle">Unggah data ruangan dari file Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), atau CSV (.csv) dari penyimpanan komputer lokal</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalImportRoom')">&times;</button>
        </div>

        <form action="{{ route('rooms.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body" style="padding: 1.5rem;">
                <div style="background-color: var(--bg-surface-hover, #f8fafc); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; text-align: center; margin-bottom: 1.25rem;">
                    <label for="import_room_file" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary); cursor: pointer;">
                        Pilih File Excel / Dokumen (.xlsx, .xls, .csv, .docx, .pdf)
                    </label>
                    <input type="file" name="file" id="import_room_file" accept=".xlsx, .xls, .csv, .docx, .pdf, .txt" required style="width: 100%; max-width: 320px; margin: 0 auto; display: block; font-size: 0.85rem;">
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.5rem;">
                        Maksimal ukuran file 5MB. Format didukung: Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), CSV (.csv)
                    </small>
                </div>

                <div style="background: rgba(99, 102, 241, 0.05); border-left: 3px solid #6366f1; padding: 0.85rem 1rem; border-radius: 4px; font-size: 0.8rem; line-height: 1.5; color: var(--text-secondary);">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.35rem;">Petunjuk Format Kolom File:</strong>
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        <li>Kolom wajib: <strong>nama_ruangan</strong> (contoh: <em>Lab Komputer 1</em>, <em>R. Teori A</em>).</li>
                        <li>Kolom opsional: <strong>tipe</strong> (contoh: <em>Praktik, Teori, Olahraga</em>) dan <strong>kapasitas</strong> (contoh: <em>36</em>).</li>
                        <li>Jika nama ruangan sudah ada di database, data tipe dan kapasitas akan diperbarui otomatis tanpa membuat data ganda.</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background-color: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalImportRoom')">
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
{{-- MODAL IN-PAGE: TAMBAH RUANGAN (TIDAK BERPINDAH)          --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalCreateRoom">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Tambah Data Ruangan</h3>
                <p class="modal-subtitle">Daftarkan ruang kelas, bengkel, atau lab baru</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalCreateRoom')">&times;</button>
        </div>

        <form action="{{ route('rooms.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_room_name">Nama Ruangan <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" id="create_room_name" placeholder="Contoh: Lab Komputer 1 / R. Teori A" required>
                </div>

                <div class="form-group">
                    <label for="create_room_type">Tipe Ruangan</label>
                    <select name="type" id="create_room_type">
                        <option value="Teori">Teori (Kelas Teori Umum)</option>
                        <option value="Praktik">Praktik (Laboratorium / Bengkel)</option>
                        <option value="Olahraga">Olahraga (Lapangan Olahraga / Hall)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="create_room_capacity">Kapasitas Maksimal (Siswa)</label>
                    <input type="number" name="capacity" id="create_room_capacity" placeholder="Contoh: 36" min="1">
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateRoom')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan Ruangan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: EDIT RUANGAN (TIDAK BERPINDAH)            --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalEditRoom">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Data Ruangan</h3>
                <p class="modal-subtitle">Perbarui informasi kapasitas dan tipe ruangan</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalEditRoom')">&times;</button>
        </div>

        <form id="formEditRoom" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_room_name">Nama Ruangan <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" id="edit_room_name" required>
                </div>

                <div class="form-group">
                    <label for="edit_room_type">Tipe Ruangan</label>
                    <select name="type" id="edit_room_type">
                        <option value="Teori">Teori (Kelas Teori Umum)</option>
                        <option value="Praktik">Praktik (Laboratorium / Bengkel)</option>
                        <option value="Olahraga">Olahraga (Lapangan Olahraga / Hall)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_room_capacity">Kapasitas Maksimal (Siswa)</label>
                    <input type="number" name="capacity" id="edit_room_capacity" min="1">
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditRoom')">
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

    function openEditRoomModal(id, name, type, capacity) {
        const form = document.getElementById('formEditRoom');
        form.action = `/rooms/${id}`;
        document.getElementById('edit_room_name').value = name;
        document.getElementById('edit_room_type').value = type || 'Teori';
        document.getElementById('edit_room_capacity').value = capacity;
        openModal('modalEditRoom');
    }
</script>
@endsection