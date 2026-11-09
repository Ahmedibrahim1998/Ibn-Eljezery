import 'package:flutter/material.dart';

import '../../mushaf/presentation/mushaf_settings_screen.dart';
import '../data/settings_store.dart';

/// إعدادات التطبيق العامة. (إعدادات المصحف لها صفحة لوحدها جوه المصحف.)
class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  static const privacyUrl = String.fromEnvironment(
    'PRIVACY_URL',
    defaultValue: 'https://ibneljezery.app/privacy',
  );

  @override
  Widget build(BuildContext context) {
    final settings = SettingsStore.instance;

    return Scaffold(
      appBar: AppBar(title: const Text('الإعدادات')),
      body: ListenableBuilder(
        listenable: settings,
        builder: (context, _) => ListView(
          children: [
            const _Header('شكل التطبيق'),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: SegmentedButton<ThemeMode>(
                segments: const [
                  ButtonSegment(
                    value: ThemeMode.light,
                    label: Text('فاتح'),
                    icon: Icon(Icons.light_mode),
                  ),
                  ButtonSegment(
                    value: ThemeMode.dark,
                    label: Text('داكن'),
                    icon: Icon(Icons.dark_mode),
                  ),
                  ButtonSegment(value: ThemeMode.system, label: Text('تلقائي')),
                ],
                selected: {settings.themeMode},
                onSelectionChanged: (s) => settings.setThemeMode(s.first),
              ),
            ),
            const SizedBox(height: 8),
            ListTile(
              leading: const Icon(Icons.tune),
              title: const Text('إعدادات المصحف'),
              subtitle: const Text(
                'شكل الصفحة، التلاوة، التنزيل، علامات الورد',
              ),
              trailing: const Icon(Icons.chevron_left),
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const MushafSettingsScreen()),
              ),
            ),
            const Divider(),
            const _Header('عن التطبيق'),
            const ListTile(
              leading: Icon(Icons.menu_book_outlined),
              title: Text('نص القرآن الكريم'),
              subtitle: Text(
                'مشروع تنزيل (tanzil.net) — النص العثماني برواية حفص عن عاصم، بدون أي تعديل.',
              ),
            ),
            const ListTile(
              leading: Icon(Icons.auto_stories_outlined),
              title: Text('صفحات مصحف المدينة'),
              subtitle: Text(
                'خطوط مجمع الملك فهد لطباعة المصحف الشريف، وترتيب الأسطر من Quran.com.',
              ),
            ),
            const ListTile(
              leading: Icon(Icons.headphones_outlined),
              title: Text('التلاوات'),
              subtitle: Text('EveryAyah.com'),
            ),
            const ListTile(
              leading: Icon(Icons.font_download_outlined),
              title: Text('خط أميري قرآن'),
              subtitle: Text('Amiri Quran — رخصة SIL OFL'),
            ),
            const ListTile(
              leading: Icon(Icons.privacy_tip_outlined),
              title: Text('سياسة الخصوصية'),
              subtitle: Text(privacyUrl),
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
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
      child: Text(
        text,
        style: Theme.of(context).textTheme.titleSmall
            ?.copyWith(color: Theme.of(context).colorScheme.primary),
      ),
    );
  }
}
