import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:just_audio/just_audio.dart';
import 'package:just_audio_background/just_audio_background.dart';

import '../../mushaf/data/mushaf_settings.dart';
import '../../mushaf/data/quran_data.dart';
import '../domain/reciter.dart';
import 'audio_downloads.dart';

/// مشغّل التلاوة: بيشغّل مقطع من آية لآية، وكل آية تتكرر حسب الإعدادات (للحفظ).
///
/// [currentVerse] = الآية اللي بتتقري دلوقتي (المصحف بيلوّنها ويقلّب لها).
class AudioController extends ChangeNotifier {
  AudioController._() {
    _player.currentIndexStream.listen((i) {
      final verse = (i == null || i >= _queue.length) ? null : _queue[i];
      if (verse != currentVerse) {
        currentVerse = verse;
        notifyListeners();
      }
    });
    _player.playerStateStream.listen((state) {
      if (state.processingState == ProcessingState.completed) {
        stop();
      } else {
        notifyListeners();
      }
    });
  }

  static final instance = AudioController._();

  final _player = AudioPlayer();

  /// رقم الآية لكل عنصر في قائمة التشغيل (الآية بتتكرر لو فيه تكرار).
  List<int> _queue = const [];

  int? currentVerse;
  int? rangeFrom;
  int? rangeTo;
  String? error;

  /// تسجيل من قسم «التسجيلات» شغّال دلوقتي (بدل التلاوة).
  String? recordingId;
  String? recordingTitle;

  Stream<Duration> get positionStream => _player.positionStream;
  Duration? get duration => _player.duration;
  Future<void> seek(Duration position) => _player.seek(position);

  bool get isActive => _queue.isNotEmpty;
  bool get isPlaying => _player.playing;
  bool get isLoading =>
      _player.processingState == ProcessingState.loading ||
      _player.processingState == ProcessingState.buffering;

  /// شغّل من [from] لـ [to] (لو مش محدد ← لآخر السورة).
  Future<void> playRange(int from, [int? to]) async {
    final q = QuranData.instance;
    final sura = q.suraOf(from);
    final last = to ?? sura.start + sura.ayahCount - 1;
    final settings = MushafSettings.instance;
    final reciter = Reciter.byId(settings.reciterId);

    _queue = [
      for (var v = from; v <= last; v++)
        for (var r = 0; r < settings.repeat; r++) v,
    ];
    recordingId = recordingTitle = null;
    rangeFrom = from;
    rangeTo = last;
    currentVerse = from;
    error = null;
    notifyListeners();

    try {
      // الـ MediaItem هو اللي بيظهر في الإشعار وشاشة القفل (just_audio_background)
      // الآيات المتنزّلة بتشتغل من الموبايل، والباقي من النت
      final downloads = AudioDownloads.instance;
      await _player.setAudioSources([
        for (final (i, v) in _queue.indexed)
          AudioSource.uri(
            switch (downloads.localPath(reciter.id, v)) {
              final path? => Uri.file(path),
              null => Uri.parse(
                reciter.urlFor(q.suraOf(v).number, q.ayahOf(v)),
              ),
            },
            tag: MediaItem(
              id: '$i:$v',
              title: 'سورة ${q.label(v)}',
              artist: reciter.name,
              album: 'ابن الجزري',
            ),
          ),
      ]);
      unawaited(_player.play());
    } catch (e) {
      error = 'تعذّر تشغيل التلاوة. تأكد من الإنترنت.';
      await stop();
    }
  }

  /// شغّل تسجيل صوتي من «التسجيلات» (بيوقف التلاوة لو شغالة).
  Future<void> playRecording({
    required String id,
    required String url,
    required String title,
    String? artist,
  }) async {
    _queue = const [];
    currentVerse = rangeFrom = rangeTo = null;
    recordingId = id;
    recordingTitle = title;
    error = null;
    notifyListeners();
    try {
      await _player.setAudioSource(
        AudioSource.uri(
          Uri.parse(url),
          tag: MediaItem(
            id: 'recording:$id',
            title: title,
            artist: artist ?? 'أكاديمية ابن الجزري',
            album: 'ابن الجزري',
          ),
        ),
      );
      unawaited(_player.play());
    } catch (e) {
      error = 'تعذّر تشغيل التسجيل. تأكد من الإنترنت.';
      await stop();
    }
  }

  Future<void> togglePause() async {
    if (_player.playing) {
      await _player.pause();
    } else {
      unawaited(_player.play());
    }
    notifyListeners();
  }

  Future<void> next() => _player.hasNext ? _player.seekToNext() : stop();

  Future<void> previous() => _player.seekToPrevious();

  Future<void> stop() async {
    await _player.stop();
    _queue = const [];
    currentVerse = rangeFrom = rangeTo = null;
    recordingId = recordingTitle = null;
    notifyListeners();
  }
}
