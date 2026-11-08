import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../audio/data/audio_controller.dart';
import '../../audio/presentation/audio_bar.dart';
import '../../wird/data/wird_store.dart';
import '../../wird/domain/wird_mark.dart';
import '../data/mushaf_settings.dart';
import '../data/qcf_fonts.dart';
import '../data/quran_data.dart';
import 'mushaf_settings_screen.dart';
import 'share_ayah.dart';
import 'tafsir_sheet.dart';
import 'widgets/mushaf_page.dart';

/// افتح المصحف على صفحة. [playFrom]/[playTo] ← يبدأ التلاوة على طول (مثلاً ورد التسميع).
/// [hifzTest] ← يفتح على وضع اختبار الحفظ (الآيات مخفية).
void openMushafPage(
  BuildContext context,
  int page, {
  int? highlight,
  int? playFrom,
  int? playTo,
  bool hifzTest = false,
}) {
  Navigator.of(context).push(
    MaterialPageRoute(
      builder: (_) => MushafReaderScreen(
        initialPage: page,
        highlight: highlight,
        playFrom: playFrom,
        playTo: playTo,
        hifzTest: hifzTest,
      ),
    ),
  );
}

/// قارئ المصحف: صفحة كاملة في إطار، تقليب يمين/شمال أو فوق/تحت.
///
/// - ضغطة على مكان فاضي ← تخفي/تظهر الشريط اللي فوق.
/// - ضغطة على آية ← تشغيل من هنا / علامة ورد / نسخ.
/// - زرار العلامة ← شريط أنواع الورد: اختار النوع ← أول آية ← آخر آية.
/// - زرار 👁 ← اختبار الحفظ: الآيات تختفي، والضغط على آية يكشفها.
class MushafReaderScreen extends StatefulWidget {
  const MushafReaderScreen({
    super.key,
    required this.initialPage,
    this.highlight,
    this.playFrom,
    this.playTo,
    this.hifzTest = false,
  });

  final int initialPage;
  final int? highlight;
  final int? playFrom;
  final int? playTo;
  final bool hifzTest;

  @override
  State<MushafReaderScreen> createState() => _MushafReaderScreenState();
}

class _MushafReaderScreenState extends State<MushafReaderScreen> {
  late final PageController _controller = PageController(
    initialPage: widget.initialPage - 1,
  );
  late int _page = widget.initialPage;
  final _audio = AudioController.instance;

  bool _chrome = true;
  bool _markMode = false;
  WirdType? _markType;
  int? _markFrom;
  int? _lastFollowed;

  /// اختبار الحفظ: الآيات اللي اتكشفت (الباقي مخفي).
  late bool _hifz = widget.hifzTest;
  final _revealed = <int>{};

  bool _verseHidden(int verse) => _hifz && !_revealed.contains(verse);

  void _toggleHifz() => setState(() {
    _hifz = !_hifz;
    _revealed.clear();
    _markMode = false;
    _markType = _markFrom = null;
  });

  /// اكشف أول آية مخفية في الصفحة (بالترتيب)، ولو الصفحة خلصت ← الصفحة اللي بعدها.
  void _revealNext() {
    final (first, last) = QuranData.instance.pageRange(_page);
    for (var v = first; v <= last; v++) {
      if (!_revealed.contains(v)) {
        setState(() => _revealed.add(v));
        return;
      }
    }
    if (_page < QuranData.totalPages) {
      _controller.nextPage(
        duration: const Duration(milliseconds: 350),
        curve: Curves.easeInOut,
      );
    }
  }

  void _revealPage() {
    final (first, last) = QuranData.instance.pageRange(_page);
    setState(() => _revealed.addAll([for (var v = first; v <= last; v++) v]));
  }

  @override
  void initState() {
    super.initState();
    WirdStore.instance.setLastReadPage(_page);
    _prefetch(_page);
    _audio.addListener(_followAudio);
    if (widget.playFrom != null) {
      _audio.playRange(widget.playFrom!, widget.playTo);
    }
  }

