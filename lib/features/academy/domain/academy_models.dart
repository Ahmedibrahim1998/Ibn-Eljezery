/// نماذج الأكاديمية (من /api/v1/academy).
library;

/// معلم. [whatsappUrl] عام عشان الطالب يسأل قبل ما يحجز (رقم الأكاديمية لو المعلم ملوش رقم).
class Teacher {
  const Teacher({
    required this.id,
    required this.name,
    this.certification,
    this.description,
    this.badge,
    this.photo,
    this.whatsappUrl,
    this.openCoursesCount,
    this.courses = const [],
  });

  final int id;
  final String name;
  final String? certification;
  final String? description;
  final String? badge;
  final String? photo;
  final String? whatsappUrl;
  final int? openCoursesCount;
  final List<Course> courses;

  factory Teacher.fromJson(Map<String, dynamic> json) => Teacher(
    id: json['id'],
    name: json['name'] ?? '',
    certification: json['certification'],
    description: json['description'],
    badge: json['badge'],
    photo: json['photo'],
    whatsappUrl: json['whatsapp_url'],
    openCoursesCount: json['open_courses_count'],
    courses: [for (final c in json['courses'] ?? const []) Course.fromJson(c)],
  );
}

/// حصة في جدول المجموعة. [joinUrl] للطالب المؤكَّد، [startUrl] للمعلم (رابط بدء Zoom).
class CourseSession {
  const CourseSession({
    required this.id,
    required this.startsAt,
    required this.durationMinutes,
    this.location,
    required this.isOnline,
    this.joinUrl,
    this.startUrl,
  });

  final int id;
  final DateTime startsAt;
  final int durationMinutes;
  final String? location;
  final bool isOnline;
  final String? joinUrl;
  final String? startUrl;

  DateTime get endsAt => startsAt.add(Duration(minutes: durationMinutes));

  /// الحصة شغالة دلوقتي (أو فاضل عليها ربع ساعة) ← زرار الدخول يبقى بارز.
  bool isLive(DateTime now) =>
      now.isAfter(startsAt.subtract(const Duration(minutes: 15))) &&
      now.isBefore(endsAt);

  factory CourseSession.fromJson(Map<String, dynamic> json) => CourseSession(
    id: json['id'],
    startsAt: DateTime.parse(json['starts_at']).toLocal(),
    durationMinutes: json['duration_minutes'] ?? 60,
    location: json['location'],
    isOnline: json['is_online'] ?? false,
    joinUrl: json['join_url'],
    startUrl: json['start_url'],
  );
}

/// مجموعة (دورة مع معلم معيّن).
class Course {
  const Course({
    required this.id,
    required this.title,
    this.categoryTitle,
    required this.isOnline,
    this.typeLabel,
    this.description,
    this.badge,
    this.items = const [],
    this.durationLabel,
    this.daysLeftLabel,
    this.teacher,
    this.sessions = const [],
    this.isEnrolled = false,
  });

  final int id;
  final String title;
  final String? categoryTitle;
  final bool isOnline;
  final String? typeLabel;
  final String? description;
  final String? badge;
  final List<String> items;
  final String? durationLabel;
  final String? daysLeftLabel;
  final Teacher? teacher;
  final List<CourseSession> sessions;
  final bool isEnrolled;

  factory Course.fromJson(Map<String, dynamic> json) => Course(
    id: json['id'],
    title: json['title'] ?? '',
    categoryTitle: json['category']?['title'],
    isOnline: json['type'] == 'online',
    typeLabel: json['type_label'],
    description: json['description'],
    badge: json['badge'],
    items: [for (final i in json['items'] ?? const []) '$i'],
    durationLabel: json['duration_label'],
    daysLeftLabel: json['days_left_label'],
    teacher: json['teacher'] == null ? null : Teacher.fromJson(json['teacher']),
    sessions: [
      for (final s in json['sessions'] ?? const []) CourseSession.fromJson(s),
    ],
    isEnrolled: json['is_enrolled'] ?? false,
  );
}

enum BookingStatus { pending, confirmed, cancelled }

/// اشتراك الطالب في مجموعة.
class MyBooking {
  const MyBooking({
    required this.id,
    required this.status,
    this.statusLabel,
    required this.course,
    required this.sessions,
    this.teacherWhatsappUrl,
    this.attendedClasses = 0,
    this.paymentDue = false,
  });

  final int id;
  final BookingStatus status;
  final String? statusLabel;
  final Course course;
  final List<CourseSession> sessions;
  final String? teacherWhatsappUrl;
  final int attendedClasses;
  final bool paymentDue;

  CourseSession? get nextSession => sessions.isEmpty ? null : sessions.first;

  factory MyBooking.fromJson(Map<String, dynamic> json) => MyBooking(
    id: json['id'],
    status:
        BookingStatus.values.asNameMap()[json['status']] ??
        BookingStatus.pending,
    statusLabel: json['status_label'],
    course: Course.fromJson(json['course']),
    sessions: [
      for (final s in json['sessions'] ?? const []) CourseSession.fromJson(s),
    ],
    teacherWhatsappUrl: json['teacher_whatsapp_url'],
    attendedClasses: json['attended_classes'] ?? 0,
    paymentDue: json['payment_due'] ?? false,
  );
}

/// مجموعة من مجموعات المعلم.
class TeacherCourse {
  const TeacherCourse({
    required this.course,
    required this.bookingUrl,
    required this.confirmedCount,
    required this.pendingCount,
    required this.sessions,
  });

  final Course course;
  final String bookingUrl;
  final int confirmedCount;
  final int pendingCount;
  final List<CourseSession> sessions;

  factory TeacherCourse.fromJson(Map<String, dynamic> json) => TeacherCourse(
    course: Course.fromJson(json),
    bookingUrl: json['booking_url'],
    confirmedCount: json['confirmed_count'] ?? 0,
    pendingCount: json['pending_count'] ?? 0,
    sessions: [
      for (final s in json['sessions'] ?? const []) CourseSession.fromJson(s),
    ],
  );
}

/// طالب في مجموعة المعلم.
class Student {
  const Student({
    required this.id,
    required this.name,
    this.phone,
    required this.status,
    this.statusLabel,
    this.attendedClasses = 0,
    this.whatsappUrl,
  });

  final int id;
  final String name;
  final String? phone;
  final BookingStatus status;
  final String? statusLabel;
  final int attendedClasses;
  final String? whatsappUrl;

  factory Student.fromJson(Map<String, dynamic> json) => Student(
    id: json['id'],
    name: json['name'] ?? '',
    phone: json['phone'],
    status:
        BookingStatus.values.asNameMap()[json['status']] ??
        BookingStatus.pending,
    statusLabel: json['status_label'],
    attendedClasses: json['attended_classes'] ?? 0,
    whatsappUrl: json['whatsapp_url'],
  );
}
