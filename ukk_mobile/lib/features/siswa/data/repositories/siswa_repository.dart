import 'dart:typed_data';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/di/core_providers.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/network/api_endpoints.dart';
import '../../../../core/network/api_exception.dart';
import '../../domain/models/siswa_models.dart';

final siswaRepositoryProvider = Provider<SiswaRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return SiswaRepository(apiClient: apiClient);
});

class SiswaRepository {
  final ApiClient apiClient;

  SiswaRepository({required this.apiClient});

  /// Mengambil data lengkap dashboard siswa
  Future<SiswaDashboardData> getDashboard() async {
    try {
      final response = await apiClient.get(ApiEndpoints.siswaDashboard);

      final student = SiswaProfile.fromJson(response['student'] as Map<String, dynamic>?);
      final cycle = response['active_cycle'] as Map<String, dynamic>?;
      final activeCycleName = cycle?['nama']?.toString();
      final todayName = response['today_name']?.toString() ?? '';
      final todayFormatted = response['today_formatted']?.toString() ?? '';
      final stats = SiswaAttendanceStats.fromJson(response['attendance_stats'] as Map<String, dynamic>?);

      final rawSchedules = response['today_schedules'] as List? ?? [];
      final schedules = rawSchedules
          .map((item) => SiswaTodaySchedule.fromJson(item as Map<String, dynamic>))
          .toList();

      final rawPermissions = response['recent_permissions'] as List? ?? [];
      final permissions = rawPermissions
          .map((item) => SiswaPermissionItem.fromJson(item as Map<String, dynamic>))
          .toList();

      return SiswaDashboardData(
        student: student,
        activeCycleName: activeCycleName,
        todayName: todayName,
        todayFormatted: todayFormatted,
        attendanceStats: stats,
        todaySchedules: schedules,
        recentPermissions: permissions,
      );
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal memuat dashboard siswa: $e');
    }
  }

  /// Mengambil seluruh riwayat pengajuan izin siswa
  Future<List<SiswaPermissionItem>> getMyPermissions() async {
    try {
      final response = await apiClient.get(ApiEndpoints.permissionRequests);

      final rawList = response['data'] as List? ?? [];
      return rawList
          .map((item) => SiswaPermissionItem.fromJson(item as Map<String, dynamic>))
          .toList();
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal memuat riwayat izin siswa: $e');
    }
  }

  /// Mengambil seluruh data catatan presensi siswa
  Future<List<SiswaAttendanceRecord>> getMyAttendances() async {
    try {
      final response = await apiClient.get(ApiEndpoints.attendances);

      final rawList = response['data'] as List? ?? [];
      return rawList
          .map((item) => SiswaAttendanceRecord.fromJson(item as Map<String, dynamic>))
          .toList();
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal memuat catatan presensi siswa: $e');
    }
  }

  /// Mengajukan permohonan izin/sakit baru dengan upload dokumen bukti
  Future<void> submitPermission({
    required int studentId,
    required String startDate,
    required String endDate,
    required String type,
    required String reason,
    required Uint8List documentBytes,
    required String documentFileName,
  }) async {
    try {
      final formData = FormData.fromMap({
        'student_id': studentId,
        'permission_date': startDate,
        'end_date': endDate,
        'type': type,
        'reason': reason,
        'document': MultipartFile.fromBytes(
          documentBytes,
          filename: documentFileName,
        ),
      });

      await apiClient.post(
        ApiEndpoints.permissionRequests,
        data: formData,
      );
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal mengirim pengajuan izin: $e');
    }
  }
}
