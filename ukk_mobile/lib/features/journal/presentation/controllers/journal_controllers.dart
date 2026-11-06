import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../data/repositories/journal_repository.dart';
import '../../domain/models/journal_models.dart';

// ==========================================
// 1. Controller Riwayat Jurnal (JournalHistory)
// ==========================================

class JournalHistoryState {
  final bool isLoading;
  final String? errorMessage;
  final List<JournalHistoryItem> journals;
  final String searchQuery;

  const JournalHistoryState({
    this.isLoading = false,
    this.errorMessage,
    this.journals = const [],
    this.searchQuery = '',
  });

  JournalHistoryState copyWith({
    bool? isLoading,
    String? errorMessage,
    List<JournalHistoryItem>? journals,
    String? searchQuery,
  }) {
    return JournalHistoryState(
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      journals: journals ?? this.journals,
      searchQuery: searchQuery ?? this.searchQuery,
    );
  }

  List<JournalHistoryItem> get filteredJournals {
    if (searchQuery.trim().isEmpty) return journals;
    final q = searchQuery.toLowerCase();
    return journals.where((j) {
      return j.mataPelajaran.toLowerCase().contains(q) ||
          j.kelas.toLowerCase().contains(q) ||
          j.materi.toLowerCase().contains(q) ||
          j.tanggal.contains(q);
    }).toList();
  }
}

class JournalHistoryController extends StateNotifier<JournalHistoryState> {
  final JournalRepository _repository;

  JournalHistoryController(this._repository) : super(const JournalHistoryState()) {
    loadHistory();
  }

  Future<void> loadHistory() async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final list = await _repository.getJournalHistory();
      state = state.copyWith(isLoading: false, journals: list);
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.toString().replaceAll('Exception: ', ''),
      );
    }
  }

  void setSearchQuery(String query) {
    state = state.copyWith(searchQuery: query);
  }

  Future<bool> deleteJournal(int id) async {
    try {
      await _repository.deleteJournal(id);
      state = state.copyWith(
        journals: state.journals.where((j) => j.id != id).toList(),
      );
      return true;
    } catch (e) {
      state = state.copyWith(
        errorMessage: e.toString().replaceAll('Exception: ', ''),
      );
      return false;
    }
  }
}

final journalHistoryControllerProvider =
    StateNotifierProvider.autoDispose<JournalHistoryController, JournalHistoryState>((ref) {
  final repo = ref.watch(journalRepositoryProvider);
  return JournalHistoryController(repo);
});

// ==========================================
// 2. Controller Form Input Jurnal KBM
// ==========================================

class JournalCreateState {
  final bool isLoadingSchedules;
  final bool isSubmitting;
  final String? errorMessage;
  final String? successMessage;
  final List<JournalSchedule> schedules;
  final JournalSchedule? selectedSchedule;
  final DateTime selectedDate;
  final Uint8List? photoBytes;
  final String? photoFilename;
  final DateTime? photoTimestamp;
  final Map<int, String> attendances; // studentId -> 'Hadir' | 'Sakit' | 'Izin' | 'Alpa'

  JournalCreateState({
    this.isLoadingSchedules = false,
    this.isSubmitting = false,
    this.errorMessage,
    this.successMessage,
    this.schedules = const [],
    this.selectedSchedule,
    DateTime? selectedDate,
    this.photoBytes,
    this.photoFilename,
    this.photoTimestamp,
    this.attendances = const {},
  }) : selectedDate = selectedDate ?? DateTime.now();

  JournalCreateState copyWith({
    bool? isLoadingSchedules,
    bool? isSubmitting,
    String? errorMessage,
    String? successMessage,
    List<JournalSchedule>? schedules,
    JournalSchedule? selectedSchedule,
    DateTime? selectedDate,
    Uint8List? photoBytes,
    String? photoFilename,
    DateTime? photoTimestamp,
    Map<int, String>? attendances,
    bool clearPhoto = false,
  }) {
    return JournalCreateState(
      isLoadingSchedules: isLoadingSchedules ?? this.isLoadingSchedules,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      errorMessage: errorMessage,
      successMessage: successMessage,
      schedules: schedules ?? this.schedules,
      selectedSchedule: selectedSchedule ?? this.selectedSchedule,
      selectedDate: selectedDate ?? this.selectedDate,
      photoBytes: clearPhoto ? null : (photoBytes ?? this.photoBytes),
      photoFilename: clearPhoto ? null : (photoFilename ?? this.photoFilename),
      photoTimestamp: clearPhoto ? null : (photoTimestamp ?? this.photoTimestamp),
      attendances: attendances ?? this.attendances,
    );
  }

  int get hadirCount => attendances.values.where((v) => v == 'Hadir').length;
  int get sakitCount => attendances.values.where((v) => v == 'Sakit').length;
  int get izinCount => attendances.values.where((v) => v == 'Izin').length;
  int get alpaCount => attendances.values.where((v) => v == 'Alpa').length;
}

