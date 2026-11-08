import 'package:flutter/material.dart';

import '../data/quran_data.dart';
import '../data/tafsir_data.dart';
import 'share_ayah.dart';

/// التفسير الميسر للآية: نص الآية + التفسير + ‹ السابقة / التالية › + مشاركة.
Future<void> showTafsirSheet(BuildContext context, int verse) {
  return showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    showDragHandle: true,
    builder: (context) => DraggableScrollableSheet(
      expand: false,
      initialChildSize: 0.6,
      minChildSize: 0.35,
      maxChildSize: 0.95,
      builder: (context, scroll) =>
          _TafsirView(initialVerse: verse, scroll: scroll),
    ),
  );
}

class _TafsirView extends StatefulWidget {
  const _TafsirView({required this.initialVerse, required this.scroll});

  final int initialVerse;
  final ScrollController scroll;

  @override
  State<_TafsirView> createState() => _TafsirViewState();
}

class _TafsirViewState extends State<_TafsirView> {
  late int _verse = widget.initialVerse;
  final _tafsir = TafsirData.load();

  void _go(int verse) {
    if (verse < 0 || verse >= QuranData.totalVerses) return;
    setState(() => _verse = verse);
    if (widget.scroll.hasClients) widget.scroll.jumpTo(0);
  }

  @override
  Widget build(BuildContext context) {
    final q = QuranData.instance;
    final scheme = Theme.of(context).colorScheme;

    return FutureBuilder<TafsirData>(
      future: _tafsir,
      builder: (context, snap) => ListView(
        controller: widget.scroll,
        padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
        children: [
          Row(
            children: [
              IconButton(
                tooltip: 'الآية السابقة',
                icon: const Icon(Icons.chevron_right),
                onPressed: _verse > 0 ? () => _go(_verse - 1) : null,
              ),
              Expanded(
                child: Text(
                  q.label(_verse),
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.titleMedium,
                ),
              ),
              IconButton(
                tooltip: 'مشاركة',
                icon: const Icon(Icons.share_outlined),
                onPressed: () => showShareAyah(context, _verse),
              ),
              IconButton(
                tooltip: 'الآية التالية',
                icon: const Icon(Icons.chevron_left),
                onPressed: _verse < QuranData.totalVerses - 1
                    ? () => _go(_verse + 1)
                    : null,
              ),
            ],
          ),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: scheme.primaryContainer.withValues(alpha: 0.35),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              q.text(_verse),
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontFamily: 'AmiriQuran',
                fontSize: 21,
                height: 1.9,
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Icon(Icons.menu_book_outlined, size: 18, color: scheme.primary),
              const SizedBox(width: 6),
              Text(
                'التفسير الميسر',
                style: TextStyle(
                  color: scheme.primary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (snap.hasData)
            SelectableText(
              snap.data!.of(_verse),
              style: const TextStyle(fontSize: 17, height: 1.8),
            )
          else if (snap.hasError)
            const Text('حصلت مشكلة في تحميل التفسير')
          else
            const Padding(
              padding: EdgeInsets.all(24),
              child: Center(child: CircularProgressIndicator()),
            ),
          const SizedBox(height: 16),
          Text(
            'المصدر: التفسير الميسر — مجمع الملك فهد لطباعة المصحف الشريف',
            style: Theme.of(context).textTheme.bodySmall,
          ),
        ],
      ),
    );
  }
}
