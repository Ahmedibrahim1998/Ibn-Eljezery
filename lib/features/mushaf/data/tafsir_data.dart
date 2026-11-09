import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

/// التفسير الميسر (مجمع الملك فهد) — جوه التطبيق، بيشتغل من غير نت.
///
/// الملف ~2.5 ميجا ← بيتحمّل أول مرة حد يفتح تفسير بس (مش مع فتح التطبيق)،
/// والـ JSON بيتحلل في isolate تاني عشان الشاشة ما تهنّجش.
class TafsirData {
  TafsirData._(this._tafsir);

  final List<String> _tafsir;

  static Future<TafsirData>? _loading;

  static Future<TafsirData> load() => _loading ??= () async {
    final raw = await rootBundle.loadString(
      'assets/quran/tafsir_muyassar.json',
    );
    return TafsirData._(await compute(_parse, raw));
  }();

  static List<String> _parse(String raw) =>
      List<String>.from((jsonDecode(raw) as Map<String, dynamic>)['tafsir']);

  /// تفسير الآية بالـ index العام (0..6235).
  String of(int verse) => _tafsir[verse];
}
