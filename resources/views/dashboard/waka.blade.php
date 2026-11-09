@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard Waka Kurikulum</h1>
        <p class="page-subtitle">Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong> &bull; {{ $todayName }}, {{ $today->format('d M Y') }}</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('academic-year.index') }}" class="btn btn-secondary btn-sm">
            Tahun Ajaran: {{ \App\Models\Setting::where('key', 'academic_year')->value('value') ?? '2025/2026' }} ({{ \App\Models\Setting::where('key', 'semester')->value('value') ?? 'Ganjil' }})
        </a>
    </div>
</div>

{{-- 5 Stat Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalTeachers }}</div>
            <div class="stat-label">Guru Pengajar</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalStudents }}</div>
            <div class="stat-label">Siswa Terdaftar</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalMajors }}</div>
            <div class="stat-label">Jurusan Keahlian</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalRooms }}</div>
            <div class="stat-label">Ruangan Belajar</div>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-value">{{ $totalSubjects }}</div>
            <div class="stat-label">Mata Pelajaran</div>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    {{-- Siklus Blok Aktif --}}
    <div class="card">
        <h2 class="card-title">
            <span>Siklus Blok Aktif</span>
            @if($activeCycle)
                <span class="badge badge-success">Sedang Berjalan</span>
            @else
                <span class="badge badge-warning">Belum Aktif</span>
            @endif
        </h2>

        @if($activeCycle)
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="font-size: 1.35rem; font-weight: 700; color: var(--accent);">
                    {{ $activeCycle->cycle }}
                </div>
                <div>
                    @if($activeCycle->is_locked)
                        <span class="badge badge-danger">Terkunci</span>
                    @else
                        <span class="badge badge-info">Terbuka</span>
                    @endif
                </div>
            </div>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                Periode Pelaksanaan: <strong>2 Minggu</strong>
            </p>
        @else
            <div style="padding: 1rem; background-color: var(--warning-bg); border-radius: var(--radius-sm); color: var(--warning-text); font-size: 0.875rem; margin-bottom: 1rem;">
                Belum ada siklus blok yang diaktifkan. Silakan aktifkan siklus agar jadwal dan pembelajaran dapat berjalan.
            </div>
        @endif

        <div>
            <a href="{{ route('block-cycles.index') }}" class="btn btn-secondary btn-sm">
                Atur Siklus Blok
            </a>
        </div>
    </div>

    {{-- Ringkasan Operasional Hari Ini --}}
    <div class="card">
        <h2 class="card-title">
            <span>Operasional Hari {{ $todayName }}</span>
            <span class="badge badge-info">{{ $todaySchedulesCount }} Sesi Terjadwal</span>
        </h2>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem; text-align: center;">
            <div style="background-color: var(--bg-surface-hover); padding: 0.75rem; border-radius: var(--radius-sm);">
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--accent);">{{ $todaySchedulesCount }}</div>
                <div style="font-size: 0.75rem; color: var(--text-secondary);">Total Sesi</div>
            </div>
            <div style="background-color: var(--bg-surface-hover); padding: 0.75rem; border-radius: var(--radius-sm);">
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--success);">{{ $todayJournalsCount }}</div>
                <div style="font-size: 0.75rem; color: var(--text-secondary);">Jurnal Terisi</div>
            </div>
            <div style="background-color: var(--bg-surface-hover); padding: 0.75rem; border-radius: var(--radius-sm);">
                <div style="font-size: 1.25rem; font-weight: 700; color: {{ $journalAlerts->count() > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">
                    {{ $journalAlerts->count() }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-secondary);">Terlambat</div>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary btn-sm">Data Guru</a>
            <a href="{{ route('majors.index') }}" class="btn btn-secondary btn-sm">Data Jurusan</a>
            <a href="{{ route('subjects.index') }}" class="btn btn-secondary btn-sm">Mapel</a>
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-sm">Ruangan ({{ $totalRooms }})</a>
            <a href="{{ route('attendances.index') }}" class="btn btn-secondary btn-sm">Rekap Presensi</a>
        </div>
    </div>
