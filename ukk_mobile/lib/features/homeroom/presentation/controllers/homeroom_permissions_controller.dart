import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/homeroom_repository.dart';
import '../../domain/models/homeroom_models.dart';

class HomeroomPermissionsState {
  final bool isLoading;
  final bool isProcessing;
  final String? errorMessage;
  final String? successMessage;
  final List<HomeroomPermissionItem> allPermissions;
  final String filterStatus; // 'Semua', 'Pending', 'Approved', 'Rejected'

  const HomeroomPermissionsState({
    this.isLoading = false,
    this.isProcessing = false,
    this.errorMessage,
    this.successMessage,
    this.allPermissions = const [],
    this.filterStatus = 'Semua',
  });

  int get totalCount => allPermissions.length;
  int get pendingCount => allPermissions.where((p) => p.isPending).length;
  int get approvedCount => allPermissions.where((p) => p.isApproved).length;
  int get rejectedCount => allPermissions.where((p) => p.isRejected).length;

  List<HomeroomPermissionItem> get filteredPermissions {
    if (filterStatus == 'Pending') {
      return allPermissions.where((p) => p.isPending).toList();
    } else if (filterStatus == 'Approved') {
      return allPermissions.where((p) => p.isApproved).toList();
    } else if (filterStatus == 'Rejected') {
      return allPermissions.where((p) => p.isRejected).toList();
    }
    return allPermissions;
  }

  HomeroomPermissionsState copyWith({
    bool? isLoading,
    bool? isProcessing,
    String? errorMessage,
    String? successMessage,
    List<HomeroomPermissionItem>? allPermissions,
    String? filterStatus,
  }) {
    return HomeroomPermissionsState(
      isLoading: isLoading ?? this.isLoading,
      isProcessing: isProcessing ?? this.isProcessing,
      errorMessage: errorMessage,
      successMessage: successMessage,
      allPermissions: allPermissions ?? this.allPermissions,
      filterStatus: filterStatus ?? this.filterStatus,
    );
  }
}

final homeroomPermissionsControllerProvider =
    StateNotifierProvider<HomeroomPermissionsController, HomeroomPermissionsState>((ref) {
  final repository = ref.watch(homeroomRepositoryProvider);
  return HomeroomPermissionsController(repository);
});

class HomeroomPermissionsController extends StateNotifier<HomeroomPermissionsState> {
  final HomeroomRepository _repository;

  HomeroomPermissionsController(this._repository)
      : super(const HomeroomPermissionsState()) {
    loadPermissions();
  }

  Future<void> loadPermissions() async {
    state = state.copyWith(isLoading: true, errorMessage: null, successMessage: null);
    try {
      final list = await _repository.getHomeroomPermissions();
      state = state.copyWith(
        isLoading: false,
        allPermissions: list,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.toString(),
      );
    }
  }

  void setFilterStatus(String status) {
    state = state.copyWith(filterStatus: status);
  }

  Future<bool> approvePermission(int id) async {
    state = state.copyWith(isProcessing: true, errorMessage: null, successMessage: null);
    try {
      await _repository.approvePermission(id);
      // Refresh list
      final list = await _repository.getHomeroomPermissions();
      state = state.copyWith(
        isProcessing: false,
        allPermissions: list,
        successMessage: 'Permohonan izin berhasil disetujui dan disinkronkan ke presensi.',
      );
      return true;
    } catch (e) {
      state = state.copyWith(
        isProcessing: false,
        errorMessage: e.toString(),
      );
      return false;
    }
  }

  Future<bool> rejectPermission(int id, String? note) async {
    state = state.copyWith(isProcessing: true, errorMessage: null, successMessage: null);
    try {
      await _repository.rejectPermission(id, note: note);
      // Refresh list
      final list = await _repository.getHomeroomPermissions();
      state = state.copyWith(
        isProcessing: false,
        allPermissions: list,
        successMessage: 'Permohonan izin telah ditolak.',
      );
      return true;
    } catch (e) {
      state = state.copyWith(
        isProcessing: false,
        errorMessage: e.toString(),
      );
      return false;
    }
  }
}

