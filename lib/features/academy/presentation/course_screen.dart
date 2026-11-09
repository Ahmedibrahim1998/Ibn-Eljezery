import 'package:flutter/material.dart';

import '../../../app/theme.dart';
import '../../../core/network/api_client.dart';
import '../../../core/utils/formatting.dart';
import '../../../core/widgets/async_view.dart';
import '../../account/data/auth_store.dart';
import '../../account/presentation/login_screen.dart';
import '../../mushaf/data/quran_data.dart';
import '../data/academy_repository.dart';
import '../domain/academy_models.dart';
import 'courses_screen.dart';
import 'teachers_screen.dart';

/// صفحة المجموعة: هيدر + المعلم + عن الدورة + مواعيد الحصص + زرار "احجز".
class CourseScreen extends StatefulWidget {
  const CourseScreen({super.key, required this.courseId, required this.title});

  final int courseId;
  final String title;

  @override
  State<CourseScreen> createState() => _CourseScreenState();
}

class _CourseScreenState extends State<CourseScreen> {
  final _repo = AcademyRepository();
  late Future<Course> _future = _repo.course(widget.courseId);
  bool _booking = false;

  void _reload() => setState(() => _future = _repo.course(widget.courseId));

  Future<void> _book() async {
    final auth = AuthStore.instance;
    if (!auth.loggedIn && !await openLogin(context)) return;
    if (!mounted) return;
    if (auth.user!.isTeacher) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('الحجز للطلاب — إنت داخل بحساب معلم')),
      );
      return;
    }
    final notes = await _askNotes();
    if (notes == null || !mounted) return;

    setState(() => _booking = true);
    try {
      final message = await _repo.book(widget.courseId, notes: notes);
      if (!mounted) return;
      await showDialog<void>(
        context: context,
        builder: (context) => AlertDialog(
          icon: const Icon(
            Icons.check_circle_rounded,
            color: Colors.green,
            size: 56,
          ),
          title: const Text('تم الحجز'),
          content: Text(message, textAlign: TextAlign.center),
          actions: [
            FilledButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('تمام'),
            ),
          ],
        ),
      );
      _reload();
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.message)));
      }
    } finally {
      if (mounted) setState(() => _booking = false);
    }
  }

  /// ملاحظة اختيارية للمعلم (مثلاً: حافظ كام جزء). null = الطالب لغى.
  Future<String?> _askNotes() {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحجز'),
        content: TextField(
          controller: controller,
          maxLines: 3,
          decoration: const InputDecoration(
            labelText: 'ملاحظة للمعلم (اختياري)',
            hintText: 'مثلاً: حافظ ٣ أجزاء',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, controller.text),
            child: const Text('احجز'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('تفاصيل الدورة')),
      body: AsyncView<Course>(
        future: _future,
        onRetry: _reload,
        builder: (context, course) => _Body(course: course),
      ),
      bottomNavigationBar: FutureBuilder<Course>(
        future: _future,
        builder: (context, snap) {
          final course = snap.data;
          if (course == null) return const SizedBox.shrink();
          return SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
              child: course.isEnrolled
                  ? FilledButton.tonalIcon(
                      style: FilledButton.styleFrom(
                        minimumSize: const Size.fromHeight(52),
                      ),
                      onPressed: null,
                      icon: const Icon(Icons.verified_rounded),
                      label: const Text('إنت مشترك — تابعها من "دوراتي"'),
                    )
                  : FilledButton.icon(
                      style: FilledButton.styleFrom(
                        minimumSize: const Size.fromHeight(52),
                      ),
                      onPressed: _booking ? null : _book,
                      icon: _booking
                          ? const SizedBox.square(
                              dimension: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.how_to_reg_rounded),
                      label: Text(_booking ? 'جاري الحجز…' : 'احجز مكانك'),
                    ),
            ),
          );
        },
      ),
    );
  }
}

class _Body extends StatelessWidget {
  const _Body({required this.course});

