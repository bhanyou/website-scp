import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/siswa_repository.dart';
import '../../domain/models/siswa_models.dart';

class SiswaAttendanceState {
  final bool isLoading;
  final String? errorMessage;
  final List<SiswaAttendanceRecord> allAttendances;
  final String selectedFilter; // 'Semua', 'Hadir', 'Sakit', 'Izin', 'Alpa'

  const SiswaAttendanceState({
    this.isLoading = false,
    this.errorMessage,
    this.allAttendances = const [],
    this.selectedFilter = 'Semua',
  });

  int get totalCount => allAttendances.length;
  int get hadirCount => allAttendances.where((a) => a.status.toLowerCase() == 'hadir').length;
  int get sakitCount => allAttendances.where((a) => a.status.toLowerCase() == 'sakit').length;
  int get izinCount => allAttendances.where((a) => a.status.toLowerCase() == 'izin').length;
  int get alpaCount => allAttendances.where((a) => a.status.toLowerCase() == 'alpa').length;

  int get percentage => totalCount > 0 ? ((hadirCount / totalCount) * 100).round() : 0;

  List<SiswaAttendanceRecord> get filteredAttendances {
    if (selectedFilter == 'Semua') return allAttendances;
    return allAttendances
        .where((a) => a.status.toLowerCase() == selectedFilter.toLowerCase())
        .toList();
  }

  SiswaAttendanceState copyWith({
    bool? isLoading,
    String? errorMessage,
    List<SiswaAttendanceRecord>? allAttendances,
    String? selectedFilter,
  }) {
    return SiswaAttendanceState(
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      allAttendances: allAttendances ?? this.allAttendances,
      selectedFilter: selectedFilter ?? this.selectedFilter,
    );
  }
}

final siswaAttendanceControllerProvider =
    StateNotifierProvider<SiswaAttendanceController, SiswaAttendanceState>((ref) {
  final repository = ref.watch(siswaRepositoryProvider);
  return SiswaAttendanceController(repository);
});

class SiswaAttendanceController extends StateNotifier<SiswaAttendanceState> {
  final SiswaRepository _repository;

  SiswaAttendanceController(this._repository) : super(const SiswaAttendanceState()) {
    loadAttendances();
  }

  Future<void> loadAttendances() async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final list = await _repository.getMyAttendances();
      state = state.copyWith(isLoading: false, allAttendances: list);
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
    }
  }

  void setFilter(String filter) {
    state = state.copyWith(selectedFilter: filter);
  }
}

