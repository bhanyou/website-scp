/// Konstanta nama & path routing aplikasi School Class Progress.
class RouteNames {
  RouteNames._();

  // Auth & General
  static const String splash = '/';
  static const String login = '/login';
  static const String forgotPassword = '/forgot-password';

  // Guru Pengajar & Wali Kelas
  static const String guruDashboard = '/guru/dashboard';
  static const String guruSchedules = '/guru/schedules';
  static const String guruJournals = '/guru/journals';
  static const String guruJournalCreate = '/guru/journals/create';
  static const String guruJournalDetail = '/guru/journals/:id';
  static const String guruHomeroomPermissions = '/guru/homeroom/permissions';
  static const String guruHomeroomAttendance = '/guru/homeroom/attendance';

  // Kesiswaan
  static const String kesiswaanDashboard = '/kesiswaan/dashboard';
  static const String kesiswaanPermissions = '/kesiswaan/permissions';
  static const String kesiswaanAttendance = '/kesiswaan/attendance';

  // Siswa
  static const String siswaDashboard = '/siswa/dashboard';
  static const String siswaAttendance = '/siswa/attendance';
  static const String siswaPermissions = '/siswa/permissions';
  static const String siswaPermissionCreate = '/siswa/permissions/create';
}

