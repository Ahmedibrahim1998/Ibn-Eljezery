import 'package:flutter/material.dart';
import 'package:flutter_widget_from_html_core/flutter_widget_from_html_core.dart';

import '../../../core/widgets/async_view.dart';
import '../../mushaf/data/quran_data.dart';
import '../../mushaf/data/mushaf_settings.dart';
import '../data/fiqh_repository.dart';
import '../domain/fiqh_models.dart';

/// صفحة الدرس: المحتوى HTML من لوحة التحكم.
///
/// الآيات في الدرس مكتوبة كمرجع [[2:255]] أو [[2:1-5]] (docs/mobile-app/06)
/// ← بنستبدلها بنص المصحف المدمج، فمستحيل يظهر خطأ في آية.
class LessonScreen extends StatefulWidget {
  const LessonScreen({super.key, required this.lessonId, required this.title});

  final int lessonId;
  final String title;

  @override
  State<LessonScreen> createState() => _LessonScreenState();
}

class _LessonScreenState extends State<LessonScreen> {
  final _repo = FiqhRepository();
  late Future<Lesson> _lesson = _repo.lesson(widget.lessonId);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.title)),
      body: AsyncView<Lesson>(
        future: _lesson,
        onRetry: () => setState(() => _lesson = _repo.lesson(widget.lessonId)),
        builder: (context, lesson) => SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: HtmlWidget(
            expandVerseReferences(lesson.content ?? ''),
            textStyle: const TextStyle(fontSize: 17, height: 1.8),
            customStylesBuilder: (element) => element.classes.contains('ayah')
                ? {
                    'font-family': 'AmiriQuran',
                    'font-size': '${MushafSettings.instance.textSize}px',
                    'text-align': 'center',
                    'margin': '12px 0',
                  }
                : null,
          ),
        ),
      ),
    );
  }
}

/// [[سورة:آية]] أو [[سورة:من-إلى]] ← ﴿نص الآيات﴾ [السورة رقم]. المرجع الغلط يفضل زي ما هو.
String expandVerseReferences(String html) {
  final q = QuranData.instance;
  return html.replaceAllMapped(
    RegExp(r'\[\[(\d{1,3}):(\d{1,3})(?:-(\d{1,3}))?\]\]'),
    (m) {
      final sura = int.parse(m[1]!);
      final from = int.parse(m[2]!);
      final to = m[3] == null ? from : int.parse(m[3]!);
      if (sura < 1 ||
          sura > 114 ||
          from < 1 ||
          to < from ||
          to > q.suras[sura - 1].ayahCount) {
        return m[0]!;
      }

      final text = [
        for (var a = from; a <= to; a++)
          '${q.text(q.indexOf(sura, a))} ﴿${arabicDigits(a)}﴾',
      ].join(' ');
      final ref = from == to
          ? arabicDigits(from)
          : '${arabicDigits(from)}-${arabicDigits(to)}';
      return '<p class="ayah">$text<br><small>[${q.suras[sura - 1].name} $ref]</small></p>';
    },
  );
}
