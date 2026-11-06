@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tahun Ajaran & Semester</h1>
        <p class="page-subtitle">Kelola tahun ajaran aktif, pergantian semester ganjil/genap, dan arsip tahun ajaran</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalCreateYear')">
            + Tambah Tahun Ajaran
        </button>
    </div>
</div>

{{-- Kartu Ringkasan Status Akademik --}}
<div class="grid-4" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--primary);">
        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">
            Status Periode Aktif
        </span>
        <div style="display: flex; align-items: baseline; gap: 0.5rem; margin-top: 0.5rem;">
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">
                {{ $currentYear }}
            </span>
            <span class="badge badge-success" style="font-size: 0.8rem;">
                Semester {{ $currentSemester }}
            </span>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem;">
        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">
            Total Siswa Aktif
        </span>
        <div style="margin-top: 0.5rem;">
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">
                {{ $totalStudents }}
            </span>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Siswa</span>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem;">
        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">
            Guru Pengajar
        </span>
        <div style="margin-top: 0.5rem;">
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">
                {{ $totalTeachers }}
            </span>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Guru</span>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem;">
        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">
            Rombongan Belajar
        </span>
        <div style="margin-top: 0.5rem;">
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">
                {{ $totalClasses }}
            </span>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Kelas</span>
        </div>
    </div>
</div>

{{-- Tabel Daftar Tahun Ajaran Sekolah --}}
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
        <span style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">
            Daftar Tahun Ajaran Sekolah
        </span>
        <span class="badge badge-info" style="font-size: 0.8rem;">
            {{ count($yearsList) }} Tahun Ajaran Terdaftar
        </span>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Tahun Ajaran</th>
                    <th>Status Saat Ini</th>
                    <th>Pilihan Semester</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($yearsList as $year)
                    @php $isActive = ($year === $currentYear); @endphp
                    <tr style="{{ $isActive ? 'background-color: rgba(99, 102, 241, 0.03);' : '' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong style="font-size: 0.95rem; color: {{ $isActive ? 'var(--primary)' : 'var(--text-primary)' }};">
                                {{ $year }}
                            </strong>
                        </td>
                        <td>
                            @if($isActive)
                                <span class="badge badge-success">
                                    Aktif &bull; Semester {{ $currentSemester }}
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('academic-year.set-active') }}" method="POST" id="form-activate-{{ $loop->index }}" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                @csrf
                                <input type="hidden" name="academic_year" value="{{ $year }}">
                                <select name="semester" style="font-size: 0.78rem; padding: 0.25rem 0.5rem; border-radius: 4px; border: 1px solid var(--border-color);">
                                    <option value="Ganjil" {{ ($isActive ? ($currentSemester === 'Ganjil') : true) ? 'selected' : '' }}>Semester Ganjil</option>
                                    <option value="Genap" {{ ($isActive && $currentSemester === 'Genap') ? 'selected' : '' }}>Semester Genap</option>
                                </select>
                            </form>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            @if(!$isActive)
                                <button type="submit" form="form-activate-{{ $loop->index }}" class="btn btn-primary btn-sm" style="font-size: 0.78rem; padding: 0.25rem 0.65rem;">
                                    Jadikan Aktif
                                </button>
                                <form action="{{ route('academic-year.destroy') }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus tahun ajaran {{ $year }} dari daftar?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.78rem; padding: 0.25rem 0.65rem;">
                                        Hapus
                                    </button>
                                </form>
                            @else
                                <button type="submit" form="form-activate-{{ $loop->index }}" class="btn btn-secondary btn-sm" style="font-size: 0.78rem; padding: 0.25rem 0.65rem;">
                                    Ubah Semester
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
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
{{-- MODAL: TAMBAH TAHUN AJARAN BARU                          --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalCreateYear">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Tambah Tahun Ajaran Baru</h3>
                <p class="modal-subtitle">Daftarkan periode tahun ajaran baru ke dalam sistem</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalCreateYear')">&times;</button>
        </div>

        <form action="{{ route('academic-year.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="create_year">
                        Tahun Ajaran <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="text" name="year" id="create_year" value="{{ $suggestedNextYear }}" placeholder="Contoh: 2026/2027" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.35rem;">
                        Format penulisan menggunakan garis miring (contoh: 2026/2027).
                    </small>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="create_semester">Semester Default</label>
                    <select name="semester" id="create_semester">
                        <option value="Ganjil">Semester Ganjil</option>
                        <option value="Genap">Semester Genap</option>
                    </select>
                </div>

                <div style="background-color: var(--bg-surface-hover, #f8fafc); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 0.75rem 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0; font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">
                        <input type="checkbox" name="set_active" value="1">
                        Langsung jadikan sebagai tahun ajaran aktif saat disimpan
                    </label>
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background-color: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateYear')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan Tahun Ajaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
