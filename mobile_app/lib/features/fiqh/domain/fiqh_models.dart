/// المذاهب المتاحة في الإصدار الأول (نفس قيم MadhhabEnum في Laravel).
enum Madhhab {
  maliki('المالكي'),
  shafii('الشافعي');

  const Madhhab(this.label);
  final String label;
}

/// مادة (مثال: فقه الطهارة) — من /learning/sections/fiqh/subjects
class Subject {
  const Subject({
    required this.id,
    required this.title,
    this.description,
    this.author,
    this.lessonsCount = 0,
  });

  final int id;
  final String title;
  final String? description;
  final String? author;
  final int lessonsCount;

  factory Subject.fromJson(Map<String, dynamic> json) => Subject(
    id: json['id'],
    title: json['title'] ?? '',
    description: json['description'],
    author: json['author'],
    lessonsCount: json['lessons_count'] ?? 0,
  );
}

/// درس — في القوائم من غير المحتوى، وفي صفحة الدرس بالمحتوى كامل.
class Lesson {
  const Lesson({
    required this.id,
    required this.title,
    this.content,
    this.durationMinutes,
    this.audioUrl,
    this.videoUrl,
  });

  final int id;
  final String title;
  final String? content;
  final int? durationMinutes;
  final String? audioUrl;
  final String? videoUrl;

  factory Lesson.fromJson(Map<String, dynamic> json) => Lesson(
    id: json['id'],
    title: json['title'] ?? '',
    content: json['content'],
    durationMinutes: json['duration_minutes'],
    audioUrl: json['audio_url'],
    videoUrl: json['video_url'],
  );
}
