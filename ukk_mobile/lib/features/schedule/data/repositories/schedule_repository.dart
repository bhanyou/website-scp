import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/di/core_providers.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/network/api_endpoints.dart';
import '../../../../core/network/api_exception.dart';
import '../../domain/models/schedule_model.dart';

final scheduleRepositoryProvider = Provider<ScheduleRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return ScheduleRepository(apiClient: apiClient);
});

class ScheduleResult {
  final BlockCycleModel? activeCycle;
  final List<ScheduleModel> schedules;

  const ScheduleResult({this.activeCycle, required this.schedules});
}

class ScheduleRepository {
  final ApiClient apiClient;

  ScheduleRepository({required this.apiClient});

  Future<ScheduleResult> getTeacherSchedules() async {
    try {
      final response = await apiClient.get(ApiEndpoints.schedules);

      BlockCycleModel? cycle;
      if (response['siklus_aktif'] is Map<String, dynamic>) {
        cycle = BlockCycleModel.fromJson(response['siklus_aktif'] as Map<String, dynamic>);
      }

      List<ScheduleModel> list = [];
      if (response['data'] is List) {
        list = (response['data'] as List)
            .map((item) => ScheduleModel.fromJson(item as Map<String, dynamic>))
            .toList();
      }

      return ScheduleResult(activeCycle: cycle, schedules: list);
    } on ApiException catch (e) {
      if (e.isNotFound) {
        return const ScheduleResult(activeCycle: null, schedules: []);
      }
      rethrow;
    } catch (e) {
      throw ApiException(message: 'Gagal memuat jadwal mengajar: $e');
    }
  }
}

