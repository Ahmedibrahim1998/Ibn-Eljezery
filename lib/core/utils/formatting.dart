import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../features/mushaf/data/quran_data.dart';

const _weekdays = [
  'الاتنين',
  'التلات',
  'الأربع',
  'الخميس',
  'الجمعة',
  'السبت',
  'الحد',
];

/// "النهارده ٨:٠٠ م" / "بكره ٨:٠٠ م" / "الحد ١٢/١١ • ٨:٠٠ م".
String formatSessionTime(DateTime t, [DateTime? now]) {
  final today = DateUtils.dateOnly(now ?? DateTime.now());
  final days = DateUtils.dateOnly(t).difference(today).inDays;
  final hour = t.hour % 12 == 0 ? 12 : t.hour % 12;
  final time =
      '${arabicDigits(hour)}:${arabicDigits(t.minute).padLeft(2, '٠')} ${t.hour < 12 ? 'ص' : 'م'}';
  return switch (days) {
    0 => 'النهارده $time',
    1 => 'بكره $time',
    _ =>
      '${_weekdays[t.weekday - 1]} ${arabicDigits(t.day)}/${arabicDigits(t.month)} • $time',
  };
}

/// افتح رابط برّه التطبيق (Zoom / واتساب / المتصفح). لو مفيش تطبيق يفتحه ← رسالة.
Future<void> openExternal(BuildContext context, String url) async {
  final ok = await launchUrl(
    Uri.parse(url),
    mode: LaunchMode.externalApplication,
  );
  if (!ok && context.mounted) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('مش لاقي تطبيق يفتح الرابط ده')),
    );
  }
}
