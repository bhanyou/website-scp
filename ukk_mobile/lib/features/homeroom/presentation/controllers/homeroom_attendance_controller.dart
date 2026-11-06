import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/homeroom_repository.dart';
import '../../domain/models/homeroom_models.dart';

class HomeroomAttendanceState {
  final bool isLoading;
  final String? errorMessage;
  final HomeroomAttendanceData data;
  final String selectedRange; // 'all', 'today', '7_days', '30_days'
  final String searchQuery;

  const HomeroomAttendanceState({
    this.isLoading = false,
    this.errorMessage,
    this.data = const HomeroomAttendanceData(),
    this.selectedRange = 'all',
    this.searchQuery = '',
  });

  List<HomeroomStudentAttendance> get filteredStudents {
    if (searchQuery.trim().isEmpty) {
      return data.students;
    }
    final q = searchQuery.toLowerCase().trim();
    return data.students.where((s) {
      return s.name.toLowerCase().contains(q) || s.nis.toLowerCase().contains(q);
    }).toList();
  }

  HomeroomAttendanceState copyWith({
    bool? isLoading,
    String? errorMessage,
    HomeroomAttendanceData? data,
    String? selectedRange,
    String? searchQuery,
  }) {
    return HomeroomAttendanceState(
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      data: data ?? this.data,
      selectedRange: selectedRange ?? this.selectedRange,
      searchQuery: searchQuery ?? this.searchQuery,
    );
  }
}

final homeroomAttendanceControllerProvider =
    StateNotifierProvider<HomeroomAttendanceController, HomeroomAttendanceState>((ref) {
  final repository = ref.watch(homeroomRepositoryProvider);
  return HomeroomAttendanceController(repository);
});

class HomeroomAttendanceController extends StateNotifier<HomeroomAttendanceState> {
  final HomeroomRepository _repository;

  HomeroomAttendanceController(this._repository)
      : super(const HomeroomAttendanceState()) {
    loadAttendanceRecap();
  }

  Future<void> loadAttendanceRecap() async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final result = await _repository.getAttendanceRecap(
        range: state.selectedRange,
        search: state.searchQuery.trim().isNotEmpty ? state.searchQuery.trim() : null,
      );
      state = state.copyWith(
        isLoading: false,
        data: result,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.toString(),
      );
    }
  }

  void setRange(String range) {
    if (state.selectedRange == range) return;
    state = state.copyWith(selectedRange: range);
    loadAttendanceRecap();
  }

  void setSearchQuery(String query) {
    state = state.copyWith(searchQuery: query);
  }
}

