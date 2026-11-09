import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// إعدادات التطبيق العامة (الثيم). إعدادات المصحف في MushafSettings.
class SettingsStore extends ChangeNotifier {
  SettingsStore._(this._prefs);

  static late SettingsStore instance;

  static const _themeKey = 'theme_mode';

  final SharedPreferences _prefs;

  static Future<void> load() async {
    instance = SettingsStore._(await SharedPreferences.getInstance());
  }

  ThemeMode get themeMode => ThemeMode.values.byName(
    _prefs.getString(_themeKey) ?? ThemeMode.system.name,
  );

  Future<void> setThemeMode(ThemeMode mode) async {
    await _prefs.setString(_themeKey, mode.name);
    notifyListeners();
  }
}
