import 'package:flutter/material.dart';

import '../../audio/domain/reciter.dart';
import '../../audio/presentation/audio_downloads_screen.dart';
import '../../reminder/reminder_service.dart';
import '../../wird/data/wird_store.dart';
import '../../wird/domain/wird_mark.dart';
import '../../wird/presentation/wird_edit_sheet.dart';
import '../data/mushaf_settings.dart';
import '../data/qcf_fonts.dart';
import '../data/quran_data.dart';

/// إعدادات المصحف: شكل العرض، التقليب، التلاوة، التنزيل، علامات الورد.
class MushafSettingsScreen extends StatelessWidget {
  const MushafSettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final s = MushafSettings.instance;
    final wird = WirdStore.instance;
    final fonts = QcfFonts.instance;

    return Scaffold(
      appBar: AppBar(title: const Text('إعدادات المصحف')),
      body: ListenableBuilder(
        listenable: Listenable.merge([s, wird, fonts]),
        builder: (context, _) => ListView(
          padding: const EdgeInsets.only(bottom: 24),
          children: [
            const _Header('شكل الصفحة'),
            _Padded(
              SegmentedButton<MushafDisplay>(
                segments: [
                  for (final d in MushafDisplay.values)
                    ButtonSegment(value: d, label: Text(d.label)),
                ],
                selected: {s.display},
                onSelectionChanged: (v) => s.setDisplay(v.first),
              ),
            ),
            _Padded(
              SegmentedButton<bool>(
                segments: const [
                  ButtonSegment(
                    value: false,
                    label: Text('تقليب يمين/شمال'),
                    icon: Icon(Icons.swap_horiz),
                  ),
                  ButtonSegment(
                    value: true,
                    label: Text('فوق/تحت'),
                    icon: Icon(Icons.swap_vert),
                  ),
                ],
                selected: {s.verticalPaging},
                onSelectionChanged: (v) => s.setVerticalPaging(v.first),
              ),
            ),
            if (s.display == MushafDisplay.text) ...[
              ListTile(
                title: const Text('أقصى حجم للخط'),
                subtitle: Text(
                  'الصفحة بتصغّره لوحدها لو مش كفاية',
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ),
              Slider(
                value: s.textSize,
                min: MushafSettings.minTextSize,
                max: MushafSettings.maxTextSize,
                divisions: 10,
                label: s.textSize.round().toString(),
                onChanged: s.setTextSize,
              ),
            ],
            const Divider(),
            const _Header('التلاوة'),
            ListTile(
              leading: const Icon(Icons.record_voice_over_outlined),
              title: const Text('القارئ'),
              subtitle: Text(Reciter.byId(s.reciterId).name),
              trailing: const Icon(Icons.chevron_left),
              onTap: () => _pickReciter(context),
            ),
            ListTile(
              leading: const Icon(Icons.repeat),
              title: const Text('تكرار كل آية (للحفظ)'),
              trailing: DropdownButton<int>(
                value: s.repeat,
                underline: const SizedBox.shrink(),
                items: [
                  for (final r in MushafSettings.repeatOptions)
                    DropdownMenuItem(
                      value: r,
                      child: Text(r == 1 ? 'بدون' : '${arabicDigits(r)} مرات'),
                    ),
                ],
                onChanged: (v) => s.setRepeat(v!),
              ),
            ),
            SwitchListTile(
              secondary: const Icon(Icons.auto_stories_outlined),
              title: const Text('الصفحة تقلّب مع التلاوة'),
              value: s.followAudio,
              onChanged: s.setFollowAudio,
            ),
            ListTile(
              leading: const Icon(Icons.download_outlined),
              title: const Text('تنزيل التلاوة (بدون إنترنت)'),
              subtitle: const Text('نزّل الأجزاء اللي بتسمعها كتير'),
              trailing: const Icon(Icons.chevron_left),
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const AudioDownloadsScreen()),
              ),
            ),
            const Divider(),
            const _Header('تذكير الورد'),
            ListenableBuilder(
              listenable: ReminderService.instance,
              builder: (context, _) {
                final r = ReminderService.instance;
                return Column(
                  children: [
                    SwitchListTile(
                      secondary: const Icon(Icons.notifications_active_outlined),
                      title: const Text('ذكّرني بوِردي كل يوم'),
                      value: r.enabled,
                      onChanged: (on) async {
                        final ok = await r.setEnabled(on);
                        if (!ok && context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text(
                                'لازم تسمح بالإشعارات من إعدادات الموبايل',
                              ),
                            ),
                          );
                        }
                      },
                    ),
                    if (r.enabled)
                      ListTile(
                        leading: const Icon(Icons.schedule),
                        title: const Text('الميعاد'),
                        trailing: Text(
                          r.time.format(context),
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        onTap: () async {
                          final t = await showTimePicker(
                            context: context,
                            initialTime: r.time,
                          );
                          if (t != null) await r.setTime(t);
                        },
                      ),
                  ],
                );
              },
            ),
            const Divider(),
            const _Header('مصحف المدينة بدون إنترنت'),
            FutureBuilder<int>(
              future: fonts.downloadedCount(),
              builder: (context, snap) {
                final count = snap.data ?? 0;
                final progress = fonts.downloadAllProgress;
                return Column(
                  children: [
                    ListTile(
                      leading: const Icon(Icons.download_for_offline_outlined),
                      title: Text(
                        'الصفحات المحفوظة: ${arabicDigits(count)} من ${arabicDigits(QuranData.totalPages)}',
                      ),
                      subtitle: const Text(
                        'كل صفحة بتتحفظ أول ما تفتحها. المصحف كله حوالي ٢٠ ميجا.',
                      ),
                    ),
                    if (progress != null)
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: LinearProgressIndicator(value: progress),
                      )
                    else
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          if (count < QuranData.totalPages)
                            FilledButton.icon(
                              onPressed: () async {
                                final ok = await fonts.downloadAll();
                                if (context.mounted) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(
                                        ok ? 'تم تنزيل المصحف كاملاً ✓' : 'بعض الصفحات لم تُنزَّل — حاول مرة أخرى',
                                      ),
                                    ),
                                  );
                                }
                              },
                              icon: const Icon(Icons.download),
                              label: const Text('تنزيل المصحف كله'),
                            ),
                          const SizedBox(width: 8),
                          if (count > 0)
                            TextButton(
                              onPressed: fonts.deleteAll,
                              child: const Text('حذف المحفوظ'),
                            ),
                        ],
                      ),
                  ],
                );
              },
            ),
            const Divider(),
            const _Header('علامات الورد'),
            for (final type in WirdType.values)
              ListTile(
                leading: Icon(Icons.bookmark, color: type.color),
                title: Text(type.label),
                subtitle: Text(wird.mark(type)?.describe() ?? 'غير محددة'),
                trailing: const Icon(Icons.edit_outlined),
                onTap: () => showWirdEditSheet(context, type),
              ),
          ],
        ),
      ),
    );
  }

  void _pickReciter(BuildContext context) {
    final s = MushafSettings.instance;
    showModalBottomSheet(
      context: context,
      showDragHandle: true,
      builder: (context) => SafeArea(
        child: ListView(
          shrinkWrap: true,
          children: [
            for (final r in Reciter.all)
              ListTile(
                leading: Icon(
                  r.id == s.reciterId
                      ? Icons.radio_button_checked
                      : Icons.radio_button_off,
                ),
                title: Text(r.name),
                onTap: () {
                  s.setReciter(r.id);
                  Navigator.pop(context);
                },
              ),
          ],
        ),
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
      child: Text(
        text,
        style: Theme.of(context).textTheme.titleSmall
            ?.copyWith(color: Theme.of(context).colorScheme.primary),
      ),
    );
  }
}

class _Padded extends StatelessWidget {
  const _Padded(this.child);

  final Widget child;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
    child: SizedBox(width: double.infinity, child: child),
  );
}
