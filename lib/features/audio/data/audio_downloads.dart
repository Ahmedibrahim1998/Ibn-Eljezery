import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:path_provider/path_provider.dart';

import '../../mushaf/data/quran_data.dart';
import '../domain/reciter.dart';

/// تنزيل التلاوة جزء جزء عشان تشتغل من غير نت.
///
/// الملفات: {documents}/audio/{reciterId}/SSSAAA.mp3 — نفس أسماء EveryAyah.
/// المشغّل بيدوّر هنا الأول، ولو الآية مش موجودة بيشغّلها من النت.
class AudioDownloads extends ChangeNotifier {
  AudioDownloads._(this._root);

  static late AudioDownloads instance;

  final String _root;
  final _dio = Dio();

  /// أسماء الملفات المحفوظة لكل قارئ (بتتقري من الفولدر مرة واحدة).
  final Map<String, Set<String>> _saved = {};

  /// الجزء اللي بيتنزل دلوقتي + نسبة التقدم.
  int? downloadingJuz;
  double? progress;
  CancelToken? _cancel;

  static Future<void> load() async {
    final dir = await getApplicationDocumentsDirectory();
    instance = AudioDownloads._('${dir.path}/audio');
  }

  static String fileName(int sura, int ayah) =>
      '${sura.toString().padLeft(3, '0')}${ayah.toString().padLeft(3, '0')}.mp3';

  Set<String> _savedFor(String reciterId) => _saved.putIfAbsent(reciterId, () {
    final dir = Directory('$_root/$reciterId');
    if (!dir.existsSync()) return <String>{};
    return {for (final f in dir.listSync()) f.uri.pathSegments.last};
  });

  /// مسار الملف لو الآية متنزّلة، وإلا null.
  String? localPath(String reciterId, int verse) {
    final q = QuranData.instance;
    final name = fileName(q.suraOf(verse).number, q.ayahOf(verse));
    return _savedFor(reciterId).contains(name)
        ? '$_root/$reciterId/$name'
        : null;
  }

  /// كام آية من الجزء متنزّلة.
  int savedInJuz(String reciterId, int juz) {
    final q = QuranData.instance;
    final saved = _savedFor(reciterId);
    var n = 0;
    for (var v = q.juzStart(juz); v <= q.juzEnd(juz); v++) {
      if (saved.contains(fileName(q.suraOf(v).number, q.ayahOf(v)))) n++;
    }
    return n;
  }

  /// نزّل الجزء (الآيات الناقصة بس). بيرجع false لو حصلت مشكلة أو اتلغى.
  Future<bool> downloadJuz(Reciter reciter, int juz) async {
    if (downloadingJuz != null) return false;
    final q = QuranData.instance;
    final saved = _savedFor(reciter.id);
    final verses = [
      for (var v = q.juzStart(juz); v <= q.juzEnd(juz); v++)
        if (!saved.contains(fileName(q.suraOf(v).number, q.ayahOf(v)))) v,
    ];
    downloadingJuz = juz;
    progress = 0;
    _cancel = CancelToken();
    notifyListeners();
    await Directory('$_root/${reciter.id}').create(recursive: true);
    try {
      for (final (i, v) in verses.indexed) {
        final sura = q.suraOf(v).number, ayah = q.ayahOf(v);
        final name = fileName(sura, ayah);
        // بينزل في ملف مؤقت الأول عشان ملف ناقص ما يتحسبش متنزّل
        final path = '$_root/${reciter.id}/$name';
        await _dio.download(
          reciter.urlFor(sura, ayah),
          '$path.part',
          cancelToken: _cancel,
        );
        await File('$path.part').rename(path);
        saved.add(name);
        progress = (i + 1) / verses.length;
        notifyListeners();
      }
      return true;
    } catch (_) {
      return false;
    } finally {
      downloadingJuz = progress = _cancel = null;
      notifyListeners();
    }
  }

  void cancel() => _cancel?.cancel();

  /// امسح الجزء من الموبايل.
  Future<void> deleteJuz(String reciterId, int juz) async {
    final q = QuranData.instance;
    final saved = _savedFor(reciterId);
    for (var v = q.juzStart(juz); v <= q.juzEnd(juz); v++) {
      final name = fileName(q.suraOf(v).number, q.ayahOf(v));
      if (saved.remove(name)) {
        final f = File('$_root/$reciterId/$name');
        if (await f.exists()) await f.delete();
      }
    }
    notifyListeners();
  }
}
