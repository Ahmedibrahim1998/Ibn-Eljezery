// أيقونة التطبيق من لوجو الأكاديمية (ibneljezery/public/assets/LOGO/logo.png → assets/images/logo_source.png).
// بيلاقي الدايرة السودا في اللوجو لوحده، ويقصها (يشيل الأطراف البيضا)، ويحفظ:
//   assets/images/logo.png          ← اللوجو دايرة بخلفية شفافة (للرئيسية)
//   assets/icon/icon.png            ← الأيقونة العادية (اللوجو على أسود)
//   assets/icon/icon_foreground.png ← الطبقة الأمامية للأيقونة التكيفية (Android 8+)
// التشغيل:  flutter test tool/icon/generate_icon_test.dart   ثم:  dart run flutter_launcher_icons
import 'dart:io';
import 'dart:math' as math;
import 'dart:typed_data';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter_test/flutter_test.dart';

/// دايرة اللوجو جوه الصورة الأصلية.
typedef _Circle = ({Offset center, double radius});

/// بنمشي من الأطراف لجوه على خط النص، لحد أول بكسل غامق (الدايرة السودا).
_Circle _findCircle(Uint8List rgba, int w, int h) {
  bool dark(int x, int y) {
    final i = (y * w + x) * 4;
    final luma = 0.299 * rgba[i] + 0.587 * rgba[i + 1] + 0.114 * rgba[i + 2];
    return rgba[i + 3] > 200 && luma < 70;
  }

  int scan(int from, int to, int step, bool Function(int) isDark) {
    for (var p = from; p != to; p += step) {
      if (isDark(p)) return p;
    }
    return from;
  }

  final cy = h ~/ 2, cx = w ~/ 2;
  final left = scan(0, w, 1, (x) => dark(x, cy));
  final right = scan(w - 1, -1, -1, (x) => dark(x, cy));
  final top = scan(0, h, 1, (y) => dark(cx, y));
  final bottom = scan(h - 1, -1, -1, (y) => dark(cx, y));
  return (
    center: Offset((left + right) / 2, (top + bottom) / 2),
    // شوية لجوه عشان الحافة الرمادي متبانش
    radius: math.min(right - left, bottom - top) / 2 - 6,
  );
}

class _LogoPainter extends CustomPainter {
  _LogoPainter(this.image, this.circle, {required this.scale, this.background});

  final ui.Image image;
  final _Circle circle;

  /// قطر اللوجو كنسبة من عرض الأيقونة.
  final double scale;
  final Color? background;

  @override
  void paint(Canvas canvas, Size size) {
    if (background != null) canvas.drawRect(Offset.zero & size, Paint()..color = background!);
    final r = size.width * scale / 2;
    final c = size.center(Offset.zero);
    canvas.clipPath(Path()..addOval(Rect.fromCircle(center: c, radius: r)));
    canvas.drawImageRect(
      image,
      Rect.fromCircle(center: circle.center, radius: circle.radius),
      Rect.fromCircle(center: c, radius: r),
      Paint()..filterQuality = FilterQuality.high,
    );
  }

  @override
  bool shouldRepaint(_LogoPainter old) => false;
}

Future<void> _render(WidgetTester tester, CustomPainter painter, String path) async {
  final key = GlobalKey();
  await tester.pumpWidget(
    Center(
      child: RepaintBoundary(
        key: key,
        child: CustomPaint(size: const Size(1024, 1024), painter: painter),
      ),
    ),
  );
  await tester.runAsync(() async {
    final boundary = key.currentContext!.findRenderObject()! as RenderRepaintBoundary;
    final image = await boundary.toImage();
    final bytes = await image.toByteData(format: ui.ImageByteFormat.png);
    File(path)
      ..createSync(recursive: true)
      ..writeAsBytesSync(bytes!.buffer.asUint8List());
  });
}

void main() {
  testWidgets('generate app icon from the academy logo', (tester) async {
    tester.view.physicalSize = const Size(1024, 1024);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.reset);

    late ui.Image logo;
    late _Circle circle;
    await tester.runAsync(() async {
      final codec = await ui.instantiateImageCodec(File('assets/images/logo_source.png').readAsBytesSync());
      logo = (await codec.getNextFrame()).image;
      final rgba = (await logo.toByteData())!.buffer.asUint8List();
      circle = _findCircle(rgba, logo.width, logo.height);
    });
    // ignore: avoid_print
    print('logo circle: center=${circle.center} radius=${circle.radius}');

    await _render(tester, _LogoPainter(logo, circle, scale: 1.0), 'assets/images/logo.png');
    await _render(tester, _LogoPainter(logo, circle, scale: 1.0, background: Colors.black), 'assets/icon/icon.png');
    // الأيقونة التكيفية بتقص الأطراف ← اللوجو جوه الـ 66% اللي في النص
    await _render(tester, _LogoPainter(logo, circle, scale: 0.68), 'assets/icon/icon_foreground.png');
  });
}
