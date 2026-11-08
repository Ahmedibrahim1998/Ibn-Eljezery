import 'package:flutter/material.dart';

import '../../mushaf/data/mushaf_settings.dart';
import '../../mushaf/data/quran_data.dart';
import '../data/audio_controller.dart';
import '../domain/reciter.dart';

/// شريط التلاوة تحت المصحف: الآية الحالية + السابق/تشغيل/التالي/إيقاف.
class AudioBar extends StatelessWidget {
  const AudioBar({super.key});

  @override
  Widget build(BuildContext context) {
    final audio = AudioController.instance;
    return ListenableBuilder(
      listenable: audio,
      builder: (context, _) {
        if (audio.error != null) {
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (audio.error == null) return;
            ScaffoldMessenger.of(context)
                .showSnackBar(SnackBar(content: Text(audio.error!)));
            audio.error = null;
          });
        }
        if (!audio.isActive) return const SizedBox.shrink();

        final q = QuranData.instance;
        final verse = audio.currentVerse;
        final settings = MushafSettings.instance;
        final scheme = Theme.of(context).colorScheme;

        return Material(
          color: scheme.primaryContainer,
          child: SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              child: Row(
                children: [
                  if (audio.isLoading)
                    const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  else
                    Icon(Icons.graphic_eq, color: scheme.primary),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          verse == null ? '…' : q.label(verse),
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        Text(
                          '${Reciter.byId(settings.reciterId).name}${settings.repeat > 1 ? ' • تكرار ×${arabicDigits(settings.repeat)}' : ''}',
                          style: Theme.of(context).textTheme.bodySmall,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                  // في الـ RTL "السابق" على اليمين
                  IconButton(
                    icon: const Icon(Icons.skip_next),
                    tooltip: 'السابق',
                    onPressed: audio.previous,
                  ),
                  IconButton.filled(
                    icon: Icon(
                      audio.isPlaying ? Icons.pause : Icons.play_arrow,
                    ),
                    onPressed: audio.togglePause,
                  ),
                  IconButton(
                    icon: const Icon(Icons.skip_previous),
                    tooltip: 'التالي',
                    onPressed: audio.next,
                  ),
                  IconButton(
                    icon: const Icon(Icons.stop),
                    tooltip: 'إيقاف',
                    onPressed: audio.stop,
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}
