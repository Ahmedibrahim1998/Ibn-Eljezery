import 'package:flutter/material.dart';

import '../data/quran_data.dart';
import 'mushaf_reader_screen.dart';

/// البحث في القرآن: اكتب كلمة ← النتايج بالرسم العثماني ← الضغط يفتح الصفحة.
class QuranSearchScreen extends StatefulWidget {
  const QuranSearchScreen({super.key});

  @override
  State<QuranSearchScreen> createState() => _QuranSearchScreenState();
}

class _QuranSearchScreenState extends State<QuranSearchScreen> {
  List<int> _results = const [];
  String _query = '';

  void _onChanged(String value) {
    setState(() {
      _query = value;
      _results = QuranData.instance.search(value);
    });
  }

  @override
  Widget build(BuildContext context) {
    final q = QuranData.instance;

    return Scaffold(
      appBar: AppBar(
        title: TextField(
          autofocus: true,
          onChanged: _onChanged,
          style: TextStyle(color: Theme.of(context).colorScheme.onPrimary),
          cursorColor: Theme.of(context).colorScheme.onPrimary,
          decoration: InputDecoration(
            hintText: 'ابحث في القرآن…',
            hintStyle: TextStyle(
              color: Theme.of(context).colorScheme.onPrimary
                  .withValues(alpha: 0.7),
            ),
            border: InputBorder.none,
          ),
        ),
      ),
      body: _query.trim().length < 2
          ? const Center(child: Text('اكتب كلمتين على الأقل… مثال: الصابرين'))
          : _results.isEmpty
          ? const Center(child: Text('لا توجد نتائج'))
          : ListView.separated(
              itemCount: _results.length + 1,
              separatorBuilder: (_, _) => const Divider(height: 1),
              itemBuilder: (context, i) {
                if (i == 0) {
                  return Padding(
                    padding: const EdgeInsets.all(12),
                    child: Text(
                      '${arabicDigits(_results.length)} نتيجة${_results.length == 200 ? ' (أول ٢٠٠)' : ''}',
                    ),
                  );
                }
                final index = _results[i - 1];
                return ListTile(
                  title: Text(
                    q.text(index),
                    style: const TextStyle(
                      fontFamily: 'AmiriQuran',
                      fontSize: 18,
                      height: 1.9,
                    ),
                  ),
                  subtitle: Text(
                    '${q.label(index)} • صفحة ${arabicDigits(q.pageOf(index))}',
                  ),
                  onTap: () => openMushafPage(
                    context,
                    q.pageOf(index),
                    highlight: index,
                  ),
                );
              },
            ),
    );
  }
}
