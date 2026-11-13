import 'dart:async';

/// تحميل بيانات التطبيق (نص القرآن، الورد، الحساب...) وهو بيعرض الواجهة الافتتاحية،
/// بدل ما الطالب يستنى على شاشة سودا قبلها.
class AppBootstrap {
  AppBootstrap._();

  static Future<void>? _loading;

  /// بيخلص لما كل البيانات تتحمل. لو التحميل ما بدأش (الاختبارات) ← خالص على طول.
  static Future<void> get ready => _loading ?? Future<void>.value();

  static void start(Future<void> Function() load) => _loading = load();
}
