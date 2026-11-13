import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';

import '../../../../app/theme.dart';
import '../../../../core/widgets/rub_el_hizb.dart';
import '../../data/mushaf_layout.dart';
import '../../data/mushaf_settings.dart';
import '../../data/qcf_fonts.dart';
import '../../data/quran_data.dart';

/// لون خلفية الآية (علامة ورد / تحديد / تلاوة) — null = من غير لون.
typedef VerseColor = Color? Function(int verse);

/// اختبار الحفظ: الآية مخفية؟ (الكلام بيختفي ورقم الآية بيفضل ظاهر عشان الطالب يعرف مكانه).
typedef VerseHidden = bool Function(int verse);

/// بيوصّل [VerseHidden] لأجزاء الصفحة من غير ما نعدّيه في كل كلاس.
class _HiddenScope extends InheritedWidget {
  const _HiddenScope({required this.hidden, required super.child});

  final VerseHidden hidden;

  static bool of(BuildContext context, int verse) =>
      context.dependOnInheritedWidgetOfExactType<_HiddenScope>()?.hidden(
        verse,
      ) ??
      false;

  @override
  bool updateShouldNotify(_HiddenScope old) => true;
}

/// شكل الكلام المخفي: شفاف، وتحته ظل خفيف عشان مكان السطر يبان.
TextStyle _hiddenStyle(Color ink) => TextStyle(
  color: Colors.transparent,
  backgroundColor: ink.withValues(alpha: 0.07),
);

const _basmala = 'بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ';

/// صفحة مصحف كاملة جوه إطار: عنوان (السورة + الجزء) ← المتن ← رقم الصفحة.
/// الصفحة كلها بتظهر من غير scroll؛ التقليب للصفحة اللي بعدها بس.
class MushafPage extends StatelessWidget {
  const MushafPage({
    super.key,
    required this.page,
    required this.onVerseTap,
    required this.verseColor,
    this.verseHidden,
  });

  final int page;
  final ValueChanged<int> onVerseTap;
  final VerseColor verseColor;
  final VerseHidden? verseHidden;

  @override
  Widget build(BuildContext context) {
    return _HiddenScope(
      hidden: verseHidden ?? (_) => false,
      child: Builder(builder: _build),
    );
  }

  Widget _build(BuildContext context) {
    final q = QuranData.instance;
    final (first, _) = q.pageRange(page);
    final scheme = Theme.of(context).colorScheme;
    final dark = Theme.of(context).brightness == Brightness.dark;
    final paper = dark ? const Color(0xFF1B1F1C) : const Color(0xFFFFFBF2);
    final ink = dark ? const Color(0xFFEDE6D6) : const Color(0xFF1A1A1A);
    final small = TextStyle(
      fontSize: 12,
      color: scheme.primary,
      fontWeight: FontWeight.w600,
    );

    return Container(
      color: paper,
      padding: const EdgeInsets.fromLTRB(6, 4, 6, 6),
      child: Container(
        // إطار مزدوج: دهبي برّه، وأخضر رفيع جوه
        decoration: BoxDecoration(
          border: Border.all(color: AppColors.gold, width: 2.5),
          borderRadius: BorderRadius.circular(6),
        ),
        padding: const EdgeInsets.all(3),
        child: Container(
          decoration: BoxDecoration(
            border: Border.all(
              color: scheme.primary.withValues(alpha: 0.6),
              width: 1,
            ),
            borderRadius: BorderRadius.circular(4),
          ),
          padding: const EdgeInsets.fromLTRB(8, 2, 8, 2),
          child: Column(
            children: [
              Row(
                children: [
                  Text('سورة ${q.suraOf(first).name}', style: small),
                  const Spacer(),
                  Text('الجزء ${arabicDigits(q.juzOf(first))}', style: small),
                ],
              ),
              Divider(
                height: 6,
                thickness: 0.6,
                color: AppColors.gold.withValues(alpha: 0.7),
              ),
              Expanded(
                child: DefaultTextStyle.merge(
                  style: TextStyle(color: ink),
                  child: ListenableBuilder(
                    listenable: MushafSettings.instance,
                    builder: (context, _) =>
                        MushafSettings.instance.display == MushafDisplay.madani
                        ? _MadaniBody(
                            page: page,
                            onVerseTap: onVerseTap,
                            verseColor: verseColor,
                            ink: ink,
                          )
                        : _TextBody(
                            page: page,
                            onVerseTap: onVerseTap,
                            verseColor: verseColor,
                            ink: ink,
                          ),
                  ),
                ),
              ),
              Divider(
                height: 6,
                thickness: 0.6,
                color: AppColors.gold.withValues(alpha: 0.7),
              ),
              _PageNumber(page: page),
            ],
          ),
        ),
      ),
    );
  }
}

