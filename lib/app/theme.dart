import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// ألوان الأكاديمية: أخضر زمردي غامق (هوية الموقع) + دهبي + خلفية عاجية دافية.
class AppColors {
  static const emerald = Color(0xFF0B5D45);
  static const emeraldDeep = Color(0xFF063D2E);
  static const gold = Color(0xFFC9A227);
  static const goldSoft = Color(0xFFF3E6BD);
  static const ivory = Color(0xFFFBF8F1);
  static const night = Color(0xFF0F1714);
  static const nightCard = Color(0xFF17221E);

  /// تدرّج الهيدر والكروت الأساسية.
  static const heroGradient = LinearGradient(
    colors: [emeraldDeep, emerald],
    begin: AlignmentDirectional.topStart,
    end: AlignmentDirectional.bottomEnd,
  );
}

/// خط الواجهة (Tajawal). نص القرآن له خطه (AmiriQuran / خطوط المجمع).
const appFont = 'Tajawal';

class AppTheme {
  static ThemeData get light => _build(Brightness.light);
  static ThemeData get dark => _build(Brightness.dark);

  static ThemeData _build(Brightness brightness) {
    final dark = brightness == Brightness.dark;
    final base = ColorScheme.fromSeed(
      seedColor: AppColors.emerald,
      brightness: brightness,
    );
    final scheme = base.copyWith(
      primary: dark ? const Color(0xFF5FC4A0) : AppColors.emerald,
      onPrimary: dark ? AppColors.night : Colors.white,
      secondary: AppColors.gold,
      onSecondary: Colors.black,
      surface: dark ? AppColors.night : AppColors.ivory,
      surfaceContainerLowest: dark ? AppColors.nightCard : Colors.white,
      surfaceContainerLow: dark ? AppColors.nightCard : Colors.white,
      outlineVariant: dark ? const Color(0xFF2A3833) : const Color(0xFFE8E1D0),
    );
    final radius = BorderRadius.circular(18);

    return ThemeData(
      colorScheme: scheme,
      useMaterial3: true,
      fontFamily: appFont,
      scaffoldBackgroundColor: scheme.surface,
      // شريط علوي رفيع وأنيق: نفس لون الخلفية، والعنوان في النص
      appBarTheme: AppBarTheme(
        centerTitle: true,
        toolbarHeight: 52,
        elevation: 0,
        scrolledUnderElevation: 0.5,
        backgroundColor: scheme.surface,
        surfaceTintColor: Colors.transparent,
        foregroundColor: scheme.onSurface,
        systemOverlayStyle: dark
            ? SystemUiOverlayStyle.light
            : SystemUiOverlayStyle.dark,
        titleTextStyle: TextStyle(
          fontFamily: appFont,
          fontSize: 19,
          fontWeight: FontWeight.bold,
          color: scheme.onSurface,
        ),
      ),
      tabBarTheme: TabBarThemeData(
        labelColor: scheme.primary,
        unselectedLabelColor: scheme.onSurfaceVariant,
        indicatorColor: AppColors.gold,
        indicatorSize: TabBarIndicatorSize.label,
        dividerColor: scheme.outlineVariant,
        labelStyle: const TextStyle(
          fontFamily: appFont,
          fontSize: 15,
          fontWeight: FontWeight.bold,
        ),
        unselectedLabelStyle: const TextStyle(
          fontFamily: appFont,
          fontSize: 15,
        ),
      ),
      // كروت فاتحة بحدّ رفيع بدل الظل التقيل
      cardTheme: CardThemeData(
        elevation: 0,
        color: scheme.surfaceContainerLowest,
        surfaceTintColor: Colors.transparent,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: radius,
          side: BorderSide(color: scheme.outlineVariant),
        ),
      ),
      listTileTheme: ListTileThemeData(
        iconColor: scheme.primary,
        titleTextStyle: TextStyle(
          fontFamily: appFont,
          fontSize: 16,
          color: scheme.onSurface,
          fontWeight: FontWeight.w500,
        ),
        subtitleTextStyle: TextStyle(
          fontFamily: appFont,
          fontSize: 13,
          color: scheme.onSurfaceVariant,
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(0, 46),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          textStyle: const TextStyle(
            fontFamily: appFont,
            fontSize: 15,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(0, 46),
          side: BorderSide(color: scheme.outlineVariant),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          textStyle: const TextStyle(
            fontFamily: appFont,
            fontSize: 15,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          textStyle: const TextStyle(
            fontFamily: appFont,
            fontSize: 15,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      segmentedButtonTheme: SegmentedButtonThemeData(
        style: SegmentedButton.styleFrom(
          selectedBackgroundColor: scheme.primary,
          selectedForegroundColor: scheme.onPrimary,
          side: BorderSide(color: scheme.outlineVariant),
          textStyle: const TextStyle(
            fontFamily: appFont,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      chipTheme: ChipThemeData(
        side: BorderSide(color: scheme.outlineVariant),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        labelStyle: TextStyle(fontFamily: appFont, color: scheme.onSurface),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: scheme.surfaceContainerLowest,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 14,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: scheme.outlineVariant),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: scheme.outlineVariant),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: scheme.primary, width: 1.6),
        ),
      ),
      bottomSheetTheme: BottomSheetThemeData(
        backgroundColor: scheme.surface,
        surfaceTintColor: Colors.transparent,
        showDragHandle: true,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: scheme.surface,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        contentTextStyle: const TextStyle(fontFamily: appFont, fontSize: 14),
      ),
      dividerTheme: DividerThemeData(color: scheme.outlineVariant, space: 24),
      progressIndicatorTheme: ProgressIndicatorThemeData(color: scheme.primary),
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.android: FadeForwardsPageTransitionsBuilder(),
        },
      ),
    );
  }
}
