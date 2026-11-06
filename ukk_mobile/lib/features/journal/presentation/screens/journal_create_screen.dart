import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/widgets/app_avatar.dart';
import '../../../../core/widgets/app_button.dart';
import '../../../../core/widgets/app_text_field.dart';
import '../../../../core/widgets/realtime_camera_input.dart';
import '../../../auth/presentation/controllers/auth_controller.dart';
import '../../domain/models/journal_models.dart';
import '../controllers/journal_controllers.dart';

class JournalCreateScreen extends ConsumerStatefulWidget {
  final int? preselectedScheduleId;

  const JournalCreateScreen({super.key, this.preselectedScheduleId});

  @override
  ConsumerState<JournalCreateScreen> createState() => _JournalCreateScreenState();
}

class _JournalCreateScreenState extends ConsumerState<JournalCreateScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _materialController;
  late final TextEditingController _activityController;
  late final TextEditingController _notesController;

  @override
  void initState() {
    super.initState();
    _materialController = TextEditingController();
    _activityController = TextEditingController();
    _notesController = TextEditingController();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref
          .read(journalCreateControllerProvider.notifier)
          .loadSchedules(preselectedScheduleId: widget.preselectedScheduleId);
    });
  }

  @override
  void dispose() {
    _materialController.dispose();
    _activityController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    final controller = ref.read(journalCreateControllerProvider.notifier);
    final success = await controller.submit(
      material: _materialController.text,
      activity: _activityController.text,
      notes: _notesController.text,
    );

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Row(
              children: [
                Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                SizedBox(width: 8),
                Text('Jurnal KBM dan presensi siswa berhasil disimpan!'),
              ],
            ),
            backgroundColor: AppColors.hadir,
            behavior: SnackBarBehavior.floating,
          ),
        );
        context.pop();
      } else {
        final err = ref.read(journalCreateControllerProvider).errorMessage;
        if (err != null) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(err),
              backgroundColor: AppColors.alpa,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authUser = ref.watch(authControllerProvider).user;
    final state = ref.watch(journalCreateControllerProvider);

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
          'Input Jurnal KBM & Presensi',
          style: TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF0F172A),
          ),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 16),
            child: AppAvatar(
              imageUrl: authUser?.avatarUrl,
              name: authUser?.name ?? 'Guru',
              radius: 17,
            ),
          ),
        ],
      ),
      body: state.isLoadingSchedules
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : state.schedules.isEmpty
              ? _buildNoSchedulesState()
              : Form(
                  key: _formKey,
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 36),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Pilihan Jadwal & Kelas
                        _buildSectionHeader('1. Jadwal & Rombel KBM', Icons.calendar_today_rounded),
                        const SizedBox(height: 8),
                        _buildScheduleSelector(state),
                        const SizedBox(height: 20),

                        // Tanggal Jurnal
                        _buildSectionHeader('2. Tanggal Pelaksanaan', Icons.event_rounded),
                        const SizedBox(height: 8),
                        _buildDatePicker(state.selectedDate),
                        const SizedBox(height: 20),

                        // Form Materi & Aktivitas
                        _buildSectionHeader('3. Materi & Aktivitas Pembelajaran', Icons.menu_book_rounded),
                        const SizedBox(height: 8),
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
                              AppTextField(
                                controller: _materialController,
                                label: 'Materi Pembelajaran *',
                                hint: 'Contoh: Pemrograman Berorientasi Objek - Pewarisan (Inheritance)',
                                prefixIcon: Icons.subject_rounded,
                                validator: (v) => v == null || v.trim().isEmpty ? 'Materi pembelajaran wajib diisi' : null,
                              ),
                              const SizedBox(height: 14),
                              AppTextField(
                                controller: _activityController,
                                label: 'Aktivitas KBM di Kelas *',
                                hint: 'Contoh: Penjelasan konsep pewarisan, dilanjutkan praktikum pembuatan sub-class.',
                                maxLines: 3,
                                prefixIcon: Icons.directions_run_rounded,
                                validator: (v) => v == null || v.trim().isEmpty ? 'Aktivitas pembelajaran wajib diisi' : null,
                              ),
                              const SizedBox(height: 14),
                              AppTextField(
                                controller: _notesController,
                                label: 'Catatan Guru (Opsional)',
                                hint: 'Catatan tambahan terkait ketertiban, siswa yang memerlukan remedial, dsb.',
                                maxLines: 2,
                                prefixIcon: Icons.note_alt_outlined,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 24),

                        // 4. Realtime Camera Input (IDENTIK DESKTOP)
                        _buildSectionHeader('4. Dokumentasi Foto Kamera Realtime', Icons.camera_alt_rounded),
                        const SizedBox(height: 8),
                        RealtimeCameraInput(
                          label: 'Foto Kegiatan KBM',
                          helpText: 'Gunakan kamera HP untuk mengambil bukti KBM secara realtime. Watermark tanggal dan jam otomatis tertempel.',
                          required: false,
                          initialBytes: state.photoBytes,
                          initialTimestamp: state.photoTimestamp,
                          onPhotoCaptured: (bytes, filename, timestamp, isRealtime) {
                            ref.read(journalCreateControllerProvider.notifier).setPhoto(bytes, filename);
                          },
                          onPhotoCleared: () {
                            ref.read(journalCreateControllerProvider.notifier).clearPhoto();
                          },
                        ),
                        const SizedBox(height: 26),

                        // 5. Presensi Siswa Kelas
                        _buildSectionHeader('5. Checklist Presensi Siswa', Icons.people_alt_rounded),
                        const SizedBox(height: 4),
                        const Text(
                          'Tentukan status kehadiran setiap siswa (Hadir, Sakit, Izin, atau Alpa).',
                          style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                        ),
                        const SizedBox(height: 12),
                        _buildAttendanceSummary(state),
                        const SizedBox(height: 12),
                        _buildStudentList(state),
                        const SizedBox(height: 32),

                        // Tombol Simpan Jurnal
                        AppButton(
                          label: 'Simpan Jurnal & Presensi',
                          isLoading: state.isSubmitting,
                          icon: Icons.check_circle_rounded,
                          onPressed: _handleSubmit,
                        ),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildNoSchedulesState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: const BoxDecoration(
                color: Color(0xFFF1F5F9),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.event_busy_rounded, size: 48, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 16),
            const Text(
              'Tidak Ada Jadwal Aktif',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 8),
            const Text(
              'Anda belum memiliki jadwal mengajar pada siklus yang aktif saat ini.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: () => context.pop(),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: const Text('Kembali ke Dashboard'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title, IconData icon) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(6),
          decoration: BoxDecoration(
            color: const Color(0x144F46E5),
            borderRadius: BorderRadius.circular(6),
          ),
          child: Icon(icon, size: 16, color: AppColors.primary),
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
        ),
      ],
    );
  }

  Widget _buildScheduleSelector(JournalCreateState state) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFCBD5E1)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<JournalSchedule>(
          isExpanded: true,
          value: state.selectedSchedule,
          hint: const Text('Pilih Jadwal Mengajar', style: TextStyle(fontSize: 13)),
          items: state.schedules.map((schedule) {
            return DropdownMenuItem<JournalSchedule>(
              value: schedule,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEEF2FF),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          schedule.kelas,
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF4F46E5),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          schedule.mataPelajaran,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '${schedule.hari}, ${schedule.jamMulai}-${schedule.jamSelesai} (${schedule.ruang}) • ${schedule.students.length} Siswa',
                    style: const TextStyle(color: Color(0xFF64748B), fontSize: 11),
                  ),
                ],
              ),
            );
          }).toList(),
          onChanged: (newSchedule) {
            if (newSchedule != null) {
              ref.read(journalCreateControllerProvider.notifier).selectSchedule(newSchedule);
            }
          },
        ),
      ),
    );
  }

  Widget _buildDatePicker(DateTime date) {
    final formattedDate = DateFormat('EEEE, dd MMMM yyyy', 'id_ID').format(date);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFFF1F5F9),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        children: [
          const Icon(Icons.calendar_month_rounded, color: AppColors.primary, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  formattedDate,
                  style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                ),
                const SizedBox(height: 2),
                const Text(
                  'Tanggal dikunci otomatis sesuai hari pelaksanaan KBM',
                  style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                ),
              ],
            ),
          ),
          const Icon(Icons.lock_outline_rounded, color: Color(0xFF94A3B8), size: 18),
        ],
      ),
    );
  }

  Widget _buildAttendanceSummary(JournalCreateState state) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: const Color(0xFF0F172A),
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          _buildBadge('Hadir', state.hadirCount, AppColors.hadir),
          _buildBadge('Sakit', state.sakitCount, AppColors.sakit),
          _buildBadge('Izin', state.izinCount, AppColors.izin),
          _buildBadge('Alpa', state.alpaCount, AppColors.alpa),
          TextButton.icon(
            onPressed: () {
              ref.read(journalCreateControllerProvider.notifier).setAllAttendance('Hadir');
            },
            icon: const Icon(Icons.done_all_rounded, size: 15, color: Colors.white),
            label: const Text(
              'Hadir Semua',
              style: TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
            ),
            style: TextButton.styleFrom(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              minimumSize: Size.zero,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBadge(String label, int count, Color color) {
    return Column(
      children: [
        Text(
          '$count',
          style: TextStyle(color: color, fontSize: 16, fontWeight: FontWeight.bold),
        ),
        Text(
          label,
          style: const TextStyle(color: Colors.white70, fontSize: 10.5),
        ),
      ],
    );
  }

  Widget _buildStudentList(JournalCreateState state) {
    final students = state.selectedSchedule?.students ?? [];
    if (students.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(20),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFE2E8F0)),
        ),
        child: const Text('Tidak ada siswa di kelas ini.', style: TextStyle(color: Color(0xFF64748B))),
      );
    }

    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: students.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, index) {
        final student = students[index];
        final currentStatus = state.attendances[student.id] ?? 'Hadir';

        return Container(
          padding: const EdgeInsets.all(12),
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
                    name: student.name,
                    radius: 17,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          student.name,
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                        if (student.nis.isNotEmpty)
                          Text(
                            'NIS/NISN: ${student.nis}',
                            style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                          ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              // Segmented H, S, I, A Buttons
              Row(
                children: [
                  _buildStatusChip(student.id, 'Hadir', 'Hadir', AppColors.hadir, currentStatus == 'Hadir'),
                  const SizedBox(width: 6),
                  _buildStatusChip(student.id, 'Sakit', 'Sakit', AppColors.sakit, currentStatus == 'Sakit'),
                  const SizedBox(width: 6),
                  _buildStatusChip(student.id, 'Izin', 'Izin', AppColors.izin, currentStatus == 'Izin'),
                  const SizedBox(width: 6),
                  _buildStatusChip(student.id, 'Alpa', 'Alpa', AppColors.alpa, currentStatus == 'Alpa'),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildStatusChip(
    int studentId,
    String statusValue,
    String label,
    Color color,
    bool isSelected,
  ) {
    return Expanded(
      child: InkWell(
        onTap: () {
          ref.read(journalCreateControllerProvider.notifier).setAttendance(studentId, statusValue);
        },
        borderRadius: BorderRadius.circular(8),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          padding: const EdgeInsets.symmetric(vertical: 7),
          decoration: BoxDecoration(
            color: isSelected ? color : const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Center(
            child: Text(
              label,
              style: TextStyle(
                color: isSelected ? Colors.white : const Color(0xFF64748B),
                fontSize: 11.5,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
              ),
            ),
          ),
        ),
      ),
    );
  }
}
