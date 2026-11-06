import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../network/api_client.dart';
import '../storage/secure_storage_service.dart';

/// Provider instance SecureStorageService
final secureStorageProvider = Provider<SecureStorageService>((ref) {
  return SecureStorageService();
});

/// Provider callback saat terjadi HTTP 401 Unauthorized
final onUnauthorizedCallbackProvider = Provider<VoidCallback?>((ref) => null);

/// Provider instance ApiClient utama
final apiClientProvider = Provider<ApiClient>((ref) {
  final storage = ref.watch(secureStorageProvider);
  final onUnauthorized = ref.watch(onUnauthorizedCallbackProvider);

  return ApiClient(
    storageService: storage,
    onUnauthorized: onUnauthorized,
  );
});

/// Provider Mode Tema (Light/Dark/System)
final themeModeProvider = StateProvider<ThemeMode>((ref) => ThemeMode.system);
