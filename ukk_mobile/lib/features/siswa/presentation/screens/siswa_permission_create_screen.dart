import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/widgets/realtime_camera_input.dart';
import '../../../auth/presentation/controllers/auth_controller.dart';
import '../controllers/siswa_dashboard_controller.dart';
import '../controllers/siswa_permission_controller.dart';

class SiswaPermissionCreateScreen extends ConsumerStatefulWidget {
  const SiswaPermissionCreateScreen({super.key});

  @override
  ConsumerState<SiswaPermissionCreateScreen> createState() => _SiswaPermissionCreateScreenState();
}

class _SiswaPermissionCreateScreenState extends ConsumerState<SiswaPermissionCreateScreen> {
  final _formKey = GlobalKey<FormState>();
  late DateTime _startDate;
  late DateTime _endDate;
  String _selectedType = 'Izin';
  final _reasonController = TextEditingController();

  Uint8List? _documentBytes;
  String? _documentFileName;
  DateTime? _documentTimestamp;
  bool _documentIsRealtime = true;

  @override
  void initState() {
    super.initState();
    _startDate = DateTime.now();
    _endDate = DateTime.now();
  }

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  int get _durationDays {
    final start = DateTime(_startDate.year, _startDate.month, _startDate.day);
    final end = DateTime(_endDate.year, _endDate.month, _endDate.day);
    final diff = end.difference(start).inDays + 1;
    return diff > 0 ? diff : 1;
  }

