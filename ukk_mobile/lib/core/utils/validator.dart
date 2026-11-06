/// Validator form input berbahasa Indonesia.
class AppValidator {
  AppValidator._();

  static String? requiredField(String? value, [String fieldName = 'Kolom ini']) {
    if (value == null || value.trim().isEmpty) {
      return '$fieldName wajib diisi';
    }
    return null;
  }

  static String? minLength(String? value, int min, [String fieldName = 'Kolom ini']) {
    if (value == null || value.trim().length < min) {
      return '$fieldName minimal $min karakter';
    }
    return null;
  }
}

