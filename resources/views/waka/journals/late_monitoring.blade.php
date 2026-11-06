@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Monitoring Keterlambatan Pengisian Jurnal</h1>
        <p class="page-subtitle">Pantau jadwal pembelajaran yang telah usai lebih dari 30 menit namun belum diunggah jurnalnya oleh guru</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('waka.journals.index') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Semua Jurnal Hari Ini
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak Rekap Keterlambatan
        </button>
        <a href="{{ route('dashboard.waka') }}" class="btn-back">
            ← Dashboard
        </a>
    </div>
</div>

{{-- Kartu Ringkasan Statistik --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- Total Keterlambatan --}}
    <div class="card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #ef4444;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Keterlambatan</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #ef4444; line-height: 1.2;">{{ $totalLateCount }} <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">Jadwal</span></div>
        </div>
    </div>

    {{-- Kritis > 2 Jam --}}
    <div class="card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #b91c1c;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(185, 28, 28, 0.12); color: #b91c1c; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Kritis (&gt; 2 Jam)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #b91c1c; line-height: 1.2;">{{ $criticalCount }} <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">Jadwal</span></div>
        </div>
    </div>

    {{-- Sedang 1-2 Jam --}}
    <div class="card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #f59e0b;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Sedang (1 - 2 Jam)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #d97706; line-height: 1.2;">{{ $mediumCount }} <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">Jadwal</span></div>
        </div>
    </div>

    {{-- Tingkat Kepatuhan Pengisian Jurnal --}}
    <div class="card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #10b981;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Tingkat Kepatuhan</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #059669; line-height: 1.2;">{{ $complianceRate }}%</div>
        </div>
    </div>
</div>

