import 'package:flutter/material.dart';

import '../../../core/widgets/async_view.dart';
import '../../mushaf/data/quran_data.dart';
import '../data/fiqh_repository.dart';
import '../domain/fiqh_models.dart';
import 'lesson_screen.dart';

/// دروس مادة واحدة.
class LessonsScreen extends StatefulWidget {
  const LessonsScreen({super.key, required this.subject});

  final Subject subject;

  @override
  State<LessonsScreen> createState() => _LessonsScreenState();
}

class _LessonsScreenState extends State<LessonsScreen> {
  final _repo = FiqhRepository();
  late Future<List<Lesson>> _lessons = _repo.lessons(widget.subject.id);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.subject.title)),
      body: AsyncView<List<Lesson>>(
        future: _lessons,
        onRetry: () =>
            setState(() => _lessons = _repo.lessons(widget.subject.id)),
        builder: (context, lessons) => lessons.isEmpty
            ? const Center(child: Text('لا توجد دروس منشورة بعد'))
            : ListView.separated(
                itemCount: lessons.length,
                separatorBuilder: (_, _) => const Divider(height: 1),
                itemBuilder: (context, i) {
                  final l = lessons[i];
                  return ListTile(
                    leading: CircleAvatar(child: Text(arabicDigits(i + 1))),
                    title: Text(l.title),
                    subtitle: l.durationMinutes == null
                        ? null
                        : Text('${arabicDigits(l.durationMinutes!)} دقيقة'),
                    trailing: const Icon(Icons.chevron_left),
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (_) =>
                            LessonScreen(lessonId: l.id, title: l.title),
                      ),
                    ),
                  );
                },
              ),
      ),
    );
  }
}
