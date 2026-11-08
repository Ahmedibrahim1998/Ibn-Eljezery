import 'package:flutter/material.dart';

import '../data/quran_data.dart';
import 'mushaf_reader_screen.dart';

/// فهرس المصحف: تبويب السور وتبويب الأجزاء.
class MushafIndexScreen extends StatelessWidget {
  const MushafIndexScreen({super.key, this.initialTab = 0});

  final int initialTab;

  @override
  Widget build(BuildContext context) {
    final q = QuranData.instance;

    return DefaultTabController(
      length: 2,
      initialIndex: initialTab,
      child: Scaffold(
        appBar: AppBar(
          toolbarHeight: 40,
          title: const Text(
            'فهرس المصحف',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
          ),
          bottom: const TabBar(
            tabs: [
              Tab(text: 'السور', height: 36),
              Tab(text: 'الأجزاء', height: 36),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            ListView.separated(
              itemCount: q.suras.length,
              separatorBuilder: (_, _) => const Divider(height: 1),
              itemBuilder: (context, i) {
                final sura = q.suras[i];
                final page = q.pageOf(sura.start);
                return ListTile(
                  leading: CircleAvatar(child: Text(arabicDigits(sura.number))),
                  title: Text(
                    sura.name,
                    style: const TextStyle(
                      fontFamily: 'AmiriQuran',
                      fontSize: 20,
                    ),
                  ),
                  subtitle: Text(
                    '${sura.isMeccan ? 'مكية' : 'مدنية'} • ${arabicDigits(sura.ayahCount)} آية',
                  ),
                  trailing: Text('ص ${arabicDigits(page)}'),
                  onTap: () => openMushafPage(context, page),
                );
              },
            ),
            ListView.separated(
              itemCount: QuranData.totalJuz,
              separatorBuilder: (_, _) => const Divider(height: 1),
              itemBuilder: (context, i) {
                final start = q.juzStart(i + 1);
                final page = q.pageOf(start);
                return ListTile(
                  leading: CircleAvatar(child: Text(arabicDigits(i + 1))),
                  title: Text('الجزء ${arabicDigits(i + 1)}'),
                  subtitle: Text('يبدأ من ${q.label(start)}'),
                  trailing: Text('ص ${arabicDigits(page)}'),
                  onTap: () => openMushafPage(context, page),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
