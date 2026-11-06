import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/widgets/app_avatar.dart';
import '../../../../core/widgets/error_state_view.dart';
import '../../domain/models/journal_models.dart';
import '../controllers/journal_controllers.dart';

class JournalDetailScreen extends ConsumerWidget {
  final int journalId;

  const JournalDetailScreen({super.key, required this.journalId});

  void _showImageDialog(BuildContext context, String imageUrl) {
    showDialog(
      context: context,
      builder: (ctx) {
        return Dialog(
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.all(12),
          child: Stack(
            alignment: Alignment.topRight,
            children: [
              InteractiveViewer(
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: CachedNetworkImage(
                    imageUrl: imageUrl,
                    fit: BoxFit.contain,
                  ),
                ),
              ),
              IconButton(
                style: IconButton.styleFrom(backgroundColor: Colors.black54),
                icon: const Icon(Icons.close, color: Colors.white),
                onPressed: () => Navigator.of(ctx).pop(),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailAsync = ref.watch(journalDetailProvider(journalId));

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Detail Jurnal & Presensi'),
      ),
      body: detailAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => ErrorStateView(
          message: err.toString().replaceAll('Exception: ', ''),
          onRetry: () => ref.refresh(journalDetailProvider(journalId)),
        ),
        data: (detail) {
          final schedule = detail.schedule;
          final mapel = schedule['mata_pelajaran']?.toString() ?? 'Mata Pelajaran';
          final kelas = schedule['kelas']?.toString() ?? '-';
          final ruang = schedule['ruang']?.toString() ?? '-';
          final hari = schedule['hari']?.toString() ?? '-';
          final jamMulai = schedule['jam_mulai']?.toString() ?? '-';
          final jamSelesai = schedule['jam_selesai']?.toString() ?? '-';
          final siklus = schedule['siklus']?.toString();

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Banner Header
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [AppColors.primaryDark, AppColors.primary],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              kelas,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                            ),
                          ),
                          Text(
                            detail.journalDate,
                            style: const TextStyle(color: Colors.white70, fontSize: 12),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Text(
                        mapel,
                        style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '$hari, $jamMulai - $jamSelesai • Ruang $ruang',
                        style: const TextStyle(color: Colors.white70, fontSize: 12.5),
                      ),
                      if (siklus != null) ...[
                        const SizedBox(height: 6),
                        Text(
                          'Siklus: $siklus',
                          style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 11.5),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Foto Dokumentasi Kamera Realtime
                if (detail.photoUrl != null) ...[
                  const Text(
                    'Dokumentasi Kamera Realtime',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimaryLight),
                  ),
                  const SizedBox(height: 8),
                  GestureDetector(
                    onTap: () => _showImageDialog(context, detail.photoUrl!),
                    child: Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFF22C55E), width: 2),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF22C55E).withValues(alpha: 0.12),
                            blurRadius: 8,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(10),
                        child: Stack(
                          alignment: Alignment.bottomRight,
                          children: [
                            CachedNetworkImage(
                              imageUrl: detail.photoUrl!,
                              height: 220,
                              width: double.infinity,
                              fit: BoxFit.cover,
                              placeholder: (_, __) => Container(
                                height: 220,
                                color: Colors.grey.shade200,
                                child: const Center(child: CircularProgressIndicator()),
                              ),
                              errorWidget: (_, __, ___) => Container(
                                height: 220,
                                color: Colors.grey.shade200,
                                child: const Icon(Icons.broken_image, size: 40, color: Colors.grey),
                              ),
                            ),
                            Positioned(
                              top: 10,
                              left: 10,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: const Color(0xE616A34A),
                                  borderRadius: BorderRadius.circular(20),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withValues(alpha: 0.25),
                                      blurRadius: 4,
                                    ),
                                  ],
                                ),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text('✓', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                                    SizedBox(width: 5),
                                    Text('Foto Realtime Terverifikasi', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
                                  ],
                                ),
                              ),
                            ),
                            Container(
                              margin: const EdgeInsets.all(10),
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                              decoration: BoxDecoration(
                                color: Colors.black.withValues(alpha: 0.7),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.zoom_in, color: Colors.white, size: 16),
                                  SizedBox(width: 4),
                                  Text(
                                    'Perbesar Foto',
                                    style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                ],

                // Materi & Aktivitas
                _buildInfoCard(
                  title: 'Materi Pembelajaran',
                  content: detail.material,
                  icon: Icons.menu_book_rounded,
                ),
                const SizedBox(height: 12),
                _buildInfoCard(
                  title: 'Aktivitas Kelas',
                  content: detail.activity,
                  icon: Icons.directions_run_rounded,
                ),
                if (detail.notes != null && detail.notes!.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  _buildInfoCard(
                    title: 'Catatan Guru',
                    content: detail.notes!,
                    icon: Icons.notes_rounded,
                  ),
                ],
                const SizedBox(height: 24),

                // Presensi Siswa
                _buildPresensiSection(detail.presensi),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildInfoCard({required String title, required String content, required IconData icon}) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 16, color: AppColors.primary),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold, color: AppColors.textPrimaryLight),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            content,
            style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.4),
          ),
        ],
      ),
    );
  }

  Widget _buildPresensiSection(List<JournalAttendanceDetail> presensi) {
    final hadir = presensi.where((p) => p.status == 'Hadir').length;
    final sakit = presensi.where((p) => p.status == 'Sakit').length;
    final izin = presensi.where((p) => p.status == 'Izin').length;
    final alpa = presensi.where((p) => p.status == 'Alpa').length;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Presensi Siswa',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimaryLight),
            ),
            Text(
              '${presensi.length} Siswa Terdaftar',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
            ),
          ],
        ),
        const SizedBox(height: 10),

        // Summary Bar
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFF0F172A),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildSummaryItem('Hadir', hadir, AppColors.hadir),
              _buildSummaryItem('Sakit', sakit, AppColors.sakit),
              _buildSummaryItem('Izin', izin, AppColors.izin),
              _buildSummaryItem('Alpa', alpa, AppColors.alpa),
            ],
          ),
        ),
        const SizedBox(height: 12),

        // List Siswa
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: presensi.length,
          separatorBuilder: (_, __) => const SizedBox(height: 8),
          itemBuilder: (context, index) {
            final item = presensi[index];
            final color = _getStatusColor(item.status);

            return Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Row(
                children: [
                  AppAvatar(
                    name: item.namaSiswa,
                    radius: 17,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item.namaSiswa,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5),
                        ),
                        if (item.nis.isNotEmpty)
                          Text(
                            'NIS: ${item.nis}',
                            style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
                          ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      item.status,
                      style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildSummaryItem(String label, int count, Color color) {
    return Column(
      children: [
        Text('$count', style: TextStyle(color: color, fontSize: 16, fontWeight: FontWeight.bold)),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11)),
      ],
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Hadir':
        return AppColors.hadir;
      case 'Sakit':
        return AppColors.sakit;
      case 'Izin':
        return AppColors.izin;
      case 'Alpa':
      default:
        return AppColors.alpa;
    }
  }
}

