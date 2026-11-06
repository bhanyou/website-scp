import '../../../../core/config/app_config.dart';

/// Model item pengajuan izin/sakit siswa untuk Wali Kelas & Kesiswaan
class HomeroomPermissionItem {
  final int id;
  final String siswa;
  final String nis;
  final String kelas;
  final String tanggal;
  final String tanggalSelesai;
  final int durasiHari;
  final String rentangTanggal;
  final String jenis; // 'Izin' atau 'Sakit'
  final String alasan;
  final String? dokumen;
  final String status; // 'Pending', 'Approved', 'Rejected'
  final dynamic diverifikasiOleh;
  final String? waktuVerifikasi;
  final String? catatanVerifikasi;

  const HomeroomPermissionItem({
    required this.id,
    required this.siswa,
    required this.nis,
    required this.kelas,
    required this.tanggal,
    required this.tanggalSelesai,
    required this.durasiHari,
    required this.rentangTanggal,
    required this.jenis,
    required this.alasan,
    this.dokumen,
    required this.status,
    this.diverifikasiOleh,
    this.waktuVerifikasi,
    this.catatanVerifikasi,
  });

  factory HomeroomPermissionItem.fromJson(Map<String, dynamic> json) {
    return HomeroomPermissionItem(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      siswa: json['siswa']?.toString() ?? 'Siswa',
      nis: json['nis']?.toString() ?? '-',
      kelas: json['kelas']?.toString() ?? '-',
      tanggal: json['tanggal']?.toString() ?? '',
      tanggalSelesai: json['tanggal_selesai']?.toString() ?? '',
      durasiHari: json['durasi_hari'] is int
          ? json['durasi_hari'] as int
          : int.tryParse(json['durasi_hari']?.toString() ?? '1') ?? 1,
      rentangTanggal: json['rentang_tanggal']?.toString() ?? '',
      jenis: json['jenis']?.toString() ?? 'Izin',
      alasan: json['alasan']?.toString() ?? '-',
      dokumen: json['dokumen']?.toString(),
      status: json['status']?.toString() ?? 'Pending',
      diverifikasiOleh: json['diverifikasi_oleh'],
      waktuVerifikasi: json['waktu_verifikasi']?.toString(),
      catatanVerifikasi: json['catatan_verifikasi']?.toString(),
    );
  }

  bool get isPending => status.toLowerCase() == 'pending';
  bool get isApproved => status.toLowerCase() == 'approved' || status.toLowerCase() == 'disetujui';
  bool get isRejected => status.toLowerCase() == 'rejected' || status.toLowerCase() == 'ditolak';
  bool get isSakit => jenis.toLowerCase() == 'sakit';

  String get documentUrl => AppConfig.resolveStorageUrl(dokumen);
}

/// Ringkasan total presensi kelas (Hadir, Sakit, Izin, Alpa)
class HomeroomAttendanceSummary {
  final int hadir;
  final int sakit;
  final int izin;
  final int alpa;
  final int total;

  const HomeroomAttendanceSummary({
    this.hadir = 0,
    this.sakit = 0,
    this.izin = 0,
    this.alpa = 0,
    this.total = 0,
  });

  factory HomeroomAttendanceSummary.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const HomeroomAttendanceSummary();
    return HomeroomAttendanceSummary(
      hadir: json['hadir'] is int ? json['hadir'] as int : int.tryParse(json['hadir']?.toString() ?? '0') ?? 0,
      sakit: json['sakit'] is int ? json['sakit'] as int : int.tryParse(json['sakit']?.toString() ?? '0') ?? 0,
      izin: json['izin'] is int ? json['izin'] as int : int.tryParse(json['izin']?.toString() ?? '0') ?? 0,
      alpa: json['alpa'] is int ? json['alpa'] as int : int.tryParse(json['alpa']?.toString() ?? '0') ?? 0,
      total: json['total'] is int ? json['total'] as int : int.tryParse(json['total']?.toString() ?? '0') ?? 0,
    );
  }
}

/// Rekap presensi per siswa di kelas binaan
class HomeroomStudentAttendance {
  final int id;
  final String nis;
  final String name;
  final String className;
  final int? classId;
  final int hadir;
  final int sakit;
  final int izin;
  final int alpa;
  final int total;
  final double percentage;

  const HomeroomStudentAttendance({
    required this.id,
    required this.nis,
    required this.name,
    required this.className,
    this.classId,
    required this.hadir,
    required this.sakit,
    required this.izin,
    required this.alpa,
    required this.total,
    required this.percentage,
  });

  factory HomeroomStudentAttendance.fromJson(Map<String, dynamic> json) {
    return HomeroomStudentAttendance(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      nis: json['nis']?.toString() ?? '-',
      name: json['name']?.toString() ?? 'Siswa',
      className: json['class_name']?.toString() ?? '-',
      classId: json['class_id'] is int ? json['class_id'] as int : int.tryParse(json['class_id']?.toString() ?? '0'),
      hadir: json['hadir'] is int ? json['hadir'] as int : int.tryParse(json['hadir']?.toString() ?? '0') ?? 0,
      sakit: json['sakit'] is int ? json['sakit'] as int : int.tryParse(json['sakit']?.toString() ?? '0') ?? 0,
      izin: json['izin'] is int ? json['izin'] as int : int.tryParse(json['izin']?.toString() ?? '0') ?? 0,
      alpa: json['alpa'] is int ? json['alpa'] as int : int.tryParse(json['alpa']?.toString() ?? '0') ?? 0,
      total: json['total'] is int ? json['total'] as int : int.tryParse(json['total']?.toString() ?? '0') ?? 0,
      percentage: (json['percentage'] is num)
          ? (json['percentage'] as num).toDouble()
          : double.tryParse(json['percentage']?.toString() ?? '0') ?? 0.0,
    );
  }
}

/// Data lengkap rekap presensi kelas dari endpoint /api/attendances
class HomeroomAttendanceData {
  final HomeroomAttendanceSummary summary;
  final List<Map<String, dynamic>> availableClasses;
  final int? selectedClassId;
  final String? range;
  final String? startDate;
  final String? endDate;
  final List<HomeroomStudentAttendance> students;

  const HomeroomAttendanceData({
    this.summary = const HomeroomAttendanceSummary(),
    this.availableClasses = const [],
    this.selectedClassId,
    this.range,
    this.startDate,
    this.endDate,
    this.students = const [],
  });

  factory HomeroomAttendanceData.fromJson(Map<String, dynamic> json) {
    final rawSummary = json['summary'] as Map<String, dynamic>?;
    final rawClasses = json['available_classes'] as List? ?? [];
    final rawData = json['data'] as List? ?? [];

    return HomeroomAttendanceData(
      summary: HomeroomAttendanceSummary.fromJson(rawSummary),
      availableClasses: rawClasses.map((item) => Map<String, dynamic>.from(item as Map)).toList(),
      selectedClassId: json['selected_class_id'] is int
          ? json['selected_class_id'] as int
          : int.tryParse(json['selected_class_id']?.toString() ?? '0'),
      range: json['range']?.toString(),
      startDate: json['start_date']?.toString(),
      endDate: json['end_date']?.toString(),
      students: rawData
          .map((item) => HomeroomStudentAttendance.fromJson(item as Map<String, dynamic>))
          .toList(),
    );
  }
}

