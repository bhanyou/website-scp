import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../storage/secure_storage_service.dart';

/// Interceptor untuk menyisipkan Bearer token, header Accept, logging debug,
/// dan mendeteksi respons 401 Unauthorized secara terpusat.
class AuthInterceptor extends QueuedInterceptor {
  final SecureStorageService storageService;
  final VoidCallback? onUnauthorized;

  AuthInterceptor({
    required this.storageService,
    this.onUnauthorized,
  });

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    options.headers['Accept'] = 'application/json';

    final token = await storageService.getToken();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }

    if (kDebugMode) {
      debugPrint('➡️ [HTTP] ${options.method} ${options.uri}');
      if (options.data != null && options.data is! FormData) {
        debugPrint('   Payload: ${options.data}');
      }
    }

    return handler.next(options);
  }

  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    if (kDebugMode) {
      debugPrint('✅ [HTTP] ${response.statusCode} ${response.requestOptions.uri}');
    }
    return handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    final statusCode = err.response?.statusCode;
    if (kDebugMode) {
      debugPrint('❌ [HTTP ERROR] $statusCode ${err.requestOptions.uri}');
      debugPrint('   Detail: ${err.response?.data}');
    }

    // Jika 401 Unauthorized (token kedaluwarsa atau invalid), bersihkan sesi dan trigger logout otomatis
    if (statusCode == 401) {
      await storageService.clearAll();
      onUnauthorized?.call();
    }

    return handler.next(err);
  }
}
