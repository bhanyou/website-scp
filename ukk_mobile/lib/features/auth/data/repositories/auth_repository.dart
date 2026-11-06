import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/di/core_providers.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/network/api_endpoints.dart';
import '../../../../core/network/api_exception.dart';
import '../../../../core/storage/secure_storage_service.dart';
import '../../domain/models/user_model.dart';

/// Provider AuthRepository
final authRepositoryProvider = Provider<AuthRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final storageService = ref.watch(secureStorageProvider);
  return AuthRepository(apiClient: apiClient, storageService: storageService);
});

/// Repository autentikasi pengguna ke Laravel Sanctum
class AuthRepository {
  final ApiClient apiClient;
  final SecureStorageService storageService;

  AuthRepository({
    required this.apiClient,
    required this.storageService,
  });

  /// Melakukan login ke backend dengan identity (NIP/NISN/Username/Email) & password
  Future<UserModel> login({
    required String identity,
    required String password,
  }) async {
    try {
      final response = await apiClient.post(
        ApiEndpoints.login,
        data: {
          'identity': identity.trim(),
          'password': password,
        },
      );

      final token = response['token']?.toString();
      if (token == null || token.isEmpty) {
        throw ApiException(message: 'Server tidak mengembalikan token autentikasi.');
      }

      final userData = response['user'];
      if (userData is! Map<String, dynamic>) {
        throw ApiException(message: 'Data pengguna tidak valid.');
      }

      final user = UserModel.fromJson(userData);

      // Simpan token dan cache data pengguna
      await storageService.saveToken(token);
      await storageService.saveCachedUserJson(user.toJsonString());

      return user;
    } on ApiException {
      rethrow;
    } catch (e) {
      throw ApiException(message: 'Gagal melakukan login: $e');
    }
  }

  /// Mengambil data user aktif dari endpoint GET /user
  Future<UserModel> getUser() async {
    try {
      final response = await apiClient.get(ApiEndpoints.user);
      final userData = response['user'] ?? response['data'] ?? response;

      if (userData is! Map<String, dynamic>) {
        throw ApiException(message: 'Gagal memuat profil pengguna.');
      }

      final user = UserModel.fromJson(userData);
      await storageService.saveCachedUserJson(user.toJsonString());
      return user;
    } on ApiException {
      rethrow;
    } catch (e) {
      throw ApiException(message: 'Gagal mengambil data user: $e');
    }
  }

  /// Memulihkan sesi pengguna dari penyimpanan lokal saat aplikasi dibuka
  Future<UserModel?> restoreSession() async {
    try {
      final token = await storageService.getToken();
      if (token == null || token.isEmpty) {
        return null;
      }

      // 1. Ambil cache lokal user terlebih dahulu untuk render instan
      final cachedJson = await storageService.getCachedUserJson();
      UserModel? cachedUser;
      if (cachedJson != null && cachedJson.isNotEmpty) {
        try {
          cachedUser = UserModel.fromJsonString(cachedJson);
        } catch (_) {}
      }

      // 2. Perbarui profil terbaru di background
      try {
        final freshUser = await getUser();
        return freshUser;
      } catch (_) {
        // Jika offline tapi ada cache lokal, gunakan cache lokal
        if (cachedUser != null) {
          return cachedUser;
        }
        return null;
      }
    } catch (_) {
      return null;
    }
  }

  /// Melakukan logout (server-side & bersihkan token lokal)
  Future<void> logout() async {
    try {
      await apiClient.post(ApiEndpoints.logout);
    } catch (_) {
      // Abaikan error jaringan saat logout agar pengguna tetap bisa keluar
    } finally {
      await storageService.clearAll();
    }
  }
}
