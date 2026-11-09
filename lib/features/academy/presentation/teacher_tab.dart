import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';

import '../../../core/utils/formatting.dart';
import '../../../core/widgets/async_view.dart';
import '../../../core/widgets/empty_state.dart';
import '../../mushaf/data/quran_data.dart';
import '../data/academy_repository.dart';
import '../domain/academy_models.dart';

/// مجموعاتي (المعلم): الحصة الجاية + "ابدأ الحصة" (Zoom) + مشاركة رابط الحجز + الطلاب.
class TeacherTab extends StatefulWidget {
  const TeacherTab({super.key});

  @override
  State<TeacherTab> createState() => _TeacherTabState();
}

class _TeacherTabState extends State<TeacherTab> {
  final _repo = AcademyRepository();
  late Future<List<TeacherCourse>> _future = _repo.teacherCourses();

  void _reload() => setState(() => _future = _repo.teacherCourses());

  @override
  Widget build(BuildContext context) {
    return AsyncView<List<TeacherCourse>>(
      future: _future,
      onRetry: _reload,
      builder: (context, courses) => RefreshIndicator(
        onRefresh: () async => _reload(),
        child: courses.isEmpty
            ? ListView(
                children: const [
                  EmptyState(
                    icon: Icons.groups_outlined,
                    title: 'مفيش مجموعات باسمك',
                    message: 'الإدارة بتضيف مجموعاتك من لوحة التحكم.',
                  ),
                ],
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: courses.length,
                itemBuilder: (context, i) =>
                    _TeacherCourseCard(item: courses[i]),
              ),
      ),
    );
  }
}

class _TeacherCourseCard extends StatelessWidget {
  const _TeacherCourseCard({required this.item});

  final TeacherCourse item;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final course = item.course;
    final next = item.sessions.isEmpty ? null : item.sessions.first;
    final now = DateTime.now();

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              course.title,
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            if (course.categoryTitle != null)
              Text(
                course.categoryTitle!,
                style: Theme.of(context).textTheme.bodySmall,
              ),
            const SizedBox(height: 6),
            Row(
              children: [
                Icon(Icons.groups, size: 18, color: scheme.primary),
                const SizedBox(width: 6),
                Text('${arabicDigits(item.confirmedCount)} طالب'),
                if (item.pendingCount > 0) ...[
                  const SizedBox(width: 10),
                  Text(
                    '+ ${arabicDigits(item.pendingCount)} مستني التأكيد',
                    style: const TextStyle(color: Colors.orange),
                  ),
                ],
              ],
            ),
            const Divider(),
            Row(
              children: [
                const Icon(Icons.event, size: 18),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    next == null
                        ? 'مفيش حصص جاية متحددة'
                        : 'الحصة الجاية: ${formatSessionTime(next.startsAt)}',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                // رابط البدء (host) — المعلم بيفتح الاجتماع بيه كمضيف
                if (next != null && (next.startUrl ?? next.joinUrl) != null)
                  FilledButton.icon(
                    onPressed: () =>
                        openExternal(context, (next.startUrl ?? next.joinUrl)!),
                    style: next.isLive(now)
                        ? FilledButton.styleFrom(
                            backgroundColor: Colors.red.shade700,
                          )
                        : null,
                    icon: const Icon(Icons.videocam),
                    label: Text(
                      next.isLive(now)
                          ? 'ابدأ الحصة دلوقتي'
                          : 'ابدأ الحصة (Zoom)',
                    ),
                  ),
                OutlinedButton.icon(
                  onPressed: () => Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => StudentsScreen(course: course),
                    ),
                  ),
                  icon: const Icon(Icons.people_outline),
                  label: const Text('الطلاب'),
                ),
                OutlinedButton.icon(
                  onPressed: () => SharePlus.instance.share(
                    ShareParams(
                      text:
                          'احجز في ${course.title} مع أكاديمية ابن الجزري:\n${item.bookingUrl}',
                    ),
                  ),
                  icon: const Icon(Icons.share_outlined),
                  label: const Text('رابط الحجز'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

/// طلاب المجموعة + واتساب لكل طالب.
class StudentsScreen extends StatefulWidget {
  const StudentsScreen({super.key, required this.course});

  final Course course;

  @override
  State<StudentsScreen> createState() => _StudentsScreenState();
}

class _StudentsScreenState extends State<StudentsScreen> {
  final _repo = AcademyRepository();
  late Future<List<Student>> _future = _repo.students(widget.course.id);

  void _reload() => setState(() => _future = _repo.students(widget.course.id));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('طلاب ${widget.course.title}')),
      body: AsyncView<List<Student>>(
        future: _future,
        onRetry: _reload,
        builder: (context, students) => students.isEmpty
            ? const Center(child: Text('مفيش طلاب في المجموعة لسه'))
            : ListView.separated(
                itemCount: students.length,
                separatorBuilder: (_, _) => const Divider(height: 1),
                itemBuilder: (context, i) {
                  final s = students[i];
                  final pending = s.status == BookingStatus.pending;
                  return ListTile(
                    leading: CircleAvatar(
                      child: Text(
                        s.name.isEmpty ? '?' : s.name.characters.first,
                      ),
                    ),
                    title: Text(s.name),
                    subtitle: Text(
                      pending
                          ? 'مستني تأكيد المشرف'
                          : 'حضر ${arabicDigits(s.attendedClasses)} حصة',
                      style: TextStyle(color: pending ? Colors.orange : null),
                    ),
                    trailing: s.whatsappUrl == null
                        ? null
                        : IconButton(
                            tooltip: 'واتساب',
                            icon: const Icon(
                              Icons.chat,
                              color: Color(0xFF25D366),
                            ),
                            onPressed: () =>
                                openExternal(context, s.whatsappUrl!),
                          ),
                  );
                },
              ),
      ),
    );
  }
}
