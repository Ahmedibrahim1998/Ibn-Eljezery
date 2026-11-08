import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../fiqh/domain/fiqh_models.dart';

/// إعدادات التطبيق العامة (الثيم + المذهب). إعدادات المصحف في MushafSettings.
class SettingsStore extends ChangeNotifier {
  SettingsStore._(this._prefs);

  static late SettingsStore instance;

  static const _themeKey = 'theme_mode';
  static const _madhhabKey = 'fiqh_madhhab';

  final SharedPreferences _prefs;

  static Future<void> load() async {
    instance = SettingsStore._(await SharedPreferences.getInstance());
  }

  ThemeMode get themeMode => ThemeMode.values.byName(
    _prefs.getString(_themeKey) ?? ThemeMode.system.name,
  );

  /// المذهب اللي الطالب اختاره في الفقه (بيفتح عليه المرة الجاية).
  Madhhab get madhhab =>
      Madhhab.values.asNameMap()[_prefs.getString(_madhhabKey)] ??
      Madhhab.maliki;

  Future<void> setThemeMode(ThemeMode mode) async {
    await _prefs.setString(_themeKey, mode.name);
    notifyListeners();
  }

  Future<void> setMadhhab(Madhhab madhhab) =>
      _prefs.setString(_madhhabKey, madhhab.name);
}
