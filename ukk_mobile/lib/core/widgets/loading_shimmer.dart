import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';
import '../theme/app_colors.dart';

/// Shimmer placeholder loader untuk list cards.
class LoadingShimmerList extends StatelessWidget {
  final int itemCount;
  final double itemHeight;

  const LoadingShimmerList({
    super.key,
    this.itemCount = 4,
    this.itemHeight = 90,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: itemCount,
      padding: const EdgeInsets.all(16),
      itemBuilder: (_, __) => Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: Shimmer.fromColors(
          baseColor: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
          highlightColor: isDark ? const Color(0xFF475569) : const Color(0xFFF1F5F9),
          child: Container(
            height: itemHeight,
            decoration: BoxDecoration(
              color: isDark ? AppColors.surfaceDark : Colors.white,
              borderRadius: BorderRadius.circular(14),
            ),
          ),
        ),
      ),
    );
  }
}

