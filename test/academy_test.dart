import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/core/utils/formatting.dart';
import 'package:ibneljezery_app/features/academy/domain/academy_models.dart';
import 'package:ibneljezery_app/features/account/domain/app_user.dart';

void main() {
  // نفس شكل رد /api/v1/academy/my/bookings (من MyBookingResource في Laravel)
  final confirmedJson = {
    'id': 7,
    'status': 'confirmed',
    'status_label': 'مؤكد',
    'attended_classes': 3,
    'payment_due': false,
    'course': {
      'id': 1,
      'title': 'مجموعة المساء',
      'category': {'id': 1, 'title': 'تحفيظ أونلاين'},
      'type': 'online',
      'items': ['تجويد', 'تسميع'],
      'teacher': {'id': 2, 'name': 'الشيخ محمد', 'photo': null},
    },
    'sessions': [
      {
        'id': 10,
        'starts_at': '2026-11-09T18:00:00+02:00',
        'duration_minutes': 60,
        'location': null,
        'is_online': true,
        'join_url': 'https://zoom.us/j/123',
      },
    ],
    'teacher_whatsapp_url': 'https://wa.me/201000000001',
  };

  test('اشتراك مؤكَّد: رابط Zoom + واتساب المعلم', () {
    final b = MyBooking.fromJson(confirmedJson);
    expect(b.status, BookingStatus.confirmed);
    expect(b.course.isOnline, isTrue);
    expect(b.course.teacher!.name, 'الشيخ محمد');
    expect(b.course.categoryTitle, 'تحفيظ أونلاين');
    expect(b.nextSession!.joinUrl, 'https://zoom.us/j/123');
    expect(b.teacherWhatsappUrl, 'https://wa.me/201000000001');
  });

  test('اشتراك مستني التأكيد: من غير روابط', () {
    final b = MyBooking.fromJson({
      ...confirmedJson,
      'status': 'pending',
      'sessions': [
        {'id': 10, 'starts_at': '2026-11-09T18:00:00+02:00', 'is_online': true},
      ],
      'teacher_whatsapp_url': null,
    });
    expect(b.status, BookingStatus.pending);
    expect(b.nextSession!.joinUrl, isNull);
    expect(b.teacherWhatsappUrl, isNull);
  });

  test('الحصة "شغالة" من قبلها بربع ساعة لحد ما تخلص', () {
    final s = CourseSession(
      id: 1,
      startsAt: DateTime(2026, 11, 9, 18),
      durationMinutes: 60,
      isOnline: true,
    );
    expect(s.isLive(DateTime(2026, 11, 9, 17, 40)), isFalse);
    expect(s.isLive(DateTime(2026, 11, 9, 17, 50)), isTrue);
    expect(s.isLive(DateTime(2026, 11, 9, 18, 59)), isTrue);
    expect(s.isLive(DateTime(2026, 11, 9, 19, 1)), isFalse);
  });

  test('مواعيد الحصص بالعربي', () {
    final now = DateTime(2026, 11, 8, 10);
    expect(
      formatSessionTime(DateTime(2026, 11, 8, 20, 5), now),
      'النهارده ٨:٠٥ م',
    );
    expect(formatSessionTime(DateTime(2026, 11, 9, 9, 30), now), 'بكره ٩:٣٠ ص');
    expect(
      formatSessionTime(DateTime(2026, 11, 12, 12), now),
      'الخميس ١٢/١١ • ١٢:٠٠ م',
    );
  });

  test('الحساب: طالب ومعلم', () {
    final student = AppUser.fromJson({
      'id': 1,
      'name': 'أحمد',
      'phone': '201012345678',
      'role': 'student',
    });
    expect(student.isTeacher, isFalse);
    final teacher = AppUser.fromJson({
      'id': 2,
      'name': 'الشيخ',
      'email': 't@x.com',
      'role': 'teacher',
      'teacher': {'id': 1, 'name': 'الشيخ محمد'},
    });
    expect(teacher.isTeacher, isTrue);
    // بيتحفظ ويرجع زي ما هو
    expect(AppUser.fromJson(teacher.toJson()).teacherName, 'الشيخ محمد');
  });
}