  Future<void> _selectDate(bool isStartDate) async {
    final initialDate = isStartDate ? _startDate : _endDate;
    final firstDate = isStartDate ? DateTime.now().subtract(const Duration(days: 30)) : _startDate;
    final lastDate = DateTime.now().add(const Duration(days: 90));

    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate.isBefore(firstDate) ? firstDate : initialDate,
      firstDate: firstDate,
      lastDate: lastDate,
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppColors.primary,
              onPrimary: Colors.white,
              onSurface: Color(0xFF0F172A),
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        if (isStartDate) {
          _startDate = picked;
          if (_endDate.isBefore(_startDate)) {
            _endDate = _startDate;
          }
        } else {
          _endDate = picked;
        }
      });
    }
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    if (_documentBytes == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Mohon lampirkan foto surat izin atau surat keterangan dokter.'),
          backgroundColor: AppColors.alpa,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final authState = ref.read(authControllerProvider);
    final studentId = authState.user?.studentId ?? 0;

    final startStr = DateFormat('yyyy-MM-dd').format(_startDate);
    final endStr = DateFormat('yyyy-MM-dd').format(_endDate);

    final success = await ref.read(siswaPermissionControllerProvider.notifier).submitPermission(
          studentId: studentId,
          startDate: startStr,
          endDate: endStr,
          type: _selectedType,
          reason: _reasonController.text.trim(),
          documentBytes: _documentBytes!,
          documentFileName: _documentFileName ?? 'surat_izin.jpg',
        );

    if (mounted && success) {
      ref.read(siswaDashboardControllerProvider.notifier).loadDashboard();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
              SizedBox(width: 8),
              Expanded(child: Text('Pengajuan izin berhasil dikirim dan menunggu verifikasi wali kelas.')),
            ],
          ),
          backgroundColor: AppColors.hadir,
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;
    final state = ref.watch(siswaPermissionControllerProvider);
    final isSubmitting = state.isSubmitting;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: Color(0xFF0F172A)),
          onPressed: () => Navigator.pop(context),
          tooltip: 'Kembali',
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(color: const Color(0xFFE2E8F0), height: 1),
        ),
        title: const Text(
          'Ajukan Izin / Sakit Siswa',
          style: TextStyle(
            fontSize: 16.5,
            fontWeight: FontWeight.w700,
            color: Color(0xFF0F172A),
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 36),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(18),
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
                    // Field Siswa (Readonly)
                    const Text(
                      'Nama Siswa',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 6),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Text(
                        '${user?.name ?? 'Siswa'} (${user?.studentClass ?? '-'})',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF334155)),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Tanggal Mulai & Tanggal Selesai (2 Kolom)
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Tanggal Mulai *',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                              ),
                              const SizedBox(height: 6),
                              InkWell(
                                onTap: () => _selectDate(true),
                                borderRadius: BorderRadius.circular(8),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(color: const Color(0xFFCBD5E1)),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.calendar_today_outlined, size: 15, color: AppColors.primary),
                                      const SizedBox(width: 8),
                                      Text(
                                        DateFormat('dd/MM/yyyy').format(_startDate),
                                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Tanggal Selesai *',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                              ),
                              const SizedBox(height: 6),
                              InkWell(
                                onTap: () => _selectDate(false),
                                borderRadius: BorderRadius.circular(8),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(color: const Color(0xFFCBD5E1)),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.calendar_today_outlined, size: 15, color: AppColors.primary),
                                      const SizedBox(width: 8),
                                      Text(
                                        DateFormat('dd/MM/yyyy').format(_endDate),
                                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Kotak Durasi Otomatis (Persis Desktop)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEEF2FF),
                        borderRadius: BorderRadius.circular(8),
                        border: const Border(left: BorderSide(color: AppColors.primary, width: 3)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.access_time_rounded, size: 16, color: AppColors.primary),
                              SizedBox(width: 6),
                              Text('Durasi Ketidakhadiran:', style: TextStyle(fontSize: 12, color: Color(0xFF475569))),
                            ],
                          ),
                          Text(
                            '$_durationDays Hari',
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w800,
                              color: AppColors.primary,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Jenis Pengajuan (Dropdown)
                    const Text(
                      'Jenis Pengajuan *',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      initialValue: _selectedType,
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Colors.white,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.primary, width: 1.8)),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'Izin', child: Text('Izin (Urusan Keluarga/Dinas)')),
                        DropdownMenuItem(value: 'Sakit', child: Text('Sakit (Kondisi Kesehatan)')),
                      ],
                      onChanged: (val) {
                        if (val != null) setState(() => _selectedType = val);
                      },
                    ),
                    const SizedBox(height: 16),

                    // Alasan Lengkap
                    const Text(
                      'Alasan Lengkap *',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: _reasonController,
                      maxLines: 3,
                      validator: (val) {
                        if (val == null || val.trim().isEmpty) {
                          return 'Mohon tuliskan alasan izin secara jelas.';
                        }
                        return null;
                      },
                      style: const TextStyle(fontSize: 13.5),
                      decoration: InputDecoration(
                        hintText: 'Jelaskan alasan izin atau sakit Anda secara sopan dan jelas...',
                        hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8)),
                        filled: true,
                        fillColor: Colors.white,
                        contentPadding: const EdgeInsets.all(12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.primary, width: 1.8)),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Realtime Camera / Document Uploader (IDENTIK DESKTOP)
                    RealtimeCameraInput(
                      label: 'Unggah Surat Dokter / Bukti Surat',
                      helpText: 'Gunakan kamera HP untuk memfoto surat dokter atau surat izin dari orang tua secara langsung.',
                      required: true,
                      initialBytes: _documentBytes,
                      initialTimestamp: _documentTimestamp,
                      isRealtime: _documentIsRealtime,
                      onPhotoCaptured: (bytes, filename, timestamp, isRealtime) {
                        setState(() {
                          _documentBytes = bytes;
                          _documentFileName = filename;
                          _documentTimestamp = timestamp;
                          _documentIsRealtime = isRealtime;
                        });
                      },
                      onPhotoCleared: () {
                        setState(() {
                          _documentBytes = null;
                          _documentFileName = null;
                          _documentTimestamp = null;
                        });
                      },
                    ),
                    const SizedBox(height: 28),

                    // Tombol Submit & Batal
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            onPressed: isSubmitting ? null : () => Navigator.pop(context),
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: Color(0xFFCBD5E1)),
                              padding: const EdgeInsets.symmetric(vertical: 13),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            ),
                            child: const Text(
                              'Batal',
                              style: TextStyle(
                                fontSize: 13.5,
                                color: Color(0xFF334155),
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          flex: 2,
                          child: ElevatedButton(
                            onPressed: isSubmitting ? null : _handleSubmit,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              foregroundColor: Colors.white,
                              elevation: 0,
                              padding: const EdgeInsets.symmetric(vertical: 13),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            ),
                            child: isSubmitting
                                ? const SizedBox(
                                    height: 20,
                                    width: 20,
                                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                  )
                                : const Text(
                                    'Kirim Pengajuan Izin',
                                    style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold),
                                  ),
                          ),
                        ),
                      ],
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
}
