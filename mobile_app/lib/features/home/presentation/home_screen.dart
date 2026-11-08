import 'package:flutter/material.dart';

import '../../../app/theme.dart';
import '../../fiqh/presentation/fiqh_screen.dart';
import '../../mushaf/presentation/mushaf_screen.dart';
import '../../settings/presentation/settings_screen.dart';

/// الشاشة الرئيسية: بابين بس ← المصحف والفقه.
class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final dark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, box) => SingleChildScrollView(
            child: ConstrainedBox(
              constraints: BoxConstraints(minHeight: box.maxHeight),
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Align(
                      alignment: AlignmentDirectional.centerEnd,
                      child: IconButton(
                        tooltip: 'الإعدادات',
                        icon: const Icon(Icons.settings_outlined),
                        onPressed: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => const SettingsScreen(),
                          ),
                        ),
                      ),
                    ),
                    // لوجو الأكاديمية (نفس لوجو الموقع) — فيه الاسم بالخط العربي
                    Center(
                      child: Container(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.gold.withValues(
                                alpha: dark ? 0.25 : 0.45,
                              ),
                              blurRadius: 24,
                            ),
                          ],
                        ),
                        child: Image.asset(
                          'assets/images/logo.png',
                          width: 170,
                          height: 170,
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'مصحف وتعليم شرعي',
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    const SizedBox(height: 28),
                    _BigCard(
                      title: 'المصحف الشريف',
                      subtitle: 'القراءة • الورد • التلاوة',
                      icon: Icons.auto_stories,
                      colors: const [Color(0xFF064E3B), AppColors.emerald],
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const MushafScreen()),
                      ),
                    ),
                    const SizedBox(height: 16),
                    _BigCard(
                      title: 'الفقه',
                      subtitle: 'المالكي والشافعي',
                      icon: Icons.school,
                      colors: const [Color(0xFF8A6D1A), AppColors.gold],
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const FiqhScreen()),
                      ),
                    ),
                    const SizedBox(height: 32),
                    Text(
                      '«خَيْرُكُمْ مَنْ تَعَلَّمَ الْقُرْآنَ وَعَلَّمَهُ»',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontFamily: 'AmiriQuran',
                        fontSize: 18,
                        color: Theme.of(context).colorScheme.onSurfaceVariant,
                      ),
                    ),
                    Text(
                      'رواه البخاري',
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _BigCard extends StatelessWidget {
  const _BigCard({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.colors,
    required this.onTap,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final List<Color> colors;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      borderRadius: BorderRadius.circular(24),
      clipBehavior: Clip.antiAlias,
      elevation: 3,
      child: Ink(
        height: 150,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: colors,
            begin: AlignmentDirectional.topStart,
            end: AlignmentDirectional.bottomEnd,
          ),
        ),
        child: InkWell(
          onTap: onTap,
          child: Stack(
            children: [
              // زخرفة خفيفة في الخلفية
              PositionedDirectional(
                end: -20,
                bottom: -30,
                child: Icon(
                  icon,
                  size: 160,
                  color: Colors.white.withValues(alpha: 0.12),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(24),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 32,
                      backgroundColor: Colors.white.withValues(alpha: 0.18),
                      child: Icon(icon, color: Colors.white, size: 34),
                    ),
                    const SizedBox(width: 18),
                    Expanded(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            title,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 26,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            subtitle,
                            style: const TextStyle(
                              color: Colors.white70,
                              fontSize: 15,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const Icon(
                      Icons.chevron_left,
                      color: Colors.white,
                      size: 30,
                    ),
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
