import 'dart:convert';

import 'package:flutter/services.dart';

import 'quran_data.dart';

/// كلمة في سطر: رمزها في خط المجمع للصفحة + رقم الآية الموحّد.
typedef MushafWord = ({String code, int verse});

/// سطر من الـ 15 سطر في صفحة مصحف المدينة.
sealed class MushafSlot {
  const MushafSlot();
}

class WordsSlot extends MushafSlot {
  const WordsSlot(this.words);
  final List<MushafWord> words;
}

class SuraHeaderSlot extends MushafSlot {
  const SuraHeaderSlot(this.sura);
  final Sura sura;
}

class BasmalaSlot extends MushafSlot {
  const BasmalaSlot();
}

class EmptySlot extends MushafSlot {
  const EmptySlot();
}

/// ترتيب سطور مصحف المدينة (assets/quran/mushaf_layout.json — من tool/build_mushaf_layout.js).
///
/// السطور اللي مفيهاش كلمات هي عنوان سورة أو بسملة، وبنستنتجها من أول آية بعدها.
class MushafLayout {
  MushafLayout._(this._pages);

  static MushafLayout? _instance;
  static Future<MushafLayout>? _loading;

  /// بيتحمّل مرة واحدة أول ما حد يحتاجه (الملف كبير شوية فمش بنأخّر فتح التطبيق).
  static Future<MushafLayout> load() => _loading ??= () async {
    final json = jsonDecode(
      await rootBundle.loadString('assets/quran/mushaf_layout.json'),
    );
    final pages = <Map<int, List<MushafWord>>>[
      for (final page in json['pages'])
        {
          for (final line in page)
            line[0] as int: [
              for (final w in line[1])
                (code: w[0] as String, verse: w[1] as int),
            ],
        },
    ];
    return _instance = MushafLayout._(pages);
  }();

  static MushafLayout? get instance => _instance;

  final List<Map<int, List<MushafWord>>> _pages;

  /// الصفحة 1 و 2 (الفاتحة وأول البقرة) أسطرها أقل ومتوسّطة.
  static bool isOpeningPage(int page) => page <= 2;

  /// الـ 15 سطر بتوع الصفحة بالترتيب.
  List<MushafSlot> slots(int page) {
    final q = QuranData.instance;
    final lines = _pages[page - 1];
    final last = lines.keys.reduce((a, b) => a > b ? a : b);
    final (pageFirstVerse, pageLastVerse) = q.pageRange(page);

    // أول آية بعد مجموعة سطور فاضية (أو أول آية في الصفحة الجاية لو الفراغ في الآخر)
    int verseAfter(int line) {
      for (var n = line + 1; n <= 15; n++) {
        if (lines[n] != null) return lines[n]!.first.verse;
      }
      return pageLastVerse + 1;
    }

    // الصفحة 1 و 2: العنوان (والبسملة) فوق، والأسطر بتخلص قبل الـ 15
    final range = isOpeningPage(page) ? (1, last) : (1, 15);
    final slots = <MushafSlot>[];
    var n = range.$1;
    while (n <= range.$2) {
      if (lines[n] != null) {
        slots.add(WordsSlot(lines[n]!));
        n++;
        continue;
      }
      // مجموعة سطور فاضية ورا بعض
      var gapEnd = n;
      while (gapEnd + 1 <= range.$2 && lines[gapEnd + 1] == null) {
        gapEnd++;
      }
      final gap = gapEnd - n + 1;
      final next = verseAfter(gapEnd);
      if (next >= QuranData.totalVerses || q.ayahOf(next) != 1) {
        slots.addAll(List.filled(gap, const EmptySlot()));
      } else {
        final sura = q.suraOf(next);
        final hasBasmala = sura.number != 1 && sura.number != 9;
        // فراغ سطر واحد في أول الصفحة والعنوان كان في آخر الصفحة اللي قبلها ← بسملة بس
        final basmalaOnly =
            gap == 1 &&
            n == 1 &&
            next == pageFirstVerse &&
            hasBasmala &&
            _endsWithGap(page - 1);
        if (basmalaOnly) {
          slots.add(const BasmalaSlot());
        } else {
          slots.add(SuraHeaderSlot(sura));
          if (gap >= 2 && hasBasmala) slots.add(const BasmalaSlot());
          final used = 1 + (gap >= 2 && hasBasmala ? 1 : 0);
          slots.addAll(List.filled(gap - used, const EmptySlot()));
        }
      }
      n = gapEnd + 1;
    }
    return slots;
  }

  /// أول آية في الصفحة حسب السطور (لو آية بدأت في الصفحة اللي قبلها وكملت هنا ← هي دي).
  int firstVerse(int page) {
    final lines = _pages[page - 1];
    final first = lines.keys.reduce((a, b) => a < b ? a : b);
    return lines[first]!.first.verse;
  }

  bool _endsWithGap(int page) => page >= 1 && _pages[page - 1][15] == null;
}
