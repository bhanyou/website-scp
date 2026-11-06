import 'package:flutter/material.dart';

/// Palet warna resmi aplikasi School Class Progress (selaras dengan Desktop SCP).
class AppColors {
  AppColors._();

  // Primary SCP Indigo & Slate
  static const Color primary = Color(0xFF4F46E5);      // Indigo 600
  static const Color primaryDark = Color(0xFF3730A3);  // Indigo 800
  static const Color primaryLight = Color(0xFF6366F1); // Indigo 500

  // Secondary & Accents
  static const Color secondary = Color(0xFF0EA5E9);    // Sky 500
  static const Color goldAccent = Color(0xFFE5B842);   // Gold / Amber accent

  // Status Presensi & Approval (Identik dengan Web Desktop SCP)
  static const Color hadir = Color(0xFF059669);        // Emerald 600
  static const Color sakit = Color(0xFF0284C7);        // Sky 600
  static const Color izin = Color(0xFFD97706);         // Amber 600
  static const Color alpa = Color(0xFFDC2626);         // Red 600

  // Status Perizinan Siswa
  static const Color pending = Color(0xFFD97706);      // Amber
  static const Color approved = Color(0xFF059669);     // Emerald
  static const Color rejected = Color(0xFFDC2626);     // Red

  // Backgrounds & Surface Light
  static const Color backgroundLight = Color(0xFFF8FAFC); // Slate 50
  static const Color surfaceLight = Colors.white;
  static const Color cardBorderLight = Color(0xFFE2E8F0); // Slate 200
  static const Color textPrimaryLight = Color(0xFF0F172A); // Slate 900
  static const Color textSecondaryLight = Color(0xFF64748B); // Slate 500

  // Backgrounds & Surface Dark
  static const Color backgroundDark = Color(0xFF0F172A);  // Slate 900
  static const Color surfaceDark = Color(0xFF1E293B);     // Slate 800
  static const Color cardBorderDark = Color(0xFF334155);  // Slate 700
  static const Color textPrimaryDark = Color(0xFFF8FAFC);
  static const Color textSecondaryDark = Color(0xFF94A3B8);
}

