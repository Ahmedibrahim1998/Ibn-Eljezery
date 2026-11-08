import 'package:flutter/material.dart';

import '../../../app/theme.dart';
import '../../khatma/presentation/khatma_card.dart';
import '../../wird/data/wird_store.dart';
import '../../wird/domain/wird_mark.dart';
import '../../wird/presentation/progress_screen.dart';
import '../../wird/presentation/wird_card.dart';
import '../data/quran_data.dart';
import 'mushaf_index_screen.dart';
import 'mushaf_reader_screen.dart';
import 'mushaf_settings_screen.dart';
import 'quran_search_screen.dart';

/// الصفحة الرئيسية للمصحف: متابعة القراءة + وِردي + الفهرس والبحث + إعدادات المصحف.
class MushafScreen extends StatelessWidget {
  const MushafScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final q = QuranData.instance;

    return Scaffold(
      appBar: AppBar(
        title: const Text('المصحف الشريف'),
        actions: [
          IconButton(
            tooltip: 'إعدادات المصحف',
            icon: const Icon(Icons.tune),
            onPressed: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const MushafSettingsScreen()),
            ),
          ),
        ],
      ),
      body: ListenableBuilder(
        listenable: WirdStore.instance,
        builder: (context, _) {
          final lastPage = WirdStore.instance.lastReadPage;
          final lastSura = lastPage == null
              ? null
              : q.suraOf(q.pageRange(lastPage).$1);
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // متابعة القراءة
              _ContinueCard(
                title: lastPage == null ? 'ابدأ القراءة' : 'متابعة القراءة',
                subtitle: lastPage == null
                    ? 'سورة الفاتحة'
                    : 'سورة ${lastSura!.name} • صفحة ${arabicDigits(lastPage)}',
                onTap: () => openMushafPage(context, lastPage ?? 1),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _Tile(
                      icon: Icons.format_list_numbered_rtl,
                      label: 'السور',
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const MushafIndexScreen(),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _Tile(
                      icon: Icons.view_module_outlined,
                      label: 'الأجزاء',
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) =>
                              const MushafIndexScreen(initialTab: 1),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _Tile(
                      icon: Icons.search,
                      label: 'البحث',
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const QuranSearchScreen(),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              StreakCard(
                stats: WirdStore.instance.stats(),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const ProgressScreen()),
                ),
              ),
              const SizedBox(height: 12),
              const KhatmaCard(),
              const SizedBox(height: 16),
              Row(
                children: [
                  Text('وِردي', style: Theme.of(context).textTheme.titleLarge),
                  const Spacer(),
                  Text(
                    'اضغط ▶ للاستماع',
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                ],
              ),
              const SizedBox(height: 6),
              for (final type in WirdType.values) WirdCard(type: type),
            ],
          );
        },
      ),
    );
  }
}

class _ContinueCard extends StatelessWidget {
  const _ContinueCard({
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  final String title;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      borderRadius: BorderRadius.circular(16),
      clipBehavior: Clip.antiAlias,
      child: Ink(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF065F46), AppColors.emerald],
            begin: AlignmentDirectional.topStart,
            end: AlignmentDirectional.bottomEnd,
          ),
        ),
        child: InkWell(
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              children: [
                const Icon(Icons.auto_stories, color: AppColors.gold, size: 40),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        subtitle,
                        style: const TextStyle(color: Colors.white70),
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_left, color: Colors.white),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _Tile extends StatelessWidget {
  const _Tile({required this.icon, required this.label, required this.onTap});

  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Card(
      margin: EdgeInsets.zero,
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16),
          child: Column(
            children: [
              Icon(icon, color: scheme.primary, size: 28),
              const SizedBox(height: 6),
              Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ),
    );
  }
}
