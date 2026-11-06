@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Data Kelas</h1>
        <p class="page-subtitle">Daftar rombongan belajar, wali kelas, dan kuota siswa</p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        <form action="{{ route('classes.clean-anomalies') }}" method="POST" style="display: inline;" onsubmit="return confirm('Bersihkan seluruh nama kelas anomali (nama orang/bukan format rombel X/XI/XII)?')">
            @csrf
            <button type="submit" class="btn btn-secondary" style="color: #ef4444; border-color: #fca5a5; display: inline-flex; align-items: center; gap: 0.35rem;" title="Hapus kelas yang bukan nama rombel resmi">
                <span>🧹</span> Bersihkan Anomali
            </button>
        </form>
        <a href="{{ route('classes.template') }}" class="btn btn-secondary">
            Unduh Format CSV
        </a>
        <button type="button" class="btn btn-secondary" onclick="openModal('modalImportClass')">
            Import Kelas
        </button>
        <button type="button" class="btn btn-primary" onclick="openModal('modalCreateClass')">
            + Tambah Kelas
        </button>
    </div>
</div>

{{-- Filter Pencarian, Tingkat, & Jurusan --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('classes.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1.5; min-width: 200px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Cari Kelas / Wali Kelas
            </label>
            <input type="text" name="search" class="form-control" placeholder="Ketik nama kelas, wali kelas, atau jurusan..." value="{{ request('search') }}" style="width: 100%;">
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Tingkat Kelas
            </label>
            <select name="grade" class="form-control" style="width: 100%;">
                <option value="">Semua Tingkat</option>
                @foreach($grades as $g)
                    <option value="{{ $g }}" {{ request('grade') == $g ? 'selected' : '' }}>Kelas {{ $g }}</option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 160px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Jurusan
            </label>
            <select name="major" class="form-control" style="width: 100%;">
                <option value="">Semua Jurusan</option>
                @foreach($allMajors as $m)
                    <option value="{{ $m->code }}" {{ request('major') == $m->code ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.25rem;">
                Filter
            </button>
            @if(request()->filled('search') || request()->filled('grade') || request()->filled('major'))
                <a href="{{ route('classes.index') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <span>Daftar Rombongan Belajar</span>
        <span class="badge badge-info">{{ $classes->count() }} Kelas</span>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kelas</th>
                    <th>Tingkat</th>
                    <th>Jurusan</th>
                    <th>Wali Kelas</th>
                    <th>Jumlah Siswa</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $class)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $class->name }}</strong></td>
                        <td>Tingkat {{ $class->grade }}</td>
                        <td>
                            <span class="badge badge-info">
                                {{ $class->department->name ?? '-' }}
                            </span>
                        </td>
                        <td>{{ $class->homeroomTeacher?->name ?? '-' }}</td>
                        <td><span class="badge badge-secondary">{{ $class->students_count }} Siswa</span></td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('students.index', ['class_id' => $class->id]) }}" 
                               class="btn btn-secondary btn-sm"
                               style="margin-right: 0.25rem;">
                                Lihat Siswa
                            </a>
                            <button type="button" 
                                class="btn btn-secondary btn-sm"
                                onclick="openEditClassModal({{ $class->id }}, '{{ addslashes($class->name) }}', '{{ addslashes($class->grade) }}', '{{ addslashes($class->major) }}', '{{ $class->homeroom_teacher_id }}')">
                                Edit
                            </button>
                            <form action="{{ route('classes.destroy', $class->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kelas ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            Tidak ada data kelas yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1rem;">
        <a href="{{ route('dashboard.waka') }}" class="btn-back">
            Kembali ke Dashboard Waka
        </a>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: IMPORT DATA KELAS                         --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalImportClass">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Import Data Kelas</h3>
                <p class="modal-subtitle">Unggah data rombel dari file Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), atau CSV (.csv)</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalImportClass')">&times;</button>
        </div>

        <form action="{{ route('classes.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body" style="padding: 1.5rem;">
                <div style="background-color: var(--bg-surface-hover, #f8fafc); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; text-align: center; margin-bottom: 1.25rem;">
                    <label for="import_class_file" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary); cursor: pointer;">
                        Pilih File Excel / Dokumen (.xlsx, .xls, .csv, .docx, .pdf)
                    </label>
                    <input type="file" name="file" id="import_class_file" accept=".xlsx, .xls, .csv, .docx, .pdf, .txt" required style="width: 100%; max-width: 320px; margin: 0 auto; display: block; font-size: 0.85rem;">
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.5rem;">
                        Maksimal ukuran file 5MB. Format didukung: Excel (.xlsx, .xls), Word (.docx), PDF (.pdf), CSV (.csv)
                    </small>
                </div>

                <div style="background: rgba(99, 102, 241, 0.05); border-left: 3px solid #6366f1; padding: 0.85rem 1rem; border-radius: 4px; font-size: 0.8rem; line-height: 1.5; color: var(--text-secondary);">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.35rem;">Petunjuk Format Import Kelas:</strong>
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        <li>Kolom wajib: <strong>nama_kelas</strong> (contoh: <em>X RPL 1</em>, <em>XI TKJ 2</em>, <em>XII DKV 1</em>).</li>
                        <li>Jurusan akan <strong>dibuat & dihubungkan otomatis</strong> ke master jurusan (Department) berdasarkan nama kelas.</li>
                        <li>Kolom opsional: <strong>tingkat</strong> (X, XI, XII), <strong>jurusan</strong> (RPL, TKJ, dll), dan <strong>nama_wali_kelas</strong>.</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background-color: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalImportClass')">
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
{{-- MODAL IN-PAGE: TAMBAH KELAS                              --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalCreateClass">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Tambah Data Kelas</h3>
                <p class="modal-subtitle">Daftarkan rombongan belajar dan tentukan wali kelas</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalCreateClass')">&times;</button>
        </div>

        <form action="{{ route('classes.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_class_name">Nama Rombel / Kelas <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" id="create_class_name" placeholder="Contoh: X RPL 1 / XI TKJ 2" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        Jurusan akan otomatis disinkronkan ke master jurusan saat disimpan.
                    </small>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="create_class_grade">Tingkat Kelas</label>
                        <select name="grade" id="create_class_grade">
                            <option value="">-- Otomatis dari Nama Kelas --</option>
                            <option value="X">X (Sepuluh)</option>
                            <option value="XI">XI (Sebelas)</option>
                            <option value="XII">XII (Dua Belas)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_class_major">Kompetensi / Jurusan</label>
                        <select name="major" id="create_class_major">
                            <option value="">-- Otomatis dari Nama Kelas --</option>
                            @foreach($allMajors as $m)
                                <option value="{{ $m->code }}">{{ $m->code }} - {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="create_homeroom_teacher">Wali Kelas</label>
                    <select name="homeroom_teacher_id" id="create_homeroom_teacher">
                        <option value="">-- Pilih Guru Wali Kelas (Opsional) --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateClass')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan Kelas
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: EDIT KELAS                                --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalEditClass">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Data Kelas</h3>
                <p class="modal-subtitle">Perbarui rombel atau pergantian wali kelas</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalEditClass')">&times;</button>
        </div>

        <form id="formEditClass" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_class_name">Nama Rombel / Kelas <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" id="edit_class_name" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        Jurusan akan otomatis disinkronkan ke master jurusan saat diperbarui.
                    </small>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="edit_class_grade">Tingkat Kelas</label>
                        <select name="grade" id="edit_class_grade">
                            <option value="X">X (Sepuluh)</option>
                            <option value="XI">XI (Sebelas)</option>
                            <option value="XII">XII (Dua Belas)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_class_major">Kompetensi / Jurusan</label>
                        <select name="major" id="edit_class_major">
                            <option value="">-- Otomatis dari Nama Kelas --</option>
                            @foreach($allMajors as $m)
                                <option value="{{ $m->code }}">{{ $m->code }} - {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_homeroom_teacher">Wali Kelas</label>
                    <select name="homeroom_teacher_id" id="edit_homeroom_teacher">
                        <option value="">-- Pilih Guru Wali Kelas (Opsional) --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditClass')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditClassModal(id, name, grade, major, teacherId) {
        const form = document.getElementById('formEditClass');
        form.action = `/classes/${id}`;
        document.getElementById('edit_class_name').value = name;
        document.getElementById('edit_class_grade').value = grade;
        document.getElementById('edit_class_major').value = major;
        document.getElementById('edit_homeroom_teacher').value = teacherId || '';
        openModal('modalEditClass');
    }
</script>
@endsection
