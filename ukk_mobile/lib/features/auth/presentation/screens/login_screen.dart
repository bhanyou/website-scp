import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/config/app_config.dart';
import '../../../../core/di/core_providers.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/utils/validator.dart';
import '../controllers/auth_controller.dart';
import '../controllers/auth_state.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _identityController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _identityController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _fillAccount(String identity, String password) {
    setState(() {
      _identityController.text = identity;
      _passwordController.text = password;
    });
  }

  void _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    FocusScope.of(context).unfocus();

    final success = await ref.read(authControllerProvider.notifier).login(
          identity: _identityController.text.trim(),
          password: _passwordController.text,
        );

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
              SizedBox(width: 10),
              Text('Selamat datang di School Class Progress!'),
            ],
          ),
          backgroundColor: AppColors.hadir,
          behavior: SnackBarBehavior.floating,
          duration: Duration(seconds: 2),
        ),
      );
    }
  }

  void _showServerSettingsDialog() {
    final serverController = TextEditingController(text: AppConfig.apiBaseUrl);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.dns_rounded, color: AppColors.primary),
            SizedBox(width: 10),
            Text('Koneksi Server API', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Aplikasi mobile terhubung langsung dengan backend REST API Laravel:',
              style: TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: serverController,
              decoration: InputDecoration(
                labelText: 'Base URL API',
                hintText: 'https://scp-bhan.web.id/api',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              ),
              style: const TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                ActionChip(
                  label: const Text('Live (scp-bhan.web.id)', style: TextStyle(fontSize: 11)),
                  onPressed: () {
                    serverController.text = AppConfig.defaultLiveApiUrl;
                  },
                ),
                ActionChip(
                  label: const Text('Lokal (10.0.2.2 / Emulator)', style: TextStyle(fontSize: 11)),
                  onPressed: () {
                    serverController.text = AppConfig.defaultAndroidEmulatorApiUrl;
                  },
                ),
                ActionChip(
                  label: const Text('Lokal (127.0.0.1:8000)', style: TextStyle(fontSize: 11)),
                  onPressed: () {
                    serverController.text = AppConfig.defaultLocalApiUrl;
                  },
                ),
              ],
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () {
              final newUrl = serverController.text.trim();
              if (newUrl.isNotEmpty) {
                AppConfig.setOverrideBaseUrl(newUrl);
                ref.read(apiClientProvider).updateBaseUrl(newUrl);
                setState(() {});
              }
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Server URL diubah ke: $newUrl'),
                  behavior: SnackBarBehavior.floating,
                ),
              );
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  void _showForgotPasswordDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        title: const Row(
          children: [
            Icon(Icons.lock_reset_rounded, color: Color(0xFF4F46E5)),
            SizedBox(width: 10),
            Text('Lupa Password?', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
          ],
        ),
        content: const Text(
          'Layanan reset kata sandi (OTP) saat ini dapat dilakukan melalui Web SCP (https://scp-bhan.web.id) atau hubungi administrator sekolah / Waka Kurikulum.',
          style: TextStyle(fontSize: 13, height: 1.45, color: Color(0xFF475569)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Mengerti', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF4F46E5))),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authControllerProvider);
    final isLoading = authState.isLoading;

    final isLiveConnected = AppConfig.apiBaseUrl.contains('scp-bhan.web.id');

    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 20.0),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: Column(
                  children: [
                    // Status Server Pill
                    InkWell(
                      onTap: _showServerSettingsDialog,
                      borderRadius: BorderRadius.circular(20),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        margin: const EdgeInsets.only(bottom: 16),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: isLiveConnected
                                ? const Color(0xFF10B981).withValues(alpha: 0.4)
                                : Colors.white.withValues(alpha: 0.2),
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              width: 8,
                              height: 8,
                              decoration: BoxDecoration(
                                color: isLiveConnected ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                                shape: BoxShape.circle,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              isLiveConnected ? 'API Terhubung: scp-bhan.web.id' : 'API: ${AppConfig.apiBaseUrl}',
                              style: const TextStyle(
                                color: Colors.white70,
                                fontSize: 11.5,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(width: 4),
                            const Icon(Icons.settings_outlined, color: Colors.white54, size: 14),
                          ],
                        ),
                      ),
                    ),

                    // Main Login Card
                    Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.25),
                            blurRadius: 25,
                            offset: const Offset(0, 15),
                          ),
                        ],
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 26.0, vertical: 30.0),
                      child: Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            // Header Logo
                            Center(
                              child: Container(
                                width: 50,
                                height: 50,
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [Color(0xFF4F46E5), Color(0xFF06B6D4)],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                                  borderRadius: BorderRadius.circular(10),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xFF4F46E5).withValues(alpha: 0.3),
                                      blurRadius: 8,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                alignment: Alignment.center,
                                child: const Text(
                                  'S',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 26,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 12),

                            const Text(
                              'School Class Progress',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 21,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF0F172A),
                                letterSpacing: -0.5,
                              ),
                            ),
                            const SizedBox(height: 4),

                            const Text(
                              'Masuk dengan akun Guru, Siswa, atau Waka',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 13,
                                color: Color(0xFF64748B),
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Alert Error
                            if (authState.status == AuthStatus.error && authState.errorMessage != null) ...[
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFEF2F2),
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: const Color(0xFFFECACA)),
                                ),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Icon(Icons.error_outline_rounded, color: Color(0xFFDC2626), size: 18),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        authState.errorMessage!,
                                        style: const TextStyle(
                                          color: Color(0xFF991B1B),
                                          fontSize: 12,
                                          fontWeight: FontWeight.w500,
                                          height: 1.35,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 16),
                            ],

                            // NIP / NISN / Username
                            const Text(
                              'NIP / NISN / Username',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF0F172A),
                              ),
                            ),
                            const SizedBox(height: 6),
                            TextFormField(
                              controller: _identityController,
                              keyboardType: TextInputType.text,
                              validator: (val) => AppValidator.requiredField(val, 'NIP / NISN / Username'),
                              style: const TextStyle(fontSize: 13.5, color: Color(0xFF0F172A)),
                              decoration: InputDecoration(
                                hintText: 'Masukkan NIP, NISN, Username, atau Email',
                                hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8)),
                                prefixIcon: const Icon(Icons.person_outline_rounded, size: 20, color: Color(0xFF64748B)),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Color(0xFF4F46E5), width: 1.8),
                                ),
                              ),
                            ),
                            const SizedBox(height: 14),

                            // Kata Sandi
                            const Text(
                              'Kata Sandi',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF0F172A),
                              ),
                            ),
                            const SizedBox(height: 6),
                            TextFormField(
                              controller: _passwordController,
                              obscureText: _obscurePassword,
                              validator: (val) => AppValidator.requiredField(val, 'Kata sandi'),
                              style: const TextStyle(fontSize: 13.5, color: Color(0xFF0F172A)),
                              decoration: InputDecoration(
                                hintText: '••••••••',
                                hintStyle: const TextStyle(fontSize: 14, color: Color(0xFF94A3B8)),
                                prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20, color: Color(0xFF64748B)),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Color(0xFF4F46E5), width: 1.8),
                                ),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                    size: 19,
                                    color: const Color(0xFF64748B),
                                  ),
                                  onPressed: () {
                                    setState(() {
                                      _obscurePassword = !_obscurePassword;
                                    });
                                  },
                                ),
                              ),
                            ),
                            const SizedBox(height: 18),

                            // Tombol Masuk
                            ElevatedButton(
                              onPressed: isLoading ? null : _handleLogin,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF4F46E5),
                                foregroundColor: Colors.white,
                                elevation: 1,
                                padding: const EdgeInsets.symmetric(vertical: 13),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              ),
                              child: isLoading
                                 ? const SizedBox(
                                      height: 20,
                                      width: 20,
                                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                    )
                                  : const Text(
                                      'Masuk ke Sistem',
                                      style: TextStyle(
                                        fontSize: 14,
                                        fontWeight: FontWeight.bold,
                                        letterSpacing: 0.2,
                                      ),
                                    ),
                            ),
                            const SizedBox(height: 12),

                            // Link Lupa Password
                            Align(
                              alignment: Alignment.centerRight,
                              child: InkWell(
                                onTap: _showForgotPasswordDialog,
                                child: const Text(
                                  'Lupa Password?',
                                  style: TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF4F46E5),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 18),

                            // Divider & Quick Demo Accounts (Sangat membantu penguji UKK!)
                            const Row(
                              children: [
                                Expanded(child: Divider(color: Color(0xFFE2E8F0))),
                                Padding(
                                  padding: EdgeInsets.symmetric(horizontal: 8),
                                  child: Text('Akun Uji Coba UKK', style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                                ),
                                Expanded(child: Divider(color: Color(0xFFE2E8F0))),
                              ],
                            ),
                            const SizedBox(height: 10),
                            Wrap(
                              spacing: 6,
                              runSpacing: 6,
                              alignment: WrapAlignment.center,
                              children: [
                                OutlinedButton(
                                  style: OutlinedButton.styleFrom(
                                    side: const BorderSide(color: Color(0xFFCBD5E1)),
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    minimumSize: Size.zero,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                                  ),
                                  onPressed: () => _fillAccount('17813', 'password'),
                                  child: const Text('🎓 Siswa (Bhanu)', style: TextStyle(fontSize: 11, color: Color(0xFF334155))),
                                ),
                                OutlinedButton(
                                  style: OutlinedButton.styleFrom(
                                    side: const BorderSide(color: Color(0xFFCBD5E1)),
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    minimumSize: Size.zero,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                                  ),
                                  onPressed: () => _fillAccount('guru1', 'password'),
                                  child: const Text('👨‍🏫 Guru Pengajar', style: TextStyle(fontSize: 11, color: Color(0xFF334155))),
                                ),
                                OutlinedButton(
                                  style: OutlinedButton.styleFrom(
                                    side: const BorderSide(color: Color(0xFFCBD5E1)),
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    minimumSize: Size.zero,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                                  ),
                                  onPressed: () => _fillAccount('waka', 'password'),
                                  child: const Text('🏛️ Kesiswaan/Waka', style: TextStyle(fontSize: 11, color: Color(0xFF334155))),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    const Text(
                      'UKK Project • School Class Progress Mobile App\nTerhubung langsung ke https://scp-bhan.web.id',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.white54,
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
