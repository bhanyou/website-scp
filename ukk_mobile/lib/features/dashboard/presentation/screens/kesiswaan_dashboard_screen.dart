import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/widgets/app_avatar.dart';
import '../../../auth/presentation/controllers/auth_controller.dart';
import '../../../homeroom/domain/models/homeroom_models.dart';
import '../../../kesiswaan/presentation/controllers/kesiswaan_attendance_controller.dart';
import '../../../kesiswaan/presentation/controllers/kesiswaan_dashboard_controller.dart';
import '../../../kesiswaan/presentation/controllers/kesiswaan_permissions_controller.dart';

class KesiswaanDashboardScreen extends ConsumerStatefulWidget {
  const KesiswaanDashboardScreen({super.key});

  @override
  ConsumerState<KesiswaanDashboardScreen> createState() => _KesiswaanDashboardScreenState();
}

class _KesiswaanDashboardScreenState extends ConsumerState<KesiswaanDashboardScreen> {
  int _currentIndex = 0;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: IndexedStack(
        index: _currentIndex,
        children: [
          _buildBerandaTab(),
          _buildRekapPresensiTab(),
          _buildMonitoringIzinTab(),
          _buildProfilTab(),
        ],
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: Color(0xFFE2E8F0), width: 1)),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) {
            setState(() {
              _currentIndex = index;
            });
          },
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          selectedItemColor: const Color(0xFF4F46E5),
          unselectedItemColor: const Color(0xFF94A3B8),
          selectedLabelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 11),
          unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w500, fontSize: 11),
          elevation: 0,
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.home_outlined),
              activeIcon: Icon(Icons.home_rounded),
              label: 'Beranda',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.fact_check_outlined),
              activeIcon: Icon(Icons.fact_check_rounded),
              label: 'Presensi',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.mark_email_unread_outlined),
              activeIcon: Icon(Icons.mark_email_read_rounded),
              label: 'Izin Siswa',
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

  // ===========================================================================
  // TAB 0: BERANDA (IDENTIK DESKTOP KESISWAAN)
  // ===========================================================================
  Widget _buildBerandaTab() {
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;
    final state = ref.watch(kesiswaanDashboardControllerProvider);
    final data = state.data;

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
        title: const Text(
          'Dashboard Staff Kesiswaan',
          style: TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF0F172A),
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'Logout',
            icon: const Icon(Icons.logout_rounded, color: Color(0xFFEF4444), size: 20),
            onPressed: _showLogoutConfirmDialog,
          ),
        ],
      ),
      body: state.isLoading && data == null
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () async {
                await ref.read(kesiswaanDashboardControllerProvider.notifier).loadDashboard();
              },
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // --- PAGE HEADER DESKTOP ---
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
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
                              AppAvatar(
                                imageUrl: user?.avatarUrl,
                                name: user?.name ?? 'Kesiswaan',
                                radius: 22,
                                backgroundColor: const Color(0xFFEEF2FF),
                                foregroundColor: const Color(0xFF4F46E5),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      user?.name ?? 'Staff Kesiswaan',
                                      style: const TextStyle(
                                        fontSize: 15,
                                        fontWeight: FontWeight.w700,
                                        color: Color(0xFF0F172A),
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      '${data?.todayName ?? "Hari Ini"}, ${data?.todayFormatted ?? ""}',
                                      style: const TextStyle(
                                        fontSize: 12,
                                        color: Color(0xFF64748B),
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFEEF2FF),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: const Color(0xFFC7D2FE)),
                                ),
                                child: const Text(
                                  'KESISWAAN',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF4F46E5),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 14),
                          Row(
                            children: [
                              Expanded(
                                child: OutlinedButton(
                                  onPressed: () {
                                    setState(() {
                                      _currentIndex = 1; // Tab Presensi
                                    });
                                  },
                                  style: OutlinedButton.styleFrom(
                                    side: const BorderSide(color: Color(0xFFCBD5E1)),
                                    padding: const EdgeInsets.symmetric(vertical: 9),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                  ),
                                  child: const Text(
                                    'Rekap Presensi',
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      color: Color(0xFF334155),
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: ElevatedButton(
                                  onPressed: () {
                                    setState(() {
                                      _currentIndex = 2; // Tab Izin
                                    });
                                  },
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF4F46E5),
                                    foregroundColor: Colors.white,
                                    elevation: 0,
                                    padding: const EdgeInsets.symmetric(vertical: 9),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      const Text(
                                        'Monitoring Izin',
                                        style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold),
                                      ),
                                      if ((data?.pendingPermissionsCount ?? 0) > 0) ...[
                                        const SizedBox(width: 6),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFFF59E0B),
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: Text(
                                            '${data!.pendingPermissionsCount}',
                                            style: const TextStyle(
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.white,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // --- 4 STAT CARDS (IDENTIK DESKTOP .grid-4) ---
                    Row(
                      children: [
                        Expanded(
                          child: _buildStatCard(
                            value: '${data?.totalStudents ?? 0}',
                            label: 'Total Siswa',
                            valueColor: const Color(0xFF0F172A),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _buildStatCard(
                            value: '${data?.pendingPermissionsCount ?? 0}',
                            label: 'Menunggu Wali Kelas',
                            valueColor: (data?.pendingPermissionsCount ?? 0) > 0
                                ? const Color(0xFFD97706)
                                : const Color(0xFF0F172A),
                            borderColor: (data?.pendingPermissionsCount ?? 0) > 0
                                ? const Color(0xFFFDE68A)
                                : const Color(0xFFE2E8F0),
                            backgroundColor: (data?.pendingPermissionsCount ?? 0) > 0
                                ? const Color(0xFFFFFDF5)
                                : Colors.white,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Expanded(
                          child: _buildStatCard(
                            value: '${data?.approvedPermissionsCount ?? 0}',
                            label: 'Izin Disetujui',
                            valueColor: const Color(0xFF0F172A),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _buildStatCard(
                            value: '${data?.todayAttendancesCount ?? 0}',
                            label: 'Presensi Hari Ini',
                            valueColor: const Color(0xFF0F172A),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // --- CATATAN KEHADIRAN HARI INI (IDENTIK DESKTOP) ---
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Catatan Kehadiran Hari ${data?.todayName ?? "Ini"}',
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF0F172A),
                                ),
                              ),
                              InkWell(
                                onTap: () {
                                  setState(() {
                                    _currentIndex = 1;
                                  });
                                },
                                child: const Text(
                                  'Lihat Detail',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF4F46E5),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 14),

                          // 4 Kotak Warna Presensi
                          Row(
                            children: [
                              Expanded(
                                child: _buildAttendanceStatBox(
                                  count: data?.presentCount ?? 0,
                                  label: 'Hadir',
                                  textColor: const Color(0xFF047857),
                                  bgColor: const Color(0xFFECFDF5),
                                  borderColor: const Color(0xFFA7F3D0),
                                ),
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: _buildAttendanceStatBox(
                                  count: data?.sickCount ?? 0,
                                  label: 'Sakit',
                                  textColor: const Color(0xFFB45309),
                                  bgColor: const Color(0xFFFFFBEB),
                                  borderColor: const Color(0xFFFDE68A),
                                ),
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: _buildAttendanceStatBox(
                                  count: data?.permitCount ?? 0,
                                  label: 'Izin',
                                  textColor: const Color(0xFF1D4ED8),
                                  bgColor: const Color(0xFFEFF6FF),
                                  borderColor: const Color(0xFFBFDBFE),
                                ),
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: _buildAttendanceStatBox(
                                  count: data?.absentCount ?? 0,
                                  label: 'Alpa',
                                  textColor: const Color(0xFFDC2626),
                                  bgColor: const Color(0xFFFEF2F2),
                                  borderColor: const Color(0xFFFECACA),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          const Text(
                            'Data dihimpun otomatis dari pengisian presensi oleh guru mata pelajaran di setiap sesi kelas pada hari ini.',
                            style: TextStyle(fontSize: 11.5, color: Color(0xFF64748B), height: 1.35),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // --- PUSAT LAYANAN KESISWAAN ---
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Pusat Layanan Kesiswaan',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Tinjau pengajuan surat izin dan pantau kepatuhan tata tertib kehadiran siswa secara menyeluruh.',
                            style: TextStyle(fontSize: 11.5, color: Color(0xFF64748B), height: 1.35),
                          ),
                          const SizedBox(height: 12),
                          ListTile(
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                              side: const BorderSide(color: Color(0xFFC7D2FE)),
                            ),
                            tileColor: const Color(0xFFEEF2FF),
                            leading: const Icon(Icons.mark_email_read_rounded, color: Color(0xFF4F46E5)),
                            title: const Text(
                              'Verifikasi Surat Izin Masuk',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E1B4B)),
                            ),
                            trailing: (data?.pendingPermissionsCount ?? 0) > 0
                                ? Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(6),
                                      border: Border.all(color: const Color(0xFFFDE68A)),
                                    ),
                                    child: Text(
                                      '${data!.pendingPermissionsCount} Butuh Tindakan',
                                      style: const TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFFD97706),
                                      ),
                                    ),
                                  )
                                : Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(6),
                                      border: Border.all(color: const Color(0xFFA7F3D0)),
                                    ),
                                    child: const Text(
                                      'Bersih',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF047857),
                                      ),
                                    ),
                                  ),
                            onTap: () {
                              setState(() {
                                _currentIndex = 2; // Tab Izin
                              });
                            },
                          ),
                          const SizedBox(height: 8),
                          ListTile(
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                              side: const BorderSide(color: Color(0xFFE2E8F0)),
                            ),
                            tileColor: const Color(0xFFF8FAFC),
                            leading: const Icon(Icons.fact_check_rounded, color: Color(0xFF475569)),
                            title: const Text(
                              'Rekapitulasi Presensi Seluruh Kelas',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
                            ),
                            trailing: const Icon(Icons.chevron_right_rounded, color: Color(0xFF94A3B8)),
                            onTap: () {
                              setState(() {
                                _currentIndex = 1; // Tab Presensi
                              });
                            },
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // --- PENGANJUAN IZIN SISWA TERBARU (6 TERKINI) ---
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Row(
                                children: [
                                  Text(
                                    'Daftar Izin Terbaru',
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w700,
                                      color: Color(0xFF0F172A),
                                    ),
                                  ),
                                  SizedBox(width: 8),
                                  Badge(
                                    backgroundColor: Color(0xFFEEF2FF),
                                    label: Text(
                                      '6 Terkini',
                                      style: TextStyle(
                                        color: Color(0xFF4F46E5),
                                        fontWeight: FontWeight.bold,
                                        fontSize: 10,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              InkWell(
                                onTap: () {
                                  setState(() {
                                    _currentIndex = 2;
                                  });
                                },
                                child: const Text(
                                  'Buka Monitoring',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF4F46E5),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          if (data?.recentPermissions.isEmpty ?? true)
                            Container(
                              padding: const EdgeInsets.symmetric(vertical: 24),
                              alignment: Alignment.center,
                              child: const Column(
                                children: [
                                  Icon(Icons.inbox_outlined, size: 36, color: Color(0xFF94A3B8)),
                                  SizedBox(height: 6),
                                  Text(
                                    'Belum ada pengajuan izin terbaru.',
                                    style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                  ),
                                ],
                              ),
                            )
                          else
                            ListView.separated(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              itemCount: data!.recentPermissions.length,
                              separatorBuilder: (context, index) => const Divider(height: 16, color: Color(0xFFF1F5F9)),
                              itemBuilder: (context, index) {
                                final perm = data.recentPermissions[index];
                                return _buildPermissionCard(perm);
                              },
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

  // ===========================================================================
  // TAB 1: REKAP PRESENSI (IDENTIK DESKTOP, TANPA CETAK LAPORAN)
  // ===========================================================================
  Widget _buildRekapPresensiTab() {
    final state = ref.watch(kesiswaanAttendanceControllerProvider);
    final data = state.data;
    final students = state.filteredStudents;
    final summary = data?.summary ?? const HomeroomAttendanceSummary();
    final dashboardData = ref.watch(kesiswaanDashboardControllerProvider).data;

    // Nilai presensi sesuai desktop: gunakan summary atau data dashboard terkini
    final hadirVal = data != null ? summary.hadir : (dashboardData?.presentCount ?? 0);
    final sakitVal = data != null ? summary.sakit : (dashboardData?.sickCount ?? 0);
    final izinVal = data != null ? summary.izin : (dashboardData?.permitCount ?? 0);
    final alpaVal = data != null ? summary.alpa : (dashboardData?.absentCount ?? 0);

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
        title: const Text(
          'Rekap Presensi Siswa',
          style: TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF0F172A),
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF64748B)),
            tooltip: 'Segarkan',
            onPressed: () {
              ref.read(kesiswaanAttendanceControllerProvider.notifier).loadAttendanceRecap();
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await ref.read(kesiswaanAttendanceControllerProvider.notifier).loadAttendanceRecap();
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // --- 4 KARTU STATISTIK RINGKASAN PRESENSI (IDENTIK DESKTOP) ---
              Row(
                children: [
                  Expanded(
                    child: _buildAttendanceBorderCard(
                      value: '$hadirVal',
                      label: 'Total Hadir',
                      accentColor: const Color(0xFF10B981),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildAttendanceBorderCard(
                      value: '$sakitVal',
                      label: 'Total Sakit',
                      accentColor: const Color(0xFFF59E0B),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: _buildAttendanceBorderCard(
                      value: '$izinVal',
                      label: 'Total Izin',
                      accentColor: const Color(0xFF06B6D4),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildAttendanceBorderCard(
                      value: '$alpaVal',
                      label: 'Tanpa Ket. (Alpa)',
                      accentColor: const Color(0xFFEF4444),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // --- FILTER FORM CARD (KELAS & PERIODE & CARI) ---
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Filter Rekapitulasi',
                      style: TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Filter Kelas Dropdown
                    DropdownButtonFormField<int?>(
                      initialValue: state.selectedClassId,
                      isExpanded: true,
                      dropdownColor: Colors.white,
                      style: const TextStyle(fontSize: 13, color: Color(0xFF0F172A), fontWeight: FontWeight.w600),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Colors.white,
                        labelText: 'PILIH KELAS',
                        labelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                        ),
                      ),
                      items: [
                        const DropdownMenuItem<int?>(
                          value: null,
                          child: Text('Semua Kelas', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF0F172A))),
                        ),
                        if (data != null)
                          ...data.availableClasses.map(
                            (c) {
                              final id = c['id'] is int ? c['id'] as int : int.tryParse(c['id']?.toString() ?? '0');
                              final name = c['name']?.toString() ?? '-';
                              return DropdownMenuItem<int?>(
                                value: id,
                                child: Text(name, style: const TextStyle(fontSize: 13, color: Color(0xFF0F172A))),
                              );
                            },
                          ),
                      ],
                      onChanged: (val) {
                        ref.read(kesiswaanAttendanceControllerProvider.notifier).setSelectedClass(val);
                      },
                    ),
                    const SizedBox(height: 12),

                    // Filter Periode Dropdown
                    DropdownButtonFormField<String>(
                      initialValue: state.selectedRange,
                      isExpanded: true,
                      dropdownColor: Colors.white,
                      style: const TextStyle(fontSize: 13, color: Color(0xFF0F172A), fontWeight: FontWeight.w600),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Colors.white,
                        labelText: 'PERIODE WAKTU',
                        labelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                        ),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'all', child: Text('Semua Waktu', style: TextStyle(fontSize: 13, color: Color(0xFF0F172A)))),
                        DropdownMenuItem(value: 'today', child: Text('Hari Ini', style: TextStyle(fontSize: 13, color: Color(0xFF0F172A)))),
                        DropdownMenuItem(value: '7_days', child: Text('1 Minggu Terakhir', style: TextStyle(fontSize: 13, color: Color(0xFF0F172A)))),
                        DropdownMenuItem(value: 'this_week', child: Text('Minggu Ini (Sen-Min)', style: TextStyle(fontSize: 13, color: Color(0xFF0F172A)))),
                        DropdownMenuItem(value: '30_days', child: Text('1 Bulan Terakhir', style: TextStyle(fontSize: 13, color: Color(0xFF0F172A)))),
                        DropdownMenuItem(value: 'this_month', child: Text('Bulan Ini', style: TextStyle(fontSize: 13, color: Color(0xFF0F172A)))),
                      ],
                      onChanged: (val) {
                        if (val != null) {
                          ref.read(kesiswaanAttendanceControllerProvider.notifier).setRange(val);
                        }
                      },
                    ),
                    const SizedBox(height: 12),

                    // Search field
                    TextField(
                      style: const TextStyle(fontSize: 13, color: Color(0xFF0F172A)),
                      onChanged: (val) {
                        ref.read(kesiswaanAttendanceControllerProvider.notifier).setSearchQuery(val);
                      },
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Colors.white,
                        hintText: 'Cari nama atau NIS siswa...',
                        hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8)),
                        prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF94A3B8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // --- STATUS FILTER TABS ---
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _buildFilterChip(
                      label: 'Semua Siswa',
                      count: data?.students.length ?? 0,
                      isActive: state.statusTab == 'all',
                      onTap: () => ref.read(kesiswaanAttendanceControllerProvider.notifier).setStatusTab('all'),
                    ),
                    const SizedBox(width: 6),
                    _buildFilterChip(
                      label: 'Hadir',
                      count: data?.students.where((s) => s.hadir > 0).length ?? 0,
                      isActive: state.statusTab == 'hadir',
                      activeColor: const Color(0xFF10B981),
                      onTap: () => ref.read(kesiswaanAttendanceControllerProvider.notifier).setStatusTab('hadir'),
                    ),
                    const SizedBox(width: 6),
                    _buildFilterChip(
                      label: 'Sakit',
                      count: data?.students.where((s) => s.sakit > 0).length ?? 0,
                      isActive: state.statusTab == 'sakit',
                      activeColor: const Color(0xFFF59E0B),
                      onTap: () => ref.read(kesiswaanAttendanceControllerProvider.notifier).setStatusTab('sakit'),
                    ),
                    const SizedBox(width: 6),
                    _buildFilterChip(
                      label: 'Izin',
                      count: data?.students.where((s) => s.izin > 0).length ?? 0,
                      isActive: state.statusTab == 'izin',
                      activeColor: const Color(0xFF06B6D4),
                      onTap: () => ref.read(kesiswaanAttendanceControllerProvider.notifier).setStatusTab('izin'),
                    ),
                    const SizedBox(width: 6),
                    _buildFilterChip(
                      label: 'Alpa',
                      count: data?.students.where((s) => s.alpa > 0).length ?? 0,
                      isActive: state.statusTab == 'alpa',
                      activeColor: const Color(0xFFEF4444),
                      onTap: () => ref.read(kesiswaanAttendanceControllerProvider.notifier).setStatusTab('alpa'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),

              // --- LIST REKAP PER SISWA ---
              if (state.isLoading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(32),
                    child: CircularProgressIndicator(),
                  ),
                )
              else if (students.isEmpty)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(32),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  alignment: Alignment.center,
                  child: const Column(
                    children: [
                      Icon(Icons.search_off_rounded, size: 40, color: Color(0xFF94A3B8)),
                      SizedBox(height: 8),
                      Text(
                        'Tidak ada data siswa ditemukan.',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                      ),
                    ],
                  ),
                )
              else
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: students.length,
                  separatorBuilder: (context, index) => const SizedBox(height: 10),
                  itemBuilder: (context, index) {
                    final std = students[index];
                    return _buildStudentRecapCard(std);
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }

  // ===========================================================================
  // TAB 2: MONITORING IZIN SISWA (IDENTIK DESKTOP, LENGKAP APPROVE & REJECT)
  // ===========================================================================
  Widget _buildMonitoringIzinTab() {
    final state = ref.watch(kesiswaanPermissionsControllerProvider);
    final items = state.filteredPermissions;

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
        title: const Text(
          'Monitoring Izin Siswa',
          style: TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF0F172A),
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF64748B)),
            tooltip: 'Segarkan',
            onPressed: () {
              ref.read(kesiswaanPermissionsControllerProvider.notifier).loadPermissions();
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await ref.read(kesiswaanPermissionsControllerProvider.notifier).loadPermissions();
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Search & Filter Bar
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  children: [
                    TextField(
                      onChanged: (val) {
                        ref.read(kesiswaanPermissionsControllerProvider.notifier).setSearchQuery(val);
                      },
                      decoration: InputDecoration(
                        hintText: 'Cari nama siswa, NIS, atau kelas...',
                        hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8)),
                        prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF94A3B8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                        ),
                      ),
                    ),
                    const SizedBox(height: 10),
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildFilterChip(
                            label: 'Semua Status',
                            count: state.permissions.length,
                            isActive: state.statusFilter == 'all',
                            onTap: () => ref.read(kesiswaanPermissionsControllerProvider.notifier).setStatusFilter('all'),
                          ),
                          const SizedBox(width: 6),
                          _buildFilterChip(
                            label: 'Menunggu',
                            count: state.pendingCount,
                            isActive: state.statusFilter == 'Pending',
                            activeColor: const Color(0xFFF59E0B),
                            onTap: () => ref.read(kesiswaanPermissionsControllerProvider.notifier).setStatusFilter('Pending'),
                          ),
                          const SizedBox(width: 6),
                          _buildFilterChip(
                            label: 'Disetujui',
                            count: state.approvedCount,
                            isActive: state.statusFilter == 'Approved',
                            activeColor: const Color(0xFF10B981),
                            onTap: () => ref.read(kesiswaanPermissionsControllerProvider.notifier).setStatusFilter('Approved'),
                          ),
                          const SizedBox(width: 6),
                          _buildFilterChip(
                            label: 'Ditolak',
                            count: state.rejectedCount,
                            isActive: state.statusFilter == 'Rejected',
                            activeColor: const Color(0xFFEF4444),
                            onTap: () => ref.read(kesiswaanPermissionsControllerProvider.notifier).setStatusFilter('Rejected'),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),

              if (state.isLoading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(32),
                    child: CircularProgressIndicator(),
                  ),
                )
              else if (items.isEmpty)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(32),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  alignment: Alignment.center,
                  child: const Column(
                    children: [
                      Icon(Icons.inbox_outlined, size: 40, color: Color(0xFF94A3B8)),
                      SizedBox(height: 8),
                      Text(
                        'Tidak ada pengajuan izin yang sesuai.',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                      ),
                    ],
                  ),
                )
              else
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: items.length,
                  separatorBuilder: (context, index) => const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    final item = items[index];
                    return _buildFullPermissionCard(item);
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }

  // ===========================================================================
  // TAB 3: PROFIL SAYA (IDENTIK DESKTOP SCP)
  // ===========================================================================
  Widget _buildProfilTab() {
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;

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
        title: const Text(
          'Profil Saya',
          style: TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF0F172A),
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Avatar Header Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                children: [
                  AppAvatar(
                    imageUrl: user?.avatarUrl,
                    name: user?.name ?? 'Kesiswaan',
                    radius: 36,
                    backgroundColor: const Color(0xFFEEF2FF),
                    foregroundColor: const Color(0xFF4F46E5),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    user?.name ?? 'Staff Kesiswaan',
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    user?.email ?? '',
                    style: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                  ),
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEEF2FF),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFC7D2FE)),
                    ),
                    child: const Text(
                      'STAFF KESISWAAN & BK',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF4F46E5),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Detail Akun Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Informasi Akun',
                    style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700, color: Color(0xFF0F172A)),
                  ),
                  const SizedBox(height: 14),
                  _buildProfileRow('ID Pengguna', '#${user?.id ?? "-"}'),
                  const Divider(height: 18, color: Color(0xFFF1F5F9)),
                  _buildProfileRow('Nama Lengkap', user?.name ?? '-'),
                  const Divider(height: 18, color: Color(0xFFF1F5F9)),
                  _buildProfileRow('Email / Username', user?.email ?? '-'),
                  const Divider(height: 18, color: Color(0xFFF1F5F9)),
                  _buildProfileRow('Hak Akses', 'Kesiswaan & Bimbingan Konseling'),
                  const Divider(height: 18, color: Color(0xFFF1F5F9)),
                  _buildProfileRow('Status Akun', 'Aktif & Terverifikasi', isSuccess: true),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Tombol Logout
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _showLogoutConfirmDialog,
                icon: const Icon(Icons.logout_rounded, size: 18),
                label: const Text('Keluar dari Akun', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFEF4444),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 13),
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'UKK Project • School Class Progress v1.0.0',
              style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
            ),
          ],
        ),
      ),
    );
  }

  // ===========================================================================
  // HELPER WIDGETS
  // ===========================================================================

  Widget _buildStatCard({
    required String value,
    required String label,
    required Color valueColor,
    Color borderColor = const Color(0xFFE2E8F0),
    Color backgroundColor = Colors.white,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: borderColor),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            value,
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: valueColor),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }

  Widget _buildAttendanceStatBox({
    required int count,
    required String label,
    required Color textColor,
    required Color bgColor,
    required Color borderColor,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: borderColor),
      ),
      child: Column(
        children: [
          Text(
            '$count',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: textColor),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: textColor),
          ),
        ],
      ),
    );
  }

  Widget _buildAttendanceBorderCard({
    required String value,
    required String label,
    required Color accentColor,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
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
        borderRadius: BorderRadius.circular(9),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                width: 4.5,
                color: accentColor,
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        value,
                        style: TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          color: accentColor,
                          letterSpacing: -0.5,
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        label,
                        style: const TextStyle(
                          fontSize: 11.5,
                          color: Color(0xFF64748B),
                          fontWeight: FontWeight.w600,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFilterChip({
    required String label,
    required int count,
    required bool isActive,
    required VoidCallback onTap,
    Color activeColor = const Color(0xFF4F46E5),
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: isActive ? activeColor : const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isActive ? activeColor : const Color(0xFFCBD5E1),
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              label,
              style: TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.bold,
                color: isActive ? Colors.white : const Color(0xFF334155),
              ),
            ),
            const SizedBox(width: 5),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
              decoration: BoxDecoration(
                color: isActive ? Colors.white.withValues(alpha: 0.25) : const Color(0xFFE2E8F0),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                '$count',
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                  color: isActive ? Colors.white : const Color(0xFF475569),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStudentRecapCard(HomeroomStudentAttendance std) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      std.name,
                      style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'NIS: ${std.nis} • Kelas: ${std.className}',
                      style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: std.percentage >= 85 ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: std.percentage >= 85 ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA),
                  ),
                ),
                child: Text(
                  '${std.percentage}% Hadir',
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.bold,
                    color: std.percentage >= 85 ? const Color(0xFF047857) : const Color(0xFFDC2626),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              _buildMiniAttendancePill('H', '${std.hadir}', const Color(0xFF10B981), const Color(0xFFECFDF5)),
              const SizedBox(width: 6),
              _buildMiniAttendancePill('S', '${std.sakit}', const Color(0xFFF59E0B), const Color(0xFFFFFBEB)),
              const SizedBox(width: 6),
              _buildMiniAttendancePill('I', '${std.izin}', const Color(0xFF06B6D4), const Color(0xFFEFF6FF)),
              const SizedBox(width: 6),
              _buildMiniAttendancePill('A', '${std.alpa}', const Color(0xFFEF4444), const Color(0xFFFEF2F2)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildMiniAttendancePill(String code, String count, Color color, Color bgColor) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 4),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              '$code: ',
              style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: color),
            ),
            Text(
              count,
              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: color),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPermissionCard(HomeroomPermissionItem perm) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(8),
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
                  perm.siswa,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                ),
              ),
              _buildStatusBadge(perm.status),
            ],
          ),
          const SizedBox(height: 3),
          Text(
            '${perm.kelas} • ${perm.jenis} (${perm.durasiHari} Hari) • ${perm.rentangTanggal}',
            style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
          ),
          const SizedBox(height: 6),
          Text(
            perm.alasan,
            style: const TextStyle(fontSize: 11.5, color: Color(0xFF334155)),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildFullPermissionCard(HomeroomPermissionItem item) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: item.isPending ? const Color(0xFFFDE68A) : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.siswa,
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'NIS: ${item.nis} • Kelas: ${item.kelas}',
                      style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              _buildStatusBadge(item.status),
            ],
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(6),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Row(
              children: [
                Icon(
                  item.isSakit ? Icons.medical_services_outlined : Icons.calendar_today_rounded,
                  size: 14,
                  color: item.isSakit ? const Color(0xFFD97706) : const Color(0xFF2563EB),
                ),
                const SizedBox(width: 6),
                Text(
                  '${item.jenis} (${item.durasiHari} Hari): ',
                  style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
                ),
                Expanded(
                  child: Text(
                    item.rentangTanggal,
                    style: const TextStyle(fontSize: 11.5, color: Color(0xFF475569)),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Alasan:',
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
          ),
          const SizedBox(height: 2),
          Text(
            item.alasan,
            style: const TextStyle(fontSize: 12.5, color: Color(0xFF1E293B), height: 1.35),
          ),

          if (item.catatanVerifikasi != null && item.catatanVerifikasi!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF2F2),
                borderRadius: BorderRadius.circular(6),
                border: Border.all(color: const Color(0xFFFECACA)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.info_outline_rounded, size: 14, color: Color(0xFFDC2626)),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Catatan: ${item.catatanVerifikasi}',
                      style: const TextStyle(fontSize: 11.5, color: Color(0xFF991B1B)),
                    ),
                  ),
                ],
              ),
            ),
          ],

          const SizedBox(height: 12),
          Row(
            children: [
              if (item.dokumen != null && item.dokumen!.isNotEmpty)
                OutlinedButton.icon(
                  onPressed: () => _showDocumentDialog(item),
                  icon: const Icon(Icons.attachment_rounded, size: 14),
                  label: const Text('Lihat Surat', style: TextStyle(fontSize: 11.5)),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Color(0xFFCBD5E1)),
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                  ),
                ),
              const Spacer(),
              if (item.isPending) ...[
                OutlinedButton(
                  onPressed: () => _showRejectDialog(item.id),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Color(0xFFEF4444)),
                    foregroundColor: const Color(0xFFEF4444),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                  ),
                  child: const Text('✕ Tolak', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: () => _handleApprove(item.id),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                  ),
                  child: const Text('✓ Setujui', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color bg = const Color(0xFFFEF3C7);
    Color text = const Color(0xFFD97706);
    Color border = const Color(0xFFFDE68A);
    String label = 'Menunggu';

    if (status.toLowerCase() == 'approved' || status.toLowerCase() == 'disetujui') {
      bg = const Color(0xFFECFDF5);
      text = const Color(0xFF047857);
      border = const Color(0xFFA7F3D0);
      label = 'Disetujui';
    } else if (status.toLowerCase() == 'rejected' || status.toLowerCase() == 'ditolak') {
      bg = const Color(0xFFFEF2F2);
      text = const Color(0xFFDC2626);
      border = const Color(0xFFFECACA);
      label = 'Ditolak';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: border),
      ),
      child: Text(
        label,
        style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: text),
      ),
    );
  }

  Widget _buildProfileRow(String label, String value, {bool isSuccess = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 12.5,
            fontWeight: FontWeight.w700,
            color: isSuccess ? const Color(0xFF047857) : const Color(0xFF0F172A),
          ),
        ),
      ],
    );
  }

  // ===========================================================================
  // DIALOG ACTIONS
  // ===========================================================================

  void _showDocumentDialog(HomeroomPermissionItem item) {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Surat Bukti ${item.siswa}',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, size: 20),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: Image.network(
                  item.documentUrl,
                  height: 320,
                  width: double.infinity,
                  fit: BoxFit.contain,
                  errorBuilder: (context, error, stackTrace) => Container(
                    height: 160,
                    color: const Color(0xFFF1F5F9),
                    alignment: Alignment.center,
                    child: const Text('Dokumen tidak dapat dimuat / format PDF.', style: TextStyle(fontSize: 12)),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _handleApprove(int id) async {
    final success = await ref.read(kesiswaanPermissionsControllerProvider.notifier).approvePermission(id);
    if (mounted && success) {
      ref.read(kesiswaanDashboardControllerProvider.notifier).loadDashboard();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Permohonan izin berhasil disetujui.'),
          backgroundColor: Color(0xFF10B981),
        ),
      );
    }
  }

  void _showRejectDialog(int id) {
    final noteController = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        title: const Text('Tolak Permohonan Izin', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Masukkan alasan atau catatan penolakan untuk siswa / orang tua:',
              style: TextStyle(fontSize: 12.5, color: Color(0xFF475569)),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: noteController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Contoh: Surat dokter tidak terlampir jelas...',
                hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal', style: TextStyle(color: Color(0xFF64748B))),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final success = await ref
                  .read(kesiswaanPermissionsControllerProvider.notifier)
                  .rejectPermission(id, note: noteController.text.trim());
              if (mounted && success) {
                ref.read(kesiswaanDashboardControllerProvider.notifier).loadDashboard();
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Permohonan izin berhasil ditolak.'),
                    backgroundColor: Color(0xFFEF4444),
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              elevation: 0,
            ),
            child: const Text('Tolak Izin'),
          ),
        ],
      ),
    );
  }

  void _showLogoutConfirmDialog() {
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
}
