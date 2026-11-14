import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/core/network/api_client.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// رد وهمي ثابت من «السيرفر».
class _FakeAdapter implements HttpClientAdapter {
  _FakeAdapter(this.status, this.body);

  final int status;
  final Object body;

  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) async => ResponseBody.fromString(
    jsonEncode(body),
    status,
    headers: {
      Headers.contentTypeHeader: [Headers.jsonContentType],
    },
  );

  @override
  void close({bool force = false}) {}
}

void main() {
  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    await ApiClient.load();
  });

  Future<String> errorFor(int status, Object body) async {
    ApiClient.instance.httpClientAdapter = _FakeAdapter(status, body);
    try {
      await ApiClient.instance.post('/auth/register', {});
      fail('كان المفروض يرمي ApiException');
    } on ApiException catch (e) {
      return e.message;
    }
  }

  test('route مش موجود على السيرفر ← رسالة عربي مش رسالة Laravel', () async {
    final msg = await errorFor(404, {
      'message': 'The route api/v1/auth/register could not be found.',
    });
    expect(msg, 'الخدمة دي مش متاحة دلوقتي، جرّب تاني بعد شوية');
  });

  test('أخطاء الـ validation العربي بتتعرض زي ما هي', () async {
    final msg = await errorFor(422, {
      'message': 'اكتب اسمك.',
      'errors': {
        'name': ['اكتب اسمك.'],
      },
    });
    expect(msg, 'اكتب اسمك.');
  });

  test('رسالة إنجليزي من السيرفر ← بديل عربي', () async {
    final msg = await errorFor(422, {
      'message': 'The given data was invalid.',
      'errors': {
        'phone': ['The phone field is required.'],
      },
    });
    expect(msg, 'راجع البيانات وجرّب تاني');
  });

  test('محاولات كتير (429) وخطأ السيرفر (500)', () async {
    expect(
      await errorFor(429, {'message': 'Too Many Attempts.'}),
      'محاولات كتير ورا بعض، استنى دقيقة وجرّب تاني',
    );
    expect(
      await errorFor(500, {'message': 'Server Error'}),
      'حصلت مشكلة في السيرفر، جرّب تاني بعد شوية',
    );
  });
}
