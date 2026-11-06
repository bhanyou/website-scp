import '../config/app_config.dart';

/// Helper pengelolaan file dan resolusi URL storage Laravel.
class FileHelper {
  FileHelper._();

  static String resolveStorageUrl(String? path) {
    return AppConfig.resolveStorageUrl(path);
  }

  static String formatBytes(int bytes) {
    if (bytes <= 0) return '0 B';
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) return '${(bytes / 1024).toStringAsFixed(1)} KB';
    return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
  }
}
