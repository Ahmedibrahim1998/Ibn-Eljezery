import 'package:flutter/material.dart';

import '../../../app/theme.dart';
import '../../mushaf/data/quran_data.dart';
import '../data/wird_store.dart';
import '../domain/progress_entry.dart';
import '../domain/wird_mark.dart';

/// سجلّي: أيام الالتزام 🔥 + آخر 7 أيام + إنجاز الأسبوع + كل مرة دست فيها "خلصت".
class ProgressScreen extends StatelessWidget {
  const ProgressScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final store = WirdStore.instance;

    return Scaffold(
      appBar: AppBar(title: const Text('سجلّي')),
      body: ListenableBuilder(
        listenable: store,
        builder: (context, _) {
          final stats = store.stats();
          final history = store.history;
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              StreakCard(stats: stats),
              const SizedBox(height: 16),
              Text(
                'هذا الأسبوع',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 8),
              Card(
                margin: EdgeInsets.zero,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.menu_book, color: AppColors.emerald),
                          const SizedBox(width: 8),
                          Text('${arabicDigits(stats.weekPages)} صفحة'),
                        ],
                      ),
                      const Divider(),
                      for (final type in WirdType.values)
                        Padding(
                          padding: const EdgeInsets.symmetric(vertical: 4),
                          child: Row(
                            children: [
                              Icon(Icons.bookmark, color: type.color, size: 18),
                              const SizedBox(width: 8),
                              Expanded(child: Text(type.label)),
                              Text(
                                '${arabicDigits(stats.weekCounts[type] ?? 0)} مرة',
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text('السجل', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              if (history.isEmpty)
                const Padding(
                  padding: EdgeInsets.all(24),
                  child: Text(
                    'لسه مفيش إنجازات. لما تخلّص وِردك دوس ✓ "خلصت" وهيتسجّل هنا.',
                    textAlign: TextAlign.center,
                  ),
                )
              else
                for (final e in history.take(100)) _EntryTile(entry: e),
            ],
          );
        },
      ),
    );
  }
}

/// كارت الالتزام: 🔥 عدد الأيام + أحسن رقم + آخر 7 أيام.
class StreakCard extends StatelessWidget {
  const StreakCard({super.key, required this.stats, this.onTap});

  final ProgressStats stats;
  final VoidCallback? onTap;

  static const _dayNames = [
    'ن',
    'ث',
    'ر',
    'خ',
    'ج',
    'س',
    'ح',
  ]; // الاتنين ← الحد

  @override
  Widget build(BuildContext context) {
    final today = dayOf(DateTime.now());
    return Material(
      borderRadius: BorderRadius.circular(16),
      clipBehavior: Clip.antiAlias,
      child: Ink(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF1F1A0E), Color(0xFF3A2E10)],
            begin: AlignmentDirectional.topStart,
            end: AlignmentDirectional.bottomEnd,
          ),
        ),
        child: InkWell(
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Row(
                  children: [
                    Text(
                      stats.currentStreak > 0 ? '🔥' : '🌱',
                      style: const TextStyle(fontSize: 34),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            stats.currentStreak == 0
                                ? 'ابدأ التزامك النهارده'
                                : '${arabicDigits(stats.currentStreak)} ${stats.currentStreak == 1 ? 'يوم' : 'أيام'} ورا بعض',
                            style: const TextStyle(
                              color: AppColors.gold,
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            stats.doneToday
                                ? 'ما شاء الله، خلّصت وِرد النهارده ✓'
                                : 'لسه ما خلّصتش وِرد النهارده',
                            style: const TextStyle(color: Colors.white70),
                          ),
                        ],
                      ),
                    ),
                    if (stats.bestStreak > 0)
                      Column(
                        children: [
                          Text(
                            arabicDigits(stats.bestStreak),
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const Text(
                            'أحسن رقم',
                            style: TextStyle(
                              color: Colors.white54,
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                    if (onTap != null)
                      const Icon(Icons.chevron_left, color: Colors.white54),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    for (var i = 0; i < 7; i++)
                      Column(
                        children: [
                          Container(
                            width: 26,
                            height: 26,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: stats.lastSevenDays[i]
                                  ? AppColors.gold
                                  : Colors.white12,
                            ),
                            child: stats.lastSevenDays[i]
                                ? const Icon(
                                    Icons.check,
                                    size: 16,
                                    color: Colors.black,
                                  )
                                : null,
                          ),
                          const SizedBox(height: 2),
                          Text(
                            _dayNames[today
                                    .subtract(Duration(days: 6 - i))
                                    .weekday -
                                1],
                            style: const TextStyle(
                              color: Colors.white54,
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _EntryTile extends StatelessWidget {
  const _EntryTile({required this.entry});

  final ProgressEntry entry;

  String _when() {
    final days = dayOf(DateTime.now()).difference(dayOf(entry.date)).inDays;
    if (days == 0) return 'النهارده';
    if (days == 1) return 'امبارح';
    return '${arabicDigits(entry.date.day)}/${arabicDigits(entry.date.month)}';
  }

  @override
  Widget build(BuildContext context) {
    final mark = WirdMark(type: entry.type, from: entry.from, to: entry.to);
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: CircleAvatar(
        backgroundColor: entry.type.color.withValues(alpha: 0.15),
        child: Icon(Icons.check, color: entry.type.color),
      ),
      title: Text(
        entry.type.label,
        style: TextStyle(color: entry.type.color, fontWeight: FontWeight.w600),
      ),
      subtitle: Text('${mark.describe()} • ${arabicDigits(entry.pages)} صفحة'),
      trailing: Text(_when(), style: Theme.of(context).textTheme.bodySmall),
    );
  }
}
