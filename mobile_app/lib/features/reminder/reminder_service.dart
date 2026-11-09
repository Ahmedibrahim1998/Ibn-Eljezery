import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_timezone/flutter_timezone.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:timezone/data/latest_10y.dart' as tz;
import 'package:timezone/timezone.dart' as tz;

/// تذكير الورد اليومي: إشعار كل يوم في الميعاد اللي الطالب يختاره (من غير نت ومن غير سيرفر).
///
/// الإشعار بيتجدول عند أندرويد نفسه ← بيوصل حتى لو التطبيق مقفول، وبيرجع يتجدول بعد إعادة تشغيل الموبايل.
class ReminderService extends ChangeNotifier {
  ReminderService._(this._prefs);

  static late ReminderService instance;

  static const _enabledKey = 'reminder_enabled';
  static const _timeKey = 'reminder_minutes'; // دقايق من نص الليل
  static const _id = 1;

  final SharedPreferences _prefs;
  final _plugin = FlutterLocalNotificationsPlugin();
  bool _ready = false;

  static Future<void> load() async {
    instance = ReminderService._(await SharedPreferences.getInstance());
  }

  bool get enabled => _prefs.getBool(_enabledKey) ?? false;

  /// الميعاد الافتراضي: بعد العشا (8 بالليل).
  TimeOfDay get time {
    final m = _prefs.getInt(_timeKey) ?? 20 * 60;
    return TimeOfDay(hour: m ~/ 60, minute: m % 60);
  }

  Future<void> _init() async {
    if (_ready) return;
    tz.initializeTimeZones();
    try {
      final local = await FlutterTimezone.getLocalTimezone();
      tz.setLocalLocation(tz.getLocation(local.identifier));
    } catch (_) {
      tz.setLocalLocation(tz.getLocation('Africa/Cairo'));
    }
    await _plugin.initialize(
      settings: const InitializationSettings(
        android: AndroidInitializationSettings('@mipmap/ic_launcher'),
        iOS: DarwinInitializationSettings(),
      ),
    );
    _ready = true;
  }

  /// شغّل/اقفل التذكير. بيرجع false لو الطالب رفض إذن الإشعارات.
  Future<bool> setEnabled(bool on) async {
    await _init();
    if (on) {
      final granted =
          await _plugin
              .resolvePlatformSpecificImplementation<
                AndroidFlutterLocalNotificationsPlugin
              >()
              ?.requestNotificationsPermission() ??
          true;
      if (!granted) return false;
    }
    await _prefs.setBool(_enabledKey, on);
    await _schedule();
    notifyListeners();
    return true;
  }

  Future<void> setTime(TimeOfDay t) async {
    await _prefs.setInt(_timeKey, t.hour * 60 + t.minute);
    await _init();
    await _schedule();
    notifyListeners();
  }

  Future<void> _schedule() async {
    await _plugin.cancel(id: _id);
    if (!enabled) return;
    final now = tz.TZDateTime.now(tz.local);
    var at = tz.TZDateTime(
      tz.local,
      now.year,
      now.month,
      now.day,
      time.hour,
      time.minute,
    );
    if (!at.isAfter(now)) at = at.add(const Duration(days: 1));
    await _plugin.zonedSchedule(
      id: _id,
      scheduledDate: at,
      title: 'وِردك اليومي 📖',
      body: 'لا تنسَ وِردك اليوم — «أحبُّ الأعمال إلى الله أدومُها وإن قلّ»',
      notificationDetails: const NotificationDetails(
        android: AndroidNotificationDetails(
          'com.ibneljezery.app.reminder',
          'تذكير الورد',
          channelDescription: 'تذكير يومي بوِرد القرآن',
          importance: Importance.high,
          priority: Priority.high,
        ),
        iOS: DarwinNotificationDetails(),
      ),
      // من غير إذن "المنبّهات الدقيقة": ممكن يتأخر دقايق بسيطة، وده كفاية لتذكير ورد
      androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
      matchDateTimeComponents: DateTimeComponents.time,
    );
  }
}
