// لقطات شاشة للتطبيق على الكمبيوتر (من غير موبايل) — للمراجعة البصرية.
//
//   php artisan serve   (عشان شاشات الأكاديمية تجيب داتا حقيقية)
//   flutter test tool/screenshots/screens_test.dart --update-goldens
//
// الصور بتطلع في tool/screenshots/out/ (مش بتترفع على git).
// ignore_for_file: invalid_use_of_visible_for_testing_member
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/app/theme.dart';
import 'package:ibneljezery_app/core/network/api_client.dart';
import 'package:ibneljezery_app/features/academy/domain/academy_models.dart';
import 'package:ibneljezery_app/features/academy/presentation/academy_screen.dart';
import 'package:ibneljezery_app/features/academy/presentation/course_screen.dart';
import 'package:ibneljezery_app/features/academy/presentation/courses_screen.dart';
import 'package:ibneljezery_app/features/academy/presentation/teachers_screen.dart';
import 'package:ibneljezery_app/features/account/data/auth_store.dart';
import 'package:ibneljezery_app/features/account/presentation/login_screen.dart';
import 'package:ibneljezery_app/features/home/presentation/home_screen.dart';
import 'package:ibneljezery_app/features/khatma/data/khatma_store.dart';
import 'package:ibneljezery_app/features/mushaf/data/mushaf_settings.dart';
import 'package:ibneljezery_app/features/mushaf/data/quran_data.dart';
import 'package:ibneljezery_app/features/mushaf/presentation/mushaf_screen.dart';
import 'package:ibneljezery_app/features/settings/data/settings_store.dart';
import 'package:ibneljezery_app/features/settings/presentation/settings_screen.dart';
import 'package:ibneljezery_app/features/wird/data/wird_store.dart';
import 'package:shared_preferences/shared_preferences.dart';

Future<void> _font(String family, List<String> paths) async {
  final loader = FontLoader(family);
  for (final p in paths) {
    loader.addFont(Future.value(ByteData.sublistView(File(p).readAsBytesSync())));
  }
  await loader.load();
}

void main() {
  final flutterRoot = Platform.environment['FLUTTER_ROOT'] ?? 'F:/Mobile-Dev-Tools/flutter';

  setUpAll(() async {
    TestWidgetsFlutterBinding.ensureInitialized();
    await _font('Tajawal', [
      'assets/fonts/tajawal/Tajawal-Regular.ttf',
      'assets/fonts/tajawal/Tajawal-Medium.ttf',
      'assets/fonts/tajawal/Tajawal-Bold.ttf',
    ]);
    await _font('AmiriQuran', ['assets/fonts/AmiriQuran-Regular.ttf']);
    await _font('MaterialIcons', ['$flutterRoot/bin/cache/artifacts/material_fonts/MaterialIcons-Regular.otf']);
  });

  setUp(() {
    SharedPreferences.setMockInitialValues({'last_read_page': 50});
    FlutterSecureStorage.setMockInitialValues({});
  });

  Future<void> shot(WidgetTester tester, String name, Widget screen, {bool dark = false}) async {
    tester.view.physicalSize = const Size(1080, 2160);
    tester.view.devicePixelRatio = 3;
    addTearDown(tester.view.reset);
    await tester.runAsync(() async {
      HttpOverrides.global = null; // طلبات حقيقية للسيرفر المحلي
      await QuranData.load();
      await WirdStore.load();
      await SettingsStore.load();
      await MushafSettings.load();
      await ApiClient.load();
      await KhatmaStore.load();
      await AuthStore.load();
    });
    await tester.pumpWidget(
      MaterialApp(
        debugShowCheckedModeBanner: false,
        locale: const Locale('ar'),
        supportedLocales: const [Locale('ar')],
        localizationsDelegates: GlobalMaterialLocalizations.delegates,
        theme: AppTheme.light,
        darkTheme: AppTheme.dark,
        themeMode: dark ? ThemeMode.dark : ThemeMode.light,
        home: screen,
      ),
    );
    // استنى الشبكة والصور
    for (var i = 0; i < 6; i++) {
      await tester.runAsync(() => Future<void>.delayed(const Duration(milliseconds: 500)));
      await tester.pump(const Duration(milliseconds: 100));
    }
    await expectLater(find.byType(MaterialApp), matchesGoldenFile('out/$name.png'));
  }

  const teacher = Teacher(id: 1, name: 'الشيخ أحمد الأنصاري');

  testWidgets('01 home', (t) => shot(t, '01_home', const HomeScreen()));
  testWidgets('02 home dark', (t) => shot(t, '02_home_dark', const HomeScreen(), dark: true));
  testWidgets('03 mushaf hub', (t) => shot(t, '03_mushaf', const MushafScreen()));
  testWidgets('04 academy', (t) => shot(t, '04_academy', const AcademyScreen()));
  testWidgets('05 online', (t) => shot(t, '05_online', const CoursesScreen(online: true)));
  testWidgets('06 offline', (t) => shot(t, '06_offline', const CoursesScreen(online: false)));
  testWidgets('07 course', (t) => shot(t, '07_course', const CourseScreen(courseId: 3, title: '')));
  testWidgets('08 teachers', (t) => shot(t, '08_teachers', const TeachersScreen()));
  testWidgets('09 teacher', (t) => shot(t, '09_teacher', const TeacherScreen(teacher: teacher)));
  testWidgets('10 login', (t) => shot(t, '10_login', const LoginScreen()));
  testWidgets('11 register', (t) => shot(t, '11_register', const LoginScreen(register: true)));
  testWidgets('12 settings', (t) => shot(t, '12_settings', const SettingsScreen()));
}
