import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../homeroom/domain/models/homeroom_models.dart';
import '../../data/repositories/kesiswaan_repository.dart';

class KesiswaanPermissionsState {
  final bool isLoading;
  final bool isActionLoading;
  final String? errorMessage;
  final List<HomeroomPermissionItem> permissions;
  final String statusFilter; // 'all', 'Pending', 'Approved', 'Rejected'
  final String typeFilter; // 'all', 'Sakit', 'Izin'
  final String searchQuery;

  const KesiswaanPermissionsState({
    this.isLoading = false,
    this.isActionLoading = false,
    this.errorMessage,
    this.permissions = const [],
    this.statusFilter = 'all',
    this.typeFilter = 'all',
    this.searchQuery = '',
  });

  List<HomeroomPermissionItem> get filteredPermissions {
    return permissions.where((item) {
      // Filter status
      if (statusFilter != 'all') {
        if (item.status.toLowerCase() != statusFilter.toLowerCase()) return false;
      }
      // Filter tipe
      if (typeFilter != 'all') {
        if (item.jenis.toLowerCase() != typeFilter.toLowerCase()) return false;
      }
      // Filter search
      if (searchQuery.isNotEmpty) {
        final q = searchQuery.toLowerCase();
        final matchesName = item.siswa.toLowerCase().contains(q);
        final matchesNis = item.nis.toLowerCase().contains(q);
        final matchesClass = item.kelas.toLowerCase().contains(q);
        if (!matchesName && !matchesNis && !matchesClass) return false;
      }
      return true;
    }).toList();
  }

  int get pendingCount => permissions.where((p) => p.isPending).length;
  int get approvedCount => permissions.where((p) => p.isApproved).length;
  int get rejectedCount => permissions.where((p) => p.isRejected).length;

  KesiswaanPermissionsState copyWith({
    bool? isLoading,
    bool? isActionLoading,
    String? errorMessage,
    List<HomeroomPermissionItem>? permissions,
    String? statusFilter,
    String? typeFilter,
    String? searchQuery,
  }) {
    return KesiswaanPermissionsState(
      isLoading: isLoading ?? this.isLoading,
      isActionLoading: isActionLoading ?? this.isActionLoading,
      errorMessage: errorMessage,
      permissions: permissions ?? this.permissions,
      statusFilter: statusFilter ?? this.statusFilter,
      typeFilter: typeFilter ?? this.typeFilter,
      searchQuery: searchQuery ?? this.searchQuery,
    );
  }
}

class KesiswaanPermissionsNotifier extends StateNotifier<KesiswaanPermissionsState> {
  final KesiswaanRepository _repository;

  KesiswaanPermissionsNotifier(this._repository) : super(const KesiswaanPermissionsState()) {
    loadPermissions();
  }

  Future<void> loadPermissions() async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final items = await _repository.getPermissions();
      state = state.copyWith(isLoading: false, permissions: items);
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
    }
  }

  void setStatusFilter(String filter) {
    state = state.copyWith(statusFilter: filter);
  }

  void setTypeFilter(String filter) {
    state = state.copyWith(typeFilter: filter);
  }

  void setSearchQuery(String query) {
    state = state.copyWith(searchQuery: query);
  }

  Future<bool> approvePermission(int id) async {
    state = state.copyWith(isActionLoading: true);
    try {
      await _repository.approvePermission(id);
      await loadPermissions();
      state = state.copyWith(isActionLoading: false);
      return true;
    } catch (e) {
      state = state.copyWith(isActionLoading: false, errorMessage: e.toString());
      return false;
    }
  }

  Future<bool> rejectPermission(int id, {String? note}) async {
    state = state.copyWith(isActionLoading: true);
    try {
      await _repository.rejectPermission(id, note: note);
      await loadPermissions();
      state = state.copyWith(isActionLoading: false);
      return true;
    } catch (e) {
      state = state.copyWith(isActionLoading: false, errorMessage: e.toString());
      return false;
    }
  }
}

final kesiswaanPermissionsControllerProvider =
    StateNotifierProvider<KesiswaanPermissionsNotifier, KesiswaanPermissionsState>((ref) {
  final repo = ref.watch(kesiswaanRepositoryProvider);
  return KesiswaanPermissionsNotifier(repo);
});

