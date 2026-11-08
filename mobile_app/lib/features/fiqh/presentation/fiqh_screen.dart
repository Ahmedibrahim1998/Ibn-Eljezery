import 'package:flutter/material.dart';

import '../../../core/widgets/async_view.dart';
import '../../mushaf/data/quran_data.dart';
import '../../settings/data/settings_store.dart';
import '../data/fiqh_repository.dart';
import '../domain/fiqh_models.dart';
import 'lessons_screen.dart';

/// شاشة الفقه: اختيار المذهب (بيتحفظ) ← المواد من الـ API.
class FiqhScreen extends StatefulWidget {
  const FiqhScreen({super.key});

  @override
  State<FiqhScreen> createState() => _FiqhScreenState();
}

class _FiqhScreenState extends State<FiqhScreen> {
  final _repo = FiqhRepository();
  late Madhhab _madhhab = SettingsStore.instance.madhhab;
  late Future<List<Subject>> _subjects = _repo.subjects(_madhhab);

  void _select(Madhhab m) {
    SettingsStore.instance.setMadhhab(m);
    setState(() {
      _madhhab = m;
      _subjects = _repo.subjects(m);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('الفقه')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: SizedBox(
              width: double.infinity,
              child: SegmentedButton<Madhhab>(
                segments: [
                  for (final m in Madhhab.values)
                    ButtonSegment(value: m, label: Text('الفقه ${m.label}')),
                ],
                selected: {_madhhab},
                onSelectionChanged: (s) => _select(s.first),
              ),
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async {
                setState(() => _subjects = _repo.subjects(_madhhab));
                await _subjects;
              },
              child: AsyncView<List<Subject>>(
                future: _subjects,
                onRetry: () =>
                    setState(() => _subjects = _repo.subjects(_madhhab)),
                builder: (context, subjects) => subjects.isEmpty
                    ? ListView(
                        children: [
                          const SizedBox(height: 80),
                          Center(
                            child: Text(
                              'دروس الفقه ${_madhhab.label} ستضاف قريباً إن شاء الله',
                            ),
                          ),
                        ],
                      )
                    : ListView.separated(
                        itemCount: subjects.length,
                        separatorBuilder: (_, _) => const Divider(height: 1),
                        itemBuilder: (context, i) {
                          final s = subjects[i];
                          return ListTile(
                            leading: const Icon(Icons.menu_book_outlined),
                            title: Text(s.title),
                            subtitle: Text(
                              [
                                if (s.author != null) s.author!,
                                '${arabicDigits(s.lessonsCount)} درس',
                              ].join(' • '),
                            ),
                            trailing: const Icon(Icons.chevron_left),
                            onTap: () => Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => LessonsScreen(subject: s),
                              ),
                            ),
                          );
                        },
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
