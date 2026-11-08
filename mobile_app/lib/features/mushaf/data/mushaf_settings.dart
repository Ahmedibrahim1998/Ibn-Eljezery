import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// شكل عرض الصفحة.
enum MushafDisplay {
  /// مصحف المدينة: 15 سطر بخط المجمع (الخط بيتنزل لكل صفحة أول مرة).
  madani('مصحف المدينة'),

  /// نص عثماني بخط أميري، حجمه بيتظبط لوحده عشان الصفحة كلها تظهر.
  text('نص');

  const MushafDisplay(this.label);
  final String label;
}

/// إعدادات المصحف (منفصلة عن إعدادات التطبيق العامة).
class MushafSettings extends ChangeNotifier {
  MushafSettings._(this._prefs);

  static late MushafSettings instance;

  static const _displayKey = 'mushaf_display';
  static const _verticalKey = 'mushaf_vertical';
  static const _textSizeKey = 'quran_font_size';
  static const _reciterKey = 'reciter';
  static const _repeatKey = 'ayah_repeat';
  static const _followKey = 'audio_follow';

  static const minTextSize = 16.0;
  static const maxTextSize = 36.0;
  static const repeatOptions = [1, 2, 3, 5, 7, 10];

  final SharedPreferences _prefs;

  static Future<void> load() async {
    instance = MushafSettings._(await SharedPreferences.getInstance());
  }

  MushafDisplay get display =>
      MushafDisplay.values.asNameMap()[_prefs.getString(_displayKey)] ??
      MushafDisplay.madani;

  /// التقليب: false = يمين/شمال (زي المصحف الورقي)، true = فوق/تحت.
  bool get verticalPaging => _prefs.getBool(_verticalKey) ?? false;

  /// أقصى حجم لخط وضع النص (الصفحة ممكن تصغّره عشان تكفي).
  double get textSize => _prefs.getDouble(_textSizeKey) ?? 24;

  String get reciterId => _prefs.getString(_reciterKey) ?? 'Alafasy_128kbps';

  /// عدد مرات تكرار كل آية في التلاوة (للحفظ).
  int get repeat => _prefs.getInt(_repeatKey) ?? 1;

  /// الصفحة تقلّب لوحدها مع التلاوة.
  bool get followAudio => _prefs.getBool(_followKey) ?? true;

  Future<void> setDisplay(MushafDisplay v) =>
      _set(() => _prefs.setString(_displayKey, v.name));
  Future<void> setVerticalPaging(bool v) =>
      _set(() => _prefs.setBool(_verticalKey, v));
  Future<void> setTextSize(double v) => _set(
    () => _prefs.setDouble(_textSizeKey, v.clamp(minTextSize, maxTextSize)),
  );
  Future<void> setReciter(String id) =>
      _set(() => _prefs.setString(_reciterKey, id));
  Future<void> setRepeat(int v) => _set(() => _prefs.setInt(_repeatKey, v));
  Future<void> setFollowAudio(bool v) =>
      _set(() => _prefs.setBool(_followKey, v));

  Future<void> _set(Future<bool> Function() write) async {
    await write();
    notifyListeners();
  }
}