class JournalCreateController extends StateNotifier<JournalCreateState> {
  final JournalRepository _repository;

  JournalCreateController(this._repository) : super(JournalCreateState());

  Future<void> loadSchedules({int? preselectedScheduleId}) async {
    state = state.copyWith(isLoadingSchedules: true, errorMessage: null);
    try {
      final list = await _repository.getJournalSchedules();
      JournalSchedule? preselected;
      if (preselectedScheduleId != null && list.isNotEmpty) {
        preselected = list.firstWhere(
          (s) => s.id == preselectedScheduleId,
          orElse: () => list.firstWhere((s) => s.canFill, orElse: () => list.first),
        );
      } else if (list.isNotEmpty) {
        // Prioritaskan jadwal yang bisa diisi hari ini
        preselected = list.firstWhere((s) => s.canFill, orElse: () => list.first);
      }

      final Map<int, String> initialAttendance = {};
      if (preselected != null) {
        for (final student in preselected.students) {
          initialAttendance[student.id] = 'Hadir';
        }
      }

      state = state.copyWith(
        isLoadingSchedules: false,
        schedules: list,
        selectedSchedule: preselected,
        attendances: initialAttendance,
      );
    } catch (e) {
      state = state.copyWith(
        isLoadingSchedules: false,
        errorMessage: e.toString().replaceAll('Exception: ', ''),
      );
    }
  }

  void selectSchedule(JournalSchedule schedule) {
    final Map<int, String> initialAttendance = {};
    for (final student in schedule.students) {
      initialAttendance[student.id] = 'Hadir';
    }
    state = state.copyWith(
      selectedSchedule: schedule,
      attendances: initialAttendance,
    );
  }

  void setDate(DateTime date) {
    state = state.copyWith(selectedDate: date);
  }

  void setPhoto(Uint8List bytes, String filename) {
    state = state.copyWith(
      photoBytes: bytes,
      photoFilename: filename,
      photoTimestamp: DateTime.now(),
    );
  }

  void clearPhoto() {
    state = state.copyWith(clearPhoto: true);
  }

  void setAttendance(int studentId, String status) {
    final newMap = Map<int, String>.from(state.attendances);
    newMap[studentId] = status;
    state = state.copyWith(attendances: newMap);
  }

  void setAllAttendance(String status) {
    if (state.selectedSchedule == null) return;
    final newMap = <int, String>{};
    for (final s in state.selectedSchedule!.students) {
      newMap[s.id] = status;
    }
    state = state.copyWith(attendances: newMap);
  }

  Future<bool> submit({
    required String material,
    required String activity,
    String? notes,
  }) async {
    if (state.selectedSchedule == null) {
      state = state.copyWith(errorMessage: 'Pilih jadwal mengajar terlebih dahulu.');
      return false;
    }
    if (material.trim().isEmpty) {
      state = state.copyWith(errorMessage: 'Materi KBM wajib diisi.');
      return false;
    }
    if (activity.trim().isEmpty) {
      state = state.copyWith(errorMessage: 'Aktivitas pembelajaran wajib diisi.');
      return false;
    }
    if (state.photoBytes == null) {
      state = state.copyWith(
        errorMessage: 'Dokumentasi foto dari kamera realtime wajib diambil.',
      );
      return false;
    }
    if (state.selectedSchedule!.students.isNotEmpty && state.attendances.isEmpty) {
      state = state.copyWith(errorMessage: 'Presensi siswa wajib dicatat.');
      return false;
    }

    state = state.copyWith(isSubmitting: true, errorMessage: null, successMessage: null);

    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(state.selectedDate);
      await _repository.submitJournal(
        scheduleId: state.selectedSchedule!.id,
        journalDate: dateStr,
        material: material.trim(),
        activity: activity.trim(),
        notes: notes?.trim(),
        photoBytes: state.photoBytes!,
        filename: state.photoFilename,
        attendanceMap: state.attendances,
      );

      state = state.copyWith(
        isSubmitting: false,
        successMessage: 'Jurnal KBM dan presensi berhasil disimpan!',
      );
      return true;
    } catch (e) {
      state = state.copyWith(
        isSubmitting: false,
        errorMessage: e.toString().replaceAll('Exception: ', ''),
      );
      return false;
    }
  }
}

final journalCreateControllerProvider =
    StateNotifierProvider.autoDispose<JournalCreateController, JournalCreateState>((ref) {
  final repo = ref.watch(journalRepositoryProvider);
  return JournalCreateController(repo);
});

// Detail Controller Family Provider
final journalDetailProvider =
    FutureProvider.family.autoDispose<JournalDetail, int>((ref, id) async {
  final repo = ref.watch(journalRepositoryProvider);
  return repo.getJournalDetail(id);
});
