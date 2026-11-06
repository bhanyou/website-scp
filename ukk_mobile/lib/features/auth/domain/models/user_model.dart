import 'dart:convert';
import '../../../../core/config/app_config.dart';

/// Model pengguna (User) aplikasi School Class Progress.
class UserModel {
  final int id;
  final String name;
  final String email;
  final String role; // 'guru', 'kesiswaan', 'siswa', 'waka'
  final String? photo;
  final bool isHomeroom;
  final List<String> homeroomClasses;
  final int? studentId;
  final String? studentNis;
  final String? studentClass;

  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.photo,
    this.isHomeroom = false,
    this.homeroomClasses = const [],
    this.studentId,
    this.studentNis,
    this.studentClass,
  });

  bool get isTeacher => role.toLowerCase() == 'guru';
  bool get isKesiswaan => role.toLowerCase() == 'kesiswaan';
  bool get isStudent => role.toLowerCase() == 'siswa';
  bool get isParent => role.toLowerCase() == 'ortu' || role.toLowerCase() == 'parent';
  bool get isStudentOrParent => isStudent || isParent;
  bool get isWaka => role.toLowerCase() == 'waka';
  bool get isWaliKelas => isTeacher && isHomeroom;

  /// Full URL foto profil dari storage Laravel
  String? get avatarUrl {
    if (photo == null || photo!.trim().isEmpty) return null;
    return AppConfig.resolveStorageUrl(photo);
  }

  String get roleDisplayName {
    if (isTeacher) {
      return isHomeroom ? 'Guru • Wali Kelas' : 'Guru Pengajar';
    }
    if (isKesiswaan) return 'Tim Kesiswaan & BK';
    if (isStudent) return 'Peserta Didik (Siswa)';
    if (isParent) return 'Orang Tua / Wali Murid';
    if (isWaka) return 'Waka Kurikulum';
    return role.toUpperCase();
  }

  factory UserModel.fromJson(Map<String, dynamic> json) {
    List<String> classes = [];
    if (json['homeroom_classes'] is List) {
      classes = (json['homeroom_classes'] as List).map((e) => e.toString()).toList();
    }

    final studentData = json['student'] as Map<String, dynamic>?;

    return UserModel(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      role: json['role']?.toString() ?? 'siswa',
      photo: json['photo']?.toString(),
      isHomeroom: json['is_homeroom'] == true || json['is_homeroom'] == 1,
      homeroomClasses: classes,
      studentId: studentData != null
          ? (studentData['id'] is int ? studentData['id'] as int : int.tryParse(studentData['id']?.toString() ?? '0'))
          : null,
      studentNis: studentData?['nis']?.toString(),
      studentClass: studentData?['class_name']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'photo': photo,
      'is_homeroom': isHomeroom,
      'homeroom_classes': homeroomClasses,
    };
  }

  String toJsonString() => jsonEncode(toJson());

  factory UserModel.fromJsonString(String source) => UserModel.fromJson(jsonDecode(source) as Map<String, dynamic>);

  UserModel copyWith({
    int? id,
    String? name,
    String? email,
    String? role,
    String? photo,
    bool? isHomeroom,
    List<String>? homeroomClasses,
  }) {
    return UserModel(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      role: role ?? this.role,
      photo: photo ?? this.photo,
      isHomeroom: isHomeroom ?? this.isHomeroom,
      homeroomClasses: homeroomClasses ?? this.homeroomClasses,
    );
  }
}