class _PageNumber extends StatelessWidget {
  const _PageNumber({required this.page});

  final int page;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 1),
      decoration: BoxDecoration(
        border: Border.all(color: AppColors.gold, width: 1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text(
        arabicDigits(page),
        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
      ),
    );
  }
}

/// عنوان السورة في إطار مزخرف.
class SuraHeaderBox extends StatelessWidget {
  const SuraHeaderBox({super.key, required this.name, this.fontSize = 18});

  final String name;
  final double fontSize;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Container(
      alignment: Alignment.center,
      margin: EdgeInsets.symmetric(vertical: fontSize * 0.12),
      decoration: BoxDecoration(
        color: scheme.primaryContainer.withValues(alpha: 0.45),
        border: Border.all(color: AppColors.gold, width: fontSize * 0.08),
        borderRadius: BorderRadius.circular(fontSize * 0.5),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          RubElHizb(size: fontSize * 0.9, color: AppColors.gold),
          SizedBox(width: fontSize),
          Text(
            'سورة $name',
            style: TextStyle(
              fontFamily: 'AmiriQuran',
              fontSize: fontSize,
              height: 1.3,
            ),
          ),
          SizedBox(width: fontSize),
          RubElHizb(size: fontSize * 0.9, color: AppColors.gold),
        ],
      ),
    );
  }
}

/// بيعمل TapGestureRecognizer لكل آية ويقفلهم لما الصفحة تتقفل أو تترسم تاني.
mixin _Recognizers<T extends StatefulWidget> on State<T> {
  final _recognizers = <TapGestureRecognizer>[];

  TapGestureRecognizer recognizer(VoidCallback onTap) {
    final r = TapGestureRecognizer()..onTap = onTap;
    _recognizers.add(r);
    return r;
  }

  void clearRecognizers() {
    for (final r in _recognizers) {
      r.dispose();
    }
    _recognizers.clear();
  }

  @override
  void dispose() {
    clearRecognizers();
    super.dispose();
  }
}

// ───────────────────────────── مصحف المدينة (خطوط المجمع) ─────────────────────────────

class _MadaniBody extends StatefulWidget {
  const _MadaniBody({
    required this.page,
    required this.onVerseTap,
    required this.verseColor,
    required this.ink,
  });

  final int page;
  final ValueChanged<int> onVerseTap;
  final VerseColor verseColor;
  final Color ink;

  @override
  State<_MadaniBody> createState() => _MadaniBodyState();
}

class _MadaniBodyState extends State<_MadaniBody> with _Recognizers {
  static const _base = 40.0;

  /// null = بنشوف الخط موجود على الموبايل ولا لأ (لحظة)،
  /// true = خط المدينة جاهز، false = نعرض الصفحة نص لحد ما الخط يوصل.
  bool? _madani;

  @override
  void initState() {
    super.initState();
    _prepare();
  }

  @override
  void didUpdateWidget(_MadaniBody old) {
    super.didUpdateWidget(old);
    if (old.page != widget.page) _prepare();
  }

  /// المصحف لازم يفتح على طول حتى من غير نت:
  /// الخط موجود ← خط المدينة. مش موجود ← نص فورًا، والخط يتنزل في الخلفية
  /// وأول ما يوصل الصفحة تتقلب لخط المدينة لوحدها.
  Future<void> _prepare() async {
    final page = widget.page;
    final fonts = QcfFonts.instance;
    if (fonts.isLoaded(page) && MushafLayout.instance != null) {
      _madani = true;
      return;
    }
    _madani = null;
    await MushafLayout.load();
    final local = await fonts.isDownloaded(page);
    if (!mounted || widget.page != page) return;
    if (!local) setState(() => _madani = false);
    final ok = await fonts.ensure(page);
    if (!mounted || widget.page != page) return;
    if (ok || _madani == null) setState(() => _madani = ok);
  }

