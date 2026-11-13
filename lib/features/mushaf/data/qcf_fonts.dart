import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:path_provider/path_provider.dart';

import 'quran_data.dart';

/// خطوط مجمع الملك فهد (QCF V1): خط لكل صفحة (~30 كيلوبايت).
///
/// الصفحة بتتنزل أول مرة تتفتح وتتحفظ على الموبايل، وبعد كده بتشتغل من غير نت.
/// المصحف كله ≈ 20 ميجا لو الطالب اختار "تنزيل الكل" من إعدادات المصحف.
class QcfFonts extends ChangeNotifier {
  QcfFonts._();

  static final instance = QcfFonts._();

  static String family(int page) => 'QCF_P${page.toString().padLeft(3, '0')}';

  static String _url(int page) =>
      'https://static.qurancdn.com/fonts/quran/hafs/v1/ttf/p$page.ttf';

  final _dio = Dio(
    BaseOptions(
      connectTimeout: const Duration(seconds: 6),
      receiveTimeout: const Duration(seconds: 15),
    ),
  );
  final _loaded = <int>{};
  final _pending = <int, Future<bool>>{};
  Directory? _dir;

  /// تقدم "تنزيل الكل" (null = مفيش تنزيل شغال).
  double? downloadAllProgress;

  bool isLoaded(int page) => _loaded.contains(page);

  /// خط الصفحة متنزّل على الموبايل؟ (من غير ما نلمس النت)
  Future<bool> isDownloaded(int page) async {
    if (_loaded.contains(page)) return true;
    try {
      return await _file(await _directory(), page).exists();
    } catch (_) {
      return false;
    }
  }

  Future<Directory> _directory() async {
    return _dir ??= await Directory(
      '${(await getApplicationSupportDirectory()).path}/qcf',
    ).create(recursive: true);
  }

  File _file(Directory dir, int page) => File('${dir.path}/p$page.ttf');

  /// جهّز خط الصفحة (من الموبايل أو من النت). بيرجّع false لو مفيش نت والصفحة مش متنزّلة.
  Future<bool> ensure(int page) {
    if (_loaded.contains(page)) return Future.value(true);
    return _pending[page] ??= _ensure(page)
        .whenComplete(() => _pending.remove(page));
  }

  Future<bool> _ensure(int page) async {
    try {
      final file = _file(await _directory(), page);
      if (!await file.exists()) {
        await _dio.download(_url(page), '${file.path}.part');
        await File('${file.path}.part').rename(file.path);
      }
      final bytes = await file.readAsBytes();
      final loader = FontLoader(family(page))
        ..addFont(Future.value(ByteData.sublistView(bytes)));
      await loader.load();
      _loaded.add(page);
      return true;
    } catch (_) {
      return false;
    }
  }

  Future<int> downloadedCount() async {
    final dir = await _directory();
    return dir
        .listSync()
        .whereType<File>()
        .where((f) => f.path.endsWith('.ttf'))
        .length;
  }

  /// تنزيل المصحف كله (بيكمل من مكان ما وقف لو اتقطع).
  Future<bool> downloadAll() async {
    if (downloadAllProgress != null) return false;
    final dir = await _directory();
    var failed = 0;
    for (var page = 1; page <= QuranData.totalPages; page++) {
      downloadAllProgress = page / QuranData.totalPages;
      if (page % 5 == 0) notifyListeners();
      final file = _file(dir, page);
      if (await file.exists()) continue;
      try {
        await _dio.download(_url(page), '${file.path}.part');
        await File('${file.path}.part').rename(file.path);
      } catch (_) {
        failed++;
      }
    }
    downloadAllProgress = null;
    notifyListeners();
    return failed == 0;
  }

  Future<void> deleteAll() async {
    final dir = await _directory();
    for (final f in dir.listSync().whereType<File>()) {
      f.deleteSync();
    }
    notifyListeners();
  }
}