  final Course course;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final teacher = course.teacher;
    final description = course.description ?? '';

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
      children: [
        // هيدر المجموعة
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: course.isOnline
                ? const LinearGradient(
                    colors: [Color(0xFF173A5E), Color(0xFF2F6690)],
                    begin: AlignmentDirectional.topStart,
                    end: AlignmentDirectional.bottomEnd,
                  )
                : AppColors.heroGradient,
            borderRadius: BorderRadius.circular(24),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (course.categoryTitle != null)
                Text(
                  course.categoryTitle!,
                  style: const TextStyle(
                    color: AppColors.goldSoft,
                    fontSize: 13,
                  ),
                ),
              const SizedBox(height: 4),
              Text(
                course.title,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 14),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _HeroChip(
                    icon: course.isOnline
                        ? Icons.videocam_rounded
                        : Icons.mosque_rounded,
                    label: course.isOnline ? 'أونلاين على Zoom' : 'حضوري',
                  ),
                  if (course.durationLabel != null)
                    _HeroChip(
                      icon: Icons.schedule_rounded,
                      label: course.durationLabel!,
                    ),
                  if (course.daysLeftLabel != null)
                    _HeroChip(
                      icon: Icons.hourglass_bottom_rounded,
                      label: course.daysLeftLabel!,
                    ),
                ],
              ),
            ],
          ),
        ),
        if (teacher != null) ...[
          const _SectionTitle('المعلم'),
          Card(
            clipBehavior: Clip.antiAlias,
            child: InkWell(
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => TeacherScreen(teacher: teacher),
                ),
              ),
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Row(
                  children: [
                    TeacherAvatar(teacher: teacher, radius: 28),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            teacher.name,
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                          if (teacher.certification != null)
                            Text(
                              teacher.certification!,
                              style: TextStyle(
                                color: scheme.onSurfaceVariant,
                                fontSize: 13,
                              ),
                            ),
                        ],
                      ),
                    ),
                    if (teacher.whatsappUrl != null)
                      IconButton.filled(
                        tooltip: 'اسأل المعلم على واتساب',
                        style: IconButton.styleFrom(
                          backgroundColor: const Color(0xFF25D366),
                        ),
                        onPressed: () =>
                            openExternal(context, teacher.whatsappUrl!),
                        icon: const Icon(
                          Icons.chat_rounded,
                          color: Colors.white,
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ),
        ],
        if (description.isNotEmpty || course.items.isNotEmpty) ...[
          const _SectionTitle('عن الدورة'),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (description.isNotEmpty)
                    Text(
                      description,
                      style: const TextStyle(fontSize: 15, height: 1.7),
                    ),
                  if (description.isNotEmpty && course.items.isNotEmpty)
                    const SizedBox(height: 10),
                  for (final item in course.items)
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 4),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(
                            Icons.check_circle_rounded,
                            size: 18,
                            color: scheme.primary,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              item,
                              style: const TextStyle(fontSize: 15),
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
          ),
        ],
        const _SectionTitle('مواعيد الحصص الجاية'),
        Card(
          child: course.sessions.isEmpty
              ? const ListTile(
                  leading: Icon(Icons.event_note_rounded),
                  title: Text('المواعيد هتتحدد قريب'),
                  subtitle: Text('هيتواصل معاك المشرف بعد الحجز'),
                )
              : Column(
                  children: [
                    for (final (i, s) in course.sessions.take(6).indexed) ...[
                      if (i > 0)
                        const Divider(height: 1, indent: 16, endIndent: 16),
                      ListTile(
                        leading: const Icon(Icons.event_rounded),
                        title: Text(formatSessionTime(s.startsAt)),
                        subtitle: Text(
                          s.location ??
                              '${arabicDigits(s.durationMinutes)} دقيقة',
                        ),
                      ),
                    ],
                  ],
                ),
        ),
        const SizedBox(height: 14),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(
              Icons.info_outline_rounded,
              size: 18,
              color: scheme.onSurfaceVariant,
            ),
            const SizedBox(width: 6),
            Expanded(
              child: Text(
                course.isOnline
                    ? 'بعد ما المشرف يأكّد اشتراكك، رابط الحصة (Zoom) ورقم واتساب المعلم هيظهروا في "دوراتي".'
                    : 'بعد ما المشرف يأكّد اشتراكك، هتلاقي المواعيد ورقم واتساب المعلم في "دوراتي".',
                style: TextStyle(
                  fontSize: 13,
                  color: scheme.onSurfaceVariant,
                  height: 1.6,
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.text);

  final String text;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsetsDirectional.only(start: 4, top: 18, bottom: 8),
    child: Text(
      text,
      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
    ),
  );
}

class _HeroChip extends StatelessWidget {
  const _HeroChip({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
    decoration: BoxDecoration(
      color: Colors.white.withValues(alpha: 0.14),
      borderRadius: BorderRadius.circular(10),
    ),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 16, color: Colors.white),
        const SizedBox(width: 6),
        Text(label, style: const TextStyle(color: Colors.white, fontSize: 13)),
      ],
    ),
  );
}
