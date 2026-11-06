@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Kenaikan Kelas & Kelulusan</h1>
        <p class="page-subtitle">Kelola promosi tingkatan kelas, kelulusan kelas XII, dan penanganan siswa tinggal kelas</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        @if($hasRollbackData)
            <form action="{{ route('class-promotions.rollback') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan kenaikan kelas terakhir? Sebanyak {{ $rollbackCount }} data siswa akan dikembalikan ke kelas asalnya.')">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    Batalkan Kenaikan Terakhir (Rollback {{ $rollbackCount }} Siswa)
                </button>
            </form>
        @endif
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalAllSchoolPromotion')">
            Kenaikan Massal Seluruh Sekolah
        </button>
    </div>
</div>

{{-- Kartu Ringkasan Siswa per Tingkat --}}
<div class="grid-4" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.25rem;">
        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">
            Siswa Kelas X
        </span>
        <div style="margin-top: 0.5rem;">
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">
                {{ $countGrade10 }}
            </span>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Siswa Aktif</span>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem;">
        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">
            Siswa Kelas XI
        </span>
        <div style="margin-top: 0.5rem;">
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">
                {{ $countGrade11 }}
            </span>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Siswa Aktif</span>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem;">
        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">
            Siswa Kelas XII
        </span>
        <div style="margin-top: 0.5rem;">
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">
                {{ $countGrade12 }}
            </span>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Siswa Aktif</span>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem;">
        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">
            Alumni Lulus
        </span>
        <div style="margin-top: 0.5rem;">
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--success, #10b981);">
                {{ $countGraduated }}
            </span>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Siswa Lulus</span>
        </div>
    </div>
</div>

{{-- Filter Jurusan, Tingkat, Rombel, dan Kelas Langsung --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('class-promotions.index') }}" method="GET" id="filterPromotionForm" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        {{-- Filter Jurusan --}}
        <div style="flex: 1; min-width: 160px;">
            <label for="filter_major" style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Jurusan
            </label>
            <select name="major" id="filter_major" class="form-control" style="width: 100%;">
                <option value="">Semua Jurusan</option>
                @foreach($majors as $m)
                    @php
                        $code = is_object($m) ? $m->code : $m;
                        $label = is_object($m) && isset($m->name) && $m->name !== $m->code ? "{$m->code} - {$m->name}" : $code;
                    @endphp
                    <option value="{{ $code }}" {{ ($selectedMajor ?? '') == $code ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filter Tingkat --}}
        <div style="flex: 1; min-width: 140px;">
            <label for="filter_grade" style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Tingkat
            </label>
            <select name="grade" id="filter_grade" class="form-control" style="width: 100%;">
                <option value="">Semua Tingkat</option>
                <option value="10" {{ ($selectedGrade ?? '') == '10' ? 'selected' : '' }}>Kelas 10 (X)</option>
                <option value="11" {{ ($selectedGrade ?? '') == '11' ? 'selected' : '' }}>Kelas 11 (XI)</option>
                <option value="12" {{ ($selectedGrade ?? '') == '12' ? 'selected' : '' }}>Kelas 12 (XII)</option>
            </select>
        </div>

        {{-- Filter Rombel --}}
        <div style="flex: 1; min-width: 140px;">
            <label for="filter_rombel" style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Rombel
            </label>
            <select name="rombel" id="filter_rombel" class="form-control" style="width: 100%;">
                <option value="">Semua Rombel</option>
                <option value="1" {{ ($selectedRombel ?? '') == '1' ? 'selected' : '' }}>Rombel 1</option>
                <option value="2" {{ ($selectedRombel ?? '') == '2' ? 'selected' : '' }}>Rombel 2</option>
                <option value="3" {{ ($selectedRombel ?? '') == '3' ? 'selected' : '' }}>Rombel 3</option>
                <option value="4" {{ ($selectedRombel ?? '') == '4' ? 'selected' : '' }}>Rombel 4</option>
            </select>
        </div>

        {{-- Pilih Kelas Langsung --}}
        <div style="flex: 1.5; min-width: 220px;">
            <label for="select_class_id" style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.35rem; text-transform: uppercase;">
                Pilih Kelas Langsung
            </label>
            <select name="class_id" id="select_class_id" class="form-control" style="width: 100%;">
                <option value="">-- Semua Kelas / Pilih Rombel --</option>
                @foreach($classes as $c)
                    @php
                        $cGradeNormalized = in_array(strtoupper($c->grade), ['X', '10']) ? '10' : (in_array(strtoupper($c->grade), ['XI', '11']) ? '11' : '12');
                        $cRombel = preg_match('/(\d+)$/', trim($c->name), $rm) ? $rm[1] : '';
                    @endphp
                    <option value="{{ $c->id }}"
                        data-major="{{ $c->major }}"
                        data-grade="{{ $cGradeNormalized }}"
                        data-rombel="{{ $cRombel }}"
                        {{ ($selectedClassId ?? '') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} (Tingkat {{ $c->grade }} &bull; {{ $c->students_count }} Siswa Aktif)
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tombol Filter & Reset --}}
        <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.25rem;">
                Filter
            </button>
            @if(request()->filled('major') || request()->filled('grade') || request()->filled('rombel') || request()->filled('class_id') || $selectedClassId || $selectedMajor || $selectedGrade || $selectedRombel)
                <a href="{{ route('class-promotions.index') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center; padding: 0 1rem;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Panel Pemrosesan Kenaikan Kelas per Rombel --}}
