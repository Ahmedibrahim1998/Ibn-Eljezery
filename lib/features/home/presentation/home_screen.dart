import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../app/theme.dart';
import '../../../core/widgets/mushaf_art.dart';
import '../../../core/widgets/rub_el_hizb.dart';
import '../../academy/presentation/academy_screen.dart';
import '../../account/data/auth_store.dart';
import '../../mushaf/data/quran_data.dart';
import '../../mushaf/presentation/mushaf_reader_screen.dart';
import '../../mushaf/presentation/mushaf_screen.dart';
import '../../settings/presentation/settings_screen.dart';
import '../../wird/data/wird_store.dart';

/// الشاشة الرئيسية: هيدر الأكاديمية + "كمّل قراءتك" + بابين ← المصحف والأكاديمية.
class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    // أيقونات شريط الحالة بيضا فوق الهيدر الأخضر
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light,
      child: Scaffold(
        body: ListenableBuilder(
          listenable: Listenable.merge([
            WirdStore.instance,
            AuthStore.instance,
          ]),
          builder: (context, _) => CustomScrollView(
            slivers: [
              const SliverToBoxAdapter(child: _Hero()),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 18, 20, 24),
                sliver: SliverList.list(
                  children: [
                    const _ContinueReading(),
                    const SizedBox(height: 18),
                    _DoorCard(
                      title: 'المصحف الشريف',
                      subtitle: 'القراءة • الورد • التلاوة • التفسير',
                      art: const MushafArt(width: 100),
                      gradient: AppColors.heroGradient,
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const MushafScreen()),
                      ),
                    ),
                    const SizedBox(height: 14),
                    _DoorCard(
                      title: 'الأكاديمية',
                      subtitle: 'الدورات • الحجز • حصصي أونلاين',
                      art: const _IconArt(Icons.school_rounded),
                      gradient: const LinearGradient(
                        colors: [Color(0xFF7A5A12), AppColors.gold],
                        begin: AlignmentDirectional.topStart,
                        end: AlignmentDirectional.bottomEnd,
                      ),
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const AcademyScreen(),
                        ),
                      ),
                    ),
                    const SizedBox(height: 32),
                    const _Hadith(),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// الهيدر: خلفية خضرا بنجمة ربع الحزب + اللوجو + التحية.
class _Hero extends StatelessWidget {
  const _Hero();

  String get _greeting {
    final h = DateTime.now().hour;
    if (h < 12) return 'صباح الخير';
    if (h < 18) return 'نهارك سعيد';
    return 'مساء الخير';
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthStore.instance.user;
    return Container(
      decoration: const BoxDecoration(
        gradient: AppColors.heroGradient,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(32)),
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(
        children: [
          // زخرفة هادية في الخلفية
          PositionedDirectional(
            start: -60,
            top: -40,
            child: RubElHizb(
              size: 200,
              color: Colors.white.withValues(alpha: 0.04),
            ),
          ),
          PositionedDirectional(
            end: -30,
            bottom: -10,
            child: RubElHizb(
              size: 120,
              color: AppColors.gold.withValues(alpha: 0.12),
            ),
          ),
          SafeArea(
            bottom: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 4, 8, 28),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          user == null
                              ? _greeting
                              : '$_greeting، ${user.name.split(' ').first}',
                          style: const TextStyle(
                            color: Colors.white70,
                            fontSize: 15,
                          ),
                        ),
                      ),
                      IconButton(
                        tooltip: 'الإعدادات',
                        color: Colors.white,
                        icon: const Icon(Icons.settings_outlined),
                        onPressed: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => const SettingsScreen(),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Container(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.gold.withValues(alpha: 0.35),
                              blurRadius: 24,
                            ),
                          ],
                        ),
                        child: Image.asset(
                          'assets/images/logo.png',
                          width: 84,
                          height: 84,
                        ),
                      ),
                      const SizedBox(width: 16),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'ابن الجزري',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 30,
                                fontWeight: FontWeight.bold,
                                height: 1.2,
                              ),
                            ),
                            SizedBox(height: 4),
                            Text(
                              'مصحفك وأكاديميتك لتعليم القرآن',
                              style: TextStyle(
                                color: AppColors.goldSoft,
                                fontSize: 14,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// "كمّل قراءتك": آخر صفحة + أيام الالتزام 🔥 — ضغطة واحدة ويفتح المصحف على طول.
class _ContinueReading extends StatelessWidget {
  const _ContinueReading();

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final q = QuranData.instance;
    final page = WirdStore.instance.lastReadPage;
    final streak = WirdStore.instance.stats().currentStreak;
    final sura = page == null ? null : q.suraOf(q.pageRange(page).$1);

    return Material(
      color: scheme.surfaceContainerLowest,
      borderRadius: BorderRadius.circular(20),
      elevation: 6,
      shadowColor: Colors.black.withValues(alpha: 0.18),
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        onTap: () => openMushafPage(context, page ?? 1),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: scheme.primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(Icons.bookmark_rounded, color: scheme.primary),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      page == null ? 'ابدأ القراءة' : 'كمّل قراءتك',
                      style: TextStyle(
                        color: scheme.onSurfaceVariant,
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      page == null
                          ? 'سورة الفاتحة'
                          : 'سورة ${sura!.name} • ص ${arabicDigits(page)}',
                      style: const TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
              if (streak > 0)
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: AppColors.gold.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '🔥 ${arabicDigits(streak)}',
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                )
              else
                Icon(
                  Icons.play_circle_fill_rounded,
                  color: scheme.primary,
                  size: 34,
                ),
            ],
          ),
        ),
      ),
    );
  }
}

