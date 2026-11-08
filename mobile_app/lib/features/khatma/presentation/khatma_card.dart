import 'package:flutter/material.dart';

import '../../../app/theme.dart';
import '../../mushaf/data/quran_data.dart';
import '../../mushaf/presentation/mushaf_reader_screen.dart';
import '../data/khatma_store.dart';
import '../domain/khatma_plan.dart';

/// كارت خطة الختمة في صفحة المصحف: حصة النهارده + التقدم + ✓ خلصت.
class KhatmaCard extends StatelessWidget {
  const KhatmaCard({super.key});

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: KhatmaStore.instance,
      builder: (context, _) {
        final plan = KhatmaStore.instance.plan;
        return plan == null ? _StartCard() : _PlanCard(plan: plan);
      },
    );
  }
}

class _StartCard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final done = KhatmaStore.instance.completed;
    return Card(
      margin: EdgeInsets.zero,
      child: ListTile(
        leading: const CircleAvatar(
          backgroundColor: AppColors.emerald,
          child: Icon(Icons.flag_outlined, color: Colors.white),
        ),
        title: const Text(
          'خطة ختمة',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        subtitle: Text(
          done == 0
              ? 'اختار تختم في كام يوم، وإحنا نقسّملك الحصة كل يوم'
              : 'ختمت ${arabicDigits(done)} ${done == 1 ? 'ختمة' : 'ختمات'} — ابدأ ختمة جديدة',
        ),
        trailing: const Icon(Icons.add),
        onTap: () => showStartKhatmaSheet(context),
      ),
    );
  }
}

class _PlanCard extends StatelessWidget {
  const _PlanCard({required this.plan});

  final KhatmaPlan plan;

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final (from, to) = plan.portion(now);
    final ahead = plan.aheadBy(now);
    final doneToday = plan.doneToday(now);
    final scheme = Theme.of(context).colorScheme;

    return Card(
      margin: EdgeInsets.zero,
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => openMushafPage(context, from),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 4, 12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.flag, color: AppColors.emerald),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'ختمة في ${arabicDigits(plan.days)} يوم • اليوم ${arabicDigits(plan.dayNumber(now).clamp(1, plan.days))}',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                  PopupMenuButton<String>(
                    icon: const Icon(Icons.more_vert),
                    onSelected: (v) async {
                      final store = KhatmaStore.instance;
                      final old = store.plan!;
                      await store.cancel();
                      if (!context.mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: const Text('اتلغت خطة الختمة'),
                          action: SnackBarAction(
                            label: 'تراجع',
                            onPressed: () => store.restore(old),
                          ),
                        ),
                      );
                    },
                    itemBuilder: (_) => const [
                      PopupMenuItem(value: 'cancel', child: Text('إلغاء الخطة')),
                    ],
                  ),
                ],
              ),
              Padding(
                padding: const EdgeInsetsDirectional.only(end: 12),
                child: LinearProgressIndicator(
                  value: plan.progress,
                  minHeight: 6,
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
              const SizedBox(height: 4),
              Text(
                '${arabicDigits(plan.pagesRead)} من ${arabicDigits(KhatmaPlan.total)} صفحة'
                '${ahead < 0 ? ' • متأخر ${arabicDigits(-ahead)} صفحة' : ahead > 0 ? ' • سابق بـ ${arabicDigits(ahead)} صفحة 👏' : ''}',
                style: TextStyle(
                  fontSize: 12,
                  color: ahead < 0 ? scheme.error : null,
                ),
              ),
              const Divider(),
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          doneToday ? 'خلّصت حصة النهارده ✓ — حصة بكره:' : 'حصة النهارده:',
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                        Text(
                          'من ص ${arabicDigits(from)} لـ ص ${arabicDigits(to)} '
                          '(${arabicDigits(to - from + 1)} صفحة)',
                          style: TextStyle(
                            color: scheme.primary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    tooltip: 'خلصت الحصة',
                    icon: Icon(
                      Icons.check_circle_outline,
                      color: scheme.primary,
                    ),
                    onPressed: () => _complete(context),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _complete(BuildContext context) async {
    final store = KhatmaStore.instance;
    final before = store.plan!;
    final finished = await store.completePortion();
    if (!context.mounted) return;
    if (finished) {
      await showDialog<void>(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('ختمت القرآن 🎉', textAlign: TextAlign.center),
          content: const Text(
            'ما شاء الله، تقبّل الله منك.\nلا تنسَ دعاء الختم، وابدأ ختمة جديدة ❤️',
            textAlign: TextAlign.center,
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('الحمد لله'),
            ),
          ],
        ),
      );
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Text('بارك الله فيك ✓ اتسجّلت الحصة'),
        action: SnackBarAction(
          label: 'تراجع',
          onPressed: () => store.restore(before),
        ),
      ),
    );
  }
}

/// اختيار مدة الختمة.
Future<void> showStartKhatmaSheet(BuildContext context) {
  const options = [7, 10, 15, 20, 30, 60];
  return showModalBottomSheet(
    context: context,
    showDragHandle: true,
    builder: (context) => SafeArea(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'عايز تختم في كام يوم؟',
              style: Theme.of(context).textTheme.titleMedium,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              alignment: WrapAlignment.center,
              children: [
                for (final d in options)
                  ActionChip(
                    label: Text(
                      '${arabicDigits(d)} يوم • ${_perDay(d)}',
                    ),
                    onPressed: () {
                      KhatmaStore.instance.start(d);
                      Navigator.pop(context);
                    },
                  ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              'الحصة بتتظبط لوحدها كل يوم: لو اتأخرت يوم بتزيد شوية، ولو سبقت بتقل.',
              style: Theme.of(context).textTheme.bodySmall,
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    ),
  );
}

String _perDay(int days) =>
    '${arabicDigits((KhatmaPlan.total / days).ceil())} صفحة/يوم';
