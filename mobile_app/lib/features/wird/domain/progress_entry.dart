import '../../mushaf/data/quran_data.dart';
import 'wird_mark.dart';

/// سطر في سجل الإنجاز: الطالب خلّص مقطع ورد في يوم معيّن.
class ProgressEntry {
  const ProgressEntry({
    required this.date,
    required this.type,
    required this.from,
    required this.to,
  });

  final DateTime date;
  final WirdType type;
  final int from;
  final int to;

  /// عدد صفحات المصحف اللي المقطع بيغطيها.
  int get pages =>
      QuranData.instance.pageOf(to) - QuranData.instance.pageOf(from) + 1;

  Map<String, dynamic> toJson() => {
    'd': date.toIso8601String(),
    't': type.name,
    'f': from,
    'to': to,
  };

  factory ProgressEntry.fromJson(Map<String, dynamic> json) => ProgressEntry(
    date: DateTime.parse(json['d']),
    type: WirdType.values.byName(json['t']),
    from: json['f'],
    to: json['to'],
  );
}

/// اليوم من غير وقت (عشان نقارن الأيام).
DateTime dayOf(DateTime d) => DateTime(d.year, d.month, d.day);

/// إحصائيات السجل: أيام الالتزام ورا بعض، وأحسن رقم، وإنجاز آخر 7 أيام.
class ProgressStats {
  ProgressStats(List<ProgressEntry> entries, DateTime now) {
    final today = dayOf(now);
    final days = {for (final e in entries) dayOf(e.date)};

    // الالتزام الحالي: بيبدأ من النهارده، أو من امبارح لو لسه ما خلّصش النهارده
    var cursor = days.contains(today)
        ? today
        : today.subtract(const Duration(days: 1));
    while (days.contains(cursor)) {
      currentStreak++;
      cursor = cursor.subtract(const Duration(days: 1));
    }

    final sorted = days.toList()..sort();
    var run = 0;
    DateTime? prev;
    for (final d in sorted) {
      run = (prev != null && d.difference(prev).inDays == 1) ? run + 1 : 1;
      if (run > bestStreak) bestStreak = run;
      prev = d;
    }

    final weekStart = today.subtract(const Duration(days: 6));
    for (final e in entries) {
      final d = dayOf(e.date);
      if (d.isBefore(weekStart)) continue;
      weekPages += e.pages;
      weekCounts[e.type] = (weekCounts[e.type] ?? 0) + 1;
    }
    lastSevenDays = [
      for (var i = 6; i >= 0; i--)
        days.contains(today.subtract(Duration(days: i))),
    ];
    doneToday = days.contains(today);
  }

  int currentStreak = 0;
  int bestStreak = 0;
  int weekPages = 0;
  final Map<WirdType, int> weekCounts = {};

  /// آخر 7 أيام (من الأقدم للنهارده): خلّص فيها حاجة ولا لأ.
  late final List<bool> lastSevenDays;
  late final bool doneToday;
}
