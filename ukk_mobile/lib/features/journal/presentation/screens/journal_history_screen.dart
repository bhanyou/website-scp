import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/router/route_names.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/widgets/app_avatar.dart';
import '../../../../core/widgets/empty_state_view.dart';
import '../../../../core/widgets/error_state_view.dart';
import '../../../../core/widgets/loading_shimmer.dart';
import '../../../auth/presentation/controllers/auth_controller.dart';
import '../../domain/models/journal_models.dart';
import '../controllers/journal_controllers.dart';

class JournalHistoryScreen extends ConsumerStatefulWidget {
  final bool isEmbedded;

  const JournalHistoryScreen({super.key, this.isEmbedded = false});

  @override
  ConsumerState<JournalHistoryScreen> createState() => _JournalHistoryScreenState();
}

class _JournalHistoryScreenState extends ConsumerState<JournalHistoryScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final authUser = ref.watch(authControllerProvider).user;
    final state = ref.watch(journalHistoryControllerProvider);

    final content = RefreshIndicator(
      onRefresh: () => ref.read(journalHistoryControllerProvider.notifier).loadHistory(),
      child: Column(
        children: [
          // Kolom Pencarian
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari mata pelajaran, kelas, atau materi...',
                prefixIcon: const Icon(Icons.search_rounded, color: Colors.grey),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          ref.read(journalHistoryControllerProvider.notifier).setSearchQuery('');
                        },
                      )
                    : null,
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.grey.shade300),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.grey.shade300),
                ),
              ),
              onChanged: (val) {
                ref.read(journalHistoryControllerProvider.notifier).setSearchQuery(val);
              },
            ),
          ),

          // Daftar Riwayat Jurnal
          Expanded(
            child: Builder(
              builder: (context) {
                if (state.isLoading) {
                  return const Center(child: LoadingShimmerList(itemHeight: 140, itemCount: 4));
                }
                if (state.errorMessage != null) {
                  return ErrorStateView(
                    message: state.errorMessage!,
                    onRetry: () => ref.read(journalHistoryControllerProvider.notifier).loadHistory(),
                  );
                }
                final items = state.filteredJournals;
                if (items.isEmpty) {
                  return EmptyStateView(
                    icon: Icons.history_edu_rounded,
                    title: 'Belum Ada Jurnal',
                    message: state.searchQuery.isNotEmpty
                        ? 'Tidak ditemukan jurnal dengan kata kunci "${state.searchQuery}".'
                        : 'Anda belum mengisi jurnal KBM.',
                  );
                }

                return ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: items.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    final item = items[index];
                    return _buildJournalCard(context, item);
                  },
                );
              },
            ),
          ),
        ],
      ),
    );

    if (widget.isEmbedded) {
      return content;
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Riwayat Jurnal KBM'),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 16),
            child: AppAvatar(
              imageUrl: authUser?.avatarUrl,
              name: authUser?.name ?? 'Guru',
              radius: 18,
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(RouteNames.guruJournalCreate),
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add_a_photo_rounded, color: Colors.white),
        label: const Text('Isi Jurnal Baru', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: content,
    );
  }

  Widget _buildJournalCard(BuildContext context, JournalHistoryItem item) {
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
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () {
            context.push('${RouteNames.guruJournals}/${item.id}');
          },
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Foto Dokumentasi Thumbnail Berstempel
                    if (item.photoUrl != null)
                      Stack(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(10),
                            child: CachedNetworkImage(
                              imageUrl: item.photoUrl!,
                              width: 68,
                              height: 68,
                              fit: BoxFit.cover,
                              placeholder: (_, __) => Container(
                                width: 68,
                                height: 68,
                                color: Colors.grey.shade100,
                                child: const Center(child: CircularProgressIndicator(strokeWidth: 2)),
                              ),
                              errorWidget: (_, __, ___) => Container(
                                width: 68,
                                height: 68,
                                color: Colors.grey.shade100,
                                child: const Icon(Icons.broken_image_rounded, color: Colors.grey),
                              ),
                            ),
                          ),
                          Positioned(
                            bottom: 2,
                            right: 2,
                            child: Container(
                              padding: const EdgeInsets.all(2),
                              decoration: const BoxDecoration(
                                color: Color(0xFF16A34A),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.check, size: 10, color: Colors.white),
                            ),
                          ),
                        ],
                      )
                    else
                      Container(
                        width: 68,
                        height: 68,
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.school_rounded, color: AppColors.primary, size: 30),
                      ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: AppColors.primary.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  item.kelas,
                                  style: const TextStyle(
                                    color: AppColors.primary,
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  item.tanggal,
                                  style: TextStyle(color: Colors.grey.shade600, fontSize: 11.5),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            item.mataPelajaran,
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimaryLight,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '${item.hari}, ${item.jamMulai} - ${item.jamSelesai} (${item.ruang})',
                            style: TextStyle(fontSize: 11.5, color: Colors.grey.shade600),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 10),
                  child: Divider(height: 1, color: Color(0xFFF1F5F9)),
                ),
                Text(
                  'Materi: ${item.materi}',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Text(
                  'Aktivitas: ${item.aktivitas}',
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 12),
                const Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(
                      'Lihat Detail & Presensi',
                      style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: AppColors.primary),
                    ),
                    SizedBox(width: 4),
                    Icon(Icons.arrow_forward_ios_rounded, size: 12, color: AppColors.primary),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