  @override
  Widget build(BuildContext context) {
    return switch (_madani) {
      true => _buildPage(),
      // لحظة قراءة الخط من الموبايل (مش من النت)
      null => const SizedBox.expand(),
      false => Column(
        children: [
          Padding(
            padding: const EdgeInsets.only(bottom: 4),
            child: Text(
              'الصفحة معروضة نص لحد ما خطها يتنزّل',
              style: Theme.of(context).textTheme.labelSmall,
            ),
          ),
          Expanded(
            child: _TextBody(
              page: widget.page,
              onVerseTap: widget.onVerseTap,
              verseColor: widget.verseColor,
              ink: widget.ink,
            ),
          ),
        ],
      ),
    };
  }

  Widget _buildPage() {
    clearRecognizers();
    final slots = MushafLayout.instance!.slots(widget.page);
    final family = QcfFonts.family(widget.page);
    final style = TextStyle(
      fontFamily: family,
      fontSize: _base,
      color: widget.ink,
      height: 1.0,
    );

    // اختبار الحفظ: آخر كلمة في كل آية هي رقم الآية ۝ ← بتفضل ظاهرة.
    // (إلا لو الآية بتكمل في الصفحة الجاية، ساعتها آخر كلمة هنا مش رقم.)
    final layout = MushafLayout.instance!;
    final continuing = widget.page < QuranData.totalPages
        ? layout.firstVerse(widget.page + 1)
        : null;
    final lastWord = <int, MushafWord>{};
    for (final s in slots.whereType<WordsSlot>()) {
      for (final w in s.words) {
        lastWord[w.verse] = w;
      }
    }
    bool isAyahNumber(MushafWord w) =>
        identical(lastWord[w.verse], w) && w.verse != continuing;

    TextSpan lineSpan(WordsSlot slot) => TextSpan(
      children: [
        for (final w in slot.words)
          TextSpan(
            text: '${w.code} ',
            recognizer: recognizer(() => widget.onVerseTap(w.verse)),
            style: _HiddenScope.of(context, w.verse) && !isAyahNumber(w)
                ? _hiddenStyle(widget.ink)
                : _bg(widget.verseColor(w.verse)),
          ),
      ],
    );

    // نقيس أعرض سطر، ونخلّي نسبة الصفحة = نسبة المساحة المتاحة ← الصفحة تملأ الإطار بالظبط
    var maxW = 0.0, maxH = 0.0;
    for (final s in slots.whereType<WordsSlot>()) {
      final tp = TextPainter(
        text: TextSpan(
          text: s.words.map((w) => '${w.code} ').join(),
          style: style,
        ),
        textDirection: TextDirection.rtl,
      )..layout();
      if (tp.width > maxW) maxW = tp.width;
      if (tp.height > maxH) maxH = tp.height;
      tp.dispose();
    }

    return LayoutBuilder(
      builder: (context, box) {
        final lines = MushafLayout.isOpeningPage(widget.page)
            ? 15
            : slots.length;
        final lineH = (maxW * box.maxHeight / box.maxWidth / lines).clamp(
          maxH,
          double.infinity,
        );

        return FittedBox(
          child: SizedBox(
            width: maxW,
            height: lineH * slots.length,
            child: Column(
              children: [
                for (final slot in slots)
                  SizedBox(
                    height: lineH,
                    width: maxW,
                    child: switch (slot) {
                      WordsSlot() => Center(
                        child: Text.rich(
                          lineSpan(slot),
                          style: style,
                          textDirection: TextDirection.rtl,
                          maxLines: 1,
                          softWrap: false,
                        ),
                      ),
                      SuraHeaderSlot(:final sura) => SuraHeaderBox(
                        name: sura.name,
                        fontSize: lineH * 0.5,
                      ),
                      BasmalaSlot() => Center(
                        child: Text(
                          _basmala,
                          style: TextStyle(
                            fontFamily: 'AmiriQuran',
                            fontSize: lineH * 0.5,
                            color: widget.ink,
                            height: 1.2,
                          ),
                        ),
                      ),
                      EmptySlot() => const SizedBox.shrink(),
                    },
                  ),
              ],
            ),
          ),
        );
      },
    );
  }
}

TextStyle? _bg(Color? color) =>
    color == null ? null : TextStyle(backgroundColor: color);

// ───────────────────────────── وضع النص (خط أميري) ─────────────────────────────

/// نص عثماني بخط أميري. حجم الخط بيصغر لوحده لحد ما الصفحة كلها تكفي الإطار.
class _TextBody extends StatefulWidget {
  const _TextBody({
    required this.page,
    required this.onVerseTap,
    required this.verseColor,
    required this.ink,
  });

