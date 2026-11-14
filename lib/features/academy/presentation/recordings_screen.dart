import 'package:flutter/material.dart';

import '../../../core/utils/formatting.dart';
import '../../../core/widgets/async_view.dart';
import '../../../core/widgets/empty_state.dart';
import '../../audio/data/audio_controller.dart';
import '../../mushaf/data/quran_data.dart';
import '../data/academy_repository.dart';
import '../domain/academy_models.dart';

/// «التسجيلات»: مواد الأكاديمية للكل — PDF، تسجيلات صوتية (بتشتغل جوه التطبيق
/// وتكمل في الخلفية)، فيديوهات وروابط (بتفتح في تطبيقها).
class RecordingsScreen extends StatefulWidget {
  const RecordingsScreen({super.key});

  @override
  State<RecordingsScreen> createState() => _RecordingsScreenState();
}

class _RecordingsScreenState extends State<RecordingsScreen> {
  final _repo = AcademyRepository();
  late Future<List<Recording>> _future = _repo.recordings();
  RecordingType? _filter;

  void _reload() => setState(() => _future = _repo.recordings());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('التسجيلات')),
      bottomNavigationBar: const _MiniPlayer(),
      body: AsyncView<List<Recording>>(
        future: _future,
        onRetry: _reload,
        builder: (context, all) {
          if (all.isEmpty) {
            return const EmptyState(
              icon: Icons.video_library_outlined,
              title: 'مفيش تسجيلات لسه',
              message: 'الدروس المسجلة والملفات هتظهر هنا أول ما تتضاف.',
            );
          }
          final types = {for (final r in all) r.type}.toList()
            ..sort((a, b) => a.index.compareTo(b.index));
          final shown = _filter == null
              ? all
              : all.where((r) => r.type == _filter).toList();
          return RefreshIndicator(
            onRefresh: () async => _reload(),
            child: ListView(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
              children: [
                if (types.length > 1)
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _FilterChip(
                          label: 'الكل',
                          selected: _filter == null,
                          onTap: () => setState(() => _filter = null),
                        ),
                        for (final t in types)
                          _FilterChip(
                            label: t.plural,
                            selected: _filter == t,
                            onTap: () => setState(() => _filter = t),
                          ),
                      ],
                    ),
                  ),
                const SizedBox(height: 8),
                for (final r in shown)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: _RecordingCard(recording: r),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsetsDirectional.only(end: 8),
    child: ChoiceChip(
      label: Text(label),
      selected: selected,
      onSelected: (_) => onTap(),
    ),
  );
}

({IconData icon, Color color}) _look(RecordingType type) => switch (type) {
  RecordingType.pdf => (
    icon: Icons.picture_as_pdf_rounded,
    color: const Color(0xFFC0392B),
  ),
  RecordingType.audio => (
    icon: Icons.graphic_eq_rounded,
    color: const Color(0xFF0B5D45),
  ),
  RecordingType.video => (
    icon: Icons.play_circle_fill_rounded,
    color: const Color(0xFFB7791F),
  ),
  RecordingType.link => (
    icon: Icons.link_rounded,
    color: const Color(0xFF2F6690),
  ),
};

class _RecordingCard extends StatelessWidget {
  const _RecordingCard({required this.recording});

  final Recording recording;

