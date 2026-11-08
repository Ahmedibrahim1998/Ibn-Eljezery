import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/features/khatma/data/khatma_store.dart';
import 'package:ibneljezery_app/app/app.dart';
import 'package:ibneljezery_app/core/network/api_client.dart';
import 'package:ibneljezery_app/features/mushaf/data/mushaf_settings.dart';
import 'package:ibneljezery_app/features/mushaf/data/quran_data.dart';
import 'package:ibneljezery_app/features/settings/data/settings_store.dart';
import 'package:ibneljezery_app/features/wird/data/wird_store.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUp(() => SharedPreferences.setMockInitialValues({}));

  Future<void> boot(WidgetTester tester) async {
    await tester.runAsync(() async {
      await QuranData.load();
      await WirdStore.load();
      await SettingsStore.load();
      await MushafSettings.load();
      await ApiClient.load();
      await KhatmaStore.load();
    });
    await tester.pumpWidget(const IbnEljezeryApp());
    await tester.pumpAndSettle();
  }

  testWidgets('الرئيسية: كارتين بس — المصحف والفقه', (tester) async {
    await boot(tester);

    expect(find.text('المصحف الشريف'), findsOneWidget);
    expect(find.text('الفقه'), findsOneWidget);
    expect(find.text('وِردي'), findsNothing); // الورد جوه المصحف
  });

  testWidgets('المصحف: متابعة القراءة + الفهرس + وِردي', (tester) async {
    await boot(tester);

    await tester.tap(find.text('المصحف الشريف'));
    await tester.pumpAndSettle();

    expect(find.text('ابدأ القراءة'), findsOneWidget);
    expect(find.text('السور'), findsOneWidget);
    expect(find.text('البحث'), findsOneWidget);
    await tester.scrollUntilVisible(find.text('ترتيل'), 200);
    expect(find.text('وِردي'), findsOneWidget);
    expect(find.byIcon(Icons.tune), findsOneWidget); // إعدادات المصحف
  });
}
