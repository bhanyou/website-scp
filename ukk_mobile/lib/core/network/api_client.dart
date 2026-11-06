import 'package:dio/dio.dart';
import '../config/app_config.dart';
import '../storage/secure_storage_service.dart';
import 'api_exception.dart';
import 'auth_interceptor.dart';

/// Client HTTP utama berbasis Dio untuk komunikasi ke Laravel REST API.
class ApiClient {
  late final Dio _dio;
  final SecureStorageService storageService;
  final void Function()? onUnauthorized;

  ApiClient({
    required this.storageService,
    this.onUnauthorized,
    Dio? customDio,
  }) {
    if (customDio != null) {
      _dio = customDio;
    } else {
      _dio = Dio(
        BaseOptions(
          baseUrl: AppConfig.apiBaseUrl,
          connectTimeout: const Duration(seconds: 15),
          receiveTimeout: const Duration(seconds: 20),
          sendTimeout: const Duration(seconds: 25),
          headers: {
            'Accept': 'application/json',
          },
          validateStatus: (status) => status != null && status >= 200 && status < 300,
        ),
      );

      _dio.interceptors.add(
        AuthInterceptor(
          storageService: storageService,
          onUnauthorized: onUnauthorized,
        ),
      );
    }
  }

  Dio get dio => _dio;

  /// Memperbarui baseUrl saat user mengganti koneksi server (Live / Lokal)
  void updateBaseUrl(String newUrl) {
    _dio.options.baseUrl = newUrl;
  }

  /// HTTP GET
  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      final response = await _dio.get(path, queryParameters: queryParameters);
      return _parseData(response.data);
    } on DioException catch (e) {
      throw ApiException.fromDioError(e);
    } catch (e) {
      throw ApiException(message: 'Terjadi kesalahan: $e');
    }
  }

  /// HTTP POST
  Future<Map<String, dynamic>> post(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    void Function(int count, int total)? onSendProgress,
  }) async {
    try {
      final response = await _dio.post(
        path,
        data: data,
        queryParameters: queryParameters,
        onSendProgress: onSendProgress,
      );
      return _parseData(response.data);
    } on DioException catch (e) {
      throw ApiException.fromDioError(e);
    } catch (e) {
      throw ApiException(message: 'Terjadi kesalahan: $e');
    }
  }

  /// HTTP PUT
  Future<Map<String, dynamic>> put(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      final response = await _dio.put(
        path,
        data: data,
        queryParameters: queryParameters,
      );
      return _parseData(response.data);
    } on DioException catch (e) {
      throw ApiException.fromDioError(e);
    } catch (e) {
      throw ApiException(message: 'Terjadi kesalahan: $e');
    }
  }

  /// HTTP DELETE
  Future<Map<String, dynamic>> delete(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      final response = await _dio.delete(
        path,
        data: data,
        queryParameters: queryParameters,
      );
      return _parseData(response.data);
    } on DioException catch (e) {
      throw ApiException.fromDioError(e);
    } catch (e) {
      throw ApiException(message: 'Terjadi kesalahan: $e');
    }
  }

  Map<String, dynamic> _parseData(dynamic data) {
    if (data is Map<String, dynamic>) {
      return data;
    } else if (data is Map) {
      return Map<String, dynamic>.from(data);
    }
    return {'data': data};
  }
}

