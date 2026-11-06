import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/widgets/empty_state_view.dart';
import '../../../../core/widgets/error_state_view.dart';
import '../../../../core/widgets/loading_shimmer.dart';
import '../../domain/models/homeroom_models.dart';
import '../controllers/homeroom_attendance_controller.dart';

class HomeroomAttendanceScreen extends ConsumerStatefulWidget {
  const HomeroomAttendanceScreen({super.key});

  @override
  ConsumerState<HomeroomAttendanceScreen> createState() => _HomeroomAttendanceScreenState();
}

class _HomeroomAttendanceScreenState extends ConsumerState<HomeroomAttendanceScreen> {
  final _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(homeroomAttendanceControllerProvider);
    final summary = state.data.summary;
    final students = state.filteredStudents;

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
          'Rekap Presensi Kelas Binaan',
          style: TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF0F172A),
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(homeroomAttendanceControllerProvider.notifier).loadAttendanceRecap(),
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            // 1. Header & 4 Stat Cards
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(14, 14, 14, 0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // 4 Stat Cards: Hadir, Sakit, Izin, Alpa
                    Row(
                      children: [
                        _buildStatCard('HADIR', '${summary.hadir}', const Color(0xFF10B981)),
                        const SizedBox(width: 6),
                        _buildStatCard('SAKIT', '${summary.sakit}', const Color(0xFFF59E0B)),
                        const SizedBox(width: 6),
                        _buildStatCard('IZIN', '${summary.izin}', const Color(0xFF06B6D4)),
                        const SizedBox(width: 6),
                        _buildStatCard('ALPA', '${summary.alpa}', const Color(0xFFEF4444)),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Filter Periode Waktu
                    SizedBox(
                      height: 38,
                      child: ListView(
                        scrollDirection: Axis.horizontal,
                        children: [
                          _buildRangeTab('all', 'Semua Waktu', state.selectedRange),
                          const SizedBox(width: 8),
                          _buildRangeTab('today', 'Hari Ini', state.selectedRange),
                          const SizedBox(width: 8),
                          _buildRangeTab('7_days', '1 Minggu Terakhir', state.selectedRange),
                          const SizedBox(width: 8),
                          _buildRangeTab('30_days', '1 Bulan Terakhir', state.selectedRange),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Search Field
                    Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFCBD5E1)),
                      ),
                      child: TextField(
                        controller: _searchController,
                        onChanged: (val) {
                          ref.read(homeroomAttendanceControllerProvider.notifier).setSearchQuery(val);
                        },
                        style: const TextStyle(fontSize: 13),
                        decoration: InputDecoration(
                          hintText: 'Cari nama atau NIS siswa...',
                          hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8)),
                          prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF64748B)),
                          suffixIcon: _searchController.text.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.clear_rounded, size: 18),
                                  onPressed: () {
                                    _searchController.clear();
                                    ref.read(homeroomAttendanceControllerProvider.notifier).setSearchQuery('');
                                  },
                                )
                              : null,
                          border: InputBorder.none,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Judul Daftar Siswa
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Daftar Kehadiran Siswa',
                          style: TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                        Text(
                          '${students.length} Siswa',
                          style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                  ],
                ),
              ),
            ),

            // 2. Daftar Siswa
            if (state.isLoading)
              const SliverFillRemaining(
                child: Center(child: LoadingShimmerList(itemHeight: 110, itemCount: 5)),
              )
            else if (state.errorMessage != null)
              SliverFillRemaining(
                child: ErrorStateView(
                  message: state.errorMessage!,
                  onRetry: () => ref.read(homeroomAttendanceControllerProvider.notifier).loadAttendanceRecap(),
                ),
              )
            else if (students.isEmpty)
              SliverFillRemaining(
                child: EmptyStateView(
                  icon: Icons.person_search_outlined,
                  title: 'Siswa Tidak Ditemukan',
                  message: _searchController.text.isNotEmpty
                      ? 'Tidak ada siswa yang cocok dengan kata kunci "${_searchController.text}".'
                      : 'Belum ada data presensi siswa pada periode ini.',
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(14, 0, 14, 24),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final item = students[index];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: _buildStudentCard(item),
                      );
                    },
                    childCount: students.length,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Expanded(
      child: Container(
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
          borderRadius: BorderRadius.circular(10),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(height: 3, color: color),
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
                child: Column(
                  children: [
                    Text(
                      value,
                      style: TextStyle(
                        fontSize: 19,
                        fontWeight: FontWeight.w800,
                        color: color,
                        height: 1.1,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      label,
                      style: const TextStyle(
                        fontSize: 8.5,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF64748B),
                        letterSpacing: 0.2,
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

  Widget _buildRangeTab(String rangeKey, String label, String activeRange) {
    final isSelected = activeRange == rangeKey;

    return InkWell(
      onTap: () => ref.read(homeroomAttendanceControllerProvider.notifier).setRange(rangeKey),
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
          style: TextStyle(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
            color: isSelected ? Colors.white : const Color(0xFF475569),
          ),
        ),
      ),
    );
  }

  Widget _buildStudentCard(HomeroomStudentAttendance item) {
    Color pctColor = const Color(0xFF10B981);
    if (item.percentage < 75) {
      pctColor = const Color(0xFFEF4444);
    } else if (item.percentage < 90) {
      pctColor = const Color(0xFFF59E0B);
    }

    return Container(
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
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Baris 1: Nama Siswa & Persentase
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.name,
                      style: const TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'NIS: ${item.nis} • Kelas ${item.className}',
                      style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: pctColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: pctColor.withValues(alpha: 0.3)),
                ),
                child: Text(
                  '${item.percentage}%',
                  style: TextStyle(
                    color: pctColor,
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Baris 2: Breakdown Jumlah Kehadiran (H, S, I, A)
          Row(
            children: [
              _buildCountBadge('H', '${item.hadir}', const Color(0xFF10B981), const Color(0xFFECFDF5)),
              const SizedBox(width: 6),
              _buildCountBadge('S', '${item.sakit}', const Color(0xFFD97706), const Color(0xFFFFFBEB)),
              const SizedBox(width: 6),
              _buildCountBadge('I', '${item.izin}', const Color(0xFF0284C7), const Color(0xFFF0F9FF)),
              const SizedBox(width: 6),
              _buildCountBadge('A', '${item.alpa}', const Color(0xFFDC2626), const Color(0xFFFEF2F2)),
              const Spacer(),
              Text(
                'Total: ${item.total} sesi',
                style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
              ),
            ],
          ),
          const SizedBox(height: 8),

          // Baris 3: Progress Bar Kehadiran
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: (item.percentage / 100).clamp(0.0, 1.0),
              backgroundColor: const Color(0xFFF1F5F9),
              valueColor: AlwaysStoppedAnimation<Color>(pctColor),
              minHeight: 5,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCountBadge(String prefix, String count, Color textColor, Color bgColor) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(5),
      ),
      child: Text(
        '$prefix: $count',
        style: TextStyle(
          color: textColor,
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}