  @override
  void dispose() {
    _audio.removeListener(_followAudio);
    _controller.dispose();
    super.dispose();
  }

  /// نزّل خطوط الصفحات اللي جنب الصفحة الحالية عشان التقليب يبقى فوري.
  void _prefetch(int page) {
    if (MushafSettings.instance.display != MushafDisplay.madani) return;
    for (final p in [page + 1, page - 1, page + 2]) {
      if (p >= 1 && p <= QuranData.totalPages) QcfFonts.instance.ensure(p);
    }
  }

  void _onPageChanged(int i) {
    setState(() => _page = i + 1);
    WirdStore.instance.setLastReadPage(_page);
    _prefetch(_page);
  }

  /// المصحف يقلّب مع التلاوة.
  void _followAudio() {
    final verse = _audio.currentVerse;
    if (verse == null ||
        verse == _lastFollowed ||
        !MushafSettings.instance.followAudio) {
      return;
    }
    _lastFollowed = verse;
    final page = QuranData.instance.pageOf(verse);
    if (page != _page && _controller.hasClients) {
      _controller.animateToPage(
        page - 1,
        duration: const Duration(milliseconds: 400),
        curve: Curves.easeInOut,
      );
    }
  }

  Color? _verseColor(int verse) {
    if (verse == _markFrom) return _markType?.color.withValues(alpha: 0.45);
    if (verse == _audio.currentVerse) {
      return Theme.of(context).colorScheme.primary.withValues(alpha: 0.28);
    }
    if (verse == widget.highlight) {
      return Theme.of(context).colorScheme.secondary.withValues(alpha: 0.35);
    }
    final marks = WirdStore.instance.marksAt(verse);
    return marks.isEmpty
        ? null
        : marks.first.type.color.withValues(alpha: 0.16);
  }

