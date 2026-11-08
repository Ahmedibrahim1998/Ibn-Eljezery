import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/features/mushaf/data/mushaf_layout.dart';
import 'package:ibneljezery_app/features/mushaf/data/quran_data.dart';

void main() {
  late MushafLayout layout;

  setUpAll(() async {
    TestWidgetsFlutterBinding.ensureInitialized();
    await QuranData.load();
    layout = await MushafLayout.load();
  });

  List<String> kinds(int page) => [
    for (final s in layout.slots(page))
      switch (s) {
        WordsSlot() => 'w',
        SuraHeaderSlot(:final sura) => 'h${sura.number}',
        BasmalaSlot() => 'b',
        EmptySlot() => '-',
      },
  ];

  test('كل الصفحات العادية 15 سطر', () {
    for (var p = 3; p <= QuranData.totalPages; p++) {
      expect(layout.slots(p).length, 15, reason: 'page $p');
    }
  });

  test('كل آية موجودة في صفحتها الصح', () {
    final q = QuranData.instance;
    for (var p = 1; p <= QuranData.totalPages; p++) {
      final (first, last) = q.pageRange(p);
      final verses = {
        for (final s in layout.slots(p).whereType<WordsSlot>())
          for (final w in s.words) w.verse,
      };
      expect(verses, {
        for (var v = first; v <= last; v++) v,
      }, reason: 'page $p');
    }
  });

  test('الفاتحة: عنوان بدون بسملة منفصلة (البسملة آية فيها)', () {
    expect(kinds(1).first, 'h1');
    expect(kinds(1).contains('b'), isFalse);
  });

  test('أول البقرة: عنوان + بسملة', () {
    expect(kinds(2).take(2), ['h2', 'b']);
  });

  test('التوبة: عنوان من غير بسملة', () {
    final page = QuranData.instance.pageOf(QuranData.instance.indexOf(9, 1));
    final k = kinds(page);
    expect(k, contains('h9'));
    expect(k[k.indexOf('h9') + 1], 'w');
  });

  test('صفحة 604: الإخلاص والفلق والناس كل واحدة بعنوان وبسملة', () {
    final k = kinds(604);
    for (final sura in [112, 113, 114]) {
      final i = k.indexOf('h$sura');
      expect(i, isNot(-1), reason: 'sura $sura');
      expect(k[i + 1], 'b', reason: 'basmala after sura $sura');
    }
  });

  test('كل سورة ليها عنوان في المصحف', () {
    final headers = <int>{};
    for (var p = 1; p <= QuranData.totalPages; p++) {
      headers.addAll(
        layout.slots(p).whereType<SuraHeaderSlot>().map((s) => s.sura.number),
      );
    }
    expect(headers.length, 114);
  });
}
