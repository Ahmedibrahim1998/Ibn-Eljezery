import 'dart:convert';

import 'package:flutter/services.dart';

/// سورة من الفهرس.
class Sura {
  const Sura({
    required this.number,
    required this.name,
    required this.ayahCount,
    required this.start,
    required this.isMeccan,
  });

  final int number;
  final String name;
  final int ayahCount;

  /// رقم أول آية في السورة من 0 لـ 6235 (ترقيم موحّد للمصحف كله).
  final int start;
  final bool isMeccan;
}

/// نص القرآن + الفهرس، من assets/quran/quran.json (Tanzil — نسخة حرفية بدون تعديل).
///
/// كل آية ليها "رقم موحّد" (index) من 0 لـ 6235، وده بيسهّل الحسابات:
/// الآية 2:255 ← index 261، والمقطع "من آية لآية" = من index لـ index.
class QuranData {
  QuranData._(
    this.suras,
    this._verses,
    this._search,
    this._juzStarts,
    this._pageStarts,
  ) : _suraStarts = [for (final s in suras) s.start];

  static late QuranData instance;

  static const totalVerses = 6236;
  static const totalPages = 604;
  static const totalJuz = 30;

  final List<Sura> suras;
  final List<String> _verses;

  /// نفس الآيات بالرسم الإملائي بدون تشكيل (Tanzil simple-clean) ← للبحث بس.
  final List<String> _search;
  late final List<String> _normalizedSearch = _search
      .map(normalizeArabic)
      .toList();
  final List<int> _juzStarts;
  final List<int> _pageStarts;
  final List<int> _suraStarts;

  /// يتنادى مرة واحدة في main() قبل ما التطبيق يفتح.
  static Future<void> load() async {
    final json = jsonDecode(
      await rootBundle.loadString('assets/quran/quran.json'),
    );

    final suras = <Sura>[
      for (final s in json['suras'])
        Sura(
          number: s['n'],
          name: s['name'],
          ayahCount: s['ayas'],
          start: s['start'],
          isMeccan: s['type'] == 'meccan',
        ),
    ];
    int toIndex(List p) => suras[(p[0] as int) - 1].start + (p[1] as int) - 1;

    // نص Tanzil حاطط البسملة في أول الآية الأولى من كل سورة، وهي مش منها
    // (غير الفاتحة). المصحف بيعرضها سطر لوحدها، فنشيلها من نص الآية
    // عشان ماتتكررش، وعشان مشاركة الآية ونسخها تبقى صح.
    final verses = List<String>.from(json['verses']);
    // «الرحيم» زي ما هي مكتوبة في الفاتحة (نفس ترتيب التشكيل اللي في الملف)
    final rahim = verses[0].split(' ').last;
    for (final s in suras) {
      if (s.number == 1 || s.number == 9) continue;
      final words = verses[s.start].split(' ');
      if (words.length > 4 && words[3] == rahim) {
        verses[s.start] = words.skip(4).join(' ');
      }
    }

    instance = QuranData._(
      suras,
      verses,
      List<String>.from(json['search']),
      [for (final p in json['juz']) toIndex(p)],
      [for (final p in json['pages']) toIndex(p)],
    );
  }

  String text(int index) => _verses[index];

  int indexOf(int sura, int ayah) => suras[sura - 1].start + ayah - 1;

  Sura suraOf(int index) => suras[_lastStartAtOrBefore(_suraStarts, index)];

  int ayahOf(int index) => index - suraOf(index).start + 1;

  /// رقم الصفحة (1–604) اللي فيها الآية.
  int pageOf(int index) => _lastStartAtOrBefore(_pageStarts, index) + 1;

  /// رقم الجزء (1–30) اللي فيه الآية.
  int juzOf(int index) => _lastStartAtOrBefore(_juzStarts, index) + 1;

  int juzStart(int juz) => _juzStarts[juz - 1];

  /// آخر آية في الجزء.
  int juzEnd(int juz) =>
      juz == totalJuz ? totalVerses - 1 : _juzStarts[juz] - 1;

  /// أول وآخر آية في الصفحة.
  (int, int) pageRange(int page) => (
    _pageStarts[page - 1],
    page == totalPages ? totalVerses - 1 : _pageStarts[page] - 1,
  );

  /// البحث في القرآن: بيتجاهل التشكيل والهمزات والتاء المربوطة. بيرجّع أرقام الآيات.
  List<int> search(String query, {int limit = 200}) {
    final q = normalizeArabic(query.trim());
    if (q.length < 2) return const [];
    final results = <int>[];
    for (
      var i = 0;
      i < _normalizedSearch.length && results.length < limit;
      i++
    ) {
      if (_normalizedSearch[i].contains(q)) results.add(i);
    }
    return results;
  }

  /// "البقرة 255"
  String label(int index) => '${suraOf(index).name} ${ayahOf(index)}';

  /// بحث ثنائي: آخر بداية <= index.
  static int _lastStartAtOrBefore(List<int> starts, int index) {
    var lo = 0, hi = starts.length - 1;
    while (lo < hi) {
      final mid = (lo + hi + 1) ~/ 2;
      if (starts[mid] <= index) {
        lo = mid;
      } else {
        hi = mid - 1;
      }
    }
    return lo;
  }
}

/// توحيد الحروف للبحث: أ إ آ ٱ ← ا، ة ← ه، ى ← ي، ؤ ئ ← ء، وحذف التشكيل والتطويل.
String normalizeArabic(String s) => s
    .replaceAll(RegExp('[ً-ٰٟـ]'), '')
    .replaceAll(RegExp('[أإآٱ]'), 'ا')
    .replaceAll('ة', 'ه')
    .replaceAll('ى', 'ي')
    .replaceAll(RegExp('[ؤئ]'), 'ء');

/// 255 ← ٢٥٥
String arabicDigits(int n) =>
    n.toString().split('').map((d) => '٠١٢٣٤٥٦٧٨٩'[int.parse(d)]).join();
