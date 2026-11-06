import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// Tombol kustom berstandar Material 3 dengan proteksi double-submit & status loading.
class AppButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final IconData? icon;
  final Color? backgroundColor;
  final Color? foregroundColor;
  final bool isOutlined;
  final double? width;
  final double height;

  const AppButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.isLoading = false,
    this.icon,
    this.backgroundColor,
    this.foregroundColor,
    this.isOutlined = false,
    this.width,
    this.height = 50,
  });

  @override
  Widget build(BuildContext context) {
    final effectiveBg = backgroundColor ?? AppColors.primary;
    final effectiveFg = foregroundColor ?? Colors.white;

    Widget childWidget;
    if (isLoading) {
      childWidget = SizedBox(
        height: 20,
        width: 20,
        child: CircularProgressIndicator(
          strokeWidth: 2.2,
          color: isOutlined ? effectiveBg : effectiveFg,
        ),
      );
    } else if (icon != null) {
      childWidget = Row(
        mainAxisSize: MainAxisSize.min,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 20),
          const SizedBox(width: 8),
          Text(label, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
        ],
      );
    } else {
      childWidget = Text(
        label,
        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
      );
    }

    if (isOutlined) {
      return SizedBox(
        width: width ?? double.infinity,
        height: height,
        child: OutlinedButton(
          onPressed: isLoading ? null : onPressed,
          style: OutlinedButton.styleFrom(
            foregroundColor: effectiveBg,
            side: BorderSide(color: effectiveBg),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          child: childWidget,
        ),
      );
    }

    return SizedBox(
      width: width ?? double.infinity,
      height: height,
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: effectiveBg,
          foregroundColor: effectiveFg,
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
        child: childWidget,
      ),
    );
  }
}

