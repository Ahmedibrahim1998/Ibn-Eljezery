import 'package:flutter/material.dart';

import '../../../app/theme.dart';
import '../../../core/utils/formatting.dart';
import '../../../core/widgets/async_view.dart';
import '../../../core/widgets/empty_state.dart';
import '../../mushaf/data/quran_data.dart';
import '../data/academy_repository.dart';
import '../domain/academy_models.dart';
import 'courses_screen.dart';

/// معلمين الأكاديمية ← صفحة كل معلم (دوراته + تواصل واتساب).
class TeachersScreen extends StatefulWidget {
  const TeachersScreen({super.key});

  @override
  State<TeachersScreen> createState() => _TeachersScreenState();
}

class _TeachersScreenState extends State<TeachersScreen> {
  final _repo = AcademyRepository();
  late Future<List<Teacher>> _future = _repo.teachers();

  void _reload() => setState(() => _future = _repo.teachers());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('المعلمين')),
      body: AsyncView<List<Teacher>>(
        future: _future,
        onRetry: _reload,
        builder: (context, teachers) => teachers.isEmpty
            ? const EmptyState(
                icon: Icons.person_off_outlined,
                title: 'مفيش معلمين لسه',
              )
            : RefreshIndicator(
                onRefresh: () async => _reload(),
                child: ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: teachers.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 12),
                  itemBuilder: (context, i) =>
                      _TeacherCard(teacher: teachers[i]),
                ),
              ),
      ),
    );
  }
}

class _TeacherCard extends StatelessWidget {
  const _TeacherCard({required this.teacher});

  final Teacher teacher;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final count = teacher.openCoursesCount ?? 0;
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => TeacherScreen(teacher: teacher)),
        ),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              TeacherAvatar(teacher: teacher, radius: 30),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      teacher.name,
                      style: const TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    if (teacher.certification != null)
                      Text(
                        teacher.certification!,
                        style: TextStyle(
                          color: scheme.onSurfaceVariant,
                          fontSize: 13,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    const SizedBox(height: 6),
                    Text(
                      count == 0
                          ? 'مفيش دورات مفتوحة دلوقتي'
                          : '${arabicDigits(count)} ${count == 1 ? 'دورة متاحة' : 'دورات متاحة'}',
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: count == 0
                            ? scheme.onSurfaceVariant
                            : scheme.primary,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(
                Icons.arrow_forward_ios_rounded,
                size: 14,
                color: scheme.onSurfaceVariant,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// صفحة المعلم: نبذة + زرار واتساب + دوراته المتاحة للحجز.
class TeacherScreen extends StatefulWidget {
  const TeacherScreen({super.key, required this.teacher});

  /// بيانات الكارت (بتتعرض على طول لحد ما التفاصيل توصل).
  final Teacher teacher;

  @override
  State<TeacherScreen> createState() => _TeacherScreenState();
}

class _TeacherScreenState extends State<TeacherScreen> {
  final _repo = AcademyRepository();
  late Future<Teacher> _future = _repo.teacher(widget.teacher.id);

  void _reload() => setState(() => _future = _repo.teacher(widget.teacher.id));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('المعلم')),
      body: AsyncView<Teacher>(
        future: _future,
        onRetry: _reload,
        builder: (context, t) => ListView(
          padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: AppColors.heroGradient,
                borderRadius: BorderRadius.circular(24),
              ),
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(3),
                    decoration: const BoxDecoration(
                      color: AppColors.gold,
                      shape: BoxShape.circle,
                    ),
                    child: TeacherAvatar(teacher: t, radius: 44),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    t.name,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  if (t.certification != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      t.certification!,
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: AppColors.goldSoft),
                    ),
                  ],
                  if (t.whatsappUrl != null) ...[
                    const SizedBox(height: 16),
                    FilledButton.icon(
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF25D366),
                        foregroundColor: Colors.white,
                        minimumSize: const Size.fromHeight(48),
                      ),
                      onPressed: () => openExternal(context, t.whatsappUrl!),
                      icon: const Icon(Icons.chat_rounded),
                      label: const Text('تواصل على واتساب'),
                    ),
                  ],
                ],
              ),
            ),
            if ((t.description ?? '').isNotEmpty) ...[
              const SizedBox(height: 18),
              const Text(
                'نبذة',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(
                    t.description!,
                    style: const TextStyle(fontSize: 15, height: 1.7),
                  ),
                ),
              ),
            ],
            const SizedBox(height: 18),
            const Text(
              'دوراته المتاحة',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            if (t.courses.isEmpty)
              const Card(
                child: ListTile(
                  leading: Icon(Icons.event_busy_rounded),
                  title: Text('مفيش دورات مفتوحة للتسجيل دلوقتي'),
                  subtitle: Text(
                    'تواصل مع المعلم على واتساب عشان تعرف المواعيد الجاية',
                  ),
                ),
              )
            else
              for (final c in t.courses)
                Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  // صفحة المعلم: الكارت من غير اسم المعلم (معروف)
                  child: CourseCard(course: c, showTeacher: false),
                ),
          ],
        ),
      ),
    );
  }
}
