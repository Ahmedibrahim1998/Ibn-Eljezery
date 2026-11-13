import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/features/mushaf/data/quran_data.dart';
import 'package:ibneljezery_app/features/wird/domain/wird_mark.dart';

void main() {
  late QuranData q;

  setUpAll(() async {
    TestWidgetsFlutterBinding.ensureInitialized();
    await QuranData.load();
    q = QuranData.instance;
  });

  test('أعداد المصحف صحيحة', () {
    expect(q.suras.length, 114);
    expect(
      q.suras.fold<int>(0, (s, x) => s + x.ayahCount),
      QuranData.totalVerses,
    );
    expect(q.text(0), startsWith('بِسْمِ'));
    expect(q.label(QuranData.totalVerses - 1), 'الناس 6');
  });

  test('البسملة مش جزء من الآية الأولى (غير الفاتحة)', () {
    expect(q.text(q.indexOf(2, 1)), 'الٓمٓ');
    for (final s in q.suras) {
      final first = q.text(s.start);
      if (s.number == 1) {
        expect(first, startsWith('بِسْمِ'));
      } else {
        expect(
          first,
          isNot(contains('ٱللَّهِ ٱلرَّحْمَٰنِ')),
          reason: 'سورة ${s.number}',
        );
      }
    }
  });

  test('آية الكرسي: البقرة 255 ← صفحة 42 والجزء 3', () {
    final i = q.indexOf(2, 255);
    expect(q.suraOf(i).name, 'البقرة');
    expect(q.ayahOf(i), 255);
    expect(q.pageOf(i), 42);
    expect(q.juzOf(i), 3);
  });

  test('بدايات الصفحات والأجزاء', () {
    expect(q.pageRange(1), (0, 6)); // الفاتحة كاملة في صفحة 1
    expect(q.label(q.pageRange(604).$1), 'الإخلاص 1');
    expect(q.label(q.juzStart(30)), 'النبإ 1');
    expect(q.juzEnd(30), QuranData.totalVerses - 1);
  });

  test('البحث بيتجاهل التشكيل والهمزات', () {
    // "العالمين" مكتوبة بالعثماني "ٱلْعَٰلَمِينَ"
    expect(q.search('رب العالمين'), contains(q.indexOf(1, 2)));
    expect(q.search('الحي القيوم'), contains(q.indexOf(2, 255)));
    expect(q.search('اله الا هو'), contains(q.indexOf(2, 255))); // من غير همزات
    expect(q.search('ا'), isEmpty); // حرف واحد ← مفيش بحث
  });

  test('خلصت بالأجزاء: 21–25 ← 26–30 ← 1–5', () {
    var m = WirdMark(
      type: WirdType.nearReview,
      from: q.juzStart(21),
      to: q.juzEnd(25),
      byJuz: true,
    );
    m = m.next();
    expect((q.juzOf(m.from), q.juzOf(m.to)), (26, 30));
    m = m.next();
    expect((q.juzOf(m.from), q.juzOf(m.to)), (1, 5));
  });

  test('خلصت بالآيات: البقرة 1–5 ← البقرة 6–10', () {
    final m = WirdMark(
      type: WirdType.newTasmee,
      from: q.indexOf(2, 1),
      to: q.indexOf(2, 5),
    ).next();
    expect(q.label(m.from), 'البقرة 6');
    expect(q.label(m.to), 'البقرة 10');
  });
}
