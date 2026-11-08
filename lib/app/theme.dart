import 'package:flutter/material.dart';

/// ألوان الأكاديمية: الأخضر الزمردي (نفس لوحة التحكم Filament) + دهبي خفيف.
class AppColors {
  static const emerald = Color(0xFF047857);
  static const gold = Color(0xFFC9A227);
}

class AppTheme {
  static ThemeData get light => _build(Brightness.light);
  static ThemeData get dark => _build(Brightness.dark);

  static ThemeData _build(Brightness brightness) {
    final scheme = ColorScheme.fromSeed(
      seedColor: AppColors.emerald,
      secondary: AppColors.gold,
      brightness: brightness,
    );

    return ThemeData(
      colorScheme: scheme,
      useMaterial3: true,
      // شريط علوي أصغر من الافتراضي (56) عشان المحتوى ياخد مساحة أكبر
      appBarTheme: AppBarTheme(
        centerTitle: true,
        toolbarHeight: 48,
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
        titleTextStyle: TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.w600,
          color: scheme.onPrimary,
        ),
      ),
      tabBarTheme: TabBarThemeData(
        labelColor: scheme.onPrimary,
        unselectedLabelColor: scheme.onPrimary.withValues(alpha: 0.7),
        indicatorColor: AppColors.gold,
        labelStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
      ),
    );
  }
}
