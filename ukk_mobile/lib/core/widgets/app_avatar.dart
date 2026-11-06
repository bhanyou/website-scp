import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// Widget Avatar Foto Profil Pengguna dengan Fallback Inisial / Icon
class AppAvatar extends StatelessWidget {
  final String? imageUrl;
  final String name;
  final double radius;
  final IconData? fallbackIcon;
  final Color? backgroundColor;
  final Color? foregroundColor;

  const AppAvatar({
    super.key,
    this.imageUrl,
    required this.name,
    this.radius = 26,
    this.fallbackIcon,
    this.backgroundColor,
    this.foregroundColor,
  });

  String _getInitials(String str) {
    final parts = str.trim().split(RegExp(r'\s+'));
    if (parts.isEmpty || parts.first.isEmpty) return 'U';
    if (parts.length == 1) return parts.first[0].toUpperCase();
    return '${parts.first[0]}${parts[1][0]}'.toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    final bg = backgroundColor ?? Colors.white;
    final fg = foregroundColor ?? AppColors.primary;
    final hasValidUrl = imageUrl != null && imageUrl!.trim().isNotEmpty;

    return Container(
      width: radius * 2,
      height: radius * 2,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: bg,
        border: Border.all(color: Colors.white, width: 2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.1),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: ClipOval(
        child: hasValidUrl
            ? CachedNetworkImage(
                imageUrl: imageUrl!,
                fit: BoxFit.cover,
                width: radius * 2,
                height: radius * 2,
                placeholder: (context, url) => Container(
                  color: bg,
                  child: Center(
                    child: SizedBox(
                      width: radius * 0.8,
                      height: radius * 0.8,
                      child: CircularProgressIndicator(strokeWidth: 2, color: fg),
                    ),
                  ),
                ),
                errorWidget: (context, url, error) => _buildFallback(fg),
              )
            : _buildFallback(fg),
      ),
    );
  }

  Widget _buildFallback(Color fg) {
    if (fallbackIcon != null) {
      return Icon(fallbackIcon, size: radius * 1.1, color: fg);
    }
    return Center(
      child: Text(
        _getInitials(name),
        style: TextStyle(
          color: fg,
          fontSize: radius * 0.75,
          fontWeight: FontWeight.bold,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}

