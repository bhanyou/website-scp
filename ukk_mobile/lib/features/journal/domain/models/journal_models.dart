import '../../../../core/config/app_config.dart';

/// Siswa dalam jadwal KBM untuk presensi
class JournalStudent {
  final int id;
  final String name;
  final String nis;

  const JournalStudent({
    required this.id,
    required this.name,
    required this.nis,
  });

  factory JournalStudent.fromJson(Map<String, dynamic> json) {
    return JournalStudent(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name']?.toString() ?? 'Siswa',
      nis: json['nis']?.toString() ?? '',
    );
  }
}

/// Jadwal mengajar untuk input jurnal KBM
class JournalSchedule {
  final int id;
  final String hari;
  final String jamMulai;
  final String jamSelesai;
  final String mataPelajaran;
  final String kelas;
  final String ruang;
  final String siklus;
  final bool canFill;
  final bool hasJournal;
  final int? journalId;
  final bool isToday;
  final bool isPassed;
  final String statusLabel;
  final List<JournalStudent> students;

  const JournalSchedule({
    required this.id,
    required this.hari,
    required this.jamMulai,
    required this.jamSelesai,
    required this.mataPelajaran,
    required this.kelas,
    required this.ruang,
    required this.siklus,
    this.canFill = true,
    this.hasJournal = false,
    this.journalId,
    this.isToday = false,
    this.isPassed = false,
    this.statusLabel = '',
    required this.students,
  });

  factory JournalSchedule.fromJson(Map<String, dynamic> json) {
    final rawStudents = json['students'] as List? ?? [];
    return JournalSchedule(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      hari: json['hari']?.toString() ?? '',
      jamMulai: json['jam_mulai']?.toString() ?? '',
      jamSelesai: json['jam_selesai']?.toString() ?? '',
      mataPelajaran: json['mata_pelajaran']?.toString() ?? 'Mata Pelajaran',
      kelas: json['kelas']?.toString() ?? '-',
      ruang: json['ruang']?.toString() ?? '-',
      siklus: json['siklus']?.toString() ?? '',
      canFill: json['can_fill'] == true,
      hasJournal: json['has_journal'] == true,
      journalId: json['journal_id'] is int ? json['journal_id'] as int : int.tryParse(json['journal_id']?.toString() ?? ''),
      isToday: json['is_today'] == true,
      isPassed: json['is_passed'] == true,
      statusLabel: json['status_label']?.toString() ?? '',
      students: rawStudents
          .map((item) => JournalStudent.fromJson(item as Map<String, dynamic>))
          .toList(),
    );
  }

  String get displayName => '$mataPelajaran • $kelas ($hari $jamMulai-$jamSelesai)';
}

/// Item riwayat jurnal guru
class JournalHistoryItem {
  final int id;
  final int scheduleId;
  final String tanggal;
  final String materi;
  final String aktivitas;
  final String? catatan;
  final String? foto;
  final String? waktuSubmit;
  final String mataPelajaran;
  final String kelas;
  final String ruang;
  final String hari;
  final String jamMulai;
  final String jamSelesai;
  final String? siklus;

  const JournalHistoryItem({
    required this.id,
    required this.scheduleId,
    required this.tanggal,
    required this.materi,
    required this.aktivitas,
    this.catatan,
    this.foto,
    this.waktuSubmit,
    required this.mataPelajaran,
    required this.kelas,
    required this.ruang,
    required this.hari,
    required this.jamMulai,
    required this.jamSelesai,
    this.siklus,
  });

  String? get photoUrl => AppConfig.resolveStorageUrl(foto);

  factory JournalHistoryItem.fromJson(Map<String, dynamic> json) {
    return JournalHistoryItem(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      scheduleId: json['schedule_id'] is int
          ? json['schedule_id'] as int
          : int.tryParse(json['schedule_id']?.toString() ?? '0') ?? 0,
      tanggal: json['tanggal']?.toString() ?? '',
      materi: json['materi']?.toString() ?? '',
      aktivitas: json['aktivitas']?.toString() ?? '',
      catatan: json['catatan']?.toString(),
      foto: json['foto']?.toString(),
      waktuSubmit: json['waktu_submit']?.toString(),
      mataPelajaran: json['mata_pelajaran']?.toString() ?? 'Mata Pelajaran',
      kelas: json['kelas']?.toString() ?? '-',
      ruang: json['ruang']?.toString() ?? '-',
      hari: json['hari']?.toString() ?? '-',
      jamMulai: json['jam_mulai']?.toString() ?? '-',
      jamSelesai: json['jam_selesai']?.toString() ?? '-',
      siklus: json['siklus']?.toString(),
    );
  }
}

/// Detail catatan presensi siswa dalam jurnal
class JournalAttendanceDetail {
  final int studentId;
  final String namaSiswa;
  final String nis;
  final String status; // Hadir, Sakit, Izin, Alpa
  final String? checkIn;
  final String? note;

  const JournalAttendanceDetail({
    required this.studentId,
    required this.namaSiswa,
    required this.nis,
    required this.status,
    this.checkIn,
    this.note,
  });

  factory JournalAttendanceDetail.fromJson(Map<String, dynamic> json) {
    return JournalAttendanceDetail(
      studentId: json['student_id'] is int
          ? json['student_id'] as int
          : int.tryParse(json['student_id']?.toString() ?? '0') ?? 0,
      namaSiswa: json['nama_siswa']?.toString() ?? 'Siswa',
      nis: json['nis']?.toString() ?? '',
      status: json['status']?.toString() ?? 'Alpa',
      checkIn: json['check_in']?.toString(),
      note: json['note']?.toString(),
    );
  }
}

/// Detail lengkap jurnal KBM
class JournalDetail {
  final int id;
  final int scheduleId;
  final int teacherId;
  final String journalDate;
  final String material;
  final String activity;
  final String? notes;
  final String? photoPath;
  final String? submittedAt;
  final Map<String, dynamic> schedule;
  final List<JournalAttendanceDetail> presensi;

  const JournalDetail({
    required this.id,
    required this.scheduleId,
    required this.teacherId,
    required this.journalDate,
    required this.material,
    required this.activity,
    this.notes,
    this.photoPath,
    this.submittedAt,
    required this.schedule,
    required this.presensi,
  });

  String? get photoUrl => AppConfig.resolveStorageUrl(photoPath);

  factory JournalDetail.fromJson(Map<String, dynamic> json) {
    final rawPresensi = json['presensi'] as List? ?? [];
    return JournalDetail(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      scheduleId: json['schedule_id'] is int
          ? json['schedule_id'] as int
          : int.tryParse(json['schedule_id']?.toString() ?? '0') ?? 0,
      teacherId: json['teacher_id'] is int
          ? json['teacher_id'] as int
          : int.tryParse(json['teacher_id']?.toString() ?? '0') ?? 0,
      journalDate: json['journal_date']?.toString() ?? '',
      material: json['material']?.toString() ?? '',
      activity: json['activity']?.toString() ?? '',
      notes: json['notes']?.toString(),
      photoPath: json['photo_path']?.toString(),
      submittedAt: json['submitted_at']?.toString(),
      schedule: json['schedule'] is Map<String, dynamic> ? json['schedule'] as Map<String, dynamic> : {},
      presensi: rawPresensi
          .map((item) => JournalAttendanceDetail.fromJson(item as Map<String, dynamic>))
          .toList(),
    );
  }
}

