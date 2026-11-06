import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/api_exception.dart';
import '../../data/repositories/auth_repository.dart';
import 'auth_state.dart';

/// Provider AuthState dan AuthController
final authControllerProvider = StateNotifierProvider<AuthController, AuthState>((ref) {
  final repository = ref.watch(authRepositoryProvider);
  return AuthController(repository: repository);
});

class AuthController extends StateNotifier<AuthState> {
  final AuthRepository repository;

  AuthController({required this.repository}) : super(AuthState.initial());

  /// Memulihkan sesi pengguna dari penyimpanan terenkripsi
  Future<void> restoreSession() async {
    try {
      final user = await repository.restoreSession();
      if (user != null) {
        state = AuthState.authenticated(user);
      } else {
        state = AuthState.unauthenticated();
      }
    } catch (_) {
      state = AuthState.unauthenticated();
    }
  }

  /// Melakukan login ke sistem
  Future<bool> login({
    required String identity,
    required String password,
  }) async {
    state = AuthState.authenticating();

    try {
      final user = await repository.login(
        identity: identity,
        password: password,
      );
      state = AuthState.authenticated(user);
      return true;
    } on ApiException catch (e) {
      state = AuthState.error(e.message);
      return false;
    } catch (e) {
      state = AuthState.error('Terjadi kesalahan saat login: $e');
      return false;
    }
  }

  /// Logout normal oleh pengguna
  Future<void> logout() async {
    state = AuthState.authenticating();
    await repository.logout();
    state = AuthState.unauthenticated();
  }

  /// Logout otomatis saat token kedaluwarsa (HTTP 401)
  void forceLogoutOnUnauthorized() {
    state = AuthState.unauthenticated();
  }
}
