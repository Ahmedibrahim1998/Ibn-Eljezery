import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../data/auth_store.dart';

/// افتح شاشة الدخول ← بترجع true لو دخل. [register] ← تفتح على "حساب جديد" على طول.
Future<bool> openLogin(BuildContext context, {bool register = false}) async =>
    await Navigator.of(context).push<bool>(
      MaterialPageRoute(builder: (_) => LoginScreen(register: register)),
    ) ??
    false;

enum _Role { student, teacher }

/// الدخول: "أنا طالب" (رقم الموبايل — دخول أو حساب جديد) / "أنا معلم" (إيميل لوحة المعلم).
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key, this.register = false});

  final bool register;

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _login = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  _Role _role = _Role.student;
  late bool _register = widget.register;
  bool _busy = false;
  bool _showPassword = false;
  String? _error;

  bool get _signUp => _role == _Role.student && _register;

  @override
  void dispose() {
    for (final c in [_name, _login, _email, _password]) {
      c.dispose();
    }
    super.dispose();
  }

  void _switch(VoidCallback change) => setState(() {
    change();
    _error = null;
    _form.currentState?.reset();
  });

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      final auth = AuthStore.instance;
      if (_signUp) {
        await auth.register(
          name: _name.text,
          phone: _login.text,
          email: _email.text,
          password: _password.text,
        );
      } else {
        await auth.login(_login.text, _password.text);
      }
      if (mounted) Navigator.pop(context, true);
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final teacher = _role == _Role.teacher;

    return Scaffold(
      appBar: AppBar(),
      body: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
          children: [
            Center(
              child: Image.asset(
                'assets/images/logo.png',
                width: 88,
                height: 88,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              _signUp ? 'حساب طالب جديد' : 'أهلاً بيك',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              teacher
                  ? 'ادخل بنفس إيميل وكلمة سر لوحة المعلم في الموقع'
                  : _signUp
                  ? 'برقم موبايلك — عشان تحجز في الدورات وتدخل حصصك'
                  : 'ادخل برقم موبايلك وكلمة السر',
              textAlign: TextAlign.center,
              style: TextStyle(color: scheme.onSurfaceVariant),
            ),
            const SizedBox(height: 20),
            SegmentedButton<_Role>(
              showSelectedIcon: false,
              segments: const [
                ButtonSegment(
                  value: _Role.student,
                  label: Text('أنا طالب'),
                  icon: Icon(Icons.person_rounded),
                ),
                ButtonSegment(
                  value: _Role.teacher,
                  label: Text('أنا معلم'),
                  icon: Icon(Icons.school_rounded),
                ),
              ],
              selected: {_role},
              onSelectionChanged: (v) => _switch(() => _role = v.first),
            ),
            const SizedBox(height: 20),
            if (_signUp) ...[
              TextFormField(
                controller: _name,
                textInputAction: TextInputAction.next,
                decoration: const InputDecoration(
                  labelText: 'الاسم بالكامل',
                  prefixIcon: Icon(Icons.badge_outlined),
                ),
                validator: (v) =>
                    (v == null || v.trim().isEmpty) ? 'اكتب اسمك' : null,
              ),
              const SizedBox(height: 12),
            ],
            TextFormField(
              controller: _login,
              textInputAction: TextInputAction.next,
              keyboardType: teacher
                  ? TextInputType.emailAddress
                  : TextInputType.phone,
              textDirection: TextDirection.ltr,
              decoration: InputDecoration(
                labelText: teacher ? 'الإيميل' : 'رقم الموبايل (واتساب)',
                hintText: teacher ? 'name@example.com' : '01xxxxxxxxx',
                prefixIcon: Icon(
                  teacher
                      ? Icons.alternate_email_rounded
                      : Icons.phone_android_rounded,
                ),
              ),
              validator: (v) {
                final value = v?.trim() ?? '';
                if (teacher) {
                  return value.contains('@') ? null : 'اكتب الإيميل صح';
                }
                return value.replaceAll(RegExp(r'\D'), '').length < 10
                    ? 'اكتب رقم موبايل صحيح'
                    : null;
              },
            ),
            if (_signUp) ...[
              const SizedBox(height: 12),
              TextFormField(
                controller: _email,
                textInputAction: TextInputAction.next,
                keyboardType: TextInputType.emailAddress,
                textDirection: TextDirection.ltr,
                decoration: const InputDecoration(
                  labelText: 'الإيميل (اختياري)',
                  prefixIcon: Icon(Icons.mail_outline_rounded),
                ),
              ),
            ],
            const SizedBox(height: 12),
            TextFormField(
              controller: _password,
              obscureText: !_showPassword,
              textDirection: TextDirection.ltr,
              onFieldSubmitted: (_) => _submit(),
              decoration: InputDecoration(
                labelText: 'كلمة السر',
                helperText: _signUp ? '٨ حروف أو أرقام على الأقل' : null,
                prefixIcon: const Icon(Icons.lock_outline_rounded),
                suffixIcon: IconButton(
                  icon: Icon(
                    _showPassword
                        ? Icons.visibility_off_rounded
                        : Icons.visibility_rounded,
                  ),
                  onPressed: () =>
                      setState(() => _showPassword = !_showPassword),
                ),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return 'اكتب كلمة السر';
                if (_signUp && v.length < 8) return '٨ حروف أو أرقام على الأقل';
                return null;
              },
            ),
            if (_error != null) ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: scheme.errorContainer,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.error_outline_rounded,
                      color: scheme.onErrorContainer,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        _error!,
                        style: TextStyle(color: scheme.onErrorContainer),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 20),
            FilledButton(
              onPressed: _busy ? null : _submit,
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(52),
              ),
              child: _busy
                  ? const SizedBox.square(
                      dimension: 22,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : Text(_signUp ? 'إنشاء الحساب' : 'دخول'),
            ),
            const SizedBox(height: 12),
            if (!teacher)
              OutlinedButton(
                onPressed: _busy
                    ? null
                    : () => _switch(() => _register = !_register),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size.fromHeight(50),
                ),
                child: Text(
                  _register
                      ? 'عندي حساب — تسجيل الدخول'
                      : 'أول مرة؟ اعمل حساب طالب جديد',
                ),
              )
            else
              Text(
                'حسابات المعلمين بتتعمل من إدارة الأكاديمية.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: scheme.onSurfaceVariant),
              ),
          ],
        ),
      ),
    );
  }
}
