import 'package:flutter/material.dart';

import '../../mushaf/data/quran_data.dart';
import '../../mushaf/presentation/mushaf_reader_screen.dart';
import '../data/wird_store.dart';
import '../domain/wird_mark.dart';
import 'wird_edit_sheet.dart';

/// سطر علامة ورد: النوع + المقطع ← افتح / استمع / خلصت / ⋮ (اختبر حفظك، تعديل، مسح).
class WirdCard extends StatelessWidget {
  const WirdCard({super.key, required this.type});

  final WirdType type;

  @override
  Widget build(BuildContext context) {
    final mark = WirdStore.instance.mark(type);
    final q = QuranData.instance;

    return Card(
      margin: const EdgeInsets.symmetric(vertical: 4),
      clipBehavior: Clip.antiAlias,
      child: Container(
        decoration: BoxDecoration(
          border: BorderDirectional(
            start: BorderSide(color: type.color, width: 5),
          ),
        ),
        child: ListTile(
          contentPadding: const EdgeInsetsDirectional.only(start: 12, end: 4),
          title: Text(
            type.label,
            style: TextStyle(color: type.color, fontWeight: FontWeight.bold),
          ),
          subtitle: Text(mark?.describe() ?? 'اضغط لتحديد المقطع'),
          onTap: mark == null
              ? () => showWirdEditSheet(context, type)
              : () => openMushafPage(context, q.pageOf(mark.from)),
          onLongPress: () => showWirdEditSheet(context, type),
          trailing: mark == null
              ? const Icon(Icons.add)
              : Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    IconButton(
                      icon: Icon(Icons.play_circle_fill, color: type.color),
                      tooltip: 'استمع للمقطع',
                      onPressed: () => openMushafPage(
                        context,
                        q.pageOf(mark.from),
                        playFrom: mark.from,
                        playTo: mark.to,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.check_circle_outline),
                      tooltip: 'خلصت',
                      onPressed: () async {
                        await WirdStore.instance.advance(type);
                        if (context.mounted) {
                          final streak = WirdStore.instance
                              .stats()
                              .currentStreak;
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                'ما شاء الله! 🔥 ${arabicDigits(streak)} — ${type.label} الجاي: ${WirdStore.instance.mark(type)!.describe()}',
                              ),
                              action: SnackBarAction(
                                label: 'تراجع',
                                onPressed: () =>
                                    WirdStore.instance.undoLast(type),
                              ),
                            ),
                          );
                        }
                      },
                    ),
                    PopupMenuButton<String>(
                      tooltip: 'خيارات',
                      icon: const Icon(Icons.more_vert),
                      onSelected: (action) async {
                        if (action == 'edit') {
                          showWirdEditSheet(context, type);
                          return;
                        }
                        if (action == 'test') {
                          openMushafPage(
                            context,
                            q.pageOf(mark.from),
                            hifzTest: true,
                          );
                          return;
                        }
                        // مسح مع "تراجع" لو اتمسحت بالغلط
                        final removed = mark;
                        await WirdStore.instance.remove(type);
                        if (!context.mounted) return;
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('اتمسحت علامة ${type.label}'),
                            action: SnackBarAction(
                              label: 'تراجع',
                              onPressed: () => WirdStore.instance.save(removed),
                            ),
                          ),
                        );
                      },
                      itemBuilder: (context) => const [
                        PopupMenuItem(
                          value: 'test',
                          child: ListTile(
                            leading: Icon(Icons.visibility_off_outlined),
                            title: Text('اختبر حفظك'),
                          ),
                        ),
                        PopupMenuItem(
                          value: 'edit',
                          child: ListTile(
                            leading: Icon(Icons.edit_outlined),
                            title: Text('تعديل'),
                          ),
                        ),
                        PopupMenuItem(
                          value: 'delete',
                          child: ListTile(
                            leading: Icon(Icons.delete_outline),
                            title: Text('مسح'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
        ),
      ),
    );
  }
}