  Future<void> _onVerseTap(int verse) async {
    if (_markType != null) return _markTap(verse);
    // اختبار الحفظ: الضغطة بتكشف الآية (أو تخفيها تاني)
    if (_hifz) {
      setState(
        () => _revealed.contains(verse)
            ? _revealed.remove(verse)
            : _revealed.add(verse),
      );
      return;
    }
    final q = QuranData.instance;

    final action = await showModalBottomSheet<Object>(
      context: context,
      showDragHandle: true,
      builder: (context) => SafeArea(
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Text(
                  q.text(verse),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontFamily: 'AmiriQuran',
                    fontSize: 20,
                    height: 1.9,
                  ),
                ),
              ),
              Text(
                q.label(verse),
                style: Theme.of(context).textTheme.labelLarge,
              ),
              const Divider(),
              ListTile(
                leading: const Icon(Icons.menu_book_outlined),
                title: const Text('التفسير الميسر'),
                onTap: () => Navigator.pop(context, 'tafsir'),
              ),
              ListTile(
                leading: const Icon(Icons.play_circle_outline),
                title: const Text('تشغيل من هنا لآخر السورة'),
                onTap: () => Navigator.pop(context, 'play'),
              ),
              ListTile(
                leading: const Icon(Icons.repeat_one),
                title: const Text('تشغيل هذه الآية فقط'),
                onTap: () => Navigator.pop(context, 'play_one'),
              ),
              ListTile(
                leading: const Icon(Icons.share_outlined),
                title: const Text('مشاركة الآية (صورة أو نص)'),
                onTap: () => Navigator.pop(context, 'share'),
              ),
              ListTile(
                leading: const Icon(Icons.copy),
                title: const Text('نسخ الآية'),
                onTap: () => Navigator.pop(context, 'copy'),
              ),
              const Divider(),
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 16),
                child: Align(
                  alignment: AlignmentDirectional.centerStart,
                  child: Text('ابدأ علامة ورد من هنا:'),
                ),
              ),
              Wrap(
                spacing: 8,
                children: [
                  for (final t in WirdType.values)
                    ActionChip(
                      avatar: Icon(Icons.bookmark, color: t.color, size: 18),
                      label: Text(t.label),
                      onPressed: () => Navigator.pop(context, t),
                    ),
                ],
              ),
              const SizedBox(height: 12),
            ],
          ),
        ),
      ),
    );

    if (!mounted || action == null) return;
    switch (action) {
      case 'tafsir':
        await showTafsirSheet(context, verse);
      case 'share':
        await showShareAyah(context, verse);
      case 'play':
        _audio.playRange(verse);
      case 'play_one':
        _audio.playRange(verse, verse);
      case 'copy':
        await Clipboard.setData(
          ClipboardData(text: '${q.text(verse)} [${q.label(verse)}]'),
        );
        if (mounted) {
          ScaffoldMessenger.of(context)
              .showSnackBar(const SnackBar(content: Text('اتنسخت الآية')));
        }
      case WirdType t:
        setState(() {
          _markMode = true;
          _markType = t;
          _markFrom = verse;
        });
    }
  }

  Future<void> _markTap(int verse) async {
    if (_markFrom == null) {
      setState(() => _markFrom = verse);
      return;
    }
    final type = _markType!;
    final from = _markFrom!;
    final mark = WirdMark(
      type: type,
      from: from < verse ? from : verse,
      to: from < verse ? verse : from,
    );
    await WirdStore.instance.save(mark);
    setState(() {
      _markType = _markFrom = null;
      _markMode = false;
    });
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('اتحفظت — ${type.label}: ${mark.describe()}')),
      );
    }
  }

  void _toggleMarkMode() => setState(() {
    _markMode = !_markMode;
    _markType = _markFrom = null;
  });

  String _markHint() {
    if (_markType == null) return 'اختار نوع العلامة';
    if (_markFrom == null) return '${_markType!.label}: اضغط على أول آية';
    return '${_markType!.label}: من ${QuranData.instance.label(_markFrom!)} — اضغط على آخر آية (ممكن تقلّب)';
  }

  @override
  Widget build(BuildContext context) {
    final q = QuranData.instance;
    final (first, last) = q.pageRange(_page);

    return Scaffold(
      appBar: _chrome
          ? AppBar(
              // شريط رفيع عشان الصفحة تاخد أكبر مساحة
              toolbarHeight: 40,
              titleTextStyle: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: Colors.white,
              ),
              iconTheme: const IconThemeData(size: 20, color: Colors.white),
              actionsIconTheme: const IconThemeData(
                size: 20,
                color: Colors.white,
              ),
              title: Text('${q.suraOf(first).name} • ص ${arabicDigits(_page)}'),
              actions: [
                IconButton(
                  tooltip: 'تشغيل الصفحة',
                  icon: const Icon(Icons.play_arrow),
                  onPressed: () => _audio.playRange(first, last),
                ),
                IconButton(
                  tooltip: 'اختبار الحفظ',
                  icon: Icon(
                    _hifz ? Icons.visibility_off : Icons.visibility_outlined,
                  ),
                  onPressed: _toggleHifz,
                ),
                IconButton(
                  tooltip: 'علامات الورد',
                  icon: Icon(
                    _markMode ? Icons.bookmark : Icons.bookmark_border,
                  ),
                  onPressed: _toggleMarkMode,
                ),
                IconButton(
                  tooltip: 'إعدادات المصحف',
                  icon: const Icon(Icons.tune),
                  onPressed: () => Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const MushafSettingsScreen(),
                    ),
                  ),
                ),
              ],
            )
          : null,
      body: SafeArea(
        top: !_chrome,
        child: Column(
          children: [
            if (_hifz)
              _HifzBar(
                onNext: _revealNext,
                onRevealPage: _revealPage,
                onHideAll: () => setState(_revealed.clear),
                onExit: _toggleHifz,
              ),
            if (_markMode)
              _MarkBar(
                selected: _markType,
                onSelected: (t) => setState(() {
                  _markType = _markType == t ? null : t;
                  _markFrom = null;
                }),
                hint: _markHint(),
              ),
            Expanded(
              child: ListenableBuilder(
                listenable: Listenable.merge([
                  WirdStore.instance,
                  MushafSettings.instance,
                  _audio,
                ]),
                builder: (context, _) => PageView.builder(
                  controller: _controller,
                  scrollDirection: MushafSettings.instance.verticalPaging
                      ? Axis.vertical
                      : Axis.horizontal,
                  itemCount: QuranData.totalPages,
                  onPageChanged: _onPageChanged,
                  itemBuilder: (context, i) => GestureDetector(
                    behavior: HitTestBehavior.opaque,
                    onTap: () => setState(() => _chrome = !_chrome),
                    child: MushafPage(
                      page: i + 1,
                      onVerseTap: _onVerseTap,
                      verseColor: _verseColor,
                      verseHidden: _verseHidden,
                    ),
                  ),
                ),
              ),
            ),
            const AudioBar(),
          ],
        ),
      ),
    );
  }
}

