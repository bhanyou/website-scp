import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/presentation/controllers/auth_controller.dart';
import '../../features/auth/presentation/controllers/auth_state.dart';
import '../../features/auth/presentation/screens/login_screen.dart';
import '../../features/dashboard/presentation/screens/guru_dashboard_screen.dart';
import '../../features/dashboard/presentation/screens/kesiswaan_dashboard_screen.dart';
import '../../features/dashboard/presentation/screens/siswa_dashboard_screen.dart';
import '../../features/siswa/presentation/screens/siswa_permission_create_screen.dart';
import '../../features/homeroom/presentation/screens/homeroom_attendance_screen.dart';
import '../../features/homeroom/presentation/screens/homeroom_permissions_screen.dart';
import '../../features/journal/presentation/screens/journal_create_screen.dart';
import '../../features/journal/presentation/screens/journal_detail_screen.dart';
import '../../features/journal/presentation/screens/journal_history_screen.dart';
import '../../features/schedule/presentation/screens/schedule_list_screen.dart';
import 'route_names.dart';

/// Provider GoRouter aplikasi School Class Progress dengan Auth & Role Guard
final routerProvider = Provider<GoRouter>((ref) {
  final authNotifier = ValueNotifier<AuthState>(ref.watch(authControllerProvider));

  ref.listen<AuthState>(authControllerProvider, (_, next) {
    authNotifier.value = next;
  });

  return GoRouter(
    initialLocation: RouteNames.login,
    refreshListenable: authNotifier,
    redirect: (context, state) {
      final authState = authNotifier.value;
      final isAuthenticated = authState.isAuthenticated;
      final isAuthenticating = authState.status == AuthStatus.authenticating;
      final isInitial = authState.status == AuthStatus.initial;
      final isLoggingIn = state.matchedLocation == RouteNames.login;

      // Tunggu hingga pemeriksaan sesi awal selesai
      if (isInitial || isAuthenticating) {
        return null;
      }

      // 1. Jika belum login dan mencoba masuk ke halaman terlindungi -> arahkan ke /login
      if (!isAuthenticated) {
        return isLoggingIn ? null : RouteNames.login;
      }

      // 2. Jika sudah terautentikasi dan berada di /login atau root -> arahkan ke dashboard sesuai role
      if (isLoggingIn || state.matchedLocation == RouteNames.splash) {
        final role = authState.user?.role.toLowerCase() ?? 'siswa';
        if (role == 'guru' || role == 'bk') {
          return RouteNames.guruDashboard;
        } else if (role == 'kesiswaan' || role == 'waka') {
          return RouteNames.kesiswaanDashboard;
        } else {
          return RouteNames.siswaDashboard;
        }
      }

      return null;
    },
    routes: [
      GoRoute(
        path: RouteNames.login,
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: RouteNames.guruDashboard,
        builder: (context, state) => const GuruDashboardScreen(),
      ),
      GoRoute(
        path: RouteNames.guruSchedules,
        builder: (context, state) => const ScheduleListScreen(),
      ),
      GoRoute(
        path: RouteNames.guruJournals,
        builder: (context, state) => const JournalHistoryScreen(),
      ),
      GoRoute(
        path: RouteNames.guruJournalCreate,
        builder: (context, state) {
          final preselectedId = state.extra is int ? state.extra as int : null;
          return JournalCreateScreen(preselectedScheduleId: preselectedId);
        },
      ),
      GoRoute(
        path: RouteNames.guruJournalDetail,
        builder: (context, state) {
          final idStr = state.pathParameters['id'] ?? '0';
          final id = int.tryParse(idStr) ?? 0;
          return JournalDetailScreen(journalId: id);
        },
      ),
      GoRoute(
        path: RouteNames.guruHomeroomPermissions,
        builder: (context, state) => const HomeroomPermissionsScreen(),
      ),
      GoRoute(
        path: RouteNames.guruHomeroomAttendance,
        builder: (context, state) => const HomeroomAttendanceScreen(),
      ),
      GoRoute(
        path: RouteNames.kesiswaanDashboard,
        builder: (context, state) => const KesiswaanDashboardScreen(),
      ),
      GoRoute(
        path: RouteNames.siswaDashboard,
        builder: (context, state) => const SiswaDashboardScreen(),
      ),
      GoRoute(
        path: RouteNames.siswaPermissionCreate,
        builder: (context, state) => const SiswaPermissionCreateScreen(),
      ),
    ],
  );
});
