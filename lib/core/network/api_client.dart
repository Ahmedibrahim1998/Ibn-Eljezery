import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// عميل الـ API بتاع Laravel (/api/v1) مع كاش أوفلاين بسيط.
///
/// كل رد ناجح للمحتوى العام بيتحفظ على الموبايل، ولو النت قطع بنرجّع آخر نسخة محفوظة.
/// بيانات الحساب (دوراتي، روابط Zoom) بتتجاب من السيرفر على طول من غير كاش ([getFresh]).
/// عنوان السيرفر: `--dart-define=API_BASE_URL=https://...`
/// (الافتراضي للتجربة: 127.0.0.1:8000 عن طريق `adb reverse tcp:8000 tcp:8000` + `php artisan serve`).
class ApiClient {
  ApiClient._(this._prefs)
    : _dio = Dio(
        BaseOptions(
          baseUrl: baseUrl,
          connectTimeout: const Duration(seconds: 8),
          receiveTimeout: const Duration(seconds: 15),
          headers: {'Accept': 'application/json', 'Accept-Language': 'ar'},
        ),
      ) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          final token = tokenProvider?.call();
          if (token != null) options.headers['Authorization'] = 'Bearer $token';
          handler.next(options);
        },
        onError: (e, handler) {
          // التوكن اتلغى (اتعمل خروج من جهاز تاني أو الحساب اتمسح)
          if (e.response?.statusCode == 401) onUnauthorized?.call();
          handler.next(e);
        },
      ),
    );
  }

  static late ApiClient instance;

  static const baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8000/api/v1',
  );

  static Future<void> load() async {
    instance = ApiClient._(await SharedPreferences.getInstance());
  }

  final Dio _dio;
  final SharedPreferences _prefs;

  /// التوكن الحالي (AuthStore بيحدده) ← بيتبعت في كل طلب.
  String? Function()? tokenProvider;
  void Function()? onUnauthorized;

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
      throw _error(e);
    }
  }

  /// GET من غير كاش (بيانات خاصة بالحساب).
  Future<dynamic> getFresh(String path) async {
    try {
      return (await _dio.get(path)).data['data'];
    } on DioException catch (e) {
      throw _error(e);
    }
  }

  /// POST وبيرجّع الرد كله (مثلاً {token, user} أو {message, data}).
  Future<Map<String, dynamic>> post(
    String path, [
    Map<String, dynamic>? body,
  ]) async {
    try {
      final response = await _dio.post(path, data: body);
      return (response.data as Map<String, dynamic>?) ?? const {};
    } on DioException catch (e) {
      throw _error(e);
    }
  }

  Future<void> delete(String path) async {
    try {
      await _dio.delete(path);
    } on DioException catch (e) {
      throw _error(e);
    }
  }

  /// رسالة مفهومة للطالب: أول خطأ validation، أو رسالة السيرفر، أو مشكلة النت.
  ApiException _error(DioException e) {
    final response = e.response;
    if (response == null) {
      return const ApiException(
        'تعذّر الاتصال بالسيرفر. تأكد من الإنترنت وحاول مرة أخرى.',
      );
    }
    final body = response.data;
    if (body is Map) {
      final errors = body['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final first = errors.values.first;
        return ApiException(
          first is List ? '${first.first}' : '$first',
          response.statusCode,
        );
      }
      final message = body['message'];
      if (message is String &&
          message.isNotEmpty &&
          response.statusCode != 500) {
        return ApiException(message, response.statusCode);
      }
    }
    return switch (response.statusCode) {
      401 => const ApiException('لازم تسجّل دخول الأول', 401),
      403 => const ApiException('غير مسموح', 403),
      404 => const ApiException('المحتوى غير موجود', 404),
      _ => ApiException(
        'حصلت مشكلة في السيرفر، حاول تاني بعد شوية',
        response.statusCode,
      ),
    };
  }
}

class ApiException implements Exception {
  const ApiException(this.message, [this.statusCode]);
  final String message;
  final int? statusCode;

  @override
  String toString() => message;
}
