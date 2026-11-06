import 'dart:typed_data';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/siswa_repository.dart';
import '../../domain/models/siswa_models.dart';

class SiswaPermissionState {
  final bool isLoading;
  final bool isSubmitting;
  final String? errorMessage;
  final String? successMessage;
  final List<SiswaPermissionItem> allPermissions;
  final String filterStatus; // 'Semua', 'Pending', 'Approved', 'Rejected'

  const SiswaPermissionState({
    this.isLoading = false,
    this.isSubmitting = false,
    this.errorMessage,
    this.successMessage,
    this.allPermissions = const [],
    this.filterStatus = 'Semua',
  });

  int get totalCount => allPermissions.length;
  int get pendingCount => allPermissions.where((p) => p.isPending).length;
  int get approvedCount => allPermissions.where((p) => p.isApproved).length;
  int get rejectedCount => allPermissions.where((p) => p.isRejected).length;

  List<SiswaPermissionItem> get filteredPermissions {
    if (filterStatus == 'Pending') {
      return allPermissions.where((p) => p.isPending).toList();
    } else if (filterStatus == 'Approved') {
      return allPermissions.where((p) => p.isApproved).toList();
    } else if (filterStatus == 'Rejected') {
      return allPermissions.where((p) => p.isRejected).toList();
    }
    return allPermissions;
  }

  SiswaPermissionState copyWith({
    bool? isLoading,
    bool? isSubmitting,
    String? errorMessage,
    String? successMessage,
    List<SiswaPermissionItem>? allPermissions,
    String? filterStatus,
  }) {
    return SiswaPermissionState(
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      errorMessage: errorMessage,
      successMessage: successMessage,
      allPermissions: allPermissions ?? this.allPermissions,
      filterStatus: filterStatus ?? this.filterStatus,
    );
  }
}

final siswaPermissionControllerProvider =
    StateNotifierProvider<SiswaPermissionController, SiswaPermissionState>((ref) {
  final repository = ref.watch(siswaRepositoryProvider);
  return SiswaPermissionController(repository);
});

class SiswaPermissionController extends StateNotifier<SiswaPermissionState> {
  final SiswaRepository _repository;

  SiswaPermissionController(this._repository) : super(const SiswaPermissionState()) {
    loadPermissions();
  }

  Future<void> loadPermissions() async {
    state = state.copyWith(isLoading: true, errorMessage: null, successMessage: null);
    try {
      final list = await _repository.getMyPermissions();
      state = state.copyWith(isLoading: false, allPermissions: list);
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
    }
  }

  void setFilterStatus(String status) {
    state = state.copyWith(filterStatus: status);
  }

  Future<bool> submitPermission({
    required int studentId,
    required String startDate,
    required String endDate,
    required String type,
    required String reason,
    required Uint8List documentBytes,
    required String documentFileName,
  }) async {
    state = state.copyWith(isSubmitting: true, errorMessage: null, successMessage: null);
    try {
      await _repository.submitPermission(
        studentId: studentId,
        startDate: startDate,
        endDate: endDate,
        type: type,
        reason: reason,
        documentBytes: documentBytes,
        documentFileName: documentFileName,
      );

      final list = await _repository.getMyPermissions();
      state = state.copyWith(
        isSubmitting: false,
        allPermissions: list,
        successMessage: 'Pengajuan izin berhasil dikirim.',
      );
      return true;
    } catch (e) {
      state = state.copyWith(isSubmitting: false, errorMessage: e.toString());
      return false;
    }
  }
}

