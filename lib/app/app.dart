import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import '../features/splash/splash_screen.dart';
import '../features/settings/data/settings_store.dart';
import 'theme.dart';

/// غلاف التطبيق: اللغة العربية واتجاه RTL والثيم والصفحة الأولى (الواجهة الافتتاحية ← الرئيسية).
class IbnEljezeryApp extends StatelessWidget {
  const IbnEljezeryApp({super.key});

  @override
  Widget build(BuildContext context) {
    // لما الطالب يغيّر الثيم من الإعدادات ← التطبيق كله يتحدث
    return ListenableBuilder(
      listenable: SettingsStore.instance,
      builder: (context, _) => MaterialApp(
        title: 'ابن الجزري',
        debugShowCheckedModeBanner: false,
        // العربية لغة التطبيق الأساسية ← Flutter يقلب الاتجاه RTL تلقائياً
        locale: const Locale('ar'),
        supportedLocales: const [Locale('ar'), Locale('en')],
        localizationsDelegates: GlobalMaterialLocalizations.delegates,
        theme: AppTheme.light,
        darkTheme: AppTheme.dark,
        themeMode: SettingsStore.instance.themeMode,
        home: const SplashScreen(),
      ),
    );
  }
}
