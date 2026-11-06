@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Data Jurusan</h1>
        <p class="page-subtitle">Daftar kompetensi keahlian, kode jurusan, jenjang tingkat, dan jumlah rombongan belajar</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <button type="button" class="btn btn-primary" onclick="openModal('modalCreateMajor')">
            + Tambah Jurusan
        </button>
    </div>
</div>

<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <span>Daftar Jurusan Keahlian</span>
        <span class="badge badge-info">{{ $majors->count() }} Jurusan Terdaftar</span>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode</th>
                    <th>Nama Jurusan</th>
                    <th>Jenjang Tingkat</th>
                    <th>Jumlah Rombel</th>
                    <th>Total Siswa</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($majors as $major)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="badge badge-info">{{ $major->code }}</span>
                        </td>
                        <td>
                            <strong>{{ $major->name }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $major->level }}</span>
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $major->rombel_count }} Rombel / Tingkat</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.2rem;">Total {{ $major->total_classes }} Kelas (X, XI, XII)</span>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $major->total_students }} Siswa</span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" 
                                class="btn btn-secondary btn-sm"
                                onclick="openEditMajorModal({{ $major->id }}, '{{ addslashes($major->code) }}', '{{ addslashes($major->name) }}', {{ $major->rombel_count }})">
                                Edit
                            </button>
                            <form action="{{ route('majors.destroy', $major->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus jurusan ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            Belum ada data jurusan yang terdaftar. Silakan klik tombol "+ Tambah Jurusan".
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
{{-- MODAL IN-PAGE: TAMBAH JURUSAN                            --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalCreateMajor">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Tambah Data Jurusan</h3>
                <p class="modal-subtitle">Daftarkan kompetensi keahlian dan kode singkatan baru</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalCreateMajor')">&times;</button>
        </div>

        <form action="{{ route('majors.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_major_code">Kode Jurusan (Singkatan) <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="code" id="create_major_code" placeholder="Contoh: RPL / TKJ / DKV / MP" required maxlength="20" style="text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label for="create_major_name">Nama Jurusan (Kepanjangan) <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" id="create_major_name" placeholder="Contoh: Manajemen Perkantoran" required>
                </div>

                <div class="form-group">
                    <label for="create_major_rombel">Jumlah Rombel per Tingkat <span style="color: var(--danger);">*</span></label>
                    <select name="rombel_count" id="create_major_rombel" required>
                        <option value="1">1 Rombel (Kelas 1 per tingkat X, XI, XII)</option>
                        <option value="2" selected>2 Rombel (Kelas 1 dan 2 per tingkat X, XI, XII)</option>
                        <option value="3">3 Rombel (Kelas 1, 2, dan 3 per tingkat X, XI, XII)</option>
                        <option value="4">4 Rombel (Kelas 1, 2, 3, dan 4 per tingkat X, XI, XII)</option>
                    </select>
                    <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 0.35rem;">
                        Sistem otomatis membuatkan rombel kelas untuk jenjang tingkat X, XI, dan XII sesuai jumlah rombel yang dipilih.
                    </small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateMajor')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan Jurusan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL IN-PAGE: EDIT JURUSAN                              --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalEditMajor">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Data Jurusan</h3>
                <p class="modal-subtitle">Perbarui kode atau nama kepanjangan jurusan</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalEditMajor')">&times;</button>
        </div>

        <form id="formEditMajor" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_major_code">Kode Jurusan (Singkatan) <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="code" id="edit_major_code" required maxlength="20" style="text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label for="edit_major_name">Nama Jurusan (Kepanjangan) <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" id="edit_major_name" required>
                </div>

                <div class="form-group">
                    <label for="edit_major_rombel">Jumlah Rombel per Tingkat <span style="color: var(--danger);">*</span></label>
                    <select name="rombel_count" id="edit_major_rombel" required>
                        <option value="1">1 Rombel</option>
                        <option value="2">2 Rombel</option>
                        <option value="3">3 Rombel</option>
                        <option value="4">4 Rombel</option>
                    </select>
                    <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 0.35rem;">
                        Penambahan rombel otomatis membuatkan kelas baru tingkat X, XI, XII.
                    </small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditMajor')">
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
    function openEditMajorModal(id, code, name, rombelCount) {
        const form = document.getElementById('formEditMajor');
        form.action = `/majors/${id}`;
        document.getElementById('edit_major_code').value = code;
        document.getElementById('edit_major_name').value = name;
        document.getElementById('edit_major_rombel').value = rombelCount || '1';
        openModal('modalEditMajor');
    }
</script>
@endsection
