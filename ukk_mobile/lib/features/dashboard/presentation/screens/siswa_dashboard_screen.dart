import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/widgets/app_avatar.dart';
import '../../../../core/widgets/empty_state_view.dart';
import '../../../../core/widgets/error_state_view.dart';
import '../../../../core/widgets/loading_shimmer.dart';
import '../../../auth/presentation/controllers/auth_controller.dart';
import '../../../siswa/domain/models/siswa_models.dart';
import '../../../siswa/presentation/controllers/siswa_attendance_controller.dart';
import '../../../siswa/presentation/controllers/siswa_dashboard_controller.dart';
import '../../../siswa/presentation/controllers/siswa_permission_controller.dart';
import '../../../siswa/presentation/screens/siswa_permission_create_screen.dart';

class SiswaDashboardScreen extends ConsumerStatefulWidget {
  const SiswaDashboardScreen({super.key});

  @override
  ConsumerState<SiswaDashboardScreen> createState() => _SiswaDashboardScreenState();
}

class _SiswaDashboardScreenState extends ConsumerState<SiswaDashboardScreen> {
  int _currentIndex = 0;

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        title: const Text('Konfirmasi Logout', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        content: const Text(
          'Apakah Anda yakin ingin keluar dari aplikasi School Class Progress?',
          style: TextStyle(color: Color(0xFF475569), fontSize: 13.5),
        ),
        actions: [
          OutlinedButton(
            onPressed: () => Navigator.of(ctx).pop(),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: Color(0xFFCBD5E1)),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('Batal', style: TextStyle(color: Color(0xFF334155), fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () {
              Navigator.of(ctx).pop();
              ref.read(authControllerProvider.notifier).logout();
            },
            child: const Text('Keluar', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _previewDocument(String url, String title) {
    if (url.isEmpty) return;

    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 8, 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      'Bukti Dokumen: $title',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, size: 20),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
            ),
            const Divider(height: 1),
            Flexible(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Image.network(
                  url,
                  fit: BoxFit.contain,
                  errorBuilder: (_, __, ___) => const Center(
                    child: Padding(
                      padding: EdgeInsets.all(24),
                      child: Column(
                        children: [
                          Icon(Icons.broken_image_rounded, size: 40, color: Color(0xFF94A3B8)),
                          SizedBox(height: 8),
                          Text('Gagal memuat berkas atau format PDF.', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;
    final dashboardData = ref.watch(siswaDashboardControllerProvider);

    String appTitle = 'Dashboard Siswa';
    if (_currentIndex == 1) {
      appTitle = 'Presensi Saya';
    } else if (_currentIndex == 2) {
      appTitle = 'Riwayat Izin Siswa';
    } else if (_currentIndex == 3) {
      appTitle = 'Profil Siswa';
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(color: const Color(0xFFE2E8F0), height: 1),
        ),
        title: Text(
          appTitle,
          style: const TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF0F172A),
          ),
        ),
        actions: [
          if (_currentIndex == 0 || _currentIndex == 3)
            IconButton(
              tooltip: 'Logout',
              icon: const Icon(Icons.logout_rounded, color: Color(0xFFEF4444), size: 20),
              onPressed: _confirmLogout,
            )
          else
            Padding(
              padding: const EdgeInsets.only(right: 16),
              child: AppAvatar(
                imageUrl: user?.avatarUrl,
                name: user?.name ?? 'Siswa',
                radius: 17,
              ),
            ),
        ],
      ),
      body: IndexedStack(
        index: _currentIndex,
        children: [
          // Tab 0: Beranda
          _buildBerandaTab(user, dashboardData),

          // Tab 1: Presensi Saya
          _buildPresensiTab(),

          // Tab 2: Riwayat Izin
          _buildIzinTab(),

          // Tab 3: Profil Saya
          _buildProfilTab(user, dashboardData.student),
        ],
      ),
      floatingActionButton: _currentIndex == 2
          ? FloatingActionButton.extended(
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const SiswaPermissionCreateScreen()),
                );
              },
              backgroundColor: const Color(0xFF4F46E5),
              elevation: 2,
              icon: const Icon(Icons.add_rounded, color: Colors.white, size: 20),
              label: const Text(
                'Ajukan Izin',
                style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
              ),
            )
          : null,
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: Color(0xFFE2E8F0), width: 1)),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) => setState(() => _currentIndex = index),
          backgroundColor: Colors.white,
          elevation: 0,
          type: BottomNavigationBarType.fixed,
          selectedItemColor: const Color(0xFF4F46E5),
          unselectedItemColor: const Color(0xFF64748B),
          selectedLabelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 11.5),
          unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w500, fontSize: 11),
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.home_outlined),
              activeIcon: Icon(Icons.home_rounded),
              label: 'Beranda',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.checklist_rtl_outlined),
              activeIcon: Icon(Icons.checklist_rtl_rounded),
              label: 'Presensi',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.assignment_outlined),
              activeIcon: Icon(Icons.assignment_rounded),
              label: 'Izin',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_outline_rounded),
              activeIcon: Icon(Icons.person_rounded),
              label: 'Profil',
            ),
          ],
        ),
      ),
    );
  }

  // ==========================================
  // TAB 0: BERANDA (PERSIS DESKTOP)
  // ==========================================
  Widget _buildBerandaTab(user, SiswaDashboardData data) {
    if (data.isLoading) {
      return const SingleChildScrollView(
        padding: EdgeInsets.all(14),
        child: Column(
          children: [
            LoadingShimmerList(itemHeight: 110, itemCount: 1),
            SizedBox(height: 12),
            LoadingShimmerList(itemHeight: 70, itemCount: 4),
          ],
        ),
      );
    }

    final stats = data.attendanceStats;

    return RefreshIndicator(
      onRefresh: () => ref.read(siswaDashboardControllerProvider.notifier).loadDashboard(),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 1. Header Card (.page-header)
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 4, offset: const Offset(0, 1)),
                ],
              ),
              child: Row(
                children: [
                  AppAvatar(
                    imageUrl: user?.avatarUrl,
                    name: data.student.name.isNotEmpty ? data.student.name : (user?.name ?? 'Siswa'),
                    radius: 24,
                    backgroundColor: const Color(0xFFEEF2FF),
                    foregroundColor: const Color(0xFF4F46E5),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Dashboard Siswa',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Color(0xFF0F172A)),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Halo, ${data.student.name.isNotEmpty ? data.student.name : (user?.name ?? 'Siswa')} • ${data.todayFormatted.isNotEmpty ? data.todayFormatted : 'Hari Ini'}',
                          style: const TextStyle(fontSize: 12, color: Color(0xFF475569)),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // 2. 4 Stat Cards: Kehadiran, Sakit, Izin, Alpa (Grid 4)
            Row(
              children: [
                _buildStatCard('KEHADIRAN', '${stats.percent}%', const Color(0xFF10B981), '${stats.hadir} Hadir'),
                const SizedBox(width: 6),
                _buildStatCard('SAKIT', '${stats.sakit}', const Color(0xFFF59E0B), 'Tercatat'),
                const SizedBox(width: 6),
                _buildStatCard('IZIN', '${stats.izin}', const Color(0xFF06B6D4), 'Tercatat'),
                const SizedBox(width: 6),
                _buildStatCard('ALPA', '${stats.alpa}', const Color(0xFFEF4444), 'Ket.'),
              ],
            ),
            const SizedBox(height: 14),

            // 3. Profil Siswa Card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Profil Siswa', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF0F172A))),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF0F9FF),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: const Color(0xFFBAE6FD)),
                        ),
                        child: Text(
                          data.student.kelas,
                          style: const TextStyle(color: Color(0xFF0369A1), fontSize: 11, fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  _buildProfileRow('Nama Lengkap', data.student.name),
                  const Divider(height: 14, color: Color(0xFFF1F5F9)),
                  _buildProfileRow('Nomor Induk (NIS)', data.student.nis),
                  const Divider(height: 14, color: Color(0xFFF1F5F9)),
                  _buildProfileRow('Wali Kelas', data.student.waliKelas),
                  const Divider(height: 14, color: Color(0xFFF1F5F9)),
                  _buildProfileRow('Orang Tua / Wali', data.student.orangTua),
                  const Divider(height: 14, color: Color(0xFFF1F5F9)),
                  _buildProfileRow('Siklus Aktif', data.activeCycleName ?? 'Tidak Ada'),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // 4. Layanan Presensi Siswa Card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Layanan Presensi Siswa', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF0F172A))),
                  const SizedBox(height: 4),
                  const Text(
                    'Pantau catatan kehadiran dan persentase presensi Anda di setiap sesi kegiatan belajar mengajar:',
                    style: TextStyle(fontSize: 12, color: Color(0xFF475569)),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: () => setState(() => _currentIndex = 1),
                      icon: const Icon(Icons.checklist_rtl_rounded, size: 16, color: Color(0xFF4F46E5)),
                      label: const Text('Buka Detail Presensi Saya', style: TextStyle(color: Color(0xFF4F46E5), fontSize: 12, fontWeight: FontWeight.w600)),
                      style: OutlinedButton.styleFrom(
                        backgroundColor: Colors.white,
                        side: const BorderSide(color: Color(0xFFC7D2FE)),
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // 5. Jadwal Pelajaran Kelas Hari Ini
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          'Jadwal Kelas Hari Ini (${data.todayName})',
                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF0F172A)),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF0F9FF),
                          borderRadius: BorderRadius.circular(9999),
                          border: Border.all(color: const Color(0xFFBAE6FD)),
                        ),
                        child: Text(
                          '${data.todaySchedules.length} Sesi',
                          style: const TextStyle(color: Color(0xFF075985), fontSize: 10.5, fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  if (data.todaySchedules.isEmpty)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text(
                        'Tidak ada jadwal kegiatan belajar untuk kelas Anda hari ini. Selamat beristirahat atau belajar mandiri!',
                        style: TextStyle(color: Color(0xFF64748B), fontSize: 11.5),
                        textAlign: TextAlign.center,
                      ),
                    )
                  else
                    ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: data.todaySchedules.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 8),
                      itemBuilder: (context, index) {
                        final sch = data.todaySchedules[index];
                        return Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 4),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFEEF2FF),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  sch.timeRangeText,
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF4F46E5)),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(sch.mataPelajaran, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                                    const SizedBox(height: 2),
                                    Text('Guru: ${sch.guru} • Ruang ${sch.ruang}', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                ],
              ),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  // ==========================================
  // TAB 1: PRESENSI SAYA (PERSIS DESKTOP)
  // ==========================================
  Widget _buildPresensiTab() {
    final state = ref.watch(siswaAttendanceControllerProvider);
    final records = state.filteredAttendances;

    return RefreshIndicator(
      onRefresh: () => ref.read(siswaAttendanceControllerProvider.notifier).loadAttendances(),
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(14, 14, 14, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // 4 Stat Cards
                  Row(
                    children: [
                      _buildStatCard('HADIR', '${state.hadirCount}', const Color(0xFF10B981), '${state.percentage}%'),
                      const SizedBox(width: 6),
                      _buildStatCard('SAKIT', '${state.sakitCount}', const Color(0xFFF59E0B), 'Sesi'),
                      const SizedBox(width: 6),
                      _buildStatCard('IZIN', '${state.izinCount}', const Color(0xFF06B6D4), 'Sesi'),
                      const SizedBox(width: 6),
                      _buildStatCard('ALPA', '${state.alpaCount}', const Color(0xFFEF4444), 'Sesi'),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Filter Status Kehadiran
                  SizedBox(
                    height: 38,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      children: [
                        _buildAttendanceFilterTab('Semua', 'Semua (${state.totalCount})', state.selectedFilter),
                        const SizedBox(width: 8),
                        _buildAttendanceFilterTab('Hadir', 'Hadir (${state.hadirCount})', state.selectedFilter),
                        const SizedBox(width: 8),
                        _buildAttendanceFilterTab('Sakit', 'Sakit (${state.sakitCount})', state.selectedFilter),
                        const SizedBox(width: 8),
                        _buildAttendanceFilterTab('Izin', 'Izin (${state.izinCount})', state.selectedFilter),
                        const SizedBox(width: 8),
                        _buildAttendanceFilterTab('Alpa', 'Alpa (${state.alpaCount})', state.selectedFilter),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
              ),
            ),
          ),
          if (state.isLoading)
            const SliverFillRemaining(
              child: Center(child: LoadingShimmerList(itemHeight: 80, itemCount: 5)),
            )
          else if (state.errorMessage != null)
            SliverFillRemaining(
              child: ErrorStateView(
                message: state.errorMessage!,
                onRetry: () => ref.read(siswaAttendanceControllerProvider.notifier).loadAttendances(),
              ),
            )
          else if (records.isEmpty)
            const SliverFillRemaining(
              child: EmptyStateView(
                icon: Icons.event_available_outlined,
                title: 'Belum Ada Data Presensi',
                message: 'Catatan presensi Anda untuk filter ini belum tersedia.',
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 24),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final item = records[index];
                    Color statusColor = const Color(0xFF10B981);
                    Color bgColor = const Color(0xFFECFDF5);
                    if (item.status.toLowerCase() == 'sakit') {
                      statusColor = const Color(0xFFD97706);
                      bgColor = const Color(0xFFFFFBEB);
                    } else if (item.status.toLowerCase() == 'izin') {
                      statusColor = const Color(0xFF0284C7);
                      bgColor = const Color(0xFFF0F9FF);
                    } else if (item.status.toLowerCase() == 'alpa') {
                      statusColor = const Color(0xFFDC2626);
                      bgColor = const Color(0xFFFEF2F2);
                    }

                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item.mataPelajaran,
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '${item.tanggal} • ${item.jam}',
                                  style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                                ),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: bgColor,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              item.status,
                              style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                  childCount: records.length,
                ),
              ),
            ),
        ],
      ),
    );
  }

  // ==========================================
  // TAB 2: RIWAYAT IZIN (PERSIS DESKTOP)
  // ==========================================
  Widget _buildIzinTab() {
    final state = ref.watch(siswaPermissionControllerProvider);
    final list = state.filteredPermissions;

    return RefreshIndicator(
      onRefresh: () => ref.read(siswaPermissionControllerProvider.notifier).loadPermissions(),
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(14, 14, 14, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // 3 Stat Cards
                  Row(
                    children: [
                      _buildStatCard('TOTAL IZIN', '${state.totalCount}', const Color(0xFF4F46E5), 'Pengajuan'),
                      const SizedBox(width: 8),
                      _buildStatCard('MENUNGGU', '${state.pendingCount}', const Color(0xFFF59E0B), 'Verifikasi'),
                      const SizedBox(width: 8),
                      _buildStatCard('DISETUJUI', '${state.approvedCount}', const Color(0xFF10B981), 'Diterima'),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Filter Tabs
                  SizedBox(
                    height: 38,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      children: [
                        _buildPermissionFilterTab('Semua', 'Semua (${state.totalCount})', state.filterStatus),
                        const SizedBox(width: 8),
                        _buildPermissionFilterTab('Pending', 'Menunggu (${state.pendingCount})', state.filterStatus),
                        const SizedBox(width: 8),
                        _buildPermissionFilterTab('Approved', 'Disetujui (${state.approvedCount})', state.filterStatus),
                        const SizedBox(width: 8),
                        _buildPermissionFilterTab('Rejected', 'Ditolak (${state.rejectedCount})', state.filterStatus),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
              ),
            ),
          ),
          if (state.isLoading)
            const SliverFillRemaining(
              child: Center(child: LoadingShimmerList(itemHeight: 120, itemCount: 4)),
            )
          else if (state.errorMessage != null)
            SliverFillRemaining(
              child: ErrorStateView(
                message: state.errorMessage!,
                onRetry: () => ref.read(siswaPermissionControllerProvider.notifier).loadPermissions(),
              ),
            )
          else if (list.isEmpty)
            const SliverFillRemaining(
              child: EmptyStateView(
                icon: Icons.assignment_turned_in_outlined,
                title: 'Belum Ada Pengajuan Izin',
                message: 'Tekan tombol "Ajukan Izin" untuk membuat permohonan izin baru.',
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 24),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final item = list[index];
                    return Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(
                                item.rentangTanggal.isNotEmpty ? item.rentangTanggal : item.tanggal,
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                              ),
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                decoration: BoxDecoration(
                                  color: item.isSakit ? const Color(0xFFFFFBEB) : const Color(0xFFEEF2FF),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  item.jenis,
                                  style: TextStyle(
                                    color: item.isSakit ? const Color(0xFF92400E) : const Color(0xFF4F46E5),
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              const Spacer(),
                              _buildStatusBadge(item),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text('Durasi: ${item.durasiHari} Hari', style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B))),
                          const SizedBox(height: 4),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(item.alasan, style: const TextStyle(fontSize: 12, color: Color(0xFF334155))),
                          ),
                          if (item.dokumen != null && item.dokumen!.isNotEmpty) ...[
                            const SizedBox(height: 8),
                            Align(
                              alignment: Alignment.centerLeft,
                              child: OutlinedButton.icon(
                                onPressed: () => _previewDocument(item.documentUrl, item.jenis),
                                icon: const Icon(Icons.description_outlined, size: 14, color: Color(0xFF4F46E5)),
                                label: const Text('Lihat Berkas Surat', style: TextStyle(fontSize: 11, color: Color(0xFF4F46E5))),
                                style: OutlinedButton.styleFrom(
                                  side: const BorderSide(color: Color(0xFFC7D2FE)),
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  minimumSize: Size.zero,
                                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                ),
                              ),
                            ),
                          ],
                          if (item.catatanVerifikasi != null && item.catatanVerifikasi!.isNotEmpty) ...[
                            const SizedBox(height: 8),
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF2F2),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: const Color(0xFFFECACA)),
                              ),
                              child: Text('Catatan Petugas: ${item.catatanVerifikasi}', style: const TextStyle(fontSize: 11, color: Color(0xFF991B1B))),
                            ),
                          ],
                        ],
                      ),
                    );
                  },
                  childCount: list.length,
                ),
              ),
            ),
        ],
      ),
    );
  }

  // ==========================================
  // TAB 3: PROFIL SAYA (PERSIS DESKTOP)
  // ==========================================
  Widget _buildProfilTab(user, SiswaProfile student) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              children: [
                Center(
                  child: AppAvatar(
                    imageUrl: user?.avatarUrl,
                    name: student.name.isNotEmpty ? student.name : (user?.name ?? 'Siswa'),
                    radius: 42,
                    backgroundColor: const Color(0xFFEEF2FF),
                    foregroundColor: const Color(0xFF4F46E5),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  student.name.isNotEmpty ? student.name : (user?.name ?? 'Peserta Didik'),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: Color(0xFF0F172A)),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 3),
                Text(user?.email ?? '', style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B))),
                const SizedBox(height: 10),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0F9FF),
                    borderRadius: BorderRadius.circular(9999),
                    border: Border.all(color: const Color(0xFFBAE6FD)),
                  ),
                  child: Text(
                    'KELAS ${student.kelas} • NIS: ${student.nis}',
                    style: const TextStyle(color: Color(0xFF0369A1), fontWeight: FontWeight.w700, fontSize: 11),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              children: [
                _buildProfileItem(Icons.badge_outlined, 'Peran / Role', 'PESERTA DIDIK (SISWA)'),
                const Divider(height: 20, color: Color(0xFFF1F5F9)),
                _buildProfileItem(Icons.school_outlined, 'Kelas Binaan', student.kelas),
                const Divider(height: 20, color: Color(0xFFF1F5F9)),
                _buildProfileItem(Icons.person_pin_outlined, 'Wali Kelas', student.waliKelas),
                const Divider(height: 20, color: Color(0xFFF1F5F9)),
                _buildProfileItem(Icons.family_restroom_outlined, 'Orang Tua / Wali', student.orangTua),
                const Divider(height: 20, color: Color(0xFFF1F5F9)),
                _buildProfileItem(Icons.verified_outlined, 'Status Akun', 'Terverifikasi & Aktif'),
              ],
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _confirmLogout,
              icon: const Icon(Icons.logout_rounded, size: 18, color: Color(0xFFEF4444)),
              label: const Text('Keluar dari Akun', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFFEF4444), fontSize: 13)),
              style: OutlinedButton.styleFrom(
                backgroundColor: const Color(0xFFFEF2F2),
                side: const BorderSide(color: Color(0xFFFECACA)),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'School Class Progress • Versi Mobile Siswa\nSMK Negeri 1',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8), height: 1.5),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // HELPER WIDGETS
  // ==========================================
  Widget _buildStatCard(String label, String value, Color color, String sub) {
    return Expanded(
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: const Color(0xFFE2E8F0)),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(height: 3, color: color),
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 4),
                child: Column(
                  children: [
                    Text(value, style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: color, height: 1.1)),
                    const SizedBox(height: 2),
                    Text(label, style: const TextStyle(fontSize: 8.5, fontWeight: FontWeight.w700, color: Color(0xFF64748B)), textAlign: TextAlign.center, maxLines: 1),
                    Text(sub, style: const TextStyle(fontSize: 8, color: Color(0xFF94A3B8)), textAlign: TextAlign.center, maxLines: 1),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildProfileRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B))),
        Text(value, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
      ],
    );
  }

  Widget _buildStatusBadge(SiswaPermissionItem item) {
    Color bg = const Color(0xFFFFFBEB);
    Color fg = const Color(0xFF92400E);
    String label = 'Menunggu';

    if (item.isApproved) {
      bg = const Color(0xFFECFDF5);
      fg = const Color(0xFF065F46);
      label = 'Disetujui';
    } else if (item.isRejected) {
      bg = const Color(0xFFFEF2F2);
      fg = const Color(0xFF991B1B);
      label = 'Ditolak';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(4)),
      child: Text(label, style: TextStyle(color: fg, fontSize: 10.5, fontWeight: FontWeight.bold)),
    );
  }

  Widget _buildAttendanceFilterTab(String key, String label, String active) {
    final isSelected = active == key;
    return InkWell(
      onTap: () => ref.read(siswaAttendanceControllerProvider.notifier).setFilter(key),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF4F46E5) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: isSelected ? const Color(0xFF4F46E5) : const Color(0xFFCBD5E1)),
        ),
        child: Text(
          label,
          style: TextStyle(fontSize: 12, fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500, color: isSelected ? Colors.white : const Color(0xFF475569)),
        ),
      ),
    );
  }

  Widget _buildPermissionFilterTab(String key, String label, String active) {
    final isSelected = active == key;
    return InkWell(
      onTap: () => ref.read(siswaPermissionControllerProvider.notifier).setFilterStatus(key),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF4F46E5) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: isSelected ? const Color(0xFF4F46E5) : const Color(0xFFCBD5E1)),
        ),
        child: Text(
          label,
          style: TextStyle(fontSize: 12, fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500, color: isSelected ? Colors.white : const Color(0xFF475569)),
        ),
      ),
    );
  }

  Widget _buildProfileItem(IconData icon, String label, String value) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(7),
          decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(8)),
          child: Icon(icon, size: 17, color: const Color(0xFF475569)),
        ),
        const SizedBox(width: 12),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
            const SizedBox(height: 2),
            Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF0F172A))),
          ],
        ),
      ],
    );
  }
}
