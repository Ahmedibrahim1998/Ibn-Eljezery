import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../domain/khatma_plan.dart';

/// خطة الختمة الحالية + عدد الختمات اللي خلصت (على الموبايل بس).
class KhatmaStore extends ChangeNotifier {
  KhatmaStore._(this._prefs);

  static late KhatmaStore instance;

  static const _planKey = 'khatma_plan';
  static const _countKey = 'khatma_count';

  final SharedPreferences _prefs;
  KhatmaPlan? _plan;

  static Future<void> load() async {
    instance = KhatmaStore._(await SharedPreferences.getInstance());
    final raw = instance._prefs.getString(_planKey);
    if (raw != null) instance._plan = KhatmaPlan.fromJson(jsonDecode(raw));
  }

  KhatmaPlan? get plan => _plan;

  /// عدد الختمات اللي اكتملت بالخطة.
  int get completed => _prefs.getInt(_countKey) ?? 0;

  Future<void> start(int days, {int fromPage = 1, DateTime? now}) => _save(
    KhatmaPlan(start: now ?? DateTime.now(), days: days, page: fromPage),
  );

  /// "خلصت حصة النهارده" ← الخطة تتقدم. بيرجع true لو الختمة كملت.
  Future<bool> completePortion({DateTime? now}) async {
    final plan = _plan;
    if (plan == null) return false;
    final at = now ?? DateTime.now();
    final (_, to) = plan.portion(at);
    final next = plan.copyWith(page: to + 1, lastDone: at);
    if (next.finished) {
      await _prefs.setInt(_countKey, completed + 1);
      await _save(null);
      return true;
    }
    await _save(next);
    return false;
  }

  Future<void> cancel() => _save(null);

  /// "تراجع": رجّع الخطة زي ما كانت.
  Future<void> restore(KhatmaPlan plan) => _save(plan);

  Future<void> _save(KhatmaPlan? plan) async {
    _plan = plan;
    if (plan == null) {
      await _prefs.remove(_planKey);
    } else {
      await _prefs.setString(_planKey, jsonEncode(plan.toJson()));
    }
    notifyListeners();
  }
}
