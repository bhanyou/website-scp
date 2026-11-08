@extends('layouts.app')

@section('content')
@php
    $activeCycle = $cycles->firstWhere('is_active', true);
    $activeCycleName = $activeCycle ? (str_starts_with(strtolower($activeCycle->cycle), 'putaran') ? $activeCycle->cycle : 'Putaran ' . $activeCycle->cycle) : 'Putaran Aktif';
@endphp

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title">Siklus / Putaran Blok</h1>
        <p class="page-subtitle">Pilih dan aktifkan putaran KBM yang sedang berjalan (Putaran 1 atau Putaran 2)</p>
    </div>

    {{-- Tombol Kunci Jadwal di Pojok Samping --}}
    @if ($activeCycle)
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            @if ($activeCycle->is_locked)
                <span class="badge badge-danger" style="font-size: 0.85rem; padding: 0.45rem 0.8rem; font-weight: 700;">
                    Jadwal Sedang Dikunci
                </span>
                <form action="{{ route('block-cycles.toggle-lock', $activeCycle->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="font-weight: 600; padding: 0.5rem 1rem; border: 1px solid var(--border-color); background-color: #f1f5f9; color: #334155;" data-custom-confirm="Buka kunci jadwal {{ $activeCycleName }}? Waka akan dapat beralih mengaktifkan putaran lain kembali.">
                        Buka Kunci Jadwal
                    </button>
                </form>
            @else
                <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.45rem 0.8rem; font-weight: 700;">
                    Jadwal Terbuka
                </span>
                <form action="{{ route('block-cycles.toggle-lock', $activeCycle->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn" style="background-color: #f59e0b; color: #ffffff; font-weight: 600; padding: 0.5rem 1rem; border: none; border-radius: var(--radius-sm);" data-custom-confirm="Kunci jadwal {{ $activeCycleName }}? Setelah dikunci, putaran tidak dapat diganti ke putaran lain sampai kunci dibuka.">
                        Kunci Jadwal
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>

{{-- Kotak Tunggal Siklus Putaran --}}
@php
    $currentActive = $cycles->firstWhere('is_active', true) ?: $cycles->first();
    $nextCycle = $cycles->firstWhere('id', '!=', $currentActive->id) ?: $cycles->last();
    $currentName = str_starts_with(strtolower($currentActive->cycle), 'putaran') ? $currentActive->cycle : 'Putaran ' . $currentActive->cycle;
    $nextName = str_starts_with(strtolower($nextCycle->cycle), 'putaran') ? $nextCycle->cycle : 'Putaran ' . $nextCycle->cycle;
@endphp

