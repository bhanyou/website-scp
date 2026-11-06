import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/di/core_providers.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/network/api_endpoints.dart';
import '../../../../core/network/api_exception.dart';
import '../../domain/models/homeroom_models.dart';

final homeroomRepositoryProvider = Provider<HomeroomRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return HomeroomRepository(apiClient: apiClient);
});

class HomeroomRepository {
  final ApiClient apiClient;

  HomeroomRepository({required this.apiClient});

  /// Mengambil daftar permohonan izin siswa kelas binaan wali kelas
  Future<List<HomeroomPermissionItem>> getHomeroomPermissions() async {
    try {
      final response = await apiClient.get(ApiEndpoints.permissionRequests);

      if (response['data'] is List) {
        return (response['data'] as List)
            .map((item) => HomeroomPermissionItem.fromJson(item as Map<String, dynamic>))
            .toList();
      }
      return [];
    } on ApiException catch (e) {
      if (e.isNotFound) return [];
      rethrow;
    } catch (e) {
      throw ApiException(message: 'Gagal memuat daftar izin siswa: $e');
    }
  }

  /// Menyetujui permohonan izin siswa
  Future<void> approvePermission(int id) async {
    try {
      await apiClient.post(ApiEndpoints.permissionApprove(id));
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal menyetujui permohonan izin: $e');
    }
  }

  /// Menolak permohonan izin siswa dengan catatan
  Future<void> rejectPermission(int id, {String? note}) async {
    try {
      await apiClient.post(
        ApiEndpoints.permissionReject(id),
        data: {
          'verification_note': note,
        },
      );
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal menolak permohonan izin: $e');
    }
  }

  /// Mengambil data rekap presensi kelas binaan
  Future<HomeroomAttendanceData> getAttendanceRecap({
    int? classId,
    String? range,
    String? startDate,
    String? endDate,
    String? search,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (classId != null) queryParams['class_id'] = classId;
      if (range != null && range.isNotEmpty) queryParams['range'] = range;
      if (startDate != null && startDate.isNotEmpty) queryParams['start_date'] = startDate;
      if (endDate != null && endDate.isNotEmpty) queryParams['end_date'] = endDate;
      if (search != null && search.isNotEmpty) queryParams['search'] = search;

      final response = await apiClient.get(
        ApiEndpoints.attendances,
        queryParameters: queryParams,
      );

      return HomeroomAttendanceData.fromJson(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal memuat rekap presensi siswa: $e');
    }
  }
}