</div>

{{-- Rekapitulasi Rombel & Siswa per Jurusan --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <div>
            <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0;">Rekapitulasi Rombel & Siswa per Jurusan</h2>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0.2rem 0 0 0;">Distribusi rombongan belajar dan total peserta didik aktif per kompetensi keahlian</p>
        </div>
        <span class="badge badge-info">{{ $majorStats->count() }} Jurusan Terdata</span>
    </div>

    @if($majorStats->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode</th>
                        <th>Nama Jurusan</th>
                        <th>Jumlah Rombel</th>
                        <th>Total Siswa Aktif</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($majorStats as $stat)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="badge badge-info">{{ $stat->code }}</span></td>
                            <td><strong>{{ $stat->name }}</strong></td>
                            <td>
                                <span class="badge badge-secondary">{{ $stat->rombel_count }} Rombel / Tingkat</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.2rem;">Total {{ $stat->total_classes }} Kelas</span>
                            </td>
                            <td><span class="badge badge-info">{{ $stat->total_students }} Siswa</span></td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="{{ route('students.index', ['major' => $stat->code]) }}" class="btn btn-secondary btn-sm">
                                    Data Siswa
                                </a>
                                <a href="{{ route('schedules.index', ['major' => $stat->code]) }}" class="btn btn-secondary btn-sm">
                                    Lihat Jadwal
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 0.875rem;">
            Belum ada data jurusan atau kelas yang terdaftar.
        </p>
    @endif
</div>

{{-- Monitoring Keterlambatan Jurnal Guru (Dengan Paginasi Per Halaman) --}}
<div class="card" style="margin-bottom: 1.5rem;" id="cardMonitoringKeterlambatan">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
        <h2 class="card-title" style="margin: 0;">
            <span>Monitoring Keterlambatan Pengisian Jurnal</span>
            @if($journalAlerts->count())
                <span class="badge badge-danger">{{ $journalAlerts->count() }} Tertunda</span>
            @else
                <span class="badge badge-success">Semua Tertib</span>
            @endif
        </h2>
    </div>

    @if($journalAlerts->count())
        <p style="color: var(--danger-text); font-size: 0.82rem; margin-bottom: 0.75rem;">
            Berikut daftar jadwal yang telah usai lebih dari 30 menit namun jurnal mengajar belum diisi pada hari ini.
        </p>

        {{-- Filter & Per Page Controls --}}
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.85rem; padding: 0.65rem 0.85rem; background: var(--bg-hover, #f8fafc); border-radius: var(--radius-sm, 8px); border: 1px solid var(--border-color, #e2e8f0);">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-secondary);">
                <span>Tampilkan</span>
                <select id="lateAlertPerPage" onchange="changeLateAlertPageSize(this.value)" style="padding: 0.3rem 0.6rem; border-radius: var(--radius-sm, 6px); border: 1px solid var(--border-color, #cbd5e1); font-size: 0.85rem; background: var(--bg-surface, #ffffff); color: var(--text-primary); cursor: pointer;">
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="all">Semua</option>
                </select>
                <span>per halaman</span>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="text" id="lateAlertSearch" onkeyup="filterLateAlerts()" placeholder="Cari guru, kelas, mapel..." style="padding: 0.35rem 0.75rem; border-radius: var(--radius-sm, 6px); border: 1px solid var(--border-color, #cbd5e1); font-size: 0.85rem; min-width: 220px; background: var(--bg-surface, #ffffff); color: var(--text-primary);">
            </div>
        </div>

        <div class="table-container">
            <table id="tableLateAlerts">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Nama Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Ruangan</th>
                        <th>Jam Selesai</th>
                        <th style="text-align: center;">Keterlambatan</th>
                    </tr>
                </thead>
                <tbody id="lateAlertsBody">
                    @foreach($journalAlerts as $alert)
                        <tr class="late-alert-row" data-search="{{ strtolower(($alert['schedule']->teacher->name ?? '') . ' ' . ($alert['schedule']->subject->name ?? '') . ' ' . ($alert['schedule']->schoolClass->name ?? '') . ' ' . ($alert['schedule']->room->name ?? '')) }}">
                            <td class="row-num" style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">{{ $loop->iteration }}</td>
                            <td><strong>{{ $alert['schedule']->teacher->name ?? '-' }}</strong></td>
                            <td>{{ $alert['schedule']->subject->name ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ $alert['schedule']->schoolClass->name ?? '-' }}</span></td>
                            <td>{{ $alert['schedule']->room->name ?? '-' }}</td>
                            <td>{{ substr($alert['schedule']->start_time, 0, 5) }} - {{ substr($alert['schedule']->end_time, 0, 5) }}</td>
                            <td style="text-align: center;"><span class="badge badge-danger">{{ number_format($alert['minutes_late'], 0) }} menit</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Navigasi Paginasi Bawah --}}
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0.85rem; background: var(--bg-hover, #f8fafc); border-radius: var(--radius-sm, 6px); margin-top: 0.75rem; flex-wrap: wrap; gap: 0.75rem; border-top: 1px solid var(--border-color, #e2e8f0);">
            <div id="lateAlertInfo" style="font-size: 0.82rem; color: var(--text-secondary);">
                Menampilkan <strong id="lateAlertShownRange">1 - 10</strong> dari <strong id="lateAlertTotalCount">{{ $journalAlerts->count() }}</strong> keterlambatan hari ini.
            </div>

            <div id="lateAlertPagination" style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                {{-- Diisi secara dinamis oleh JavaScript --}}
            </div>
        </div>
    @else
        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem; background-color: var(--success-bg); border-radius: var(--radius-sm); color: var(--success-text);">
            <div>
                <strong>Kondisi Ideal:</strong> Tidak ada keterlambatan pengisian jurnal hari ini. Seluruh KBM berjalan tertib sesuai kurikulum.
            </div>
        </div>
    @endif
</div>

{{-- Log Jurnal Pembelajaran Terbaru --}}
<div class="card">
    <h2 class="card-title">
        <span>Aktivitas Jurnal Mengajar Terbaru</span>
        @if($recentJournals->count() > 0)
            <span class="badge badge-info">{{ $recentJournals->count() }} Data Terbaru</span>
        @endif
    </h2>

    @if($recentJournals->count())
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Guru Pengajar</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Materi / Pembahasan</th>
                        <th>Waktu Submit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentJournals as $journal)
                        <tr>
                            <td>{{ $journal->journal_date ? $journal->journal_date->format('d/m/Y') : '-' }}</td>
                            <td><strong>{{ $journal->teacher->name ?? '-' }}</strong></td>
                            <td>{{ $journal->schedule->subject->name ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ $journal->schedule->schoolClass->name ?? '-' }}</span></td>
                            <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $journal->material }}
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; color: var(--text-secondary);">
                                    {{ $journal->submitted_at ? $journal->submitted_at->format('H:i') : $journal->created_at->format('H:i') }} WIB
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 0.875rem;">
            Belum ada data jurnal mengajar yang tersimpan di sistem.
        </p>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function() {
        let currentPage = 1;
        let pageSize = 10;
        let visibleRows = [];

        function initLateAlertsPagination() {
            const allRows = Array.from(document.querySelectorAll('#lateAlertsBody .late-alert-row'));
            if (!allRows.length) return;
            visibleRows = allRows;
            renderLateAlerts();
        }

        window.changeLateAlertPageSize = function(val) {
            pageSize = val === 'all' ? 999999 : parseInt(val, 10);
            currentPage = 1;
            renderLateAlerts();
        };

        window.filterLateAlerts = function() {
            const query = (document.getElementById('lateAlertSearch')?.value || '').toLowerCase().trim();
            const allRows = Array.from(document.querySelectorAll('#lateAlertsBody .late-alert-row'));
            
            visibleRows = allRows.filter(row => {
                const searchStr = row.getAttribute('data-search') || '';
                return searchStr.includes(query);
            });
            
            currentPage = 1;
            renderLateAlerts();
        };

        window.goToLateAlertPage = function(page) {
            currentPage = page;
            renderLateAlerts();
            const card = document.getElementById('cardMonitoringKeterlambatan');
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        function renderLateAlerts() {
            const allRows = Array.from(document.querySelectorAll('#lateAlertsBody .late-alert-row'));
            const total = visibleRows.length;
            const totalPages = Math.ceil(total / pageSize) || 1;
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIdx = (currentPage - 1) * pageSize;
            const endIdx = startIdx + pageSize;

            // Sembunyikan semua dulu
            allRows.forEach(r => r.style.display = 'none');

            // Tampilkan yang sesuai halaman dan perbarui nomor urut
            visibleRows.slice(startIdx, endIdx).forEach((r, idx) => {
                r.style.display = '';
                const numCell = r.querySelector('.row-num');
                if (numCell) numCell.textContent = startIdx + idx + 1;
            });

            // Update teks info
            const rangeEl = document.getElementById('lateAlertShownRange');
            const totalEl = document.getElementById('lateAlertTotalCount');
            if (rangeEl && totalEl) {
                totalEl.textContent = total;
                if (total === 0) {
                    rangeEl.textContent = '0';
                } else {
                    const actualEnd = Math.min(endIdx, total);
                    rangeEl.textContent = `${startIdx + 1} - ${actualEnd}`;
                }
            }

            // Render tombol paginasi
            renderPaginationButtons(totalPages);
        }

        function renderPaginationButtons(totalPages) {
            const container = document.getElementById('lateAlertPagination');
            if (!container) return;
            container.innerHTML = '';

            if (totalPages <= 1) return;

            const createBtn = (text, page, disabled = false, active = false) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerHTML = text;
                btn.style.cssText = `
                    padding: 0.35rem 0.65rem;
                    font-size: 0.8rem;
                    font-weight: 600;
                    border: 1px solid ${active ? 'var(--accent, #4f46e5)' : 'var(--border-color, #cbd5e1)'};
                    background: ${active ? 'var(--accent, #4f46e5)' : 'var(--bg-surface, #ffffff)'};
                    color: ${active ? '#ffffff' : 'var(--text-primary, #1e293b)'};
                    border-radius: 6px;
                    cursor: ${disabled ? 'not-allowed' : 'pointer'};
                    opacity: ${disabled ? '0.5' : '1'};
                    transition: all 0.15s ease;
                `;
                if (!disabled && !active) {
                    btn.onclick = () => goToLateAlertPage(page);
                }
                return btn;
            };

            // Tombol Prev
            container.appendChild(createBtn('&laquo; Prev', currentPage - 1, currentPage === 1));

            // Daftar Halaman dengan Windowing cerdas
            let pages = [];
            if (totalPages <= 7) {
                pages = Array.from({length: totalPages}, (_, i) => i + 1);
            } else {
                if (currentPage <= 4) {
                    pages = [1, 2, 3, 4, 5, '...', totalPages];
                } else if (currentPage >= totalPages - 3) {
                    pages = [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
                } else {
                    pages = [1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages];
                }
            }

            pages.forEach(p => {
                if (p === '...') {
                    const dots = document.createElement('span');
                    dots.textContent = '...';
                    dots.style.cssText = 'padding: 0.35rem 0.5rem; font-size: 0.8rem; color: var(--text-muted);';
                    container.appendChild(dots);
                } else {
                    container.appendChild(createBtn(p, p, false, p === currentPage));
                }
            });

            // Tombol Next
            container.appendChild(createBtn('Next &raquo;', currentPage + 1, currentPage === totalPages));
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLateAlertsPagination);
        } else {
            initLateAlertsPagination();
        }
    })();
</script>
@endpush