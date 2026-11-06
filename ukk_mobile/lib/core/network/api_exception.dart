import 'package:dio/dio.dart';

/// Representasi error terpusat dari request HTTP / API Laravel Sanctum.
class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, List<String>>? validationErrors;
  final bool isUnauthorized;
  final bool isForbidden;
  final bool isNotFound;

  ApiException({
    required this.message,
    this.statusCode,
    this.validationErrors,
    this.isUnauthorized = false,
    this.isForbidden = false,
    this.isNotFound = false,
  });

  /// Factory untuk memetakan DioException menjadi ApiException yang rapi & aman.
  factory ApiException.fromDioError(DioException dioError) {
    switch (dioError.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return ApiException(
          message: 'Koneksi ke server timeout. Silakan periksa jaringan internet Anda.',
          statusCode: dioError.response?.statusCode,
        );

      case DioExceptionType.connectionError:
        return ApiException(
          message: 'Gagal terhubung ke server. Pastikan server backend Laravel berjalan dan URL API sesuai.',
          statusCode: dioError.response?.statusCode,
        );

      case DioExceptionType.badResponse:
        return _handleBadResponse(dioError.response);

      case DioExceptionType.cancel:
        return ApiException(
          message: 'Permintaan dibatalkan.',
          statusCode: dioError.response?.statusCode,
        );

      default:
        return ApiException(
          message: dioError.message ?? 'Terjadi kesalahan pada jaringan.',
          statusCode: dioError.response?.statusCode,
        );
    }
  }

  static ApiException _handleBadResponse(Response? response) {
    final status = response?.statusCode;
    final data = response?.data;

    String serverMessage = 'Terjadi kesalahan pada server ($status).';
    Map<String, List<String>>? valErrors;

    if (data is Map<String, dynamic>) {
      if (data['message'] != null && data['message'].toString().trim().isNotEmpty) {
        serverMessage = data['message'].toString();
      }

      // Handle validasi Laravel 422 errors
      if (data['errors'] is Map) {
        valErrors = {};
        final rawErrors = data['errors'] as Map<dynamic, dynamic>;
        rawErrors.forEach((key, value) {
          final strKey = key.toString();
          if (value is List) {
            valErrors![strKey] = value.map((e) => e.toString()).toList();
          } else if (value is String) {
            valErrors![strKey] = [value];
          }
        });
      }
    }

    if (status == 401) {
      return ApiException(
        message: serverMessage.isNotEmpty ? serverMessage : 'Sesi Anda telah berakhir. Silakan login kembali.',
        statusCode: 401,
        isUnauthorized: true,
      );
    }

    if (status == 403) {
      return ApiException(
        message: serverMessage.isNotEmpty ? serverMessage : 'Anda tidak memiliki hak akses untuk tindakan ini.',
        statusCode: 403,
        isForbidden: true,
      );
    }

    if (status == 404) {
      return ApiException(
        message: serverMessage.isNotEmpty ? serverMessage : 'Data yang diminta tidak ditemukan.',
        statusCode: 404,
        isNotFound: true,
      );
    }

    if (status == 422) {
      String detailedMessage = serverMessage;
      if (valErrors != null && valErrors.isNotEmpty) {
        final firstError = valErrors.values.first.firstOrNull;
        if (firstError != null && detailedMessage == 'The given data was invalid.') {
          detailedMessage = firstError;
        }
      }
      return ApiException(
        message: detailedMessage,
        statusCode: 422,
        validationErrors: valErrors,
      );
    }

    return ApiException(
      message: serverMessage,
      statusCode: status,
      validationErrors: valErrors,
    );
  }

  @override
  String toString() => message;
}

