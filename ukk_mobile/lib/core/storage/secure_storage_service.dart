import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Layanan penyimpanan kredensial & token terenkripsi menggunakan flutter_secure_storage.
class SecureStorageService {
  final FlutterSecureStorage _storage;

  SecureStorageService({FlutterSecureStorage? storage})
      : _storage = storage ??
            const FlutterSecureStorage(
              aOptions: AndroidOptions(encryptedSharedPreferences: true),
              iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
            );

  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'cached_user';

  /// Mendapatkan token Sanctum
  Future<String?> getToken() async {
    try {
      return await _storage.read(key: _keyToken);
    } catch (_) {
      return null;
    }
  }

  /// Menyimpan token Sanctum
  Future<void> saveToken(String token) async {
    await _storage.write(key: _keyToken, value: token);
  }

  /// Menghapus token Sanctum
  Future<void> deleteToken() async {
    await _storage.delete(key: _keyToken);
  }

  /// Mendapatkan cache JSON user
  Future<String?> getCachedUserJson() async {
    try {
      return await _storage.read(key: _keyUser);
    } catch (_) {
      return null;
    }
  }

  /// Menyimpan cache JSON user
  Future<void> saveCachedUserJson(String jsonStr) async {
    await _storage.write(key: _keyUser, value: jsonStr);
  }

  /// Menghapus cache user
  Future<void> deleteCachedUser() async {
    await _storage.delete(key: _keyUser);
  }

  /// Membersihkan seluruh data sesi lokal
  Future<void> clearAll() async {
    try {
      await _storage.deleteAll();
    } catch (_) {
      await deleteToken();
      await deleteCachedUser();
    }
  }
}