/// باب كبير: العنوان والوصف على جنب، ورسمة معبّرة على الجنب التاني.
class _DoorCard extends StatelessWidget {
  const _DoorCard({
    required this.title,
    required this.subtitle,
    required this.art,
    required this.gradient,
    required this.onTap,
  });

  final String title;
  final String subtitle;
  final Widget art;
  final Gradient gradient;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      borderRadius: BorderRadius.circular(26),
      clipBehavior: Clip.antiAlias,
      elevation: 4,
      shadowColor: Colors.black.withValues(alpha: 0.25),
      child: Ink(
        decoration: BoxDecoration(gradient: gradient),
        child: InkWell(
          onTap: onTap,
          child: Stack(
            children: [
              PositionedDirectional(
                start: -40,
                bottom: -50,
                child: RubElHizb(
                  size: 160,
                  color: Colors.white.withValues(alpha: 0.07),
                ),
              ),
              Container(
                // ارتفاع أدنى بس — النص الطويل يكبّر الكارت بدل ما يتقص
                constraints: const BoxConstraints(minHeight: 140),
                padding: const EdgeInsetsDirectional.fromSTEB(22, 18, 16, 18),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            title,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 23,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            subtitle,
                            style: const TextStyle(
                              color: Colors.white70,
                              fontSize: 13.5,
                              height: 1.4,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 5,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.16),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  'ادخل',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                SizedBox(width: 4),
                                Icon(
                                  Icons.arrow_forward_rounded,
                                  color: Colors.white,
                                  size: 16,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    art,
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// رسمة باب الأكاديمية: أيقونة كبيرة في دايرة بحلقة دهبي.
class _IconArt extends StatelessWidget {
  const _IconArt(this.icon);

  final IconData icon;

  @override
  Widget build(BuildContext context) => Container(
    width: 96,
    height: 96,
    decoration: BoxDecoration(
      shape: BoxShape.circle,
      color: Colors.white.withValues(alpha: 0.14),
      border: Border.all(color: Colors.white.withValues(alpha: 0.35), width: 2),
    ),
    child: Icon(icon, color: Colors.white, size: 48),
  );
}

class _Hadith extends StatelessWidget {
  const _Hadith();

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: Divider(color: AppColors.gold.withValues(alpha: 0.4)),
            ),
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 10),
              child: RubElHizb(size: 18, color: AppColors.gold),
            ),
            Expanded(
              child: Divider(color: AppColors.gold.withValues(alpha: 0.4)),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Text(
          '«خَيْرُكُمْ مَنْ تَعَلَّمَ الْقُرْآنَ وَعَلَّمَهُ»',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontFamily: 'AmiriQuran',
            fontSize: 19,
            color: scheme.onSurface,
          ),
        ),
        Text(
          'رواه البخاري',
          style: TextStyle(fontSize: 12, color: scheme.onSurfaceVariant),
        ),
      ],
    );
  }
}