@if($selectedClass)
    <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem; border-left: 4px solid var(--primary);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="font-size: 1rem; font-weight: 700; margin: 0; color: var(--text-primary);">
                    Pemrosesan Rombel: {{ $selectedClass->name }}
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0.25rem 0 0 0;">
                    Tingkat: <strong>Kelas {{ $selectedClass->grade }}</strong> &bull;
                    Jurusan: <strong>{{ $selectedClass->major ?? '-' }}</strong> &bull;
                    Target Kenaikan:
                    @if(in_array(strtoupper($selectedClass->grade), ['12', 'XII']))
                        <span class="badge badge-success">Kelulusan (Alumni)</span>
                    @elseif($targetClass)
                        <span class="badge badge-info">Naik ke {{ $targetClass->name }}</span>
                    @else
                        <span class="badge badge-warning">Kelas tujuan belum tersedia di master data</span>
                    @endif
                </p>
            </div>

            <button type="submit" form="formPromoteSingleClass" class="btn btn-primary btn-sm">
                Proses Kenaikan Kelas Rombel Ini
            </button>
        </div>
    </div>
@endif

{{-- Tabel Daftar Siswa --}}
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <span style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">
                Daftar Peserta Didik
            </span>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">
                @if($selectedClass)
                    Beri tanda centang pada kolom <strong>Tinggal Kelas</strong> jika ada siswa yang tidak dinaikkan
                @else
                    Pilih rombel spesifik (atau filter Jurusan, Tingkat, &amp; Rombel) di atas untuk memproses kenaikan kelas per rombel
                @endif
            </p>
        </div>
        <span class="badge badge-info" style="font-size: 0.8rem;">
            {{ $students->count() }} Siswa Ditampilkan
        </span>
    </div>

    @if($selectedClass)
        <form action="{{ route('class-promotions.process') }}" method="POST" id="formPromoteSingleClass">
            @csrf
            <input type="hidden" name="mode" value="single_class">
            <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>NIS</th>
                    <th>Nama Lengkap Siswa</th>
                    <th>Kelas Saat Ini</th>
                    <th style="width: 200px; text-align: center; background-color: rgba(239, 68, 68, 0.05); color: var(--danger);">
                        Opsi Pengecualian
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php
                        $isGrade12 = in_array(strtoupper($student->schoolClass?->grade ?? ''), ['12', 'XII']);
                    @endphp
                    <tr>
                        <td style="text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                            {{ $loop->iteration }}
                        </td>
                        <td><strong>{{ $student->nis }}</strong></td>
                        <td>{{ $student->name }}</td>
                        <td>
                            <a href="{{ route('class-promotions.index', ['class_id' => $student->schoolClass?->id]) }}" class="badge badge-info" style="text-decoration: none;" title="Pilih rombel ini">
                                {{ $student->schoolClass?->name ?? '-' }}
                            </a>
                        </td>
                        <td style="text-align: center; background-color: rgba(239, 68, 68, 0.03);">
                            <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.8rem; margin: 0; color: var(--danger); font-weight: 600;">
                                <input type="checkbox"
                                    name="stay_students[]"
                                    value="{{ $student->id }}"
                                    form="{{ $selectedClass ? 'formPromoteSingleClass' : 'formPromoteAllSchool' }}"
                                    style="accent-color: var(--danger); width: 16px; height: 16px;">
                                {{ $isGrade12 ? 'Tidak Lulus' : 'Tinggal Kelas' }}
                            </label>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            Tidak ada data siswa aktif yang ditemukan pada pilihan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($selectedClass)
        </form>
    @endif

    <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <a href="{{ route('dashboard.waka') }}" class="btn-back">
            Kembali ke Dashboard Waka
        </a>

        @if($selectedClass && $students->isNotEmpty())
            <button type="submit" form="formPromoteSingleClass" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin memproses kenaikan kelas untuk rombel {{ $selectedClass->name }}?')">
                Simpan & Eksekusi Kenaikan Kelas Rombel Ini
            </button>
        @endif
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL: KENAIKAN MASSAL SELURUH SEKOLAH                   --}}
{{-- ========================================================= --}}
<div class="modal-overlay" id="modalAllSchoolPromotion">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Kenaikan Kelas Massal Seluruh Sekolah</h3>
                <p class="modal-subtitle">Promosikan seluruh siswa aktif tingkat X dan XI serta luluskan tingkat XII</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalAllSchoolPromotion')">&times;</button>
        </div>

        <form action="{{ route('class-promotions.process') }}" method="POST" id="formPromoteAllSchool" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <input type="hidden" name="mode" value="all_school">

            <div class="modal-body" style="padding: 1.5rem;">
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.25rem;">
                    <strong style="color: #991b1b; font-size: 0.85rem; display: block; margin-bottom: 0.35rem;">
                        Ringkasan Eksekusi Massal:
                    </strong>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem; color: #7f1d1d; line-height: 1.6;">
                        <li><strong>{{ $countGrade12 }} siswa kelas XII</strong> akan otomatis dinyatakan <strong>Lulus / Alumni</strong>.</li>
                        <li><strong>{{ $countGrade11 }} siswa kelas XI</strong> akan dipromosikan naik ke <strong>Kelas XII</strong>.</li>
                        <li><strong>{{ $countGrade10 }} siswa kelas X</strong> akan dipromosikan naik ke <strong>Kelas XI</strong>.</li>
                        <li>Siswa yang dicentang <em>Tinggal Kelas</em> pada tabel akan dilewati dan tetap di kelas asalnya.</li>
                    </ul>
                </div>

                <div style="background-color: var(--bg-surface-hover, #f8fafc); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 0.85rem; font-size: 0.8rem; color: var(--text-secondary);">
                    <strong>Catatan Keamanan (Fitur Rollback):</strong><br>
                    Sistem secara otomatis mencatat jejak kelas asal setiap siswa. Jika sewaktu-waktu Anda ingin membatalkan proses ini saat demonstrasi, Anda dapat menggunakan tombol <strong>Rollback</strong> kapan saja.
                </div>
            </div>

            <div class="modal-footer" style="flex-shrink: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background-color: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAllSchoolPromotion')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Mulai proses kenaikan kelas massal untuk seluruh sekolah sekarang?')">
                    Mulai Eksekusi Massal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const classSelect = document.getElementById('select_class_id');
    const majorSelect = document.getElementById('filter_major');
    const gradeSelect = document.getElementById('filter_grade');
    const rombelSelect = document.getElementById('filter_rombel');

    if (classSelect) {
        classSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                if (opt.dataset.major && majorSelect) majorSelect.value = opt.dataset.major;
                if (opt.dataset.grade && gradeSelect) gradeSelect.value = opt.dataset.grade;
                if (opt.dataset.rombel && rombelSelect) rombelSelect.value = opt.dataset.rombel;
            }
        });
    }

    [majorSelect, gradeSelect, rombelSelect].forEach(function (el) {
        if (el) {
            el.addEventListener('change', function () {
                if (classSelect && classSelect.value) {
                    classSelect.value = '';
                }
            });
        }
    });
});
</script>
@endsection
