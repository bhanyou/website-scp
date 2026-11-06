import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../journal/data/repositories/journal_repository.dart';
import '../../../journal/domain/models/journal_models.dart';
import '../../../schedule/data/repositories/schedule_repository.dart';
import '../../../schedule/domain/models/schedule_model.dart';

class GuruDashboardData {
  final bool isLoading;
  final String? errorMessage;
  final BlockCycleModel? activeCycle;
  final List<ScheduleModel> allSchedules;
  final List<ScheduleModel> todaySchedules;
  final List<ScheduleModel> pendingJournalAlerts;
  final List<JournalHistoryItem> recentJournals;
  final Set<int> filledScheduleIdsToday;
  final int totalSchedules;
  final int totalJournals;
  final String todayName;
  final String todayFormatted;

  const GuruDashboardData({
    this.isLoading = false,
    this.errorMessage,
    this.activeCycle,
    this.allSchedules = const [],
    this.todaySchedules = const [],
    this.pendingJournalAlerts = const [],
    this.recentJournals = const [],
    this.filledScheduleIdsToday = const {},
    this.totalSchedules = 0,
    this.totalJournals = 0,
    this.todayName = '',
    this.todayFormatted = '',
  });

  GuruDashboardData copyWith({
    bool? isLoading,
    String? errorMessage,
    BlockCycleModel? activeCycle,
    List<ScheduleModel>? allSchedules,
    List<ScheduleModel>? todaySchedules,
    List<ScheduleModel>? pendingJournalAlerts,
    List<JournalHistoryItem>? recentJournals,
    Set<int>? filledScheduleIdsToday,
    int? totalSchedules,
    int? totalJournals,
    String? todayName,
    String? todayFormatted,
  }) {
    return GuruDashboardData(
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      activeCycle: activeCycle ?? this.activeCycle,
      allSchedules: allSchedules ?? this.allSchedules,
      todaySchedules: todaySchedules ?? this.todaySchedules,
      pendingJournalAlerts: pendingJournalAlerts ?? this.pendingJournalAlerts,
      recentJournals: recentJournals ?? this.recentJournals,
      filledScheduleIdsToday: filledScheduleIdsToday ?? this.filledScheduleIdsToday,
      totalSchedules: totalSchedules ?? this.totalSchedules,
      totalJournals: totalJournals ?? this.totalJournals,
      todayName: todayName ?? this.todayName,
      todayFormatted: todayFormatted ?? this.todayFormatted,
    );
  }
}

class GuruDashboardController extends StateNotifier<GuruDashboardData> {
  final ScheduleRepository _scheduleRepo;
  final JournalRepository _journalRepo;

  GuruDashboardController(this._scheduleRepo, this._journalRepo)
      : super(const GuruDashboardData()) {
    loadDashboardData();
  }

  static String _getIndonesianDay(DateTime date) {
    switch (date.weekday) {
      case DateTime.monday:
        return 'Senin';
      case DateTime.tuesday:
        return 'Selasa';
      case DateTime.wednesday:
        return 'Rabu';
      case DateTime.thursday:
        return 'Kamis';
      case DateTime.friday:
        return 'Jumat';
      case DateTime.saturday:
        return 'Sabtu';
      case DateTime.sunday:
        return 'Minggu';
      default:
        return 'Senin';
    }
  }

  Future<void> loadDashboardData() async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    final now = DateTime.now();
    final todayName = _getIndonesianDay(now);
    final todayStr = DateFormat('yyyy-MM-dd').format(now);
    final todayFormatted = DateFormat('EEEE, dd MMMM yyyy', 'id_ID').format(now);

    try {
      // 1. Ambil data jadwal siklus aktif
      final scheduleResult = await _scheduleRepo.getTeacherSchedules();
      final schedules = scheduleResult.schedules;
      final activeCycle = scheduleResult.activeCycle;

      // 2. Ambil riwayat jurnal guru
      final journals = await _journalRepo.getJournalHistory();

      // Filter jadwal hari ini
      final todaySchedules = schedules
          .where((s) => s.hari.toLowerCase() == todayName.toLowerCase())
          .toList();

      // Cek jadwal hari ini yang sudah terisi jurnalnya
      final filledScheduleIds = <int>{};
      for (final j in journals) {
        if (j.tanggal == todayStr) {
          filledScheduleIds.add(j.scheduleId);
        }
      }

      // Jadwal hari ini yang BELUM diisi jurnal
      final pendingAlerts = todaySchedules
          .where((s) => !filledScheduleIds.contains(s.id))
          .toList();

      // Ambil 5 riwayat jurnal terbaru
      final recent = journals.take(5).toList();

      state = state.copyWith(
        isLoading: false,
        activeCycle: activeCycle,
        allSchedules: schedules,
        todaySchedules: todaySchedules,
        pendingJournalAlerts: pendingAlerts,
        recentJournals: recent,
        filledScheduleIdsToday: filledScheduleIds,
        totalSchedules: schedules.length,
        totalJournals: journals.length,
        todayName: todayName,
        todayFormatted: todayFormatted,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.toString().replaceAll('Exception: ', ''),
      );
    }
  }
}

final guruDashboardControllerProvider =
    StateNotifierProvider.autoDispose<GuruDashboardController, GuruDashboardData>((ref) {
  final scheduleRepo = ref.watch(scheduleRepositoryProvider);
  final journalRepo = ref.watch(journalRepositoryProvider);
  return GuruDashboardController(scheduleRepo, journalRepo);
});

