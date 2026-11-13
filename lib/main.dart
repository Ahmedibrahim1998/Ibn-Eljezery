import 'package:flutter/material.dart';
import 'package:just_audio_background/just_audio_background.dart';

import 'app/app.dart';
import 'app/bootstrap.dart';
import 'core/network/api_client.dart';
import 'features/account/data/auth_store.dart';
import 'features/audio/data/audio_downloads.dart';
import 'features/khatma/data/khatma_store.dart';
import 'features/reminder/reminder_service.dart';
import 'features/mushaf/data/mushaf_layout.dart';
import 'features/mushaf/data/mushaf_settings.dart';
import 'features/mushaf/data/quran_data.dart';
import 'features/settings/data/settings_store.dart';
import 'features/wird/data/wird_store.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // التلاوة في الخلفية: إشعار فيه تشغيل/إيقاف/التالي، ويشتغل مع قفل الشاشة
  await JustAudioBackground.init(
    androidNotificationChannelId: 'com.ibneljezery.app.recitation',
    androidNotificationChannelName: 'التلاوة',
    androidNotificationOngoing: true,
  );
  // الثيم بس قبل أول شاشة (سريع)، والباقي بيتحمّل والواجهة الافتتاحية ظاهرة
  await SettingsStore.load();
  AppBootstrap.start(() async {
    await QuranData.load();
    await WirdStore.load();
    await MushafSettings.load();
    await ApiClient.load();
    await AuthStore.load();
    await KhatmaStore.load();
    await ReminderService.load();
    await AudioDownloads.load();
    // ترتيب سطور مصحف المدينة بيتحمّل في الخلفية (مش بيأخّر فتح التطبيق)
    MushafLayout.load();
  });
  runApp(const IbnEljezeryApp());
}
