import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/app/theme.dart';
import 'package:ibneljezery_app/core/network/api_client.dart';
import 'package:ibneljezery_app/features/academy/domain/academy_models.dart';
import 'package:ibneljezery_app/features/academy/presentation/recordings_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';

class _Recordings implements HttpClientAdapter {
  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) async => ResponseBody.fromString(
    jsonEncode({
      'data': [
        {
          'id': 1,
          'title': 'شرح أحكام النون الساكنة',
          'type': 'audio',
          'url': 'https://example.com/a.mp3',
          'teacher': {'id': 1, 'name': 'الشيخ أحمد'},
        },
        {
          'id': 2,
          'title': 'متن تحفة الأطفال',
          'type': 'pdf',
          'url': 'https://example.com/t.pdf',
          'description': 'المتن كامل للحفظ',
        },
        {
          'id': 3,
          'title': 'درس المدود',
          'type': 'video',
          'url': 'https://youtu.be/x',
          'course': {'id': 4, 'title': 'دورة التجويد'},
        },
      ],
    }),
    200,
    headers: {
      Headers.contentTypeHeader: [Headers.jsonContentType],
    },
  );

  @override
  void close({bool force = false}) {}
}

void main() {
  test('Recording.fromJson', () {
    final r = Recording.fromJson({
      'id': 9,
      'title': 'ملف',
      'type': 'pdf',
      'url': 'https://x/y.pdf',
      'teacher': {'id': 1, 'name': 'الشيخ'},
      'course': null,
    });
    expect(r.type, RecordingType.pdf);
    expect(r.teacherName, 'الشيخ');
    expect(r.courseTitle, isNull);
    expect(RecordingType.from('weird'), RecordingType.link);
  });

  testWidgets('التسجيلات: الكروت + الفلترة بالنوع', (tester) async {
    SharedPreferences.setMockInitialValues({});
    await tester.runAsync(ApiClient.load);
    ApiClient.instance.httpClientAdapter = _Recordings();

    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.light,
        home: const Directionality(
          textDirection: TextDirection.rtl,
          child: RecordingsScreen(),
        ),
      ),
    );
    await tester.runAsync(
      () => Future<void>.delayed(const Duration(milliseconds: 50)),
    );
    await tester.pumpAndSettle();

    expect(find.text('شرح أحكام النون الساكنة'), findsOneWidget);
    expect(find.text('متن تحفة الأطفال'), findsOneWidget);
    expect(find.text('درس المدود'), findsOneWidget);
    expect(find.text('صوت • الشيخ أحمد'), findsOneWidget);
    expect(find.byIcon(Icons.play_arrow_rounded), findsOneWidget);

    await tester.tap(find.text('ملفات PDF'));
    await tester.pumpAndSettle();
    expect(find.text('متن تحفة الأطفال'), findsOneWidget);
    expect(find.text('درس المدود'), findsNothing);
    expect(find.text('شرح أحكام النون الساكنة'), findsNothing);
  });
}
