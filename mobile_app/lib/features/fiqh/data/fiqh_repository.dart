import '../../../core/network/api_client.dart';
import '../domain/fiqh_models.dart';

/// بيجيب محتوى الفقه من الـ API (والكاش لو مفيش نت).
class FiqhRepository {
  static const _section = 'fiqh';

  Future<List<Subject>> subjects(Madhhab madhhab) async {
    final data = await ApiClient.instance.get(
      '/learning/sections/$_section/subjects',
      query: {'madhhab': madhhab.name},
    );
    return [for (final s in data) Subject.fromJson(s)];
  }

  Future<List<Lesson>> lessons(int subjectId) async {
    final data = await ApiClient.instance.get(
      '/learning/subjects/$subjectId/lessons',
    );
    return [for (final l in data) Lesson.fromJson(l)];
  }

  Future<Lesson> lesson(int id) async {
    return Lesson.fromJson(
      await ApiClient.instance.get('/learning/lessons/$id'),
    );
  }
}
