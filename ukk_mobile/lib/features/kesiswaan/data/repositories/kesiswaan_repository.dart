import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/di/core_providers.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/network/api_endpoints.dart';
import '../../../../core/network/api_exception.dart';
import '../../../homeroom/domain/models/homeroom_models.dart';
import '../../domain/models/kesiswaan_dashboard_model.dart';

final kesiswaanRepositoryProvider = Provider<KesiswaanRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return KesiswaanRepository(apiClient: apiClient);
});

class KesiswaanRepository {
  final ApiClient apiClient;

  KesiswaanRepository({required this.apiClient});

  /// Mengambil data statistik dashboard kesiswaan
  Future<KesiswaanDashboardData> getDashboard() async {
    try {
      final response = await apiClient.get(ApiEndpoints.kesiswaanDashboard);
      return KesiswaanDashboardData.fromJson(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal memuat dashboard kesiswaan: $e');
    }
  }

  /// Mengambil daftar semua permohonan izin siswa sekolah
  Future<List<HomeroomPermissionItem>> getPermissions() async {
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
      throw ApiException(message: 'Gagal memuat monitoring izin siswa: $e');
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

  /// Mengambil data rekap presensi seluruh kelas
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
