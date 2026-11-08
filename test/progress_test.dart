import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/features/mushaf/data/quran_data.dart';
import 'package:ibneljezery_app/features/wird/data/wird_store.dart';
import 'package:ibneljezery_app/features/wird/domain/wird_mark.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  late QuranData q;

  setUpAll(() async {
    TestWidgetsFlutterBinding.ensureInitialized();
    await QuranData.load();
    q = QuranData.instance;
  });

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    await WirdStore.load();
  });

  final now = DateTime(2026, 11, 10, 20);
  DateTime daysAgo(int n) => now.subtract(Duration(days: n));

  Future<void> markJuz(int from, int to) => WirdStore.instance.save(
    WirdMark(
      type: WirdType.nearReview,
      from: q.juzStart(from),
      to: q.juzEnd(to),
      byJuz: true,
    ),
  );

  test('"خلصت" بتتسجّل وبتنقل العلامة', () async {
    await markJuz(21, 25);
    await WirdStore.instance.advance(WirdType.nearReview, at: now);

    final h = WirdStore.instance.history;
    expect(h, hasLength(1));
    expect((q.juzOf(h.first.from), q.juzOf(h.first.to)), (21, 25));
    expect(q.juzOf(WirdStore.instance.mark(WirdType.nearReview)!.from), 26);
  });

  test('أيام الالتزام ورا بعض: 3 أيام، وأحسن رقم محفوظ', () async {
    await markJuz(1, 1);
    // يوم 9 و 8 (اتقطع يوم 7) ← بعدين 3 أيام ورا بعض لحد امبارح
    for (final d in [9, 8, 3, 2, 1]) {
      await WirdStore.instance.advance(WirdType.nearReview, at: daysAgo(d));
    }
    var stats = WirdStore.instance.stats(now);
    expect(stats.currentStreak, 3); // لسه ما خلّصش النهارده ← بنعد من امبارح
    expect(stats.doneToday, isFalse);

    await WirdStore.instance.advance(WirdType.nearReview, at: now);
    stats = WirdStore.instance.stats(now);
    expect(stats.currentStreak, 4);
    expect(stats.bestStreak, 4);
    expect(stats.doneToday, isTrue);
    expect(stats.lastSevenDays, [false, false, false, true, true, true, true]);
  });

  test('لو فات يومين من غير ورد ← الالتزام يرجع صفر', () async {
    await markJuz(1, 1);
    await WirdStore.instance.advance(WirdType.nearReview, at: daysAgo(2));
    expect(WirdStore.instance.stats(now).currentStreak, 0);
    expect(WirdStore.instance.stats(now).bestStreak, 1);
  });

  test('تراجع: بيمسح آخر "خلصت" ويرجّع العلامة', () async {
    await markJuz(21, 25);
    await WirdStore.instance.advance(WirdType.nearReview, at: now);
    await WirdStore.instance.undoLast(WirdType.nearReview);

    expect(WirdStore.instance.history, isEmpty);
    final mark = WirdStore.instance.mark(WirdType.nearReview)!;
    expect((q.juzOf(mark.from), q.juzOf(mark.to)), (21, 25));
    expect(mark.byJuz, isTrue);
  });

  test('صفحات الأسبوع: جزء 1 = 21 صفحة', () async {
    await markJuz(1, 1);
    await WirdStore.instance.advance(WirdType.nearReview, at: now);
    final stats = WirdStore.instance.stats(now);
    expect(stats.weekPages, 21);
    expect(stats.weekCounts[WirdType.nearReview], 1);
  });

  test('السجل بيتحفظ ويرجع بعد قفل التطبيق', () async {
    await markJuz(1, 1);
    await WirdStore.instance.advance(WirdType.nearReview, at: now);
    await WirdStore.load(); // زي ما التطبيق اتقفل واتفتح
    expect(WirdStore.instance.history, hasLength(1));
  });
}
