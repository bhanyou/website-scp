import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/widgets/empty_state_view.dart';
import '../../../../core/widgets/error_state_view.dart';
import '../../../../core/widgets/loading_shimmer.dart';
import '../../domain/models/homeroom_models.dart';
import '../controllers/homeroom_permissions_controller.dart';

class HomeroomPermissionsScreen extends ConsumerStatefulWidget {
  const HomeroomPermissionsScreen({super.key});

  @override
  ConsumerState<HomeroomPermissionsScreen> createState() => _HomeroomPermissionsScreenState();
}

class _HomeroomPermissionsScreenState extends ConsumerState<HomeroomPermissionsScreen> {
  void _confirmApprove(HomeroomPermissionItem item) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        title: const Text('Konfirmasi Persetujuan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        content: Text(
          'Setujui permohonan ${item.jenis.toLowerCase()} dari ${item.siswa}?\n\nKehadiran siswa pada tanggal ${item.rentangTanggal.isNotEmpty ? item.rentangTanggal : item.tanggal} akan otomatis tercatat sebagai ${item.jenis}.',
          style: const TextStyle(fontSize: 13, color: Color(0xFF475569), height: 1.4),
        ),
        actions: [
          OutlinedButton(
            onPressed: () => Navigator.pop(ctx),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: Color(0xFFCBD5E1)),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('Batal', style: TextStyle(color: Color(0xFF334155), fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF10B981),
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              final success = await ref
                  .read(homeroomPermissionsControllerProvider.notifier)
                  .approvePermission(item.id);

              if (mounted && success) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Permohonan izin berhasil disetujui dan disinkronkan ke presensi.'),
                    backgroundColor: Color(0xFF10B981),
                    duration: Duration(seconds: 2),
                  ),
                );
              }
            },
            child: const Text('✓ Ya, Setujui', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _showRejectDialog(HomeroomPermissionItem item) {
    final noteController = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        title: const Row(
          children: [
            Icon(Icons.cancel_rounded, color: Color(0xFFEF4444), size: 22),
            SizedBox(width: 8),
            Text('Tolak Permohonan Izin', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFFBEB),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFFDE68A)),
                ),
                child: const Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('⚠️ ', style: TextStyle(fontSize: 14)),
                    Expanded(
                      child: Text(
                        'Perhatian Wali Kelas: Menolak pengajuan ini akan mencatat ketidakhadiran siswa pada rentang tanggal tersebut sebagai ALPA pada rekap presensi.',
                        style: TextStyle(fontSize: 11.5, color: Color(0xFF92400E), height: 1.35),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              Text(
                'Siswa: ${item.siswa} (${item.kelas})',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
              ),
              const SizedBox(height: 10),
              const Text('Alasan Penolakan (Opsional):', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF475569))),
              const SizedBox(height: 4),
              TextField(
                controller: noteController,
                maxLines: 2,
                style: const TextStyle(fontSize: 13),
                decoration: InputDecoration(
                  hintText: 'Contoh: Surat dokter tidak valid / tidak ada tanda tangan',
                  hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  contentPadding: const EdgeInsets.all(10),
                ),
              ),
            ],
          ),
        ),
        actions: [
          OutlinedButton(
            onPressed: () => Navigator.pop(ctx),
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
            onPressed: () async {
              final note = noteController.text.trim();
              Navigator.pop(ctx);
              final success = await ref
                  .read(homeroomPermissionsControllerProvider.notifier)
                  .rejectPermission(item.id, note.isNotEmpty ? note : null);

              if (mounted && success) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Permohonan izin berhasil ditolak.'),
                    backgroundColor: Color(0xFFDC2626),
                    duration: Duration(seconds: 2),
                  ),
                );
              }
            },
            child: const Text('✕ Tolak Izin', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _previewDocument(HomeroomPermissionItem item) {
    if (item.dokumen == null || item.dokumen!.isEmpty) return;

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
                      'Bukti Dokumen: ${item.siswa}',
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
                  item.documentUrl,
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
                  loadingBuilder: (context, child, loadingProgress) {
                    if (loadingProgress == null) return child;
                    return const Center(
                      child: Padding(
                        padding: EdgeInsets.all(32),
                        child: CircularProgressIndicator(strokeWidth: 2),
                      ),
                    );
                  },
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
    final state = ref.watch(homeroomPermissionsControllerProvider);

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
          'Izin & Sakit Kelas Binaan',
          style: TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF0F172A),
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(homeroomPermissionsControllerProvider.notifier).loadPermissions(),
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            // 1. Header Deskripsi & 3 Stat Cards (Persis Desktop)
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(14, 14, 14, 0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Stat Cards Row
                    Row(
                      children: [
                        _buildStatCard('TOTAL IZIN', '${state.totalCount}', const Color(0xFF4F46E5)),
                        const SizedBox(width: 8),
                        _buildStatCard('MENUNGGU', '${state.pendingCount}', const Color(0xFFF59E0B)),
                        const SizedBox(width: 8),
                        _buildStatCard('DISETUJUI', '${state.approvedCount}', const Color(0xFF10B981)),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Filter Tab Pills
                    SizedBox(
                      height: 38,
                      child: ListView(
                        scrollDirection: Axis.horizontal,
                        children: [
                          _buildFilterTab('Semua', 'Semua (${state.totalCount})', state.filterStatus),
                          const SizedBox(width: 8),
                          _buildFilterTab('Pending', 'Menunggu (${state.pendingCount})', state.filterStatus),
                          const SizedBox(width: 8),
                          _buildFilterTab('Approved', 'Disetujui (${state.approvedCount})', state.filterStatus),
                          const SizedBox(width: 8),
                          _buildFilterTab('Rejected', 'Ditolak (${state.rejectedCount})', state.filterStatus),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),
                  ],
                ),
              ),
            ),

            // 2. Daftar Pengajuan Izin
            if (state.isLoading)
              const SliverFillRemaining(
                child: Center(child: LoadingShimmerList(itemHeight: 140, itemCount: 4)),
              )
            else if (state.errorMessage != null)
              SliverFillRemaining(
                child: ErrorStateView(
                  message: state.errorMessage!,
                  onRetry: () => ref.read(homeroomPermissionsControllerProvider.notifier).loadPermissions(),
                ),
              )
            else if (state.filteredPermissions.isEmpty)
              SliverFillRemaining(
                child: EmptyStateView(
                  icon: Icons.assignment_turned_in_outlined,
                  title: 'Tidak Ada Data',
                  message: state.filterStatus == 'Pending'
                      ? 'Tidak ada permohonan izin yang menunggu verifikasi Anda.'
                      : 'Belum ada data permohonan izin untuk filter ini.',
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(14, 4, 14, 24),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final item = state.filteredPermissions[index];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: _buildPermissionCard(item),
                      );
                    },
                    childCount: state.filteredPermissions.length,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, Color stripeColor) {
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
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 6),
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
                    const SizedBox(height: 3),
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

  Widget _buildFilterTab(String statusKey, String label, String activeStatus) {
    final isSelected = activeStatus == statusKey;

    return InkWell(
      onTap: () => ref.read(homeroomPermissionsControllerProvider.notifier).setFilterStatus(statusKey),
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

  Widget _buildPermissionCard(HomeroomPermissionItem item) {
    final isSakit = item.isSakit;

    return Container(
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
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Baris 1: Nama Siswa & Kategori (Izin / Sakit)
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.siswa,
                      style: const TextStyle(
                        fontSize: 14.5,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'NIS: ${item.nis} • Kelas ${item.kelas}',
                      style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: isSakit ? const Color(0xFFFFFBEB) : const Color(0xFFEEF2FF),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: isSakit ? const Color(0xFFFDE68A) : const Color(0xFFC7D2FE),
                  ),
                ),
                child: Text(
                  item.jenis,
                  style: TextStyle(
                    color: isSakit ? const Color(0xFF92400E) : const Color(0xFF4F46E5),
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          const SizedBox(height: 10),

          // Baris 2: Rentang Tanggal & Durasi
          Row(
            children: [
              const Icon(Icons.calendar_today_outlined, size: 13, color: Color(0xFF64748B)),
              const SizedBox(width: 5),
              Expanded(
                child: Text(
                  item.rentangTanggal.isNotEmpty ? item.rentangTanggal : item.tanggal,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF334155),
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFFEEF2FF),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  '${item.durasiHari} Hari',
                  style: const TextStyle(
                    color: Color(0xFF4F46E5),
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),

          // Baris 3: Alasan Izin
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Alasan:', style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8), fontWeight: FontWeight.w600)),
                const SizedBox(height: 2),
                Text(
                  item.alasan,
                  style: const TextStyle(fontSize: 12, color: Color(0xFF334155), height: 1.35),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),

          // Baris 4: Dokumen & Status
          Row(
            children: [
              if (item.dokumen != null && item.dokumen!.isNotEmpty)
                OutlinedButton.icon(
                  onPressed: () => _previewDocument(item),
                  icon: const Icon(Icons.description_outlined, size: 14, color: Color(0xFF4F46E5)),
                  label: const Text(
                    'Lihat Berkas',
                    style: TextStyle(fontSize: 11, color: Color(0xFF4F46E5), fontWeight: FontWeight.w600),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Color(0xFFC7D2FE)),
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                  ),
                )
              else
                const Text(
                  'Tanpa lampiran berkas',
                  style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8), fontStyle: FontStyle.italic),
                ),
              const Spacer(),
              // Badge Status
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: item.isApproved
                      ? const Color(0xFFECFDF5)
                      : item.isRejected
                          ? const Color(0xFFFEF2F2)
                          : const Color(0xFFFFFBEB),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: item.isApproved
                        ? const Color(0xFFA7F3D0)
                        : item.isRejected
                            ? const Color(0xFFFECACA)
                            : const Color(0xFFFDE68A),
                  ),
                ),
                child: Text(
                  item.isApproved
                      ? 'Disetujui'
                      : item.isRejected
                          ? 'Ditolak'
                          : 'Menunggu',
                  style: TextStyle(
                    color: item.isApproved
                        ? const Color(0xFF065F46)
                        : item.isRejected
                            ? const Color(0xFF991B1B)
                            : const Color(0xFF92400E),
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),

          // Catatan Penolakan (Jika ada)
          if (item.isRejected && item.catatanVerifikasi != null && item.catatanVerifikasi!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF2F2),
                borderRadius: BorderRadius.circular(6),
                border: Border.all(color: const Color(0xFFFECACA)),
              ),
              child: Text(
                'Catatan Penolakan: ${item.catatanVerifikasi}',
                style: const TextStyle(fontSize: 11, color: Color(0xFF991B1B)),
              ),
            ),
          ],

          // Baris 5: Aksi Verifikasi (Hanya muncul jika status Pending)
          if (item.isPending) ...[
            const SizedBox(height: 12),
            const Divider(height: 1, color: Color(0xFFF1F5F9)),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: ElevatedButton(
                    onPressed: () => _confirmApprove(item),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF10B981),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: const Text('✓ Setuju', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () => _showRejectDialog(item),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFEF4444),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: const Text('✕ Tolak', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

