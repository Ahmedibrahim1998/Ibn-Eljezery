import '../../../core/network/api_client.dart';
import '../domain/academy_models.dart';

/// الأكاديمية من الـ API: الدورات (بكاش) + دوراتي/مجموعاتي (من غير كاش — فيها روابط Zoom).
class AcademyRepository {
  Future<List<Course>> courses({bool? online}) async {
    final data = await ApiClient.instance.get(
      '/academy/courses',
      query: online == null ? null : {'type': online ? 'online' : 'offline'},
    );
    return [for (final c in data) Course.fromJson(c)];
  }

  Future<List<Teacher>> teachers() async {
    final data = await ApiClient.instance.get('/academy/teachers');
    return [for (final t in data) Teacher.fromJson(t)];
  }

  /// صفحة المعلم: بياناته + واتساب + مجموعاته المفتوحة.
  Future<Teacher> teacher(int id) async =>
      Teacher.fromJson(await ApiClient.instance.get('/academy/teachers/$id'));

  /// صفحة المجموعة (من غير كاش عشان "مشترك ولا لأ" تبقى صح).
  Future<Course> course(int id) async => Course.fromJson(
    await ApiClient.instance.getFresh('/academy/courses/$id'),
  );

  /// احجز ← بيرجّع رسالة السيرفر ("تم استلام طلبك…").
  Future<String> book(int courseId, {String? notes}) async {
    final response = await ApiClient.instance.post(
      '/academy/courses/$courseId/book',
      {if (notes != null && notes.trim().isNotEmpty) 'notes': notes.trim()},
    );
    return response['message'] ?? 'تم الحجز';
  }

  Future<List<MyBooking>> myBookings() async {
    final data = await ApiClient.instance.getFresh('/academy/my/bookings');
    return [for (final b in data) MyBooking.fromJson(b)];
  }

  Future<List<TeacherCourse>> teacherCourses() async {
    final data = await ApiClient.instance.getFresh('/academy/teacher/courses');
    return [for (final c in data) TeacherCourse.fromJson(c)];
  }

  Future<List<Student>> students(int courseId) async {
    final data = await ApiClient.instance.getFresh(
      '/academy/teacher/courses/$courseId/students',
    );
    return [for (final s in data) Student.fromJson(s)];
  }
}
