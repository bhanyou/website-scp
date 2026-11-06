/// Konfigurasi global aplikasi School Class Progress.
/// Terhubung langsung dengan Laravel REST API (Live Hosting scp-bhan.web.id).
class AppConfig {
  AppConfig._();

  static const String appName = 'School Class Progress';

  /// URL Live Server Produksi (DomaiNesia HTTPS)
  static const String defaultLiveApiUrl = 'https://scp-bhan.web.id/api';
  static const String defaultLocalApiUrl = 'http://127.0.0.1:8000/api';
  static const String defaultAndroidEmulatorApiUrl = 'http://10.0.2.2:8000/api';

  static String? _overrideBaseUrl;

  /// Memungkinkan pengalihan server dinamis (misal toggle Live vs Lokal)
  static void setOverrideBaseUrl(String? url) {
    if (url != null && url.trim().isNotEmpty) {
      _overrideBaseUrl = url.trim();
    } else {
      _overrideBaseUrl = null;
    }
  }

  /// Base URL aktif untuk API
  static String get apiBaseUrl {
    if (_overrideBaseUrl != null && _overrideBaseUrl!.isNotEmpty) {
      return _overrideBaseUrl!;
    }
    const fromEnv = String.fromEnvironment('API_BASE_URL');
    if (fromEnv.isNotEmpty) {
      return fromEnv;
    }
    // Default langsung ke server live produksi scp-bhan.web.id
    // sehingga aplikasi mobile langsung tersinkronisasi penuh dengan web.
    return defaultLiveApiUrl;
  }

  /// Menghasilkan URL root server (tanpa suffix `/api`)
  static String get serverOrigin {
    final uri = Uri.tryParse(apiBaseUrl);
    if (uri == null) return 'https://scp-bhan.web.id';
    final port = (uri.hasPort && uri.port != 80 && uri.port != 443) ? ':${uri.port}' : '';
    return '${uri.scheme}://${uri.host}$port';
  }

  /// Membangun full URL untuk aset publik (foto jurnal / dokumen surat izin) di Laravel storage.
  /// Format storage Laravel: {serverOrigin}/storage/{path}
  static String resolveStorageUrl(String? path) {
    if (path == null || path.trim().isEmpty) return '';
    final cleanPath = path.trim();
    if (cleanPath.startsWith('http://') || cleanPath.startsWith('https://')) {
      return cleanPath;
    }
    final normalized = cleanPath.startsWith('/') ? cleanPath.substring(1) : cleanPath;
    if (normalized.startsWith('storage/')) {
      return '$serverOrigin/$normalized';
    }
    return '$serverOrigin/storage/$normalized';
  }
}
