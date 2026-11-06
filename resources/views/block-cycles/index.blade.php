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
                    <button type="submit" class="btn btn-secondary" style="font-weight: 600; padding: 0.5rem 1rem; border: 1px solid var(--border-color); background-color: #f1f5f9; color: #334155;" onclick="return confirm('Buka kunci jadwal {{ $activeCycleName }}? Waka akan dapat beralih mengaktifkan putaran lain kembali.')">
                        Buka Kunci Jadwal
                    </button>
                </form>
            @else
                <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.45rem 0.8rem; font-weight: 700;">
                    Jadwal Terbuka
                </span>
                <form action="{{ route('block-cycles.toggle-lock', $activeCycle->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn" style="background-color: #f59e0b; color: #ffffff; font-weight: 600; padding: 0.5rem 1rem; border: none; border-radius: var(--radius-sm);" onclick="return confirm('Kunci jadwal {{ $activeCycleName }}? Setelah dikunci, putaran tidak dapat diganti ke putaran lain sampai kunci dibuka.')">
                        Kunci Jadwal
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>

{{-- Kartu Pilihan Putaran 1 & Putaran 2 --}}
<div class="grid-2" style="margin-bottom: 1.5rem;">
    @foreach ($cycles as $cycle)
        @php
            $namaPutaran = str_starts_with(strtolower($cycle->cycle), 'putaran') ? $cycle->cycle : 'Putaran ' . $cycle->cycle;
            $isActive = $cycle->is_active;
        @endphp
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; {{ $isActive ? 'border: 2px solid var(--accent); box-shadow: 0 4px 14px rgba(79, 70, 229, 0.12);' : 'opacity: 0.9;' }}">
            
            <div>
                {{-- Header Kartu --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                            {{ $namaPutaran }}
                        </h2>
                    </div>

                    @if ($isActive)
                        <span class="badge badge-success" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; font-weight: 700;">
                            Sedang Aktif
                        </span>
                    @else
                        <span class="badge badge-secondary" style="background-color: var(--bg-surface-hover); color: var(--text-muted); font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                            Tidak Aktif
                        </span>
                    @endif
                </div>

                {{-- Detail Informasi Putaran --}}
                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.25rem;">
                    <div class="detail-item" style="padding: 0.5rem 0;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Periode Pelaksanaan</span>
                        <p style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary); margin: 0.2rem 0 0 0;">
                            2 Minggu
                        </p>
                    </div>

                    <div class="detail-item" style="padding: 0.5rem 0;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Total Jadwal Terdaftar</span>
                        <p style="font-size: 1.1rem; font-weight: 700; color: var(--accent); margin: 0.2rem 0 0 0;">
                            {{ $cycle->schedules_count ?? $cycle->schedules->count() }} <span style="font-size: 0.85rem; font-weight: normal; color: var(--text-secondary);">Jadwal Pelajaran</span>
                        </p>
                    </div>

                    <div class="detail-item" style="padding: 0.5rem 0;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Status Penguncian Jadwal</span>
                        <div style="margin-top: 0.25rem;">
                            @if ($cycle->is_locked)
                                <span class="badge badge-danger">Jadwal Terkunci</span>
                                @if($cycle->lockedBy)
                                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 0.25rem;">oleh {{ $cycle->lockedBy->name }}</span>
                                @endif
                            @else
                                <span class="badge badge-info">Jadwal Terbuka</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi Aktivasi --}}
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                @if ($isActive)
                    <div style="background-color: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: var(--radius-sm); padding: 0.75rem 1rem; text-align: center; color: #10b981; font-weight: 700; font-size: 0.9rem;">
                        Putaran Ini Sedang Digunakan
                    </div>
                @elseif ($activeCycle && $activeCycle->is_locked)
                    <div style="background-color: #f8fafc; border: 1px dashed var(--border-color); border-radius: var(--radius-sm); padding: 0.75rem 1rem; text-align: center; color: #64748b; font-size: 0.88rem; font-weight: 600;" title="Buka kunci jadwal di pojok atas untuk mengganti putaran">
                        Terkunci (Tidak Bisa Beralih Putaran)
                    </div>
                @else
                    <form action="{{ route('block-cycles.toggle-active', $cycle->id) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem 1rem; font-weight: 600;" onclick="return confirm('Apakah Anda yakin ingin mengaktifkan {{ $namaPutaran }}?')">
                            Aktifkan {{ $namaPutaran }}
                        </button>
                    </form>
                @endif
            </div>

        </div>
    @endforeach
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
                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Aktifkan {{ $namaPutaran }}?')">
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