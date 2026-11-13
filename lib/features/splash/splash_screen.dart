import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../app/bootstrap.dart';
import '../home/presentation/home_screen.dart';

/// الواجهة الافتتاحية: صورة التطبيق ← الرئيسية على طول.
///
/// عشان الفتح يبقى ناعم من غير «نطّة»:
/// - خلفية أندرويد قبل Flutter بنفس لون الصورة (مفيش وميض).
/// - الصورة بتتفك بمقاس الشاشة الأول، وبعدها بس بتظهر بحركة ناعمة.
/// - بيانات التطبيق بتتحمل وهي ظاهرة، والانتقال للرئيسية لما الاتنين يخلصوا.
/// اللمس في أي مكان يتخطاها (بعد ما البيانات تجهز).
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  /// نفس لون خلفية الصورة (وخلفية أندرويد قبل ما Flutter يفتح).
  static const background = Color(0xFF051E1A);
  static const asset = 'assets/images/splash.jpg';

  /// أقل مدة تفضل الصورة ظاهرة فيها.
  static const showFor = Duration(milliseconds: 1800);
  static const fadeIn = Duration(milliseconds: 350);

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  ImageProvider? _image;
  bool _visible = false;
  bool _ready = false;
  bool _done = false;
  Timer? _timer;
  Timer? _fallback;

  @override
  void initState() {
    super.initState();
    AppBootstrap.ready.whenComplete(() {
      _ready = true;
      if (_timer == null && _visible) _maybeGoHome();
    });
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_image != null) return;
    // فك الصورة بعرض الشاشة الحقيقي بس (أخف وأسرع على الموبايلات القديمة)
    final media = MediaQuery.of(context);
    final width = (media.size.width * media.devicePixelRatio).round();
    final image = ResizeImage(
      const AssetImage(SplashScreen.asset),
      width: width > 0 ? width : 1080,
    );
    _image = image;
    precacheImage(image, context).whenComplete(_show);
    // احتياطي: لو فك الصورة اتأخر لأي سبب، مانعلقش عليها
    _fallback = Timer(const Duration(milliseconds: 1200), _show);
  }

  void _show() {
    if (_visible || !mounted) return;
    setState(() => _visible = true);
    _timer = Timer(SplashScreen.showFor, () {
      _timer = null;
      _maybeGoHome();
    });
  }

  void _maybeGoHome({bool skip = false}) {
    if (_done || !mounted || !_ready) return;
    if (!skip && _timer != null) return;
    _done = true;
    _timer?.cancel();
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        transitionDuration: const Duration(milliseconds: 450),
        pageBuilder: (_, _, _) => const HomeScreen(),
        transitionsBuilder: (_, animation, _, child) => FadeTransition(
          opacity: CurvedAnimation(parent: animation, curve: Curves.easeOut),
          child: child,
        ),
      ),
    );
  }

  @override
  void dispose() {
    _timer?.cancel();
    _fallback?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light.copyWith(
        statusBarColor: Colors.transparent,
        systemNavigationBarColor: SplashScreen.background,
        systemNavigationBarIconBrightness: Brightness.light,
      ),
      child: Material(
        color: SplashScreen.background,
        child: GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTap: () => _maybeGoHome(skip: true),
          child: AnimatedOpacity(
            opacity: _visible ? 1 : 0,
            duration: SplashScreen.fadeIn,
            curve: Curves.easeOut,
            // contain: الإطار الذهبي يفضل كامل في أي مقاس شاشة
            child: SizedBox.expand(
              child: _image == null
                  ? null
                  : Image(
                      image: _image!,
                      fit: BoxFit.contain,
                      gaplessPlayback: true,
                      semanticLabel: 'تطبيق ابن الجزري للقرآن الكريم',
                    ),
            ),
          ),
        ),
      ),
    );
  }
}
