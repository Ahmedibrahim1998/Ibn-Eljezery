import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../domain/progress_entry.dart';
import '../domain/wird_mark.dart';

/// بيحفظ علامات الورد وسجل الإنجاز وآخر صفحة اتقرت على الموبايل (بدون إنترنت وبدون حساب).
///
/// ChangeNotifier ← أي شاشة بتسمع له (ListenableBuilder) بتتحدث لوحدها لما علامة تتغير.
class WirdStore extends ChangeNotifier {
  WirdStore._(this._prefs);

  static late WirdStore instance;

  static const _marksKey = 'wird_marks';
  static const _lastPageKey = 'last_read_page';
  static const _historyKey = 'wird_history';

  /// السجل بيحتفظ بآخر 500 إنجاز (أكتر من سنة لطالب ملتزم).
  static const _maxHistory = 500;

  final SharedPreferences _prefs;
  final Map<WirdType, WirdMark> _marks = {};
  final List<ProgressEntry> _history = [];

  static Future<void> load() async {
    instance = WirdStore._(await SharedPreferences.getInstance());
    instance._read();
  }

  WirdMark? mark(WirdType type) => _marks[type];

  /// علامات الورد اللي فيها الآية دي (ممكن أكتر من واحدة).
  List<WirdMark> marksAt(int index) => [
    for (final m in _marks.values)
      if (m.contains(index)) m,
  ];

  int? get lastReadPage => _prefs.getInt(_lastPageKey);

  Future<void> setLastReadPage(int page) => _prefs.setInt(_lastPageKey, page);

  Future<void> save(WirdMark mark) async {
    _marks[mark.type] = mark;
    await _write();
  }

  Future<void> remove(WirdType type) async {
    _marks.remove(type);
    await _write();
  }

  /// "خلصت" ← سجّل الإنجاز، وانقل العلامة للمقطع اللي بعده.
  Future<void> advance(WirdType type, {DateTime? at}) async {
    final current = _marks[type];
    if (current == null) return;
    _history.insert(
      0,
      ProgressEntry(
        date: at ?? DateTime.now(),
        type: type,
        from: current.from,
        to: current.to,
      ),
    );
    if (_history.length > _maxHistory) {
      _history.removeRange(_maxHistory, _history.length);
    }
    await _prefs.setString(
      _historyKey,
      jsonEncode([for (final e in _history) e.toJson()]),
    );
    await save(current.next());
  }

  /// سجل الإنجاز (الأحدث الأول).
  List<ProgressEntry> get history => List.unmodifiable(_history);

  ProgressStats stats([DateTime? now]) =>
      ProgressStats(_history, now ?? DateTime.now());

  /// امسح آخر "خلصت" (لو اتداست بالغلط) ورجّع العلامة لمكانها.
  Future<void> undoLast(WirdType type) async {
    final i = _history.indexWhere((e) => e.type == type);
    if (i == -1) return;
    final entry = _history.removeAt(i);
    await _prefs.setString(
      _historyKey,
      jsonEncode([for (final e in _history) e.toJson()]),
    );
    final mark = _marks[type];
    await save(
      WirdMark(
        type: type,
        from: entry.from,
        to: entry.to,
        byJuz: mark?.byJuz ?? false,
      ),
    );
  }

  void _read() {
    final history = _prefs.getString(_historyKey);
    if (history != null) {
      _history.addAll([
        for (final e in jsonDecode(history)) ProgressEntry.fromJson(e),
      ]);
    }
    final raw = _prefs.getString(_marksKey);
    if (raw == null) return;
    final map = jsonDecode(raw) as Map<String, dynamic>;
    for (final type in WirdType.values) {
      if (map[type.name] != null) {
        _marks[type] = WirdMark.fromJson(type, map[type.name]);
      }
    }
  }

  Future<void> _write() async {
    await _prefs.setString(
      _marksKey,
      jsonEncode({for (final m in _marks.values) m.type.name: m.toJson()}),
    );
    notifyListeners();
  }
}
