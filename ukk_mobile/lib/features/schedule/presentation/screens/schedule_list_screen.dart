import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/widgets/app_avatar.dart';
import '../../../../core/widgets/empty_state_view.dart';
import '../../../../core/widgets/error_state_view.dart';
import '../../../../core/widgets/loading_shimmer.dart';
import '../../../auth/presentation/controllers/auth_controller.dart';
import '../../domain/models/schedule_model.dart';
import '../controllers/schedule_controller.dart';

class ScheduleListScreen extends ConsumerWidget {
  final bool isEmbedded;

  const ScheduleListScreen({super.key, this.isEmbedded = false});

  static const List<String> days = ['Semua', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;
    final state = ref.watch(scheduleControllerProvider);

    final content = RefreshIndicator(
      onRefresh: () => ref.read(scheduleControllerProvider.notifier).loadSchedules(),
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          // Banner Siklus Aktif
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: _buildCycleCard(state.activeCycle),
            ),
          ),

          // Tab Filter Hari
          SliverToBoxAdapter(
            child: SizedBox(
              height: 48,
              child: ListView.separated(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                scrollDirection: Axis.horizontal,
                itemCount: days.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (context, index) {
                  final day = days[index];
                  final isSelected = state.selectedDay == day;
                  return FilterChip(
                    label: Text(day),
                    selected: isSelected,
                    showCheckmark: false,
                    labelStyle: TextStyle(
                      fontSize: 13,
                      fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                      color: isSelected ? Colors.white : AppColors.textPrimaryLight,
                    ),
                    selectedColor: AppColors.primary,
                    backgroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                      side: BorderSide(
                        color: isSelected ? AppColors.primary : Colors.grey.shade300,
                      ),
                    ),
                    onSelected: (_) {
                      ref.read(scheduleControllerProvider.notifier).setSelectedDay(day);
                    },
                  );
                },
              ),
            ),
          ),

          // Konten Jadwal
          if (state.isLoading)
            const SliverFillRemaining(
              child: Center(child: LoadingShimmerList(itemHeight: 120, itemCount: 4)),
            )
          else if (state.errorMessage != null)
            SliverFillRemaining(
              child: ErrorStateView(
                message: state.errorMessage!,
                onRetry: () => ref.read(scheduleControllerProvider.notifier).loadSchedules(),
              ),
            )
          else if (state.filteredSchedules.isEmpty)
            SliverFillRemaining(
              child: EmptyStateView(
                icon: Icons.calendar_today_outlined,
                title: 'Tidak Ada Jadwal',
                message: state.selectedDay == 'Semua'
                    ? 'Tidak ada jadwal mengajar pada siklus ini.'
                    : 'Tidak ada jadwal mengajar untuk hari ${state.selectedDay}.',
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final item = state.filteredSchedules[index];
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _buildScheduleCard(context, item),
                    );
                  },
                  childCount: state.filteredSchedules.length,
                ),
              ),
            ),
        ],
      ),
    );

    if (isEmbedded) {
      return content;
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Jadwal Mengajar KBM'),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 16),
            child: AppAvatar(
              imageUrl: user?.avatarUrl,
              name: user?.name ?? 'Guru',
              radius: 18,
            ),
          ),
        ],
      ),
      body: content,
    );
  }

  Widget _buildCycleCard(BlockCycleModel? cycle) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.2),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.sync_alt_rounded, color: Colors.indigoAccent, size: 24),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Siklus Blok Aktif',
                  style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.w500),
                ),
                const SizedBox(height: 2),
                Text(
                  cycle?.nama ?? 'Siklus Pembelajaran',
                  style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                ),
                if (cycle?.mulai != null && cycle?.selesai != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    'Periode: ${cycle!.mulai} s/d ${cycle.selesai}',
                    style: const TextStyle(color: Colors.white60, fontSize: 11.5),
                  ),
                ],
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: AppColors.hadir.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.hadir.withValues(alpha: 0.4)),
            ),
            child: const Text(
              'AKTIF',
              style: TextStyle(color: AppColors.hadir, fontSize: 11, fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildScheduleCard(BuildContext context, ScheduleModel item) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Baris Atas: Mata Pelajaran & Badge Hari
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Text(
                    item.mataPelajaran,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimaryLight,
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    item.hari,
                    style: const TextStyle(
                      color: AppColors.primary,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),

            // Baris Info: Kelas & Ruangan
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.school_outlined, size: 14, color: Color(0xFF475569)),
                      const SizedBox(width: 4),
                      Text(
                        'Kelas ${item.kelas}',
                        style: const TextStyle(
                          color: Color(0xFF334155),
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.meeting_room_outlined, size: 14, color: Color(0xFF475569)),
                      const SizedBox(width: 4),
                      Text(
                        'Ruang ${item.ruang}',
                        style: const TextStyle(
                          color: Color(0xFF334155),
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 12),
              child: Divider(height: 1, color: Color(0xFFF1F5F9)),
            ),

            // Baris Bawah: Jam Pelajaran & Jam Pelajaran Ke-
            Row(
              children: [
                const Icon(Icons.access_time_rounded, size: 16, color: AppColors.primary),
                const SizedBox(width: 6),
                Text(
                  item.timeRangeText,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimaryLight,
                  ),
                ),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEEF2FF),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    item.periodText,
                    style: const TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      color: AppColors.primary,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

