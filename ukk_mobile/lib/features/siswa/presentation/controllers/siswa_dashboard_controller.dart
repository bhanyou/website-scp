import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/siswa_repository.dart';
import '../../domain/models/siswa_models.dart';

final siswaDashboardControllerProvider =
    StateNotifierProvider<SiswaDashboardController, SiswaDashboardData>((ref) {
  final repository = ref.watch(siswaRepositoryProvider);
  return SiswaDashboardController(repository);
});

class SiswaDashboardController extends StateNotifier<SiswaDashboardData> {
  final SiswaRepository _repository;

  SiswaDashboardController(this._repository) : super(const SiswaDashboardData()) {
    loadDashboard();
  }

  Future<void> loadDashboard() async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final data = await _repository.getDashboard();
      state = data.copyWith(isLoading: false);
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.toString().replaceAll('Exception: ', ''),
      );
    }
  }
}

