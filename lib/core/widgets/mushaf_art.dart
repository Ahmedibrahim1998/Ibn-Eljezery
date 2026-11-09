import 'package:flutter/material.dart';

import '../../app/theme.dart';

/// رسمة مصحف مفتوح: صفحتين عاجي بإطار دهبي وسطور خفيفة (بالكود — من غير صور).
class MushafArt extends StatelessWidget {
  const MushafArt({super.key, this.width = 120});

  final double width;

  @override
  Widget build(BuildContext context) =>
      CustomPaint(size: Size(width, width * 0.72), painter: _MushafPainter());
}

class _MushafPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width, h = size.height;
    final mid = w / 2;

    // ظل خفيف تحت المصحف
    canvas.drawOval(
      Rect.fromCenter(
        center: Offset(mid, h * 0.97),
        width: w * 0.9,
        height: h * 0.1,
      ),
      Paint()..color = Colors.black.withValues(alpha: 0.18),
    );

    for (final side in [-1.0, 1.0]) {
      final outer = w * 0.47 * side;
      // الصفحة: مايلة شوية لفوق عند الكعب زي المصحف المفتوح
      final page = Path()
        ..moveTo(mid, h * 0.14)
        ..quadraticBezierTo(mid + outer * 0.5, h * 0.02, mid + outer, h * 0.06)
        ..lineTo(mid + outer, h * 0.86)
        ..quadraticBezierTo(mid + outer * 0.5, h * 0.82, mid, h * 0.94)
        ..close();
      canvas.drawPath(page, Paint()..color = const Color(0xFFFBF5E4));
      canvas.drawPath(
        page,
        Paint()
          ..color = AppColors.gold
          ..style = PaintingStyle.stroke
          ..strokeWidth = w * 0.018,
      );

      // الإطار الدهبي الداخلي
      final inset = w * 0.06;
      final frame = Rect.fromLTRB(
        side < 0 ? mid + outer + inset : mid + inset * 0.6,
        h * 0.17,
        side < 0 ? mid - inset * 0.6 : mid + outer - inset,
        h * 0.78,
      );
      canvas.drawRRect(
        RRect.fromRectAndRadius(frame, Radius.circular(w * 0.02)),
        Paint()
          ..color = AppColors.gold.withValues(alpha: 0.85)
          ..style = PaintingStyle.stroke
          ..strokeWidth = w * 0.01,
      );

      // سطور النص
      final line = Paint()
        ..color = AppColors.emerald.withValues(alpha: 0.35)
        ..strokeWidth = w * 0.012
        ..strokeCap = StrokeCap.round;
      for (var i = 1; i <= 5; i++) {
        final y = frame.top + frame.height * i / 6;
        canvas.drawLine(
          Offset(frame.left + w * 0.025, y),
          Offset(frame.right - w * 0.025, y),
          line,
        );
      }
    }

    // الكعب
    canvas.drawLine(
      Offset(mid, h * 0.14),
      Offset(mid, h * 0.94),
      Paint()
        ..color = AppColors.gold
        ..strokeWidth = w * 0.02,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
