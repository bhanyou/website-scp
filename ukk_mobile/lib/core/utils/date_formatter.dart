import 'package:intl/intl.dart';

/// Helper formatting tanggal dan waktu dengan locale Indonesia (id_ID).
class DateFormatter {
  DateFormatter._();

  static DateTime? tryParse(dynamic value) {
    if (value == null) return null;
    if (value is DateTime) return value;
    final str = value.toString().trim();
    if (str.isEmpty) return null;

    try {
      return DateTime.parse(str);
    } catch (_) {
      try {
        return DateFormat('yyyy-MM-dd').parse(str);
      } catch (_) {
        return null;
      }
    }
  }

  /// Format lengkap: "Senin, 3 November 2026"
  static String formatFull(dynamic value) {
    final dt = tryParse(value);
    if (dt == null) return value?.toString() ?? '-';
    try {
      return DateFormat('EEEE, d MMMM yyyy', 'id_ID').format(dt);
    } catch (_) {
      return DateFormat('EEEE, d MMMM yyyy').format(dt);
    }
  }

  /// Format tanggal sedang: "3 Nov 2026"
  static String formatMedium(dynamic value) {
    final dt = tryParse(value);
    if (dt == null) return value?.toString() ?? '-';
    try {
      return DateFormat('d MMM yyyy', 'id_ID').format(dt);
    } catch (_) {
      return DateFormat('d MMM yyyy').format(dt);
    }
  }

  /// Format standar API: "2026-11-03" (Y-m-d)
  static String formatApi(DateTime dt) {
    return DateFormat('yyyy-MM-dd').format(dt);
  }

  /// Format waktu: "07:30"
  static String formatTime(dynamic value) {
    if (value == null) return '-';
    final str = value.toString().trim();
    if (str.length >= 5 && str.contains(':')) {
      return str.substring(0, 5);
    }
    final dt = tryParse(value);
    if (dt != null) {
      return DateFormat('HH:mm').format(dt);
    }
    return str;
  }
}

