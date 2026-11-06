import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/di/core_providers.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/network/api_endpoints.dart';
import '../../../../core/network/api_exception.dart';
import '../../domain/models/journal_models.dart';

final journalRepositoryProvider = Provider<JournalRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return JournalRepository(apiClient: apiClient);
});

class JournalRepository {
  final ApiClient apiClient;

  JournalRepository({required this.apiClient});

  /// Mengambil daftar jadwal yang siap diisi jurnal
  Future<List<JournalSchedule>> getJournalSchedules() async {
    try {
      final response = await apiClient.get(ApiEndpoints.journals);
      if (response['data'] is List) {
        return (response['data'] as List)
            .map((item) => JournalSchedule.fromJson(item as Map<String, dynamic>))
            .toList();
      }
      return [];
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal memuat jadwal untuk jurnal: $e');
    }
  }

  /// Mengambil riwayat pengisian jurnal guru
  Future<List<JournalHistoryItem>> getJournalHistory() async {
    try {
      final response = await apiClient.get('${ApiEndpoints.journals}/history');
      if (response['data'] is List) {
        return (response['data'] as List)
            .map((item) => JournalHistoryItem.fromJson(item as Map<String, dynamic>))
            .toList();
      }
      return [];
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal memuat riwayat jurnal: $e');
    }
  }

  /// Mengambil detail jurnal beserta catatan presensi siswa
  Future<JournalDetail> getJournalDetail(int id) async {
    try {
      final response = await apiClient.get('${ApiEndpoints.journals}/$id');
      if (response['data'] is Map<String, dynamic>) {
        return JournalDetail.fromJson(response['data'] as Map<String, dynamic>);
      }
      throw ApiException(message: 'Format data jurnal tidak valid');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal memuat detail jurnal: $e');
    }
  }

  /// Menyimpan jurnal KBM dan presensi siswa dengan foto kamera realtime
  Future<Map<String, dynamic>> submitJournal({
    required int scheduleId,
    required String journalDate,
    required String material,
    required String activity,
    String? notes,
    required Uint8List photoBytes,
    String? filename,
    required Map<int, String> attendanceMap,
    void Function(int count, int total)? onProgress,
  }) async {
    try {
      final formData = FormData();

      formData.fields.add(MapEntry('schedule_id', scheduleId.toString()));
      formData.fields.add(MapEntry('journal_date', journalDate));
      formData.fields.add(MapEntry('material', material));
      formData.fields.add(MapEntry('activity', activity));

      if (notes != null && notes.trim().isNotEmpty) {
        formData.fields.add(MapEntry('notes', notes.trim()));
      }

      // Masukkan presensi setiap siswa: attendance[student_id] = 'Hadir' | 'Sakit' | 'Izin' | 'Alpa'
      attendanceMap.forEach((studentId, status) {
        formData.fields.add(MapEntry('attendance[$studentId]', status));
      });

      // Tambahkan foto dari kamera realtime HP (kompatibel Mobile & Web)
      final actualFilename = filename ?? 'journal_${DateTime.now().millisecondsSinceEpoch}.jpg';
      formData.files.add(
        MapEntry(
          'photo',
          MultipartFile.fromBytes(photoBytes, filename: actualFilename),
        ),
      );

      final response = await apiClient.post(
        ApiEndpoints.journals,
        data: formData,
        onSendProgress: onProgress,
      );

      return response;
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal menyimpan jurnal KBM: $e');
    }
  }

  /// Menghapus jurnal jika diperlukan
  Future<void> deleteJournal(int id) async {
    try {
      await apiClient.delete('${ApiEndpoints.journals}/$id');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(message: 'Gagal menghapus jurnal: $e');
    }
  }
}
