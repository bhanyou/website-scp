import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/router/route_names.dart';
import '../../../../core/widgets/app_avatar.dart';
import '../../../../core/widgets/loading_shimmer.dart';
import '../../../auth/presentation/controllers/auth_controller.dart';
import '../../../journal/domain/models/journal_models.dart';
import '../../../journal/presentation/screens/journal_history_screen.dart';
import '../../../schedule/domain/models/schedule_model.dart';
import '../../../schedule/presentation/screens/schedule_list_screen.dart';
import '../controllers/guru_dashboard_controller.dart';

class GuruDashboardScreen extends ConsumerStatefulWidget {
  const GuruDashboardScreen({super.key});

  @override
  ConsumerState<GuruDashboardScreen> createState() => _GuruDashboardScreenState();
}

class _GuruDashboardScreenState extends ConsumerState<GuruDashboardScreen> {
  int _currentIndex = 0;

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
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

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;
    final isHomeroom = user?.isHomeroom == true;
    final homeroomClasses = user?.homeroomClasses.join(', ') ?? '';

    final dashboardData = ref.watch(guruDashboardControllerProvider);

    // Judul Header sesuai tab yang dipilih
    String appTitle = 'Dashboard Guru Pengajar';
    if (_currentIndex == 1) {
      appTitle = 'Jadwal Mengajar KBM';
    } else if (_currentIndex == 2) {
      appTitle = 'Riwayat Jurnal KBM';
    } else if (_currentIndex == 3) {
      appTitle = 'Profil Guru Pengajar';
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
                name: user?.name ?? 'Guru',
                radius: 17,
              ),
            ),
        ],
      ),
      body: IndexedStack(
        index: _currentIndex,
        children: [
          // Tab 0: Beranda (Persis Tampilan Desktop)
          _buildBerandaTab(user, isHomeroom, homeroomClasses, dashboardData),

          // Tab 1: Jadwal Mengajar KBM
          const ScheduleListScreen(isEmbedded: true),

          // Tab 2: Riwayat Jurnal KBM
          const JournalHistoryScreen(isEmbedded: true),

          // Tab 3: Profil Guru
          _buildProfileTab(user, isHomeroom, homeroomClasses),
        ],
      ),
      floatingActionButton: _currentIndex == 2
          ? FloatingActionButton.extended(
              onPressed: () => context.push(RouteNames.guruJournalCreate),
              backgroundColor: const Color(0xFF4F46E5),
              elevation: 2,
              icon: const Icon(Icons.add_a_photo_rounded, color: Colors.white, size: 20),
              label: const Text(
                'Isi Jurnal Baru',
                style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
              ),
            )
          : null,
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          border: const Border(top: BorderSide(color: Color(0xFFE2E8F0), width: 1)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.03),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
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
              icon: Icon(Icons.calendar_month_outlined),
              activeIcon: Icon(Icons.calendar_month_rounded),
              label: 'Jadwal',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.assignment_outlined),
              activeIcon: Icon(Icons.assignment_rounded),
              label: 'Jurnal',
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

  Widget _buildBerandaTab(user, bool isHomeroom, String homeroomClasses, GuruDashboardData data) {
    if (data.isLoading) {
      return const SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            LoadingShimmerList(itemHeight: 110, itemCount: 1),
            SizedBox(height: 14),
            LoadingShimmerList(itemHeight: 80, itemCount: 3),
            SizedBox(height: 14),
            LoadingShimmerList(itemHeight: 120, itemCount: 2),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => ref.read(guruDashboardControllerProvider.notifier).loadDashboardData(),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 1. Page Header (Persis Desktop)
            _buildDesktopPageHeader(user, isHomeroom, homeroomClasses, data.todayFormatted),
            const SizedBox(height: 14),

            // 2. Alert Peringatan Jurnal Belum Terisi (Jika ada jadwal tertunda)
            if (data.pendingJournalAlerts.isNotEmpty) ...[
              _buildJournalAlertBanner(data.pendingJournalAlerts),
              const SizedBox(height: 14),
            ],

            // 3. Banner Tugas Tambahan Wali Kelas (Jika Guru adalah Wali Kelas)
            if (isHomeroom) ...[
              _buildHomeroomBanner(homeroomClasses),
              const SizedBox(height: 14),
            ],

            // 4. Stat Cards (Total Jadwal, Hari Ini, Jurnal Terisi)
            _buildStatCardsRow(data),
            const SizedBox(height: 14),

            // 5. Siklus Pembelajaran Aktif
            _buildActiveCycleCard(data.activeCycle),
            const SizedBox(height: 14),

            // 6. Navigasi Mengajar (Akses Menu Cepat Guru)
            _buildQuickNavCard(),
            const SizedBox(height: 14),

            // 7. Jadwal Mengajar Hari Ini
            _buildTodaySchedulesCard(data),
            const SizedBox(height: 14),

            // 8. Riwayat Jurnal Terakhir Saya
            _buildRecentJournalsCard(data.recentJournals),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  /// 1. Page Header: Persis desktop .page-header
  Widget _buildDesktopPageHeader(user, bool isHomeroom, String homeroomClasses, String todayFormatted) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              AppAvatar(
                imageUrl: user?.avatarUrl,
                name: user?.name ?? 'Guru',
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
                      'Dashboard Guru Pengajar',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Selamat datang, ${user?.name ?? 'Guru'} • ${todayFormatted.isNotEmpty ? todayFormatted : 'Hari Ini'}',
                      style: const TextStyle(
                        fontSize: 12,
                        color: Color(0xFF475569),
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          const SizedBox(height: 12),
          // Action Buttons: "Jadwal Saya" & "Tulis Jurnal" persis desktop
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => setState(() => _currentIndex = 1),
                  icon: const Icon(Icons.calendar_today_outlined, size: 14, color: Color(0xFF334155)),
                  label: const Text(
                    'Jadwal Saya',
                    style: TextStyle(color: Color(0xFF334155), fontSize: 12, fontWeight: FontWeight.w600),
                  ),
                  style: OutlinedButton.styleFrom(
                    backgroundColor: Colors.white,
                    side: const BorderSide(color: Color(0xFFCBD5E1)),
                    padding: const EdgeInsets.symmetric(vertical: 9),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => context.push(RouteNames.guruJournalCreate),
                  icon: const Icon(Icons.edit_note_rounded, size: 16, color: Colors.white),
                  label: const Text(
                    'Tulis Jurnal',
                    style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4F46E5),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(vertical: 9),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// 2. Alert Peringatan Jurnal Belum Terisi: Persis desktop warning banner
  Widget _buildJournalAlertBanner(List<ScheduleModel> alerts) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFFECACA)),
        boxShadow: [
          BoxShadow(
            color: Colors.red.withValues(alpha: 0.03),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(14),
        child: Container(
          decoration: const BoxDecoration(
            border: Border(left: BorderSide(color: Color(0xFFEF4444), width: 4)),
          ),
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  const Expanded(
                    child: Text(
                      'Perhatian: Jadwal Memerlukan Pengisian Jurnal',
                      style: TextStyle(
                        color: Color(0xFF991B1B),
                        fontWeight: FontWeight.w700,
                        fontSize: 13.5,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(9999),
                      border: Border.all(color: const Color(0xFFFECACA)),
                    ),
                    child: Text(
                      '${alerts.length} Jadwal Lewat Waktu',
                      style: const TextStyle(
                        color: Color(0xFF991B1B),
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              const Text(
                'Waktu pembelajaran jadwal di bawah ini telah usai lebih dari 30 menit. Mohon segera input jurnal materi dan aktivitas siswa:',
                style: TextStyle(color: Color(0xFF475569), fontSize: 11.5, height: 1.4),
              ),
              const SizedBox(height: 12),
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: alerts.length,
                separatorBuilder: (_, __) => const SizedBox(height: 8),
                itemBuilder: (context, index) {
                  final alert = alerts[index];
                  return Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFFECACA)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${alert.mataPelajaran} • Kelas ${alert.kelas}',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          'Ruang: ${alert.ruang} • Pukul: ${alert.timeRangeText}',
                          style: const TextStyle(fontSize: 11.5, color: Color(0xFF475569)),
                        ),
                        const SizedBox(height: 8),
                        Align(
                          alignment: Alignment.centerRight,
                          child: ElevatedButton(
                            onPressed: () {
                              context.push(RouteNames.guruJournalCreate, extra: alert.id);
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF4F46E5),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              minimumSize: Size.zero,
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              elevation: 0,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                            ),
                            child: const Text(
                              'Isi Jurnal Sekarang',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                            ),
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
      ),
    );
  }

  /// 3. Banner Tugas Tambahan Wali Kelas: Persis desktop .card wali kelas
  Widget _buildHomeroomBanner(String homeroomClasses) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFC7D2FE)),
        boxShadow: [
          BoxShadow(
            color: Colors.indigo.withValues(alpha: 0.03),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(14),
        child: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFFEEF2FF), Color(0xFFF8FAFC)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            border: Border(left: BorderSide(color: Color(0xFF4F46E5), width: 4)),
          ),
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEEF2FF),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFC7D2FE)),
                    ),
                    child: const Text(
                      'Tugas Tambahan',
                      style: TextStyle(
                        color: Color(0xFF4F46E5),
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Wali Kelas $homeroomClasses',
                      style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 14.5,
                        color: Color(0xFF0F172A),
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              const Text(
                'Kelola perizinan surat dokter/orang tua dan pantau rekapitulasi kehadiran siswa binaan Anda.',
                style: TextStyle(fontSize: 12, color: Color(0xFF475569)),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => context.push(RouteNames.guruHomeroomAttendance),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4F46E5),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: const Text(
                        'Rekap Presensi Kelas',
                        style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => context.push(RouteNames.guruHomeroomPermissions),
                      style: OutlinedButton.styleFrom(
                        backgroundColor: Colors.white,
                        side: const BorderSide(color: Color(0xFFCBD5E1)),
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: const Text(
                        'Izin Siswa Kelas',
                        style: TextStyle(fontSize: 11.5, color: Color(0xFF334155), fontWeight: FontWeight.w600),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// 4. Stat Cards (Row of 3 cards persis desktop .stat-card)
  Widget _buildStatCardsRow(GuruDashboardData data) {
    return Row(
      children: [
        _buildStatCard(
          label: 'TOTAL JADWAL',
          value: '${data.totalSchedules}',
          stripeColor: const Color(0xFF4F46E5),
        ),
        const SizedBox(width: 8),
        _buildStatCard(
          label: 'HARI INI (${data.todayName.toUpperCase()})',
          value: '${data.todaySchedules.length}',
          stripeColor: const Color(0xFF0284C7),
        ),
        const SizedBox(width: 8),
        _buildStatCard(
          label: 'JURNAL TERISI',
          value: '${data.totalJournals}',
          stripeColor: const Color(0xFF10B981),
        ),
      ],
    );
  }

  Widget _buildStatCard({
    required String label,
    required String value,
    required Color stripeColor,
  }) {
    return Expanded(
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 4,
              offset: const Offset(0, 1),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(height: 3, color: stripeColor),
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                child: Column(
                  children: [
                    Text(
                      value,
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF0F172A),
                        height: 1.1,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      label,
                      style: const TextStyle(
                        fontSize: 9,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF64748B),
                        letterSpacing: 0.3,
                      ),
                      textAlign: TextAlign.center,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// 5. Siklus Pembelajaran Aktif: Persis desktop .card siklus aktif
  Widget _buildActiveCycleCard(BlockCycleModel? cycle) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Siklus Pembelajaran Aktif',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF0F172A),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: cycle != null ? const Color(0xFFECFDF5) : const Color(0xFFFFFBEB),
                  borderRadius: BorderRadius.circular(9999),
                  border: Border.all(
                    color: cycle != null ? const Color(0xFFA7F3D0) : const Color(0xFFFDE68A),
                  ),
                ),
                child: Text(
                  cycle != null ? 'Berjalan' : 'Belum Ada',
                  style: TextStyle(
                    color: cycle != null ? const Color(0xFF065F46) : const Color(0xFF92400E),
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            cycle?.nama ?? 'Belum ada siklus aktif',
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: Color(0xFF4F46E5),
            ),
          ),
          const SizedBox(height: 4),
          if (cycle?.mulai != null && cycle?.selesai != null)
            Text(
              'Periode Pelaksanaan: ${cycle!.mulai} s/d ${cycle.selesai}',
              style: const TextStyle(fontSize: 12.5, color: Color(0xFF475569)),
            )
          else
            const Text(
              'Periode Pelaksanaan: 2 Minggu per Siklus Blok',
              style: TextStyle(fontSize: 12.5, color: Color(0xFF475569)),
            ),
          const SizedBox(height: 8),
          const Text(
            'Pastikan seluruh kegiatan belajar dan presensi siswa dicatat sesuai rentang siklus yang berlaku.',
            style: TextStyle(fontSize: 11.5, color: Color(0xFF94A3B8), height: 1.4),
          ),
        ],
      ),
    );
  }

  /// 6. Navigasi Mengajar (Akses Menu Cepat Guru: Persis desktop .card navigasi mengajar)
  Widget _buildQuickNavCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Navigasi Mengajar',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: Color(0xFF0F172A),
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Akses cepat fitur pendukung kegiatan pembelajaran kelas Anda:',
            style: TextStyle(fontSize: 12, color: Color(0xFF475569)),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              OutlinedButton(
                onPressed: () => setState(() => _currentIndex = 1),
                style: OutlinedButton.styleFrom(
                  backgroundColor: Colors.white,
                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                child: const Text(
                  'Jadwal Mengajar Lengkap',
                  style: TextStyle(color: Color(0xFF334155), fontSize: 11.5, fontWeight: FontWeight.w600),
                ),
              ),
              OutlinedButton(
                onPressed: () => setState(() => _currentIndex = 2),
                style: OutlinedButton.styleFrom(
                  backgroundColor: Colors.white,
                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                child: const Text(
                  'Daftar Riwayat Jurnal',
                  style: TextStyle(color: Color(0xFF334155), fontSize: 11.5, fontWeight: FontWeight.w600),
                ),
              ),
              ElevatedButton(
                onPressed: () => context.push(RouteNames.guruJournalCreate),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4F46E5),
                  foregroundColor: Colors.white,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                child: const Text(
                  'Input Jurnal Sekarang',
                  style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// 7. Jadwal Mengajar Hari Ini: Persis desktop .card jadwal mengajar
  /// Catatan User: Ada matapelajaran, kelas, ruangan, hari, dan jam pelajaran; TIDAK ADA tombol isi jurnal di sini.
  Widget _buildTodaySchedulesCard(GuruDashboardData data) {
    final list = data.todaySchedules;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  'Jadwal Mengajar Hari Ini (${data.todayName})',
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF0F172A),
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFF0F9FF),
                  borderRadius: BorderRadius.circular(9999),
                  border: Border.all(color: const Color(0xFFBAE6FD)),
                ),
                child: Text(
                  '${list.length} Sesi Terjadwal',
                  style: const TextStyle(
                    color: Color(0xFF075985),
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (list.isEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                children: [
                  Text(
                    'Tidak ada jadwal mengajar pada hari ${data.todayName}.',
                    style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF334155), fontSize: 12.5),
                  ),
                  const SizedBox(height: 2),
                  const Text(
                    'Gunakan waktu luang untuk evaluasi materi atau meninjau jurnal yang telah diselesaikan.',
                    style: TextStyle(color: Color(0xFF64748B), fontSize: 11),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (context, index) {
                final item = list[index];

                return Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Baris 1: Mata Pelajaran & Badge Kelas
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Text(
                              item.mataPelajaran,
                              style: const TextStyle(
                                fontWeight: FontWeight.w700,
                                fontSize: 13.5,
                                color: Color(0xFF0F172A),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF0F9FF),
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(color: const Color(0xFFBAE6FD)),
                            ),
                            child: Text(
                              item.kelas,
                              style: const TextStyle(
                                color: Color(0xFF0369A1),
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      // Baris 2: Ruangan, Hari & Jam Pelajaran
                      Row(
                        children: [
                          const Icon(Icons.meeting_room_outlined, size: 13, color: Color(0xFF64748B)),
                          const SizedBox(width: 4),
                          Text(
                            'Ruang ${item.ruang}',
                            style: const TextStyle(fontSize: 11.5, color: Color(0xFF475569)),
                          ),
                          const SizedBox(width: 10),
                          const Icon(Icons.calendar_today_outlined, size: 12, color: Color(0xFF64748B)),
                          const SizedBox(width: 4),
                          Text(
                            item.hari,
                            style: const TextStyle(fontSize: 11.5, color: Color(0xFF475569)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.access_time_rounded, size: 13, color: Color(0xFF4F46E5)),
                          const SizedBox(width: 4),
                          Text(
                            item.timeRangeText,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            '(${item.periodText})',
                            style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                          ),
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
        ],
      ),
    );
  }

  /// 8. Riwayat Jurnal Terakhir Saya: Persis desktop .card riwayat jurnal
  Widget _buildRecentJournalsCard(List<JournalHistoryItem> recentJournals) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Expanded(
                child: Text(
                  'Riwayat Jurnal Mengajar Saya',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF0F172A),
                  ),
                ),
              ),
              OutlinedButton(
                onPressed: () => setState(() => _currentIndex = 2),
                style: OutlinedButton.styleFrom(
                  backgroundColor: Colors.white,
                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  minimumSize: Size.zero,
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                ),
                child: const Text(
                  'Lihat Semua',
                  style: TextStyle(color: Color(0xFF334155), fontSize: 11, fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (recentJournals.isEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: const Text(
                'Anda belum mengisi jurnal mengajar. Mulai buat jurnal setelah sesi pembelajaran selesai.',
                style: TextStyle(color: Color(0xFF64748B), fontSize: 11.5),
                textAlign: TextAlign.center,
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: recentJournals.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (context, index) {
                final journal = recentJournals[index];

                return InkWell(
                  onTap: () => context.push('${RouteNames.guruJournals}/${journal.id}'),
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              journal.tanggal,
                              style: const TextStyle(
                                fontSize: 11,
                                color: Color(0xFF64748B),
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEEF2FF),
                                borderRadius: BorderRadius.circular(4),
                                border: Border.all(color: const Color(0xFFC7D2FE)),
                              ),
                              child: Text(
                                journal.kelas,
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF4F46E5),
                                ),
                              ),
                            ),
                            const Spacer(),
                            if (journal.waktuSubmit != null && journal.waktuSubmit!.isNotEmpty)
                              Text(
                                '${journal.waktuSubmit} WIB',
                                style: const TextStyle(fontSize: 10.5, color: Color(0xFF64748B)),
                              ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          journal.mataPelajaran,
                          style: const TextStyle(
                            fontWeight: FontWeight.w700,
                            fontSize: 13,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Materi: ${journal.materi}',
                          style: const TextStyle(fontSize: 11.5, color: Color(0xFF475569)),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            OutlinedButton(
                              onPressed: () => context.push('${RouteNames.guruJournals}/${journal.id}'),
                              style: OutlinedButton.styleFrom(
                                backgroundColor: Colors.white,
                                side: const BorderSide(color: Color(0xFFCBD5E1)),
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                minimumSize: Size.zero,
                                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                              ),
                              child: const Text(
                                'Lihat Detail',
                                style: TextStyle(color: Color(0xFF334155), fontSize: 10.5, fontWeight: FontWeight.w600),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
        ],
      ),
    );
  }

  /// Profil Guru Tab: Persis desktop card styling
  Widget _buildProfileTab(user, bool isHomeroom, String homeroomClasses) {
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
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.02),
                  blurRadius: 4,
                  offset: const Offset(0, 1),
                ),
              ],
            ),
            child: Column(
              children: [
                Center(
                  child: AppAvatar(
                    imageUrl: user?.avatarUrl,
                    name: user?.name ?? 'Guru',
                    radius: 42,
                    backgroundColor: const Color(0xFFEEF2FF),
                    foregroundColor: const Color(0xFF4F46E5),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  user?.name ?? 'Guru Pengajar',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF0F172A),
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 3),
                Text(
                  user?.email ?? '',
                  style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
                ),
                const SizedBox(height: 10),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEEF2FF),
                    borderRadius: BorderRadius.circular(9999),
                    border: Border.all(color: const Color(0xFFC7D2FE)),
                  ),
                  child: Text(
                    isHomeroom ? 'WALI KELAS • $homeroomClasses' : 'GURU PENGAJAR',
                    style: const TextStyle(
                      color: Color(0xFF4F46E5),
                      fontWeight: FontWeight.w700,
                      fontSize: 11,
                    ),
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
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.02),
                  blurRadius: 4,
                  offset: const Offset(0, 1),
                ),
              ],
            ),
            child: Column(
              children: [
                _buildProfileItem(Icons.badge_outlined, 'Peran / Role', user?.role.toUpperCase() ?? 'GURU'),
                const Divider(height: 20, color: Color(0xFFF1F5F9)),
                _buildProfileItem(Icons.email_outlined, 'Alamat Email', user?.email ?? '-'),
                const Divider(height: 20, color: Color(0xFFF1F5F9)),
                _buildProfileItem(
                  Icons.class_outlined,
                  'Kelas Binaan',
                  isHomeroom ? homeroomClasses : 'Bukan Wali Kelas',
                ),
                const Divider(height: 20, color: Color(0xFFF1F5F9)),
                _buildProfileItem(Icons.verified_outlined, 'Status Akun', 'Aktif & Terverifikasi'),
              ],
            ),
          ),
          const SizedBox(height: 16),

          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _confirmLogout,
              icon: const Icon(Icons.logout_rounded, size: 18, color: Color(0xFFEF4444)),
              label: const Text(
                'Keluar dari Akun',
                style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFFEF4444), fontSize: 13),
              ),
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
            'School Class Progress • Versi Mobile\nSMK Negeri 1',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8), height: 1.5),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileItem(IconData icon, String label, String value) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(7),
          decoration: BoxDecoration(
            color: const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 17, color: const Color(0xFF475569)),
        ),
        const SizedBox(width: 12),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
            const SizedBox(height: 2),
            Text(
              value,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Color(0xFF0F172A),
              ),
            ),
          ],
        ),
      ],
    );
  }
}
