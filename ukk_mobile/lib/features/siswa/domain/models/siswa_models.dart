import '../../../../core/config/app_config.dart';

class SiswaProfile {
  final int id;
  final String name;
  final String nis;
  final String kelas;
  final String waliKelas;
  final String orangTua;

  const SiswaProfile({
    required this.id,
    required this.name,
    required this.nis,
    required this.kelas,
    required this.waliKelas,
    required this.orangTua,
  });

  factory SiswaProfile.fromJson(Map<String, dynamic>? json) {
    if (json == null) {
      return const SiswaProfile(
        id: 0,
        name: 'Siswa',
        nis: '-',
        kelas: '-',
        waliKelas: '-',
        orangTua: '-',
      );
    }
    return SiswaProfile(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name']?.toString() ?? 'Siswa',
      nis: json['nis']?.toString() ?? '-',
      kelas: json['kelas']?.toString() ?? '-',
      waliKelas: json['wali_kelas']?.toString() ?? '-',
      orangTua: json['orang_tua']?.toString() ?? '-',
    );
  }
}

class SiswaAttendanceStats {
  final int total;
  final int hadir;
  final int sakit;
  final int izin;
  final int alpa;
  final int percent;

  const SiswaAttendanceStats({
    this.total = 0,
    this.hadir = 0,
    this.sakit = 0,
    this.izin = 0,
    this.alpa = 0,
    this.percent = 0,
  });

  factory SiswaAttendanceStats.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const SiswaAttendanceStats();
    return SiswaAttendanceStats(
      total: json['total'] is int ? json['total'] as int : int.tryParse(json['total']?.toString() ?? '0') ?? 0,
      hadir: json['hadir'] is int ? json['hadir'] as int : int.tryParse(json['hadir']?.toString() ?? '0') ?? 0,
      sakit: json['sakit'] is int ? json['sakit'] as int : int.tryParse(json['sakit']?.toString() ?? '0') ?? 0,
      izin: json['izin'] is int ? json['izin'] as int : int.tryParse(json['izin']?.toString() ?? '0') ?? 0,
      alpa: json['alpa'] is int ? json['alpa'] as int : int.tryParse(json['alpa']?.toString() ?? '0') ?? 0,
      percent: json['percent'] is int ? json['percent'] as int : int.tryParse(json['percent']?.toString() ?? '0') ?? 0,
    );
  }
}

class SiswaTodaySchedule {
  final int id;
  final String mataPelajaran;
  final String guru;
  final String ruang;
  final String jamMulai;
  final String jamSelesai;
  final String jamKe;

  const SiswaTodaySchedule({
    required this.id,
    required this.mataPelajaran,
    required this.guru,
    required this.ruang,
    required this.jamMulai,
    required this.jamSelesai,
    required this.jamKe,
  });

  factory SiswaTodaySchedule.fromJson(Map<String, dynamic> json) {
    return SiswaTodaySchedule(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      mataPelajaran: json['mata_pelajaran']?.toString() ?? '-',
      guru: json['guru']?.toString() ?? '-',
      ruang: json['ruang']?.toString() ?? '-',
      jamMulai: json['jam_mulai']?.toString() ?? '',
      jamSelesai: json['jam_selesai']?.toString() ?? '',
      jamKe: json['jam_ke']?.toString() ?? '-',
    );
  }

  String get timeRangeText => '$jamMulai - $jamSelesai';
}

class SiswaPermissionItem {
  final int id;
  final String tanggal;
  final String tanggalSelesai;
  final int durasiHari;
  final String rentangTanggal;
  final String jenis; // 'Izin' atau 'Sakit'
  final String alasan;
  final String? dokumen;
  final String status; // 'Pending', 'Approved', 'Rejected'
  final String? catatanVerifikasi;

  const SiswaPermissionItem({
    required this.id,
    required this.tanggal,
    required this.tanggalSelesai,
    required this.durasiHari,
    required this.rentangTanggal,
    required this.jenis,
    required this.alasan,
    this.dokumen,
    required this.status,
    this.catatanVerifikasi,
  });

  factory SiswaPermissionItem.fromJson(Map<String, dynamic> json) {
    return SiswaPermissionItem(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
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
      catatanVerifikasi: json['catatan_verifikasi']?.toString(),
    );
  }

  bool get isPending => status.toLowerCase() == 'pending';
  bool get isApproved => status.toLowerCase() == 'approved' || status.toLowerCase() == 'disetujui';
  bool get isRejected => status.toLowerCase() == 'rejected' || status.toLowerCase() == 'ditolak';
  bool get isSakit => jenis.toLowerCase() == 'sakit';

  String get documentUrl => AppConfig.resolveStorageUrl(dokumen);
}

class SiswaAttendanceRecord {
  final int id;
  final String tanggal;
  final String mataPelajaran;
  final String kelas;
  final String jam;
  final String status; // 'Hadir', 'Sakit', 'Izin', 'Alpa'
  final String? catatan;

  const SiswaAttendanceRecord({
    required this.id,
    required this.tanggal,
    required this.mataPelajaran,
    required this.kelas,
    required this.jam,
    required this.status,
    this.catatan,
  });

  factory SiswaAttendanceRecord.fromJson(Map<String, dynamic> json) {
    return SiswaAttendanceRecord(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      tanggal: json['tanggal']?.toString() ?? '',
      mataPelajaran: json['mata_pelajaran']?.toString() ?? 'KBM',
      kelas: json['kelas']?.toString() ?? '-',
      jam: json['jam']?.toString() ?? '-',
      status: json['status']?.toString() ?? 'Hadir',
      catatan: json['catatan']?.toString(),
    );
  }
}

class SiswaDashboardData {
  final bool isLoading;
  final String? errorMessage;
  final SiswaProfile student;
  final String? activeCycleName;
  final String todayName;
  final String todayFormatted;
  final SiswaAttendanceStats attendanceStats;
  final List<SiswaTodaySchedule> todaySchedules;
  final List<SiswaPermissionItem> recentPermissions;

  const SiswaDashboardData({
    this.isLoading = false,
    this.errorMessage,
    this.student = const SiswaProfile(
      id: 0,
      name: 'Siswa',
      nis: '-',
      kelas: '-',
      waliKelas: '-',
      orangTua: '-',
    ),
    this.activeCycleName,
    this.todayName = '',
    this.todayFormatted = '',
    this.attendanceStats = const SiswaAttendanceStats(),
    this.todaySchedules = const [],
    this.recentPermissions = const [],
  });

  SiswaDashboardData copyWith({
    bool? isLoading,
    String? errorMessage,
    SiswaProfile? student,
    String? activeCycleName,
    String? todayName,
    String? todayFormatted,
    SiswaAttendanceStats? attendanceStats,
    List<SiswaTodaySchedule>? todaySchedules,
    List<SiswaPermissionItem>? recentPermissions,
  }) {
    return SiswaDashboardData(
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      student: student ?? this.student,
      activeCycleName: activeCycleName ?? this.activeCycleName,
      todayName: todayName ?? this.todayName,
      todayFormatted: todayFormatted ?? this.todayFormatted,
      attendanceStats: attendanceStats ?? this.attendanceStats,
      todaySchedules: todaySchedules ?? this.todaySchedules,
      recentPermissions: recentPermissions ?? this.recentPermissions,
    );
  }
}

