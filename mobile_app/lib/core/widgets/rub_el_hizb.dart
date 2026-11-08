import 'dart:math' as math;

import 'package:flutter/material.dart';

/// نجمة ربع الحزب ۞: مربعين متداخلين (نجمة ثمانية) بحدود، ودايرة في النص.
/// مستخدمة في الرئيسية وأيقونة التطبيق.
class RubElHizb extends StatelessWidget {
  const RubElHizb({
    super.key,
    required this.size,
    required this.color,
    this.centerColor,
  });

  final double size;
  final Color color;

  /// لون الدايرة اللي في النص (الافتراضي: شفاف ← بيبان لون الخلفية).
  final Color? centerColor;

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      size: Size.square(size),
      painter: _RubElHizbPainter(color, centerColor),
    );
  }
}

class _RubElHizbPainter extends CustomPainter {
  _RubElHizbPainter(this.color, this.centerColor);

  final Color color;
  final Color? centerColor;

  @override
  void paint(Canvas canvas, Size size) {
    final c = size.center(Offset.zero);
    final r = size.width / 2;
    final stroke = r * 0.07;

    // مربع بزاوية دوران معيّنة (نص القطر = r)
    Path square(double angle) {
      final path = Path();
      for (var i = 0; i < 4; i++) {
        final a = angle + i * math.pi / 2;
        final p = c + Offset(math.cos(a), math.sin(a)) * (r - stroke);
        i == 0 ? path.moveTo(p.dx, p.dy) : path.lineTo(p.dx, p.dy);
      }
      return path..close();
    }

    final star = Path.combine(
      PathOperation.union,
      square(0),
      square(math.pi / 4),
    );
    canvas.drawPath(star, Paint()..color = color.withValues(alpha: 0.18));
    canvas.drawPath(
      star,
      Paint()
        ..color = color
        ..style = PaintingStyle.stroke
        ..strokeWidth = stroke
        ..strokeJoin = StrokeJoin.miter,
    );

    // الدايرة الداخلية
    final inner = r * 0.36;
    if (centerColor != null) {
      canvas.drawCircle(c, inner, Paint()..color = centerColor!);
    }
    canvas.drawCircle(
      c,
      inner,
      Paint()
        ..color = color
        ..style = PaintingStyle.stroke
        ..strokeWidth = stroke * 0.8,
    );
    canvas.drawCircle(c, inner * 0.28, Paint()..color = color);
  }

  @override
  bool shouldRepaint(_RubElHizbPainter old) =>
      old.color != color || old.centerColor != centerColor;
}