<div class="card" style="margin-bottom: 2rem; border: 2px solid var(--accent); box-shadow: 0 8px 24px rgba(79, 70, 229, 0.12); border-radius: var(--radius-lg, 14px); padding: 1.75rem; background: var(--bg-surface, #ffffff);">
    {{-- Header Kotak Tunggal --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                Siklus Pembelajaran KBM Saat Ini
            </span>
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin: 0.2rem 0 0 0; display: flex; align-items: center; gap: 0.75rem;">
                {{ $currentName }}
                <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.4rem 0.85rem; font-weight: 700; border-radius: 9999px;">
                    Sedang Aktif
                </span>
            </h2>
        </div>

        <div style="text-align: right;">
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block;">Status Jadwal</span>
            <div style="margin-top: 0.25rem;">
                @if ($currentActive->is_locked)
                    <span class="badge badge-danger" style="font-size: 0.82rem; padding: 0.35rem 0.75rem;">Jadwal Terkunci</span>
                    @if($currentActive->lockedBy)
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">oleh {{ $currentActive->lockedBy->name }}</div>
                    @endif
                @else
                    <span class="badge badge-info" style="font-size: 0.82rem; padding: 0.35rem 0.75rem;">Jadwal Terbuka</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Detail Informasi dalam Grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: var(--bg-surface-hover, #f8fafc); padding: 1.25rem; border-radius: var(--radius-md, 10px); border: 1px solid var(--border-color);">
        <div>
            <span style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Periode Pelaksanaan</span>
            <p style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0.2rem 0 0 0;">
                2 Minggu
            </p>
        </div>

        <div>
            <span style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Total Jadwal Terdaftar</span>
            <p style="font-size: 1.25rem; font-weight: 800; color: var(--accent); margin: 0.2rem 0 0 0;">
                {{ $currentActive->schedules_count ?? $currentActive->schedules->count() }} <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">Jadwal Pelajaran</span>
            </p>
        </div>

        <div>
            <span style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Siklus Tujuan Jika Beralih</span>
            <p style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0.2rem 0 0 0;">
                {{ $nextName }} <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted);">({{ $nextCycle->schedules_count ?? $nextCycle->schedules->count() }} Jadwal)</span>
            </p>
        </div>
    </div>

    {{-- Tombol Beralih Putaran (Hanya 1 Tombol Aksi) --}}
    <div>
        @if ($currentActive->is_locked)
            <div style="background-color: #f8fafc; border: 1px dashed var(--border-color); border-radius: var(--radius-sm); padding: 0.85rem 1rem; text-align: center; color: #64748b; font-size: 0.88rem; font-weight: 600;" title="Buka kunci jadwal di pojok atas untuk mengganti putaran">
                🔒 Jadwal Sedang Dikunci (Buka kunci jadwal di pojok atas untuk beralih ke {{ $nextName }})
            </div>
        @else
            <form action="{{ route('block-cycles.toggle-active', $nextCycle->id) }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" 
                        class="btn btn-primary" 
                        style="width: 100%; justify-content: center; padding: 0.85rem 1.5rem; font-size: 1rem; font-weight: 700; gap: 0.5rem; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);"
                        data-custom-confirm="Apakah Anda yakin ingin beralih dari {{ $currentName }} ke {{ $nextName }}? Seluruh agenda mengajar guru dan jadwal kelas akan langsung disesuaikan dengan {{ $nextName }}."
                        data-custom-confirm-title="Konfirmasi Beralih {{ $nextName }}"
                        data-custom-confirm-ok="Ya, Setujui & Beralih (Accept)"
                        data-custom-confirm-cancel="Batal (Reject)">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2.2;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Beralih ke {{ $nextName }}
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Tabel Ringkasan Siklus Blok --}}
<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
        <span>Ringkasan Data Siklus Putaran</span>
        <span class="badge badge-info">2 Putaran Tetap</span>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Siklus</th>
                    <th>Periode Pelaksanaan</th>
                    <th>Jadwal Terdaftar</th>
                    <th>Status Penguncian</th>
                    <th>Status Aktif</th>
                    <th style="text-align: right;">Aksi Aktivasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cycles as $cycle)
                    @php
                        $namaPutaran = str_starts_with(strtolower($cycle->cycle), 'putaran') ? $cycle->cycle : 'Putaran ' . $cycle->cycle;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong style="color: var(--accent); font-size: 0.95rem;">{{ $namaPutaran }}</strong>
                        </td>
                        <td>2 Minggu</td>
                        <td>
                            <span class="badge badge-secondary" style="background-color: var(--bg-surface-hover); color: var(--text-primary);">
                                {{ $cycle->schedules_count ?? $cycle->schedules->count() }} Jadwal
                            </span>
                        </td>
                        <td>
                            @if ($cycle->is_locked)
                                <span class="badge badge-danger">Terkunci</span>
                            @else
                                <span class="badge badge-info">Terbuka</span>
                            @endif
                        </td>
                        <td>
                            @if ($cycle->is_active)
                                <span class="badge badge-success">Sedang Aktif</span>
                            @else
                                <span class="badge badge-secondary" style="background-color: var(--bg-surface-hover); color: var(--text-muted);">Non-Aktif</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            @if ($cycle->is_active)
                                <span style="font-size: 0.85rem; font-weight: 600; color: #10b981;">
                                    Sedang Digunakan
                                </span>
                            @elseif ($activeCycle && $activeCycle->is_locked)
                                <span class="badge badge-secondary" style="font-size: 0.75rem; background-color: var(--bg-surface-hover); color: var(--text-muted);" title="Buka kunci jadwal di pojok atas untuk mengganti putaran">
                                    Terkunci
                                </span>
                            @else
                                <form action="{{ route('block-cycles.toggle-active', $cycle->id) }}" method="POST" style="display:inline; margin: 0;">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-primary btn-sm" 
                                            data-custom-confirm="Apakah Anda yakin ingin mengaktifkan {{ $namaPutaran }}? Seluruh agenda mengajar guru dan jadwal kelas akan langsung disesuaikan."
                                            data-custom-confirm-title="Konfirmasi Beralih {{ $namaPutaran }}"
                                            data-custom-confirm-ok="Ya, Setujui & Beralih (Accept)"
                                            data-custom-confirm-cancel="Batal (Reject)">
                                        Aktifkan
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        <a href="{{ route('dashboard.waka') }}" class="btn-back">
            Kembali ke Dashboard Waka Kurikulum
        </a>
    </div>
</div>
@endsection