  void _open(BuildContext context) {
    final url = recording.url;
    if (url == null) return;
    if (recording.type == RecordingType.audio) {
      final audio = AudioController.instance;
      if (audio.recordingId == '${recording.id}') {
        audio.togglePause();
      } else {
        audio.playRecording(
          id: '${recording.id}',
          url: url,
          title: recording.title,
          artist: recording.teacherName,
        );
      }
    } else {
      openExternal(context, url);
    }
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final look = _look(recording.type);
    final meta = [
      recording.type.short,
      if (recording.teacherName != null) recording.teacherName!,
      if (recording.courseTitle != null) recording.courseTitle!,
    ].join(' • ');

    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: recording.url == null ? null : () => _open(context),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: look.color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(look.icon, color: look.color, size: 26),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      recording.title,
                      style: const TextStyle(
                        fontSize: 15.5,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      meta,
                      style: TextStyle(
                        fontSize: 12.5,
                        color: scheme.onSurfaceVariant,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if ((recording.description ?? '').isNotEmpty) ...[
                      const SizedBox(height: 6),
                      Text(
                        recording.description!,
                        style: const TextStyle(fontSize: 13.5, height: 1.5),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(width: 8),
              if (recording.type == RecordingType.audio)
                ListenableBuilder(
                  listenable: AudioController.instance,
                  builder: (context, _) {
                    final audio = AudioController.instance;
                    final mine = audio.recordingId == '${recording.id}';
                    if (mine && audio.isLoading) {
                      return const SizedBox(
                        width: 40,
                        height: 40,
                        child: Padding(
                          padding: EdgeInsets.all(10),
                          child: CircularProgressIndicator(strokeWidth: 2),
                        ),
                      );
                    }
                    return IconButton.filledTonal(
                      tooltip: mine && audio.isPlaying ? 'إيقاف مؤقت' : 'استمع',
                      onPressed: () => _open(context),
                      icon: Icon(
                        mine && audio.isPlaying
                            ? Icons.pause_rounded
                            : Icons.play_arrow_rounded,
                      ),
                    );
                  },
                )
              else
                Icon(
                  recording.type == RecordingType.pdf
                      ? Icons.download_rounded
                      : Icons.open_in_new_rounded,
                  size: 20,
                  color: scheme.onSurfaceVariant,
                ),
            ],
          ),
        ),
      ),
    );
  }
}

/// شريط التشغيل تحت: اسم التسجيل + تشغيل/إيقاف + شريط التقدم.
class _MiniPlayer extends StatelessWidget {
  const _MiniPlayer();

  static String _time(Duration d) {
    final m = d.inMinutes;
    final s = d.inSeconds % 60;
    return '${arabicDigits(m)}:${s < 10 ? '٠' : ''}${arabicDigits(s)}';
  }

  @override
  Widget build(BuildContext context) {
    final audio = AudioController.instance;
    return ListenableBuilder(
      listenable: audio,
      builder: (context, _) {
        if (audio.recordingId == null) return const SizedBox.shrink();
        final scheme = Theme.of(context).colorScheme;
        return Material(
          color: scheme.surfaceContainerHigh,
          child: SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(8, 6, 8, 4),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      IconButton.filled(
                        onPressed: audio.togglePause,
                        icon: Icon(
                          audio.isPlaying
                              ? Icons.pause_rounded
                              : Icons.play_arrow_rounded,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          audio.recordingTitle ?? '',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      IconButton(
                        tooltip: 'إيقاف',
                        onPressed: audio.stop,
                        icon: const Icon(Icons.close_rounded),
                      ),
                    ],
                  ),
                  StreamBuilder<Duration>(
                    stream: audio.positionStream,
                    builder: (context, snap) {
                      final total = audio.duration ?? Duration.zero;
                      final pos = snap.data ?? Duration.zero;
                      final max = total.inMilliseconds.toDouble();
                      return Row(
                        children: [
                          Text(
                            _time(pos),
                            style: const TextStyle(fontSize: 12),
                          ),
                          Expanded(
                            child: Slider(
                              value: max <= 0
                                  ? 0
                                  : pos.inMilliseconds.clamp(0, max).toDouble(),
                              max: max <= 0 ? 1 : max,
                              onChanged: max <= 0
                                  ? null
                                  : (v) => audio.seek(
                                      Duration(milliseconds: v.round()),
                                    ),
                            ),
                          ),
                          Text(
                            _time(total),
                            style: const TextStyle(fontSize: 12),
                          ),
                        ],
                      );
                    },
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
