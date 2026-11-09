import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/features/khatma/data/khatma_store.dart';
import 'package:ibneljezery_app/features/khatma/domain/khatma_plan.dart';
import 'package:ibneljezery_app/features/mushaf/data/quran_data.dart';
import 'package:ibneljezery_app/features/mushaf/data/tafsir_data.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUpAll(() async {
    TestWidgetsFlutterBinding.ensureInitialized();
    await QuranData.load();
  });

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    await KhatmaStore.load();
  });

  final start = DateTime(2026, 11, 1, 9);
  DateTime day(int n) => start.add(Duration(days: n - 1));

  test('ختمة في 30 يوم: الحصة 21 صفحة', () {
    final plan = KhatmaPlan(start: start, days: 30);
    expect(plan.portion(day(1)), (1, 21));
    expect(plan.aheadBy(day(1)), 0);
  });

  test('خلصت الحصة ← النهارده متسجل والحصة اللي ظاهرة بتاعة بكره', () async {
    final store = KhatmaStore.instance;
    await store.start(30, now: start);
    await store.completePortion(now: day(1));
    final plan = store.plan!;
    expect(plan.page, 22);
    expect(plan.doneToday(day(1)), isTrue);
    // 583 صفحة باقية على 29 يوم ← 21 صفحة
    expect(plan.portion(day(1)), (22, 42));
    expect(plan.portion(day(2)), (22, 42));
    expect(plan.doneToday(day(2)), isFalse);
  });

  test('لو اتأخر يومين ← متأخر والحصة بتكبر', () async {
    final store = KhatmaStore.instance;
    await store.start(30, now: start);
    final plan = store.plan!;
    // يوم 3 ولسه ما قراش حاجة: المفروض كان خلّص يومين (40 صفحة)
    expect(plan.aheadBy(day(3)), -40);
    // 604 صفحة على 28 يوم ← 22 صفحة
    expect(plan.portion(day(3)), (1, 22));
  });

  test('آخر حصة بتكمّل الختمة وبتتعد', () async {
    final store = KhatmaStore.instance;
    await store.start(1, fromPage: 600, now: start);
    expect(store.plan!.portion(day(1)), (600, 604));
    final finished = await store.completePortion(now: day(1));
    expect(finished, isTrue);
    expect(store.plan, isNull);
    expect(store.completed, 1);
  });

  test('الخطة بتتحفظ بعد قفل التطبيق', () async {
    await KhatmaStore.instance.start(15, now: start);
    await KhatmaStore.instance.completePortion(now: day(1));
    await KhatmaStore.load();
    expect(KhatmaStore.instance.plan!.days, 15);
    expect(KhatmaStore.instance.plan!.page, 42);
  });

  test('التفسير الميسر: 6236 آية وكل آية في مكانها', () async {
    final t = await TafsirData.load();
    final q = QuranData.instance;
    expect(t.of(q.indexOf(112, 3)), 'ليس له ولد ولا والد ولا صاحبة.');
    expect(t.of(QuranData.totalVerses - 1), isNotEmpty);
  });
}
