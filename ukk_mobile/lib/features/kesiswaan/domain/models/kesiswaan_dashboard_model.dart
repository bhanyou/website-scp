import '../../../homeroom/domain/models/homeroom_models.dart';

/// Model response data untuk Dashboard Kesiswaan sesuai desktop SCP
class KesiswaanDashboardData {
  final String todayName;
  final String todayFormatted;
  final String todayFullFormatted;
  final int totalStudents;
  final int pendingPermissionsCount;
  final int approvedPermissionsCount;
  final int rejectedPermissionsCount;
  final int todayAttendancesCount;
  final int presentCount;
  final int sickCount;
  final int permitCount;
  final int absentCount;
  final List<HomeroomPermissionItem> recentPermissions;

  const KesiswaanDashboardData({
    required this.todayName,
    required this.todayFormatted,
    required this.todayFullFormatted,
    required this.totalStudents,
    required this.pendingPermissionsCount,
    required this.approvedPermissionsCount,
    required this.rejectedPermissionsCount,
    required this.todayAttendancesCount,
    required this.presentCount,
    required this.sickCount,
    required this.permitCount,
    required this.absentCount,
    required this.recentPermissions,
  });

  factory KesiswaanDashboardData.fromJson(Map<String, dynamic> json) {
    final summary = json['attendance_summary'] as Map<String, dynamic>? ?? {};
    final recentList = json['recent_permissions'] as List<dynamic>? ?? [];

    return KesiswaanDashboardData(
      todayName: json['today_name']?.toString() ?? 'Hari Ini',
      todayFormatted: json['today_formatted']?.toString() ?? '',
      todayFullFormatted: json['today_full_formatted']?.toString() ?? '',
      totalStudents: json['total_students'] is int
          ? json['total_students'] as int
          : int.tryParse(json['total_students']?.toString() ?? '0') ?? 0,
      pendingPermissionsCount: json['pending_permissions_count'] is int
          ? json['pending_permissions_count'] as int
          : int.tryParse(json['pending_permissions_count']?.toString() ?? '0') ?? 0,
      approvedPermissionsCount: json['approved_permissions_count'] is int
          ? json['approved_permissions_count'] as int
          : int.tryParse(json['approved_permissions_count']?.toString() ?? '0') ?? 0,
      rejectedPermissionsCount: json['rejected_permissions_count'] is int
          ? json['rejected_permissions_count'] as int
          : int.tryParse(json['rejected_permissions_count']?.toString() ?? '0') ?? 0,
      todayAttendancesCount: json['today_attendances_count'] is int
          ? json['today_attendances_count'] as int
          : int.tryParse(json['today_attendances_count']?.toString() ?? '0') ?? 0,
      presentCount: summary['hadir'] is int
          ? summary['hadir'] as int
          : int.tryParse(summary['hadir']?.toString() ?? '0') ?? 0,
      sickCount: summary['sakit'] is int
          ? summary['sakit'] as int
          : int.tryParse(summary['sakit']?.toString() ?? '0') ?? 0,
      permitCount: summary['izin'] is int
          ? summary['izin'] as int
          : int.tryParse(summary['izin']?.toString() ?? '0') ?? 0,
      absentCount: summary['alpa'] is int
          ? summary['alpa'] as int
          : int.tryParse(summary['alpa']?.toString() ?? '0') ?? 0,
      recentPermissions: recentList
          .map((item) => HomeroomPermissionItem.fromJson(item as Map<String, dynamic>))
          .toList(),
    );
  }
}

