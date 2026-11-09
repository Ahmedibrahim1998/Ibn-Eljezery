import 'package:flutter/material.dart';

import '../../../app/theme.dart';
import '../../../core/widgets/async_view.dart';
import '../../../core/widgets/empty_state.dart';
import '../data/academy_repository.dart';
import '../domain/academy_models.dart';
import 'course_screen.dart';

/// الدورات المتاحة للتسجيل من نوع واحد (أونلاين / حضوري).
class CoursesScreen extends StatefulWidget {
  const CoursesScreen({super.key, required this.online});

  final bool online;

  @override
  State<CoursesScreen> createState() => _CoursesScreenState();
}

class _CoursesScreenState extends State<CoursesScreen> {
  final _repo = AcademyRepository();
  late Future<List<Course>> _future = _repo.courses(online: widget.online);

  void _reload() =>
      setState(() => _future = _repo.courses(online: widget.online));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.online ? 'دورات أونلاين' : 'دورات حضورية'),
      ),
      body: AsyncView<List<Course>>(
        future: _future,
        onRetry: _reload,
        builder: (context, courses) => courses.isEmpty
            ? EmptyState(
                icon: widget.online
                    ? Icons.videocam_off_rounded
                    : Icons.event_busy_rounded,
                title: 'مفيش دورات متاحة دلوقتي',
                message:
                    'التسجيل بيفتح مع بداية كل دورة جديدة — ارجع تاني قريب.',
              )
            : RefreshIndicator(
                onRefresh: () async => _reload(),
                child: ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: courses.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 12),
                  itemBuilder: (context, i) => CourseCard(course: courses[i]),
                ),
              ),
      ),
    );
  }
}

/// كارت مجموعة: شريط بنوعها (أونلاين/حضوري) + العنوان + المعلم + المدة وآخر ميعاد للتسجيل.
class CourseCard extends StatelessWidget {
  const CourseCard({super.key, required this.course, this.showTeacher = true});

  final Course course;

  /// في صفحة المعلم مش محتاجين اسمه على كل كارت.
  final bool showTeacher;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final teacher = course.teacher;
    final accent = course.isOnline
        ? const Color(0xFF2F6690)
        : AppColors.emerald;

    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) =>
                CourseScreen(courseId: course.id, title: course.title),
          ),
        ),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(width: 5, color: accent),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(14, 14, 14, 14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(
                            course.isOnline
                                ? Icons.videocam_rounded
                                : Icons.mosque_rounded,
                            size: 16,
                            color: accent,
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              course.categoryTitle ??
                                  (course.isOnline ? 'أونلاين' : 'حضوري'),
                              style: TextStyle(
                                fontSize: 12.5,
                                color: accent,
                                fontWeight: FontWeight.w600,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          if (course.daysLeftLabel != null)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: scheme.errorContainer,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                course.daysLeftLabel!,
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: scheme.onErrorContainer,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        course.title,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 17,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          if (showTeacher) ...[
                            TeacherAvatar(teacher: teacher, radius: 15),
                            const SizedBox(width: 8),
                          ],
                          Expanded(
                            child: Text(
                              showTeacher ? teacher?.name ?? '' : '',
                              style: TextStyle(
                                color: scheme.onSurfaceVariant,
                                fontSize: 14,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          if (course.durationLabel != null) ...[
                            Icon(
                              Icons.schedule_rounded,
                              size: 15,
                              color: scheme.onSurfaceVariant,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              course.durationLabel!,
                              style: TextStyle(
                                fontSize: 12.5,
                                color: scheme.onSurfaceVariant,
                              ),
                            ),
                          ],
                          const SizedBox(width: 6),
                          Icon(
                            Icons.arrow_forward_ios_rounded,
                            size: 14,
                            color: scheme.onSurfaceVariant,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// صورة المعلم، أو أول حرف من اسمه لو مفيش صورة.
class TeacherAvatar extends StatelessWidget {
  const TeacherAvatar({super.key, required this.teacher, this.radius = 22});

  final Teacher? teacher;
  final double radius;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final photo = teacher?.photo;
    final name = teacher?.name.replaceFirst('الشيخ ', '') ?? '';
    return CircleAvatar(
      radius: radius,
      backgroundColor: AppColors.gold.withValues(alpha: 0.2),
      foregroundColor: scheme.onSurface,
      backgroundImage: photo == null ? null : NetworkImage(photo),
      child: photo == null
          ? Text(
              name.isEmpty ? '؟' : name.characters.first,
              style: TextStyle(
                fontSize: radius * 0.85,
                fontWeight: FontWeight.bold,
              ),
            )
          : null,
    );
  }
}
