import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// عميل الـ API بتاع Laravel (/api/v1) مع كاش أوفلاين بسيط.
///
/// كل رد ناجح بيتحفظ على الموبايل، ولو النت قطع بنرجّع آخر نسخة محفوظة.
/// عنوان السيرفر: `--dart-define=API_BASE_URL=https://...`
/// (الافتراضي للتجربة: 127.0.0.1:8000 عن طريق `adb reverse tcp:8000 tcp:8000` + `php artisan serve`).
class ApiClient {
  ApiClient._(this._prefs)
    : _dio = Dio(
        BaseOptions(
          baseUrl: const String.fromEnvironment(
            'API_BASE_URL',
            defaultValue: 'http://127.0.0.1:8000/api/v1',
          ),
          connectTimeout: const Duration(seconds: 8),
          receiveTimeout: const Duration(seconds: 15),
          headers: {'Accept': 'application/json', 'Accept-Language': 'ar'},
        ),
      );

  static late ApiClient instance;

  static Future<void> load() async {
    instance = ApiClient._(await SharedPreferences.getInstance());
  }

  final Dio _dio;
  final SharedPreferences _prefs;

  /// GET وبيرجّع `data` من رد Laravel. لو فشل ← آخر نسخة محفوظة، ولو مفيش ← [ApiException].
  Future<dynamic> get(String path, {Map<String, dynamic>? query}) async {
    final cacheKey = 'api_cache:$path?${jsonEncode(query ?? {})}';
    try {
      final response = await _dio.get(path, queryParameters: query);
      final data = response.data['data'];
      await _prefs.setString(cacheKey, jsonEncode(data));
      return data;
    } on DioException catch (e) {
      final cached = _prefs.getString(cacheKey);
      if (cached != null) return jsonDecode(cached);
      if (e.response?.statusCode == 404) {
        throw const ApiException('المحتوى غير موجود');
      }
      throw const ApiException(
        'تعذّر الاتصال بالسيرفر. تأكد من الإنترنت وحاول مرة أخرى.',
      );
    }
  }
}

class ApiException implements Exception {
  const ApiException(this.message);
  final String message;

  @override
  String toString() => message;
}
