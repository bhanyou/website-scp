/// Daftar seluruh endpoint REST API Laravel 11 + Sanctum.
class ApiEndpoints {
  ApiEndpoints._();

  // Auth
  static const String login = '/login';
  static const String logout = '/logout';
  static const String user = '/user';

  // Presensi
  static const String attendances = '/attendances';

  // Siswa & Kesiswaan
  static const String siswaDashboard = '/siswa/dashboard';
  static const String kesiswaanDashboard = '/kesiswaan/dashboard';

  // Izin & Sakit
  static const String permissionRequests = '/permission-requests';
  static String permissionDetail(int id) => '/permission-requests/$id';
  static String permissionApprove(int id) => '/permission-requests/$id/approve';
  static String permissionReject(int id) => '/permission-requests/$id/reject';

  // Jadwal Mengajar KBM
  static const String schedules = '/schedules';

  // Jurnal KBM
  static const String journals = '/journals';
  static const String journalHistory = '/journals/history';
  static String journalDetail(int id) => '/journals/$id';
}

