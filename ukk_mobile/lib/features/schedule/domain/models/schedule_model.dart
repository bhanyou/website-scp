/// Model Siklus Blok Aktif
class BlockCycleModel {
  final int id;
  final String nama;
  final String? mulai;
  final String? selesai;
  final bool isLocked;

  const BlockCycleModel({
    required this.id,
    required this.nama,
    this.mulai,
    this.selesai,
    this.isLocked = false,
  });

  factory BlockCycleModel.fromJson(Map<String, dynamic> json) {
    return BlockCycleModel(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      nama: json['nama']?.toString() ?? '',
      mulai: json['mulai']?.toString(),
      selesai: json['selesai']?.toString(),
      isLocked: json['is_locked'] == true || json['is_locked'] == 1,
    );
  }
}

/// Model Jadwal Mengajar KBM Guru
class ScheduleModel {
  final int id;
  final String guru;
  final String hari;
  final String jamMulai;
  final String jamSelesai;
  final String sesi;
  final int jamKeMulai;
  final int jamKeSelesai;
  final String mataPelajaran;
  final String kelas;
  final String ruang;
  final String siklus;

  const ScheduleModel({
    required this.id,
    required this.guru,
    required this.hari,
    required this.jamMulai,
    required this.jamSelesai,
    required this.sesi,
    required this.jamKeMulai,
    required this.jamKeSelesai,
    required this.mataPelajaran,
    required this.kelas,
    required this.ruang,
    required this.siklus,
  });

  factory ScheduleModel.fromJson(Map<String, dynamic> json) {
    return ScheduleModel(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      guru: json['guru']?.toString() ?? '',
      hari: json['hari']?.toString() ?? '',
      jamMulai: json['jam_mulai']?.toString() ?? '',
      jamSelesai: json['jam_selesai']?.toString() ?? '',
      sesi: json['sesi']?.toString() ?? '',
      jamKeMulai: json['jam_ke_mulai'] is int ? json['jam_ke_mulai'] as int : int.tryParse(json['jam_ke_mulai']?.toString() ?? '1') ?? 1,
      jamKeSelesai: json['jam_ke_selesai'] is int ? json['jam_ke_selesai'] as int : int.tryParse(json['jam_ke_selesai']?.toString() ?? '1') ?? 1,
      mataPelajaran: json['mata_pelajaran']?.toString() ?? '',
      kelas: json['kelas']?.toString() ?? '',
      ruang: json['ruang']?.toString() ?? '',
      siklus: json['siklus']?.toString() ?? '',
    );
  }

  String get timeRangeText {
    final start = jamMulai.length >= 5 ? jamMulai.substring(0, 5) : jamMulai;
    final end = jamSelesai.length >= 5 ? jamSelesai.substring(0, 5) : jamSelesai;
    return '$start - $end WIB';
  }

  String get periodText => 'Jam ke-$jamKeMulai s/d $jamKeSelesai';
}