/// شريط اختبار الحفظ: كشف الآية التالية / الصفحة كلها / إخفاء الكل / خروج.
class _HifzBar extends StatelessWidget {
  const _HifzBar({
    required this.onNext,
    required this.onRevealPage,
    required this.onHideAll,
    required this.onExit,
  });

  final VoidCallback onNext;
  final VoidCallback onRevealPage;
  final VoidCallback onHideAll;
  final VoidCallback onExit;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Material(
      color: scheme.secondaryContainer,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        child: Row(
          children: [
            const Icon(Icons.psychology_alt_outlined, size: 20),
            const SizedBox(width: 6),
            const Expanded(
              child: Text(
                'اختبار الحفظ — اقرأ من حفظك، واضغط على الآية لكشفها',
                style: TextStyle(fontSize: 12),
              ),
            ),
            FilledButton.tonal(
              style: FilledButton.styleFrom(
                visualDensity: VisualDensity.compact,
              ),
              onPressed: onNext,
              child: const Text('التالية'),
            ),
            PopupMenuButton<VoidCallback>(
              icon: const Icon(Icons.more_vert, size: 20),
              onSelected: (action) => action(),
              itemBuilder: (context) => [
                PopupMenuItem(
                  value: onRevealPage,
                  child: const Text('كشف الصفحة'),
                ),
                PopupMenuItem(
                  value: onHideAll,
                  child: const Text('إخفاء الكل'),
                ),
                PopupMenuItem(
                  value: onExit,
                  child: const Text('إنهاء الاختبار'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

/// شريط أنواع الورد (بيظهر لما تدوس زرار العلامة).
class _MarkBar extends StatelessWidget {
  const _MarkBar({
    required this.selected,
    required this.onSelected,
    required this.hint,
  });

  final WirdType? selected;
  final ValueChanged<WirdType> onSelected;
  final String hint;

  @override
  Widget build(BuildContext context) {
    return Material(
      color:
          (selected?.color ??
                  Theme.of(context).colorScheme.surfaceContainerHighest)
              .withValues(alpha: 0.18),
      child: Column(
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.fromLTRB(8, 6, 8, 0),
            child: Row(
              children: [
                for (final type in WirdType.values)
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 4),
                    child: ChoiceChip(
                      avatar: Icon(
                        Icons.bookmark,
                        color: selected == type ? Colors.white : type.color,
                        size: 18,
                      ),
                      label: Text(type.label),
                      selected: selected == type,
                      selectedColor: type.color,
                      labelStyle: TextStyle(
                        color: selected == type ? Colors.white : null,
                      ),
                      showCheckmark: false,
                      onSelected: (_) => onSelected(type),
                    ),
                  ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 2, 12, 6),
            child: Text(hint, style: Theme.of(context).textTheme.bodySmall),
          ),
        ],
      ),
    );
  }
}
