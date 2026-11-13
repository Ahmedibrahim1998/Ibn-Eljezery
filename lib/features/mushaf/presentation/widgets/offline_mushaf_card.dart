import 'package:flutter/material.dart';

import '../../../../app/theme.dart';
import '../../data/qcf_fonts.dart';
import '../../data/quran_data.dart';

/// كارت في شاشة المصحف: «نزّل المصحف كامل عشان يشتغل من غير نت».
/// بيختفي لوحده أول ما كل الصفحات تتنزل.
class OfflineMushafCard extends StatefulWidget {
  const OfflineMushafCard({super.key});

  @override
  State<OfflineMushafCard> createState() => _OfflineMushafCardState();
}

class _OfflineMushafCardState extends State<OfflineMushafCard> {
  final _fonts = QcfFonts.instance;
  late Future<int> _count = _fonts.downloadedCount();

  Future<void> _download() async {
    final ok = await _fonts.downloadAll();
    if (!mounted) return;
    setState(() => _count = _fonts.downloadedCount());
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? 'المصحف كله اتنزّل ✓ يشتغل دلوقتي من غير نت'
              : 'شوية صفحات ما اتنزلتش — اتأكد من النت وجرّب تاني',
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return ListenableBuilder(
      listenable: _fonts,
      builder: (context, _) => FutureBuilder<int>(
        future: _count,
        builder: (context, snap) {
          final count = snap.data;
          final total = QuranData.totalPages;
          final progress = _fonts.downloadAllProgress;
          if (count == null || (count >= total && progress == null)) {
            return const SizedBox.shrink();
          }
          return Padding(
            padding: const EdgeInsets.only(top: 12),
            child: Card(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
                child: Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: AppColors.gold.withValues(alpha: 0.18),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.download_for_offline_rounded,
                        color: AppColors.gold,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'المصحف من غير نت',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 15,
                            ),
                          ),
                          const SizedBox(height: 2),
                          if (progress != null) ...[
                            Text(
                              'بيتنزّل… ${arabicDigits((progress * 100).round())}٪',
                              style: TextStyle(
                                fontSize: 12.5,
                                color: scheme.onSurfaceVariant,
                              ),
                            ),
                            const SizedBox(height: 6),
                            LinearProgressIndicator(
                              value: progress,
                              borderRadius: BorderRadius.circular(4),
                            ),
                          ] else
                            Text(
                              'نزّل كل الصفحات بخط المدينة مرة واحدة (حوالي ٢٠ ميجا)',
                              style: TextStyle(
                                fontSize: 12.5,
                                color: scheme.onSurfaceVariant,
                              ),
                            ),
                        ],
                      ),
                    ),
                    if (progress == null) ...[
                      const SizedBox(width: 8),
                      FilledButton(
                        onPressed: _download,
                        style: FilledButton.styleFrom(
                          minimumSize: const Size(0, 40),
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                        ),
                        child: const Text('نزّل'),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
