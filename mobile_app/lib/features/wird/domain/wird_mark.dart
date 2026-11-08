import 'package:flutter/material.dart';

import '../../mushaf/data/quran_data.dart';

/// أنواع علامات الورد (docs/mobile-app/01 §2.1.1) — علامة واحدة لكل نوع.
enum WirdType {
  newTasmee('تسميع جديد', Color(0xFF2E7D32)),
  nearReview('مراجعة قريبة', Color(0xFF1565C0)),
  farReview('مراجعة بعيدة', Color(0xFF6A1B9A)),
  tarteel('ترتيل', Color(0xFFB8860B));

  const WirdType(this.label, this.color);
  final String label;
  final Color color;
}

/// مقطع العلامة: من آية لآية (بالترقيم الموحّد 0–6235).
/// [byJuz] = الطالب اختاره بالأجزاء ← "خلصت" تنقله بنفس عدد الأجزاء.
class WirdMark {
  const WirdMark({
    required this.type,
    required this.from,
    required this.to,
    this.byJuz = false,
  });

  final WirdType type;
  final int from;
  final int to;
  final bool byJuz;

  bool contains(int index) => index >= from && index <= to;

  /// "الأجزاء ٢١ ← ٢٥" أو "البقرة 1 ← البقرة 5"
  String describe() {
    final q = QuranData.instance;
    if (byJuz) {
      final a = q.juzOf(from), b = q.juzOf(to);
      return a == b
          ? 'الجزء ${arabicDigits(a)}'
          : 'الأجزاء ${arabicDigits(a)} ← ${arabicDigits(b)}';
    }
    return '${q.label(from)} ← ${q.label(to)}';
  }

  /// المقطع اللي بعده بنفس الطول، ولو وصل لآخر المصحف يرجع من الأول.
  WirdMark next() {
    final q = QuranData.instance;
    if (byJuz) {
      final count = q.juzOf(to) - q.juzOf(from) + 1;
      var first = q.juzOf(to) + 1;
      if (first > QuranData.totalJuz) first = 1;
      final last = (first + count - 1).clamp(1, QuranData.totalJuz);
      return WirdMark(
        type: type,
        from: q.juzStart(first),
        to: q.juzEnd(last),
        byJuz: true,
      );
    }
    final length = to - from + 1;
    var first = to + 1;
    if (first >= QuranData.totalVerses) first = 0;
    final last = (first + length - 1).clamp(0, QuranData.totalVerses - 1);
    return WirdMark(type: type, from: first, to: last);
  }

  Map<String, dynamic> toJson() => {'from': from, 'to': to, 'byJuz': byJuz};

  factory WirdMark.fromJson(WirdType type, Map<String, dynamic> json) =>
      WirdMark(
        type: type,
        from: json['from'],
        to: json['to'],
        byJuz: json['byJuz'] ?? false,
      );
}
