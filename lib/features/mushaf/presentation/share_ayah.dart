import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';

import '../../../app/theme.dart';
import '../data/quran_data.dart';
import '../data/tafsir_data.dart';

/// مشاركة آية: معاينة الصورة (لوجو + الآية + اختياري التفسير) ← مشاركة كصورة أو كنص.
Future<void> showShareAyah(BuildContext context, int verse) {
  return showDialog(
    context: context,
    builder: (_) => _ShareAyahDialog(verse: verse),
  );
}

class _ShareAyahDialog extends StatefulWidget {
  const _ShareAyahDialog({required this.verse});

  final int verse;

  @override
  State<_ShareAyahDialog> createState() => _ShareAyahDialogState();
}

class _ShareAyahDialogState extends State<_ShareAyahDialog> {
  final _boundary = GlobalKey();
  bool _withTafsir = false;
  String? _tafsir;
  bool _busy = false;

  Future<void> _toggleTafsir(bool on) async {
    _tafsir ??= (await TafsirData.load()).of(widget.verse);
    if (mounted) setState(() => _withTafsir = on);
  }

  String get _plainText {
    final q = QuranData.instance;
    final b = StringBuffer(
      '﴿${q.text(widget.verse)}﴾\n[${q.label(widget.verse)}]',
    );
    if (_withTafsir && _tafsir != null) b.write('\n\nالتفسير الميسر: $_tafsir');
    b.write('\n\n— تطبيق ابن الجزري');
    return b.toString();
  }

  Future<void> _shareImage() async {
    setState(() => _busy = true);
    try {
      final boundary =
          _boundary.currentContext!.findRenderObject()!
              as RenderRepaintBoundary;
      // دقة عالية عشان الصورة تبان حلوة على الواتساب والستوري
      final image = await boundary.toImage(pixelRatio: 3);
      final png = await image.toByteData(format: ui.ImageByteFormat.png);
      final dir = await getTemporaryDirectory();
      final file = File('${dir.path}/ayah_${widget.verse + 1}.png');
      await file.writeAsBytes(png!.buffer.asUint8List());
      await SharePlus.instance.share(
        ShareParams(
          files: [XFile(file.path, mimeType: 'image/png')],
          text: QuranData.instance.label(widget.verse),
        ),
      );
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      insetPadding: const EdgeInsets.all(16),
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(12),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            RepaintBoundary(
              key: _boundary,
              child: AyahCard(
                verse: widget.verse,
                tafsir: _withTafsir ? _tafsir : null,
              ),
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('أضف التفسير الميسر'),
              value: _withTafsir,
              onChanged: _toggleTafsir,
            ),
            Row(
              children: [
                Expanded(
                  child: FilledButton.icon(
                    onPressed: _busy ? null : _shareImage,
                    icon: const Icon(Icons.image_outlined),
                    label: const Text('صورة'),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () =>
                        SharePlus.instance.share(ShareParams(text: _plainText)),
                    icon: const Icon(Icons.text_fields),
                    label: const Text('نص'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

/// كارت الآية اللي بيتحول لصورة: ألوان ثابتة (مش بتتغير مع الوضع الليلي).
class AyahCard extends StatelessWidget {
  const AyahCard({super.key, required this.verse, this.tafsir});

  final int verse;
  final String? tafsir;

  @override
  Widget build(BuildContext context) {
    final q = QuranData.instance;
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Container(
        width: 360,
        padding: const EdgeInsets.all(10),
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF033D2C), Color(0xFF065F46)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: Container(
          padding: const EdgeInsets.fromLTRB(18, 16, 18, 12),
          decoration: BoxDecoration(
            border: Border.all(color: AppColors.gold, width: 1.5),
            borderRadius: BorderRadius.circular(6),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                '﴿${q.text(verse)}﴾',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontFamily: 'AmiriQuran',
                  fontSize: 22,
                  height: 2,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                '[ ${q.label(verse)} ]',
                style: const TextStyle(color: AppColors.gold, fontSize: 14),
              ),
              if (tafsir != null) ...[
                const Divider(color: Colors.white24, height: 24),
                Text(
                  tafsir!,
                  textAlign: TextAlign.justify,
                  style: const TextStyle(
                    color: Colors.white70,
                    fontSize: 13,
                    height: 1.7,
                  ),
                ),
              ],
              const SizedBox(height: 14),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Image.asset('assets/images/logo.png', width: 28, height: 28),
                  const SizedBox(width: 8),
                  const Text(
                    'تطبيق ابن الجزري',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
