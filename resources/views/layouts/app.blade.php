<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'School Class Progress' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Stylesheet Global untuk Semua Role -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    @php
        $user = auth()->user();
        $role = $user->role ?? 'siswa';
        
        // Label Role
        $roleLabel = match($role) {
            'waka' => 'Waka Kurikulum',
            'guru' => 'Guru Pengajar',
            'bk' => 'Guru BK',
            'siswa' => 'Siswa',
            'ortu' => 'Orang Tua Siswa',
            default => ucfirst($role)
        };
    @endphp

    <div class="app-container">
        <!-- ================= SIDEBAR DINAMIS PER ROLE ================= -->
        <aside class="sidebar {{ $role === 'waka' ? 'sidebar-waka' : '' }}" id="appSidebar">
            <!-- Header Brand -->
            <div class="sidebar-header" style="display: flex; align-items: center; gap: 0.65rem;">
                <img src="{{ asset('images/logo-emblem.png') }}" alt="School Class Progress" class="brand-logo" style="width: 36px; height: 36px; object-fit: contain; background: transparent; box-shadow: none; padding: 0; flex-shrink: 0;">
                <div style="display: flex; flex-direction: column; min-width: 0;">
                    <span class="brand-title" style="font-size: 0.95rem; line-height: 1.2; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">School Class Progress</span>
                    <span style="font-size: 0.675rem; color: var(--accent); font-weight: 700; text-transform: uppercase;">{{ $roleLabel }}</span>
                </div>
            </div>

            <!-- Navigasi Menu Sesuai Role -->
            <div class="sidebar-content">
                <div class="nav-category">Menu Utama</div>
                <ul class="nav-menu">

                    {{-- 1. ROLE WAKA KURIKULUM --}}
                    @if ($role === 'waka')
                        <li class="nav-item {{ request()->routeIs('dashboard.waka') ? 'active' : '' }}">
                            <a href="{{ route('dashboard.waka') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Dashboard Waka</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('block-cycles.*') ? 'active' : '' }}">
                            <a href="{{ route('block-cycles.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Siklus Blok</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                            <a href="{{ route('teachers.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>Data Guru</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('students.*') ? 'active' : '' }}">
                            <a href="{{ route('students.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Data Siswa</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('classes.*') ? 'active' : '' }}">
                            <a href="{{ route('classes.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Data Kelas</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('majors.*') ? 'active' : '' }}">
                            <a href="{{ route('majors.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <span>Data Jurusan</span>
                            </a>
                        </li>

                        <div class="nav-category">Master Akademik</div>
                        
                        <li class="nav-item {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
                            <a href="{{ route('schedules.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Jadwal Mengajar</span>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('lesson-hours.*') ? 'active' : '' }}">
                            <a href="{{ route('lesson-hours.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Jam Pelajaran (Sesi)</span>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                            <a href="{{ route('subjects.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span>Mata Pelajaran</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                            <a href="{{ route('rooms.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                                <span>Data Ruangan</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('academic-year.*') ? 'active' : '' }}">
                            <a href="{{ route('academic-year.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                <span>Tahun Ajaran</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('class-promotions.*') ? 'active' : '' }}">
                            <a href="{{ route('class-promotions.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                                <span>Kenaikan Kelas</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('waka.journals.*') ? 'active' : '' }}">
                            <a href="{{ route('waka.journals.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Monitoring Jurnal Guru</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('attendances.*') ? 'active' : '' }}">
                            <a href="{{ route('attendances.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span>Rekap Presensi Siswa</span>
                            </a>
                        </li>
                    {{-- 2. ROLE GURU & GURU BK --}}
                    @elseif ($role === 'guru' || $role === 'bk')
                        <li class="nav-item {{ request()->routeIs('dashboard.guru') ? 'active' : '' }}">
                            <a href="{{ route('dashboard.guru') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Dashboard Guru</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
                            <a href="{{ route('student.schedule') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Jadwal Mengajar</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('journals.*') ? 'active' : '' }}">
                            <a href="{{ route('journals.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Jurnal Mengajar</span>
                            </a>
                        </li>

                        {{-- Menu Tambahan Khusus Wali Kelas --}}
                        @if(auth()->user()->teacher && auth()->user()->teacher->homeroomClasses->isNotEmpty())
                            <div class="nav-category" style="margin-top: 1rem; color: #4f46e5; font-weight: 600;">
                                Wali Kelas: {{ auth()->user()->teacher->homeroomClasses->pluck('name')->join(', ') }}
                            </div>

                            <li class="nav-item {{ request()->routeIs('attendances.*') ? 'active' : '' }}">
                                <a href="{{ route('attendances.index') }}">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span>Rekap Presensi Kelas</span>
                                </a>
                            </li>

                            <li class="nav-item {{ request()->routeIs('homeroom.permissions.*') ? 'active' : '' }}">
                                <a href="{{ route('homeroom.permissions.index') }}">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Verifikasi Izin Siswa</span>
                                </a>
                            </li>
                        @endif

                        {{-- Menu Tambahan Khusus Guru BK & Tim Kesiswaan --}}
                        @if(in_array($role, ['bk', 'kesiswaan'], true) || (auth()->user()->teacher && auth()->user()->teacher->isBkTeacher()))
                            <div class="nav-category" style="margin-top: 1rem; color: #4f46e5; font-weight: 600;">
                                Kesiswaan & Bimbingan Konseling (BK)
                            </div>

                            <li class="nav-item {{ request()->routeIs('students.*') ? 'active' : '' }}">
                                <a href="{{ route('students.index') }}">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <span>Data Siswa Binaan</span>
                                </a>
                            </li>

                            <li class="nav-item {{ request()->routeIs('attendances.*') ? 'active' : '' }}">
                                <a href="{{ route('attendances.index') }}">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span>Rekap Presensi Siswa</span>
                                </a>
                            </li>

                            <li class="nav-item {{ request()->routeIs('permission-requests.verification') ? 'active' : '' }}">
                                <a href="{{ route('permission-requests.verification') }}">
                                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Monitoring Izin Siswa</span>
                                </a>
                            </li>
                        @endif

                    {{-- 3. ROLE SISWA / ORTU --}}
                    @else
                        <li class="nav-item {{ request()->routeIs('dashboard.siswa') ? 'active' : '' }}">
                            <a href="{{ route('dashboard.siswa') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>{{ $role === 'ortu' ? 'Monitoring Siswa' : 'Dashboard Siswa' }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
                            <a href="{{ route('student.schedule') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Jadwal Pelajaran</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('permission-requests.create') ? 'active' : '' }}">
                            <a href="{{ route('permission-requests.create') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Ajukan Izin</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('permission-requests.index') || request()->routeIs('permission-requests.show') ? 'active' : '' }}">
                            <a href="{{ route('permission-requests.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Riwayat Izin</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('attendances.*') ? 'active' : '' }}">
                            <a href="{{ route('attendances.index') }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span>Presensi Saya</span>
                            </a>
                        </li>
                    @endif

                    <div class="nav-category">Akun Saya</div>
                    <li class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <a href="{{ route('profile.edit') }}">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7 7z"/>
                            </svg>
                            <span>Profil Pengguna</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Sidebar Footer: Tombol Keluar Langsung Berfungsi -->
            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: flex-start; color: var(--danger);">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar / Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ================= MAIN WRAPPER (NAVBAR + CONTENT) ================= -->
        <div class="main-wrapper">
            <!-- Top Navbar -->
            <header class="top-navbar">
                <div class="navbar-left">
                    <button type="button" class="btn btn-secondary" onclick="toggleSidebar()" id="mobileToggle" style="display: none; padding: 0.35rem 0.65rem;">
                        ☰
                    </button>
                    <div class="search-box">
                        <svg viewBox="0 0 24 24">
                            <path d="M10 2a8 8 0 015.29 13.99l4.86 4.86a1 1 0 01-1.42 1.42l-4.86-4.86A8 8 0 1110 2zm0 2a6 6 0 100 12 6 6 0 000-12z"/>
                        </svg>
                        <input type="text" placeholder="Cari data...">
                    </div>
                </div>

                <div class="navbar-right" style="display: flex; align-items: center; gap: 0.75rem;">
                    <a href="{{ route('profile.edit') }}" class="user-profile" title="Buka Profil">
                        <div class="user-avatar">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ $user->name ?? 'Pengguna' }}</span>
                            <span class="user-role-badge">{{ $roleLabel }}</span>
                        </div>
                    </a>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="content-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning">
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin-left: 1.2rem; list-style: disc;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Global Helpers for In-Page Modals -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            sidebar.classList.toggle('open');
        }

        // ==========================================
        // IN-PAGE MODAL CONTROLLER
        // ==========================================
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Auto-focus first input if available
            setTimeout(() => {
                const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
                if (firstInput) firstInput.focus();
            }, 100);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.classList.remove('active');
            
            // Check if any other modal is still active
            const activeModals = document.querySelectorAll('.modal-overlay.active');
            if (activeModals.length === 0) {
                document.body.style.overflow = '';
            }
        }

        // Close on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const activeModals = document.querySelectorAll('.modal-overlay.active');
                activeModals.forEach(modal => {
                    modal.classList.remove('active');
                });
                document.body.style.overflow = '';
            }
        });

        // Close when clicking backdrop outside .modal-card
        document.addEventListener('click', function(e) {
            if (e.target.classList && e.target.classList.contains('modal-overlay')) {
                e.target.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // ==========================================================================
        // CUSTOM CSS TOOLTIP ENGINE (Pengganti Tooltip Bawaan Google Chrome)
        // ==========================================================================
        (function() {
            let tooltipEl = null;
            let currentTarget = null;
            let hideTimer = null;

            function createTooltipElement() {
                if (!tooltipEl) {
                    tooltipEl = document.createElement('div');
                    tooltipEl.className = 'custom-tooltip-box';
                    tooltipEl.setAttribute('role', 'tooltip');
                    tooltipEl.setAttribute('aria-hidden', 'true');
                    document.body.appendChild(tooltipEl);
                }
                return tooltipEl;
            }

            function showTooltip(el, text) {
                if (!text || !text.trim()) return;
                clearTimeout(hideTimer);
                const tt = createTooltipElement();
                tt.textContent = text.trim();
                currentTarget = el;

                tt.className = 'custom-tooltip-box';
                tt.style.visibility = 'hidden';
                tt.style.opacity = '0';
                tt.style.display = 'block';
                tt.style.left = '0px';
                tt.style.top = '0px';

                const rect = el.getBoundingClientRect();
                if (rect.width === 0 && rect.height === 0) return;

                const ttRect = tt.getBoundingClientRect();
                const gap = 8;
                const viewportWidth = window.innerWidth;

                let isTop = rect.top - ttRect.height - gap >= 10;
                let top = isTop ? (rect.top - ttRect.height - gap) : (rect.bottom + gap);

                let left = rect.left + (rect.width / 2) - (ttRect.width / 2);
                const padding = 10;
                let clampedLeft = Math.max(padding, Math.min(left, viewportWidth - ttRect.width - padding));

                let arrowOffset = (rect.left + rect.width / 2) - clampedLeft;
                arrowOffset = Math.max(12, Math.min(arrowOffset, ttRect.width - 12));

                tt.style.top = `${top}px`;
                tt.style.left = `${clampedLeft}px`;
                tt.style.setProperty('--arrow-left', `${arrowOffset}px`);

                tt.classList.add(isTop ? 'pos-top' : 'pos-bottom');
                tt.classList.add('is-visible');
            }

            function hideTooltip() {
                if (tooltipEl) {
                    tooltipEl.classList.remove('is-visible');
                    hideTimer = setTimeout(() => {
                        if (tooltipEl && !tooltipEl.classList.contains('is-visible')) {
                            tooltipEl.style.display = 'none';
                        }
                    }, 150);
                }
                currentTarget = null;
            }

            document.addEventListener('mouseover', function(e) {
                const target = e.target.closest('[title], [data-tooltip]');
                if (!target) return;

                // Cegah tooltip bawaan Google Chrome dengan memindahkan attribute title ke data-tooltip
                if (target.hasAttribute('title')) {
                    const rawTitle = target.getAttribute('title');
                    if (rawTitle && rawTitle.trim()) {
                        target.setAttribute('data-tooltip', rawTitle.trim());
                    }
                    target.removeAttribute('title');
                }

                const tooltipText = target.getAttribute('data-tooltip');
                if (tooltipText) {
                    showTooltip(target, tooltipText);
                }
            }, { passive: true });

            document.addEventListener('mouseout', function(e) {
                if (currentTarget) {
                    const related = e.relatedTarget;
                    if (!related || (!currentTarget.contains(related) && currentTarget !== related)) {
                        hideTooltip();
                    }
                }
            }, { passive: true });

            window.addEventListener('scroll', hideTooltip, { passive: true });
            document.addEventListener('click', hideTooltip, { passive: true });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') hideTooltip();
            });
        })();
    </script>

    <style>
        .custom-confirm-overlay { display: none !important; pointer-events: none !important; }
    </style>
    <script>
        // Dialog Konfirmasi & Alert Standar (Kompatibel 100% Bawaan Browser & Tidak Memblokir Tombol)
        document.addEventListener('click', function(e) {
            const target = e.target.closest('[data-custom-confirm]');
            if (!target) return;
            const msg = target.getAttribute('data-custom-confirm');
            if (msg) {
                if (!window.confirm(msg)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        });

        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form) return;
            const msg = form.getAttribute('data-custom-confirm');
            if (msg) {
                if (!window.confirm(msg)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        });

        window.showCustomConfirm = function(message, callback) {
            const res = window.confirm(message);
            if (typeof callback === 'function') {
                callback(res);
            }
            return res;
        };

        window.showCustomAlert = function(message, callback) {
            window.alert(message);
            if (typeof callback === 'function') {
                callback();
            }
        };
    </script>
    @stack('scripts')
</body>
</html>
