import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// Tampilan empty state ketika data belum tersedia.
class EmptyStateView extends StatelessWidget {
  final String title;
  final String message;
  final IconData icon;
  final Widget? actionButton;

  const EmptyStateView({
    super.key,
    this.title = 'Belum Ada Data',
    this.message = 'Tidak ada catatan atau riwayat yang dapat ditampilkan saat ini.',
    this.icon = Icons.inbox_outlined,
    this.actionButton,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 48),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 52, color: AppColors.primary),
            ),
            const SizedBox(height: 18),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13.5, color: AppColors.textSecondaryLight, height: 1.4),
            ),
            if (actionButton != null) ...[
              const SizedBox(height: 20),
              actionButton!,
            ],
          ],
        ),
      ),
    );
  }
}