  final int page;
  final ValueChanged<int> onVerseTap;
  final VerseColor verseColor;
  final Color ink;

  @override
  State<_TextBody> createState() => _TextBodyState();
}

/// جزء من الصفحة: عنوان سورة + بسملة، أو مجموعة آيات.
typedef _Segment = ({Sura? header, bool basmala, List<int> verses});

class _TextBodyState extends State<_TextBody> with _Recognizers {
  List<_Segment> _segments() {
    final q = QuranData.instance;
    final (first, last) = q.pageRange(widget.page);
    final segments = <_Segment>[];
    var verses = <int>[];
    for (var i = first; i <= last; i++) {
      if (q.ayahOf(i) == 1) {
        if (verses.isNotEmpty) {
          segments.add((header: null, basmala: false, verses: verses));
        }
        verses = [];
        final sura = q.suraOf(i);
        segments.add((
          header: sura,
          basmala: sura.number != 1 && sura.number != 9,
          verses: const [],
        ));
      }
      verses.add(i);
    }
    if (verses.isNotEmpty) {
      segments.add((header: null, basmala: false, verses: verses));
    }
    return segments;
  }

  TextSpan _versesSpan(List<int> verses, {bool interactive = true}) {
    final q = QuranData.instance;
    return TextSpan(
      children: [
        for (final v in verses)
          // اختبار الحفظ: نص الآية مخفي، ورقمها ظاهر
          if (interactive && _HiddenScope.of(context, v))
            TextSpan(
              recognizer: recognizer(() => widget.onVerseTap(v)),
              children: [
                TextSpan(
                  text: '${q.text(v)} ',
                  style: _hiddenStyle(widget.ink),
                ),
                TextSpan(text: '﴿${arabicDigits(q.ayahOf(v))}﴾ '),
              ],
            )
          else
            TextSpan(
              text: '${q.text(v)} ﴿${arabicDigits(q.ayahOf(v))}﴾ ',
              recognizer: interactive
                  ? recognizer(() => widget.onVerseTap(v))
                  : null,
              style: _bg(widget.verseColor(v)),
            ),
      ],
    );
  }

  /// أكبر حجم خط (لحد إعداد الطالب) اللي الصفحة كلها تكفي فيه.
  double _fitFontSize(List<_Segment> segments, double width, double height) {
    double measure(double size) {
      var total = 0.0;
      for (final s in segments) {
        if (s.header != null) {
          total += size * 1.9 + (s.basmala ? size * 2.0 : 0);
        }
        if (s.verses.isEmpty) continue;
        final tp = TextPainter(
          text: TextSpan(
            style: _style(size),
            children: [_versesSpan(s.verses, interactive: false)],
          ),
          textDirection: TextDirection.rtl,
          textAlign: TextAlign.justify,
        )..layout(maxWidth: width);
        total += tp.height;
        tp.dispose();
      }
      return total;
    }

    var lo = 10.0, hi = MushafSettings.instance.textSize;
    if (measure(hi) <= height) return hi;
    for (var i = 0; i < 9; i++) {
      final mid = (lo + hi) / 2;
      if (measure(mid) <= height) {
        lo = mid;
      } else {
        hi = mid;
      }
    }
    return lo;
  }

  TextStyle _style(double size) => TextStyle(
    fontFamily: 'AmiriQuran',
    fontSize: size,
    height: 1.95,
    color: widget.ink,
  );

  @override
  Widget build(BuildContext context) {
    final segments = _segments();
    return LayoutBuilder(
      builder: (context, box) {
        final size = _fitFontSize(segments, box.maxWidth, box.maxHeight - 4);
        clearRecognizers();
        return Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            for (final s in segments) ...[
              if (s.header != null)
                SizedBox(
                  height: size * 1.9,
                  child: SuraHeaderBox(
                    name: s.header!.name,
                    fontSize: size * 0.85,
                  ),
                ),
              if (s.basmala)
                SizedBox(
                  height: size * 2.0,
                  child: Center(
                    child: Text(
                      _basmala,
                      style: _style(size).copyWith(height: 1.3),
                    ),
                  ),
                ),
              if (s.verses.isNotEmpty)
                Text.rich(
                  _versesSpan(s.verses),
                  textAlign: TextAlign.justify,
                  textDirection: TextDirection.rtl,
                  style: _style(size),
                ),
            ],
          ],
        );
      },
    );
  }
}
