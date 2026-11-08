import 'package:flutter/material.dart';

import '../../mushaf/data/mushaf_settings.dart';
import '../../mushaf/data/quran_data.dart';
import '../data/audio_downloads.dart';
import '../domain/reciter.dart';

/// تنزيل التلاوة بدون إنترنت: 30 جزء للقارئ المختار، كل جزء ينزل أو يتمسح لوحده.
class AudioDownloadsScreen extends StatelessWidget {
  const AudioDownloadsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final downloads = AudioDownloads.instance;
    final reciter = Reciter.byId(MushafSettings.instance.reciterId);
    final q = QuranData.instance;

    return Scaffold(
      appBar: AppBar(title: const Text('تنزيل التلاوة')),
      body: ListenableBuilder(
        listenable: downloads,
        builder: (context, _) => ListView(
          children: [
            ListTile(
              leading: const Icon(Icons.record_voice_over_outlined),
              title: Text('القارئ: ${reciter.name}'),
              subtitle: const Text(
                'الجزء حوالي ٢٠–٤٠ ميجا. التلاوة المتنزّلة بتشتغل من غير نت، '
                'ولتغيير القارئ ارجع لإعدادات المصحف.',
              ),
            ),
            const Divider(height: 1),
            for (var juz = 1; juz <= QuranData.totalJuz; juz++)
              _JuzTile(
                juz: juz,
                saved: downloads.savedInJuz(reciter.id, juz),
                total: q.juzEnd(juz) - q.juzStart(juz) + 1,
                reciter: reciter,
              ),
          ],
        ),
      ),
    );
  }
}

class _JuzTile extends StatelessWidget {
  const _JuzTile({
    required this.juz,
    required this.saved,
    required this.total,
    required this.reciter,
  });

  final int juz;
  final int saved;
  final int total;
  final Reciter reciter;

  @override
  Widget build(BuildContext context) {
    final downloads = AudioDownloads.instance;
    final busy = downloads.downloadingJuz == juz;
    final complete = saved == total;
    final scheme = Theme.of(context).colorScheme;

    return ListTile(
      leading: CircleAvatar(
        backgroundColor: complete
            ? scheme.primary
            : scheme.surfaceContainerHighest,
        foregroundColor: complete ? scheme.onPrimary : null,
        child: Text(arabicDigits(juz)),
      ),
      title: Text('الجزء ${arabicDigits(juz)}'),
      subtitle: busy
          ? LinearProgressIndicator(value: downloads.progress)
          : Text(
              complete
                  ? 'متنزّل ✓'
                  : saved == 0
                  ? 'مش متنزّل'
                  : '${arabicDigits(saved)} من ${arabicDigits(total)} آية',
            ),
      trailing: busy
          ? IconButton(
              tooltip: 'إيقاف',
              icon: const Icon(Icons.close),
              onPressed: downloads.cancel,
            )
          : Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (saved > 0)
                  IconButton(
                    tooltip: 'مسح',
                    icon: const Icon(Icons.delete_outline),
                    onPressed: () => downloads.deleteJuz(reciter.id, juz),
                  ),
                if (!complete)
                  IconButton(
                    tooltip: 'تنزيل',
                    icon: const Icon(Icons.download),
                    // جزء واحد في المرة عشان النت والبطارية
                    onPressed: downloads.downloadingJuz != null
                        ? null
                        : () async {
                            final ok = await downloads.downloadJuz(
                              reciter,
                              juz,
                            );
                            if (!ok && context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text(
                                    'التنزيل وقف — اللي اتنزّل محفوظ، دوس تنزيل تاني للباقي',
                                  ),
                                ),
                              );
                            }
                          },
                  ),
              ],
            ),
    );
  }
}
