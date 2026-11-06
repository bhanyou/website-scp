import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/kesiswaan_repository.dart';
import '../../domain/models/kesiswaan_dashboard_model.dart';

class KesiswaanDashboardState {
  final bool isLoading;
  final String? errorMessage;
  final KesiswaanDashboardData? data;

  const KesiswaanDashboardState({
    this.isLoading = false,
    this.errorMessage,
    this.data,
  });

  KesiswaanDashboardState copyWith({
    bool? isLoading,
    String? errorMessage,
    KesiswaanDashboardData? data,
  }) {
    return KesiswaanDashboardState(
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      data: data ?? this.data,
    );
  }
}

class KesiswaanDashboardNotifier extends StateNotifier<KesiswaanDashboardState> {
  final KesiswaanRepository _repository;

  KesiswaanDashboardNotifier(this._repository) : super(const KesiswaanDashboardState()) {
    loadDashboard();
  }

  Future<void> loadDashboard() async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final data = await _repository.getDashboard();
      state = state.copyWith(isLoading: false, data: data);
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
    }
  }
}

final kesiswaanDashboardControllerProvider =
    StateNotifierProvider<KesiswaanDashboardNotifier, KesiswaanDashboardState>((ref) {
  final repo = ref.watch(kesiswaanRepositoryProvider);
  return KesiswaanDashboardNotifier(repo);
});

