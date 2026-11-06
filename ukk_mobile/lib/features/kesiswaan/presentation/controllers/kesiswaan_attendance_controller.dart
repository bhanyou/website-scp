import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../homeroom/domain/models/homeroom_models.dart';
import '../../data/repositories/kesiswaan_repository.dart';

class KesiswaanAttendanceState {
  final bool isLoading;
  final String? errorMessage;
  final HomeroomAttendanceData? data;
  final int? selectedClassId;
  final String selectedRange; // 'all', 'today', '7_days', 'this_week', '30_days', 'this_month', 'custom'
  final String? customStartDate;
  final String? customEndDate;
  final String searchQuery;
  final String statusTab; // 'all', 'hadir', 'sakit', 'izin', 'alpa'

  const KesiswaanAttendanceState({
    this.isLoading = false,
    this.errorMessage,
    this.data,
    this.selectedClassId,
    this.selectedRange = 'all',
    this.customStartDate,
    this.customEndDate,
    this.searchQuery = '',
    this.statusTab = 'all',
  });

  List<HomeroomStudentAttendance> get filteredStudents {
    if (data == null) return [];
    var list = data!.students;

    // Filter status tab
    if (statusTab == 'hadir') {
      list = list.where((s) => s.hadir > 0).toList();
    } else if (statusTab == 'sakit') {
      list = list.where((s) => s.sakit > 0).toList();
    } else if (statusTab == 'izin') {
      list = list.where((s) => s.izin > 0).toList();
    } else if (statusTab == 'alpa') {
      list = list.where((s) => s.alpa > 0).toList();
    }

    // Filter search query
    if (searchQuery.isNotEmpty) {
      final q = searchQuery.toLowerCase();
      list = list.where((s) {
        return s.name.toLowerCase().contains(q) ||
            s.nis.toLowerCase().contains(q) ||
            s.className.toLowerCase().contains(q);
      }).toList();
    }

    return list;
  }

  KesiswaanAttendanceState copyWith({
    bool? isLoading,
    String? errorMessage,
    HomeroomAttendanceData? data,
    int? selectedClassId,
    bool clearClassId = false,
    String? selectedRange,
    String? customStartDate,
    String? customEndDate,
    String? searchQuery,
    String? statusTab,
  }) {
    return KesiswaanAttendanceState(
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      data: data ?? this.data,
      selectedClassId: clearClassId ? null : (selectedClassId ?? this.selectedClassId),
      selectedRange: selectedRange ?? this.selectedRange,
      customStartDate: customStartDate ?? this.customStartDate,
      customEndDate: customEndDate ?? this.customEndDate,
      searchQuery: searchQuery ?? this.searchQuery,
      statusTab: statusTab ?? this.statusTab,
    );
  }
}

class KesiswaanAttendanceNotifier extends StateNotifier<KesiswaanAttendanceState> {
  final KesiswaanRepository _repository;

  KesiswaanAttendanceNotifier(this._repository) : super(const KesiswaanAttendanceState()) {
    loadAttendanceRecap();
  }

  Future<void> loadAttendanceRecap() async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final result = await _repository.getAttendanceRecap(
        classId: state.selectedClassId,
        range: state.selectedRange != 'all' ? state.selectedRange : null,
        startDate: state.selectedRange == 'custom' ? state.customStartDate : null,
        endDate: state.selectedRange == 'custom' ? state.customEndDate : null,
      );
      state = state.copyWith(isLoading: false, data: result);
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
    }
  }

  void setSelectedClass(int? classId) {
    if (classId == null) {
      state = state.copyWith(clearClassId: true);
    } else {
      state = state.copyWith(selectedClassId: classId);
    }
    loadAttendanceRecap();
  }

  void setRange(String range, {String? startDate, String? endDate}) {
    state = state.copyWith(
      selectedRange: range,
      customStartDate: startDate,
      customEndDate: endDate,
    );
    loadAttendanceRecap();
  }

  void setSearchQuery(String query) {
    state = state.copyWith(searchQuery: query);
  }

  void setStatusTab(String tab) {
    state = state.copyWith(statusTab: tab);
  }
}

final kesiswaanAttendanceControllerProvider =
    StateNotifierProvider<KesiswaanAttendanceNotifier, KesiswaanAttendanceState>((ref) {
  final repo = ref.watch(kesiswaanRepositoryProvider);
  return KesiswaanAttendanceNotifier(repo);
});
