import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/utils/formatting.dart';
import '../../account/data/auth_store.dart';
import '../../account/presentation/login_screen.dart';
import '../../mushaf/presentation/mushaf_settings_screen.dart';
import '../data/settings_store.dart';

/// إعدادات التطبيق العامة. (إعدادات المصحف لها صفحة لوحدها جوه المصحف.)
class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  /// صفحة الخصوصية على نفس سيرفر الـ API (Laravel: /privacy).
  static final privacyUrl =
      const String.fromEnvironment('PRIVACY_URL').isNotEmpty
      ? const String.fromEnvironment('PRIVACY_URL')
      : '${ApiClient.baseUrl.replaceFirst(RegExp(r'/api/v1/?$'), '')}/privacy';

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
            const _Header('الحساب'),
            const _AccountSection(),
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
              leading: Icon(Icons.menu_book_rounded),
              title: Text('التفسير الميسر'),
              subtitle: Text(
                'مجمع الملك فهد لطباعة المصحف الشريف، عبر Tanzil.net',
              ),
            ),
            const ListTile(
              leading: Icon(Icons.font_download_outlined),
              title: Text('الخطوط'),
              subtitle: Text('Amiri Quran و Tajawal — رخصة SIL OFL'),
            ),
            ListTile(
              leading: const Icon(Icons.privacy_tip_outlined),
              title: const Text('سياسة الخصوصية'),
              trailing: const Icon(Icons.open_in_new_rounded, size: 18),
              onTap: () => openExternal(context, privacyUrl),
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

/// الحساب: دخول، أو (الاسم + خروج + مسح الحساب نهائياً — مطلوب من Google Play).
class _AccountSection extends StatelessWidget {
  const _AccountSection();

  Future<void> _delete(BuildContext context) async {
    final sure = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('مسح الحساب نهائياً؟'),
        content: const Text(
          'هيتمسح حسابك من السيرفر ومش هتقدر ترجّعه. '
          'اشتراكاتك في الدورات بتفضل عند الأكاديمية باسمك ورقمك.\n'
          'وِردك وعلاماتك في المصحف مش هتتأثر (محفوظة على الموبايل).',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('إلغاء'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(
              backgroundColor: Theme.of(context).colorScheme.error,
            ),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('امسح'),
          ),
        ],
      ),
    );
    if (sure != true || !context.mounted) return;
    try {
      await AuthStore.instance.deleteAccount();
      if (context.mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(const SnackBar(content: Text('اتمسح الحساب')));
      }
    } on ApiException catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: AuthStore.instance,
      builder: (context, _) {
        final user = AuthStore.instance.user;
        if (user == null) {
          return ListTile(
            leading: const Icon(Icons.login),
            title: const Text('تسجيل الدخول / حساب جديد'),
            subtitle: const Text('للحجز في دورات الأكاديمية ومتابعة حصصك'),
            trailing: const Icon(Icons.chevron_left),
            onTap: () => openLogin(context),
          );
        }
        return Column(
          children: [
            ListTile(
              leading: const Icon(Icons.account_circle_outlined),
              title: Text(user.name),
              subtitle: Text(
                '${user.isTeacher ? 'معلم' : 'طالب'} • ${user.phone ?? user.email ?? ''}',
                textDirection: TextDirection.rtl,
              ),
            ),
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('تسجيل الخروج'),
              onTap: AuthStore.instance.logout,
            ),
            if (!user.isTeacher)
              ListTile(
                leading: Icon(
                  Icons.delete_forever_outlined,
                  color: Theme.of(context).colorScheme.error,
                ),
                title: Text(
                  'مسح الحساب',
                  style: TextStyle(color: Theme.of(context).colorScheme.error),
                ),
                onTap: () => _delete(context),
              ),
          ],
        );
      },
    );
  }
}
