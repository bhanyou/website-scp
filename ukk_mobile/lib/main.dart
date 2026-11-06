import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'core/config/app_config.dart';
import 'core/router/app_router.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/presentation/controllers/auth_controller.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(
    const ProviderScope(
      child: SchoolClassProgressApp(),
    ),
  );
}

class SchoolClassProgressApp extends ConsumerStatefulWidget {
  const SchoolClassProgressApp({super.key});

  @override
  ConsumerState<SchoolClassProgressApp> createState() => _SchoolClassProgressAppState();
}

class _SchoolClassProgressAppState extends ConsumerState<SchoolClassProgressApp> {
  @override
  void initState() {
    super.initState();
    // Memulihkan sesi pengguna yang tersimpan secara terenkripsi saat aplikasi pertama kali dibuka
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(authControllerProvider.notifier).restoreSession();
    });
  }

  @override
  Widget build(BuildContext context) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: AppConfig.appName,
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: ThemeMode.system,
      routerConfig: router,
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: const [
        Locale('id', 'ID'),
        Locale('en', 'US'),
      ],
    );
  }
}
