import 'package:flutter/material.dart';

import '../../../app/theme.dart';
import '../../../core/widgets/rub_el_hizb.dart';
import '../../account/data/auth_store.dart';
import '../../account/presentation/login_screen.dart';
import 'courses_screen.dart';
import 'my_courses_tab.dart';
import 'teacher_tab.dart';
import 'teachers_screen.dart';

/// الأكاديمية: كروت ← دورات أونلاين / حضوري / المعلمين / دوراتي (أو مجموعاتي للمعلم).
class AcademyScreen extends StatelessWidget {
  const AcademyScreen({super.key});

  void _push(BuildContext context, Widget screen) =>
      Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('الأكاديمية')),
      body: ListenableBuilder(
        listenable: AuthStore.instance,
        builder: (context, _) {
          final user = AuthStore.instance.user;
          final teacher = user?.isTeacher ?? false;
          return ListView(
            padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
            children: [
              const _AccountHeader(),
              const SizedBox(height: 20),
              Padding(
                padding: const EdgeInsetsDirectional.only(start: 4, bottom: 10),
                child: Text(
                  teacher ? 'لوحتك' : 'اختار اللي يناسبك',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.15,
                children: [
                  if (teacher)
                    _Tile(
                      title: 'مجموعاتي',
                      subtitle: 'الحصص والطلاب',
                      icon: Icons.groups_rounded,
                      color: const Color(0xFF6B4FA3),
                      onTap: () => _push(
                        context,
                        const _Wrapped(title: 'مجموعاتي', child: TeacherTab()),
                      ),
                    ),
                  _Tile(
                    title: 'دورات أونلاين',
                    subtitle: 'من بيتك على Zoom',
                    icon: Icons.videocam_rounded,
                    color: const Color(0xFF2F6690),
                    onTap: () =>
                        _push(context, const CoursesScreen(online: true)),
                  ),
                  _Tile(
                    title: 'دورات حضورية',
                    subtitle: 'في مقر الأكاديمية',
                    icon: Icons.mosque_rounded,
                    color: AppColors.emerald,
                    onTap: () =>
                        _push(context, const CoursesScreen(online: false)),
                  ),
                  _Tile(
                    title: 'المعلمين',
                    subtitle: 'دوراتهم وتواصل معاهم',
                    icon: Icons.person_search_rounded,
                    color: const Color(0xFF9A7413),
                    onTap: () => _push(context, const TeachersScreen()),
                  ),
                  if (!teacher)
                    _Tile(
                      title: 'دوراتي',
                      subtitle: 'حصصي ورابط Zoom',
                      icon: Icons.event_available_rounded,
                      color: const Color(0xFF6B4FA3),
                      onTap: () async {
                        if (!AuthStore.instance.loggedIn &&
                            !await openLogin(context)) {
                          return;
                        }
                        if (context.mounted) {
                          _push(
                            context,
                            const _Wrapped(
                              title: 'دوراتي',
                              child: MyCoursesTab(),
                            ),
                          );
                        }
                      },
                    ),
                ],
              ),
            ],
          );
        },
      ),
    );
  }
}

/// أعلى الصفحة: لو مش داخل ← دعوة واضحة "إنشاء حساب طالب" + "دخول"؛ لو داخل ← اسمه ودوره.
class _AccountHeader extends StatelessWidget {
  const _AccountHeader();

  @override
  Widget build(BuildContext context) {
    final user = AuthStore.instance.user;
    return Container(
      decoration: BoxDecoration(
        gradient: AppColors.heroGradient,
        borderRadius: BorderRadius.circular(24),
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(
        children: [
          PositionedDirectional(
            end: -30,
            top: -30,
            child: RubElHizb(
              size: 140,
              color: Colors.white.withValues(alpha: 0.07),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(20),
            child: user == null
                ? Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'أهلاً بيك في أكاديمية ابن الجزري',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 19,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'اعمل حساب طالب برقم موبايلك عشان تحجز في الدورات وتدخل حصصك الأونلاين.',
                        style: TextStyle(color: Colors.white70, height: 1.5),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: FilledButton(
                              style: FilledButton.styleFrom(
                                backgroundColor: AppColors.gold,
                                foregroundColor: Colors.black,
                              ),
                              onPressed: () =>
                                  openLogin(context, register: true),
                              child: const Text('حساب جديد'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: OutlinedButton(
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.white,
                                side: const BorderSide(color: Colors.white54),
                              ),
                              onPressed: () => openLogin(context),
                              child: const Text('دخول'),
                            ),
                          ),
                        ],
                      ),
                    ],
                  )
                : Row(
                    children: [
                      CircleAvatar(
                        radius: 26,
                        backgroundColor: AppColors.gold,
                        foregroundColor: Colors.black,
                        child: Text(
                          user.name.characters.first,
                          style: const TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              user.name,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              user.isTeacher ? 'حساب معلم' : 'حساب طالب',
                              style: const TextStyle(color: AppColors.goldSoft),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
          ),
        ],
      ),
    );
  }
}

class _Tile extends StatelessWidget {
  const _Tile({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 52,
                height: 52,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Icon(icon, color: color, size: 28),
              ),
              const Spacer(),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                subtitle,
                style: TextStyle(
                  fontSize: 12.5,
                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// شاشة بعنوان لمحتوى (دوراتي / مجموعاتي).
class _Wrapped extends StatelessWidget {
  const _Wrapped({required this.title, required this.child});

  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: Text(title)),
    body: child,
  );
}
