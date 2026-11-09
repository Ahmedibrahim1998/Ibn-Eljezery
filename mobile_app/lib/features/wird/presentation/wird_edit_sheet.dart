import 'package:flutter/material.dart';

import '../../mushaf/data/quran_data.dart';
import '../data/wird_store.dart';
import '../domain/wird_mark.dart';

/// تحديد مقطع علامة: بالأجزاء (للمراجعة) أو من آية لآية (للتسميع).
Future<void> showWirdEditSheet(BuildContext context, WirdType type) {
  return showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    builder: (_) => _WirdEditSheet(type: type),
  );
}

class _WirdEditSheet extends StatefulWidget {
  const _WirdEditSheet({required this.type});

  final WirdType type;

  @override
  State<_WirdEditSheet> createState() => _WirdEditSheetState();
}

class _WirdEditSheetState extends State<_WirdEditSheet> {
  final q = QuranData.instance;

  late bool _byJuz;
  late int _fromJuz, _toJuz;
  late int _fromSura, _fromAyah, _toSura, _toAyah;

  @override
  void initState() {
    super.initState();
    final mark = WirdStore.instance.mark(widget.type);
    final from = mark?.from ?? 0;
    final to = mark?.to ?? 0;
    // المراجعة غالباً بالأجزاء، والتسميع والترتيل بالآيات
    _byJuz =
        mark?.byJuz ??
        (widget.type == WirdType.nearReview ||
            widget.type == WirdType.farReview);
    _fromJuz = q.juzOf(from);
    _toJuz = q.juzOf(to);
    _fromSura = q.suraOf(from).number;
    _fromAyah = q.ayahOf(from);
    _toSura = q.suraOf(to).number;
    _toAyah = q.ayahOf(to);
  }

  Future<void> _save() async {
    int from, to;
    if (_byJuz) {
      from = q.juzStart(_fromJuz);
      to = q.juzEnd(_toJuz);
    } else {
      from = q.indexOf(_fromSura, _fromAyah);
      to = q.indexOf(_toSura, _toAyah);
    }
    if (from > to) (from, to) = (to, from);
    await WirdStore.instance.save(
      WirdMark(type: widget.type, from: from, to: to, byJuz: _byJuz),
    );
    if (mounted) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final hasMark = WirdStore.instance.mark(widget.type) != null;

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              children: [
                Icon(Icons.bookmark, color: widget.type.color),
                const SizedBox(width: 8),
                Text(
                  widget.type.label,
                  style: Theme.of(context).textTheme.titleLarge,
                ),
              ],
            ),
            const SizedBox(height: 16),
            SegmentedButton<bool>(
              segments: const [
                ButtonSegment(value: true, label: Text('بالأجزاء')),
                ButtonSegment(value: false, label: Text('من آية لآية')),
              ],
              selected: {_byJuz},
              onSelectionChanged: (s) => setState(() => _byJuz = s.first),
            ),
            const SizedBox(height: 16),
            if (_byJuz) ...[
              _JuzPicker(
                label: 'من الجزء',
                value: _fromJuz,
                onChanged: (v) => setState(() => _fromJuz = v),
              ),
              _JuzPicker(
                label: 'إلى الجزء',
                value: _toJuz,
                onChanged: (v) => setState(() => _toJuz = v),
              ),
            ] else ...[
              _AyahPicker(
                label: 'من',
                sura: _fromSura,
                ayah: _fromAyah,
                onChanged: (s, a) => setState(() {
                  _fromSura = s;
                  _fromAyah = a;
                }),
              ),
              _AyahPicker(
                label: 'إلى',
                sura: _toSura,
                ayah: _toAyah,
                onChanged: (s, a) => setState(() {
                  _toSura = s;
                  _toAyah = a;
                }),
              ),
              const Text(
                'أو من جوه المصحف: اختار النوع من الشريط اللي فوق ← اضغط أول آية وآخر آية.',
              ),
            ],
            const SizedBox(height: 16),
            Row(
              children: [
                if (hasMark)
                  TextButton(
                    onPressed: () async {
                      await WirdStore.instance.remove(widget.type);
                      if (context.mounted) Navigator.pop(context);
                    },
                    child: const Text('مسح العلامة'),
                  ),
                const Spacer(),
                FilledButton(onPressed: _save, child: const Text('حفظ')),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _JuzPicker extends StatelessWidget {
  const _JuzPicker({
    required this.label,
    required this.value,
    required this.onChanged,
  });

  final String label;
  final int value;
  final ValueChanged<int> onChanged;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        SizedBox(width: 80, child: Text(label)),
        DropdownButton<int>(
          value: value,
          items: [
            for (var j = 1; j <= QuranData.totalJuz; j++)
              DropdownMenuItem(
                value: j,
                child: Text('الجزء ${arabicDigits(j)}'),
              ),
          ],
          onChanged: (v) => onChanged(v!),
        ),
      ],
    );
  }
}

class _AyahPicker extends StatelessWidget {
  const _AyahPicker({
    required this.label,
    required this.sura,
    required this.ayah,
    required this.onChanged,
  });

  final String label;
  final int sura;
  final int ayah;
  final void Function(int sura, int ayah) onChanged;

  @override
  Widget build(BuildContext context) {
    final q = QuranData.instance;
    return Row(
      children: [
        SizedBox(width: 40, child: Text(label)),
        Expanded(
          child: DropdownButton<int>(
            isExpanded: true,
            value: sura,
            items: [
              for (final s in q.suras)
                DropdownMenuItem(
                  value: s.number,
                  child: Text('${arabicDigits(s.number)}. ${s.name}'),
                ),
            ],
            // تغيير السورة يرجّع الآية لأول السورة
            onChanged: (v) => onChanged(v!, 1),
          ),
        ),
        const SizedBox(width: 8),
        const Text('آية'),
        const SizedBox(width: 8),
        DropdownButton<int>(
          value: ayah,
          items: [
            for (var a = 1; a <= q.suras[sura - 1].ayahCount; a++)
              DropdownMenuItem(value: a, child: Text(arabicDigits(a))),
          ],
          onChanged: (v) => onChanged(sura, v!),
        ),
      ],
    );
  }
}