{{-- Filter & Pencarian --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="{{ route('monitoring.keterlambatan') }}">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; align-items: flex-end;">
            {{-- Tanggal --}}
            <div>
                <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 0.35rem;">
                    Tanggal Pemantauan
                </label>
                <input type="date" name="date" class="form-control" value="{{ request('date', $targetDate->format('Y-m-d')) }}">
            </div>

            {{-- Tingkat Keterlambatan --}}
            <div>
                <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 0.35rem;">
                    Status Keterlambatan
                </label>
                <select name="delay_level" class="form-control">
                    <option value="all" {{ $selectedDelayLevel === 'all' ? 'selected' : '' }}>Semua Keterlambatan</option>
                    <option value="critical" {{ $selectedDelayLevel === 'critical' ? 'selected' : '' }}>🚨 Kritis (&gt; 2 Jam)</option>
                    <option value="medium" {{ $selectedDelayLevel === 'medium' ? 'selected' : '' }}>⚠️ Sedang (1 - 2 Jam)</option>
                    <option value="warning" {{ $selectedDelayLevel === 'warning' ? 'selected' : '' }}>ℹ️ Perhatian (&gt; 30 Menit)</option>
                </select>
            </div>

            {{-- Tingkat Kelas --}}
            <div>
                <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 0.35rem;">
                    Tingkat Kelas
                </label>
                <select name="grade" class="form-control">
                    <option value="">Semua Tingkat</option>
                    <option value="X" {{ $selectedGrade === 'X' ? 'selected' : '' }}>Kelas X</option>
                    <option value="XI" {{ $selectedGrade === 'XI' ? 'selected' : '' }}>Kelas XI</option>
                    <option value="XII" {{ $selectedGrade === 'XII' ? 'selected' : '' }}>Kelas XII</option>
                </select>
            </div>

            {{-- Jurusan --}}
            <div>
                <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 0.35rem;">
                    Jurusan
                </label>
                <select name="major" class="form-control">
                    <option value="">Semua Jurusan</option>
                    @foreach($majors as $m)
                        <option value="{{ $m }}" {{ $selectedMajor === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Guru Pengampu --}}
            <div>
                <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 0.35rem;">
                    Guru Pengampu
                </label>
                <select name="teacher_id" class="form-control">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $selectedTeacherId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Pencarian Kata Kunci --}}
            <div>
                <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 0.35rem;">
                    Pencarian Cepat
                </label>
                <input type="text" name="search" class="form-control" placeholder="Guru, Mapel, Kelas, Ruang..." value="{{ $search }}">
            </div>

            {{-- Tombol Filter --}}
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Terapkan
                </button>
                <a href="{{ route('monitoring.keterlambatan') }}" class="btn btn-secondary" title="Reset Filter">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Tabel Data Monitoring Keterlambatan --}}
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h2 class="card-title" style="margin: 0;">
                <span>Daftar Keterlambatan Jurnal — {{ $dateFormatted }}</span>
                @if($totalLateCount > 0)
                    <span class="badge badge-danger">{{ $totalLateCount }} Jadwal Tertunda</span>
                @else
                    <span class="badge badge-success">Semua Tertib</span>
                @endif
            </h2>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">
                Standar Kepatuhan: Jurnal wajib diisi maksimal 30 menit setelah jam pembelajaran usai.
            </div>
        </div>
    </div>

    @if($lateItems->isEmpty())
        <div style="text-align: center; padding: 3rem 1.5rem; background: var(--bg-hover, #f8fafc); border-radius: var(--radius-sm, 8px); border: 1px dashed var(--border-color, #e2e8f0);">
            <div style="width: 64px; height: 64px; margin: 0 auto 1rem; border-radius: 50%; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                <svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.35rem;">
                Kondisi Ideal: Tidak Ada Keterlambatan!
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); max-width: 480px; margin: 0 auto;">
                Seluruh guru pengampu telah tertib mengisi jurnal materi dan presensi tepat waktu sesuai jadwal kurikulum yang berlaku.
            </p>
        </div>
    @else
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Guru Pengampu</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas & Jurusan</th>
                        <th>Ruangan</th>
                        <th>Waktu KBM</th>
                        <th style="text-align: center;">Keterlambatan</th>
                        <th style="text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lateItems as $item)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-primary);">
                                    {{ $item['schedule']->teacher->name ?? '-' }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    NIP: {{ $item['schedule']->teacher->nip ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $item['schedule']->subject->name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                    {{ $item['schedule']->schoolClass->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                    {{ $item['schedule']->room->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.85rem;">{{ $item['time_range'] }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    Usai Pukul: {{ $item['end_time_formatted'] }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 0.2rem;">
                                    <span class="badge {{ $item['badge_class'] }}" style="font-size: 0.82rem; font-weight: 700; padding: 0.3rem 0.65rem;">
                                        {{ $item['formatted_late'] }}
                                    </span>
                                    <span style="font-size: 0.7rem; color: var(--text-muted);">
                                        {{ $item['level_label'] }}
                                    </span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $pesanWa = urlencode(
                                        "Yth. Bapak/Ibu " . ($item['schedule']->teacher->name ?? 'Guru') . ",\n\n" .
                                        "Mohon segera mengisi Jurnal Mengajar untuk:\n" .
                                        "• Mapel: " . ($item['schedule']->subject->name ?? '-') . "\n" .
                                        "• Kelas: " . ($item['schedule']->schoolClass->name ?? '-') . "\n" .
                                        "• Waktu: " . $item['time_range'] . " (" . $item['schedule']->day . ")\n\n" .
                                        "Keterlambatan saat ini: " . $item['formatted_late'] . ".\n" .
                                        "Akses sistem: " . url('/login') . "\n\nTerima kasih."
                                    );
                                @endphp
                                <a href="https://wa.me/?text={{ $pesanWa }}" target="_blank" class="btn btn-secondary btn-sm" title="Ingatkan Guru via WhatsApp" style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; padding: 0.3rem 0.5rem;">
                                    <span>💬</span> Ingatkan
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<style>
@media print {
    .page-header div:last-child,
    .btn-back,
    form,
    .card:has(form),
    nav,
    .sidebar,
    .topbar {
        display: none !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
    }
    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    th, td {
        border: 1px solid #333 !important;
        padding: 6px 8px !important;
    }
}
</style>
@endsection

