import '../../mushaf/data/quran_data.dart';
import '../../wird/domain/progress_entry.dart';

/// خطة ختمة: "عايز أختم في [days] يوم" ← حصة كل يوم بالصفحات.
///
/// الحصة بتتحسب كل يوم من الباقي: لو اتأخرت يوم الحصة بتكبر شوية، ولو سبقت بتصغر.
class KhatmaPlan {
  const KhatmaPlan({
    required this.start,
    required this.days,
    this.page = 1,
    this.lastDone,
  });

  final DateTime start;
  final int days;

  /// أول صفحة لسه ما اتقرتش.
  final int page;

  /// آخر يوم الطالب خلّص فيه حصته.
  final DateTime? lastDone;

  static const total = QuranData.totalPages;

  bool get finished => page > total;
  int get pagesRead => page - 1;
  double get progress => pagesRead / total;

  /// رقم اليوم في الخطة (يوم 1 = يوم البداية).
  int dayNumber(DateTime now) =>
      dayOf(now).difference(dayOf(start)).inDays + 1;

  bool doneToday(DateTime now) =>
      lastDone != null && dayOf(lastDone!) == dayOf(now);

  /// الحصة اللي عليه دلوقتي: (من صفحة، لصفحة).
  /// لو خلّص حصة النهارده ← بيوريه حصة بكره.
  (int, int) portion(DateTime now) {
    var day = dayNumber(now);
    if (doneToday(now)) day++;
    final daysLeft = (days - day + 1).clamp(1, days);
    final remaining = total - page + 1;
    final size = (remaining / daysLeft).ceil();
    return (page, (page + size - 1).clamp(page, total));
  }

  /// الفرق عن الجدول بالصفحات: موجب = سابق، سالب = متأخر.
  int aheadBy(DateTime now) {
    final day = dayNumber(now).clamp(1, days);
    // المفروض يكون خلّص أيام قبل النهارده كاملة (والنهارده لو داس خلصت)
    final doneDays = doneToday(now) ? day : day - 1;
    final expected = (total * doneDays / days).floor();
    return pagesRead - expected;
  }

  KhatmaPlan copyWith({int? page, DateTime? lastDone}) => KhatmaPlan(
    start: start,
    days: days,
    page: page ?? this.page,
    lastDone: lastDone ?? this.lastDone,
  );

  Map<String, dynamic> toJson() => {
    's': start.toIso8601String(),
    'd': days,
    'p': page,
    if (lastDone != null) 'l': lastDone!.toIso8601String(),
  };

  factory KhatmaPlan.fromJson(Map<String, dynamic> json) => KhatmaPlan(
    start: DateTime.parse(json['s']),
    days: json['d'],
    page: json['p'],
    lastDone: json['l'] == null ? null : DateTime.parse(json['l']),
  );
}
