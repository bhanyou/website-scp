import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import 'app_button.dart';

/// Tampilan error terpusat dengan tombol "Coba Lagi".
class ErrorStateView extends StatelessWidget {
  final String message;
  final VoidCallback? onRetry;
  final IconData icon;

  const ErrorStateView({
    super.key,
    required this.message,
    this.onRetry,
    this.icon = Icons.error_outline_rounded,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 36),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.alpa.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 48, color: AppColors.alpa),
            ),
            const SizedBox(height: 16),
            const Text(
              'Gagal Memuat Data',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13.5, color: AppColors.textSecondaryLight, height: 1.4),
            ),
            if (onRetry != null) ...[
              const SizedBox(height: 20),
              AppButton(
                label: 'Coba Lagi',
                icon: Icons.refresh_rounded,
                width: 170,
                height: 44,
                onPressed: onRetry,
              ),
            ],
          ],
        ),
      ),
    );
  }
}

