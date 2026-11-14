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
import 'package:ibneljezery_app/features/academy/presentation/recordings_screen.dart';
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
import 'package:ibneljezery_app/features/mushaf/presentation/mushaf_reader_screen.dart';
import 'package:ibneljezery_app/features/splash/splash_screen.dart';

/// فحص «الريسبونسف»: كل شاشة على مقاسات مختلفة + خط كبير (إعدادات إمكانية الوصول).
/// أي سطر بيخرج برا الشاشة (RenderFlex overflow) بيتسجل في التقرير.
/// تشغيل: flutter test tool/responsive/responsive_test.dart --update-goldens
/// (محتاج php artisan serve على 127.0.0.1:8000 عشان بيانات الأكاديمية)
Future<void> _font(String family, List<String> paths) async {
  final loader = FontLoader(family);
  for (final p in paths) {
    loader.addFont(
      Future.value(ByteData.sublistView(File(p).readAsBytesSync())),
    );
  }
  await loader.load();
}

typedef _Device = ({String name, Size size, double dpr, double text});

const _devices = <_Device>[
  (name: 'small', size: Size(320, 568), dpr: 2, text: 1.0), // موبايل صغير قديم
  (name: 'normal', size: Size(360, 780), dpr: 3, text: 1.0),
  (name: 'large', size: Size(430, 932), dpr: 3, text: 1.0),
  (name: 'bigtext', size: Size(360, 780), dpr: 3, text: 1.3), // خط كبير
  (name: 'tablet', size: Size(800, 1280), dpr: 2, text: 1.0),
  (name: 'landscape', size: Size(780, 360), dpr: 3, text: 1.0), // بالعرض
];

void main() {
  final flutterRoot =
      Platform.environment['FLUTTER_ROOT'] ?? 'F:/Mobile-Dev-Tools/flutter';
  final problems = <String>[];

  setUpAll(() async {
    TestWidgetsFlutterBinding.ensureInitialized();
    await _font('Tajawal', [
      'assets/fonts/tajawal/Tajawal-Regular.ttf',
      'assets/fonts/tajawal/Tajawal-Medium.ttf',
      'assets/fonts/tajawal/Tajawal-Bold.ttf',
    ]);
    await _font('AmiriQuran', ['assets/fonts/AmiriQuran-Regular.ttf']);
    await _font('MaterialIcons', [
      '$flutterRoot/bin/cache/artifacts/material_fonts/MaterialIcons-Regular.otf',
    ]);
  });

  tearDownAll(() {
    // ignore: avoid_print
    print(
      problems.isEmpty
          ? '\nRESPONSIVE: كل الشاشات سليمة ✓'
          : '\nRESPONSIVE PROBLEMS:\n${problems.join('\n')}',
    );
  });

  setUp(() {
    SharedPreferences.setMockInitialValues({'last_read_page': 50});
    FlutterSecureStorage.setMockInitialValues({});
  });

  Future<void> check(
    WidgetTester tester,
    String name,
    Widget Function() screen,
  ) async {
    await tester.runAsync(() async {
      HttpOverrides.global = null;
      await QuranData.load();
      await WirdStore.load();
      await SettingsStore.load();
      await MushafSettings.load();
      await ApiClient.load();
      await KhatmaStore.load();
      await AuthStore.load();
    });
    for (final d in _devices) {
      tester.view.physicalSize = d.size * d.dpr;
      tester.view.devicePixelRatio = d.dpr;
      final errors = <String>[];
      final old = FlutterError.onError;
      FlutterError.onError = (details) {
        final msg = details.exceptionAsString().split('\n').first;
        if (!msg.contains('HTTP request failed')) errors.add(msg);
      };
      await tester.pumpWidget(
        MaterialApp(
          key: ValueKey('$name-${d.name}'),
          debugShowCheckedModeBanner: false,
          locale: const Locale('ar'),
          supportedLocales: const [Locale('ar')],
          localizationsDelegates: GlobalMaterialLocalizations.delegates,
          theme: AppTheme.light,
          builder: (context, child) => MediaQuery(
            data: MediaQuery.of(context)
                .copyWith(textScaler: TextScaler.linear(d.text)),
            child: child!,
          ),
          home: screen(),
        ),
      );
      for (var i = 0; i < 5; i++) {
        await tester.runAsync(
          () => Future<void>.delayed(const Duration(milliseconds: 300)),
        );
        await tester.pump(const Duration(milliseconds: 100));
      }
      FlutterError.onError = old;
      if (errors.isNotEmpty) {
        problems.add('$name @ ${d.name}: ${errors.toSet().join(' | ')}');
      }
      if (d.name == 'small' ||
          d.name == 'bigtext' ||
          d.name == 'landscape' ||
          d.name == 'tablet') {
        await expectLater(
          find.byType(MaterialApp),
          matchesGoldenFile('out/${name}_${d.name}.png'),
        );
      }
      // يقفل التايمرز (الواجهة الافتتاحية) قبل المقاس اللي بعده
      await tester.pumpWidget(const SizedBox());
      await tester.pump(const Duration(seconds: 5));
    }
    // مهلة الشبكة (Dio) تخلص قبل نهاية الاختبار
    await tester.pump(const Duration(seconds: 30));
    tester.view.reset();
  }

  const teacher = Teacher(id: 1, name: 'الشيخ أحمد الأنصاري');
  final screens = <String, Widget Function()>{
    'splash': () => const SplashScreen(),
    'home': () => const HomeScreen(),
    'mushaf': () => const MushafScreen(),
    'reader': () => const MushafReaderScreen(initialPage: 50),
    'academy': () => const AcademyScreen(),
    'online': () => const CoursesScreen(online: true),
    'course': () => const CourseScreen(courseId: 3, title: ''),
    'teachers': () => const TeachersScreen(),
    'teacher': () => const TeacherScreen(teacher: teacher),
    'recordings': () => const RecordingsScreen(),
    'login': () => const LoginScreen(),
    'register': () => const LoginScreen(register: true),
    'settings': () => const SettingsScreen(),
  };
  for (final e in screens.entries) {
    testWidgets(e.key, (t) => check(t, e.key, e.value));
  }
}
