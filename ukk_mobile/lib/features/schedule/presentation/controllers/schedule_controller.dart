import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/schedule_repository.dart';
import '../../domain/models/schedule_model.dart';

class ScheduleState {
  final bool isLoading;
  final String? errorMessage;
  final BlockCycleModel? activeCycle;
  final List<ScheduleModel> schedules;
  final String selectedDay;

  const ScheduleState({
    this.isLoading = false,
    this.errorMessage,
    this.activeCycle,
    this.schedules = const [],
    this.selectedDay = 'Semua',
  });

  ScheduleState copyWith({
    bool? isLoading,
    String? errorMessage,
    BlockCycleModel? activeCycle,
    List<ScheduleModel>? schedules,
    String? selectedDay,
  }) {
    return ScheduleState(
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      activeCycle: activeCycle ?? this.activeCycle,
      schedules: schedules ?? this.schedules,
      selectedDay: selectedDay ?? this.selectedDay,
    );
  }

  List<ScheduleModel> get filteredSchedules {
    if (selectedDay == 'Semua') {
      return schedules;
    }
    return schedules.where((s) => s.hari.toLowerCase() == selectedDay.toLowerCase()).toList();
  }
}

class ScheduleController extends StateNotifier<ScheduleState> {
  final ScheduleRepository _repository;

  ScheduleController(this._repository) : super(const ScheduleState()) {
    loadSchedules();
  }

  Future<void> loadSchedules() async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final result = await _repository.getTeacherSchedules();
      state = state.copyWith(
        isLoading: false,
        activeCycle: result.activeCycle,
        schedules: result.schedules,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.toString().replaceAll('Exception: ', ''),
      );
    }
  }

  void setSelectedDay(String day) {
    state = state.copyWith(selectedDay: day);
  }
}

final scheduleControllerProvider =
    StateNotifierProvider.autoDispose<ScheduleController, ScheduleState>((ref) {
  final repository = ref.watch(scheduleRepositoryProvider);
  return ScheduleController(repository);
});

