import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../../core/network/api_client.dart';
import '../domain/app_user.dart';

/// الحساب: دخول / تسجيل / خروج / مسح الحساب.
///
/// التوكن بيتحفظ متشفّر (Keystore) ← الطالب يفضل داخل لحد ما يعمل خروج.
/// المصحف والفقه شغالين من غير حساب؛ الحساب للأكاديمية بس (الحجز ودوراتي).
class AuthStore extends ChangeNotifier {
  AuthStore._(this._storage);

  static late AuthStore instance;

  static const _tokenKey = 'auth_token';
  static const _userKey = 'auth_user';

  final FlutterSecureStorage _storage;
  String? _token;
  AppUser? _user;

  static Future<void> load({FlutterSecureStorage? storage}) async {
    instance = AuthStore._(storage ?? const FlutterSecureStorage());
    await instance._read();
    final api = ApiClient.instance;
    api.tokenProvider = () => instance._token;
    api.onUnauthorized = instance._clear;
  }

  AppUser? get user => _user;
  bool get loggedIn => _token != null && _user != null;

  Future<void> _read() async {
    try {
      _token = await _storage.read(key: _tokenKey);
      final raw = await _storage.read(key: _userKey);
      if (raw != null) _user = AppUser.fromJson(jsonDecode(raw));
    } catch (_) {
      // التخزين المشفّر ممكن يبوظ بعد استرجاع نسخة احتياطية ← يدخل تاني وخلاص
      _token = _user = null;
    }
  }

  /// [login] = رقم الموبايل (طالب) أو البريد الإلكتروني (معلم).
  Future<void> login(String login, String password) => _authenticate(
    '/auth/login',
    {'login': login.trim(), 'password': password, 'device_name': 'android'},
  );

  Future<void> register({
    required String name,
    required String phone,
    String? email,
    required String password,
  }) => _authenticate('/auth/register', {
    'name': name.trim(),
    'phone': phone.trim(),
    if (email != null && email.trim().isNotEmpty) 'email': email.trim(),
    'password': password,
    'device_name': 'android',
  });

  Future<void> _authenticate(String path, Map<String, dynamic> body) async {
    final response = await ApiClient.instance.post(path, body);
    _token = response['token'];
    _user = AppUser.fromJson(response['user']);
    await _storage.write(key: _tokenKey, value: _token);
    await _storage.write(key: _userKey, value: jsonEncode(_user!.toJson()));
    notifyListeners();
  }

  /// تحديث بيانات الحساب (لو الأدمن غيّر الدور مثلاً).
  Future<void> refresh() async {
    if (!loggedIn) return;
    try {
      _user = AppUser.fromJson(await ApiClient.instance.getFresh('/auth/me'));
      await _storage.write(key: _userKey, value: jsonEncode(_user!.toJson()));
      notifyListeners();
    } on ApiException {
      // من غير نت: نفضل بالبيانات المحفوظة
    }
  }

  Future<void> logout() async {
    try {
      await ApiClient.instance.post('/auth/logout');
    } on ApiException {
      // حتى لو السيرفر مش متاح، نخرج من الموبايل
    }
    await _clear();
  }

  /// مسح الحساب نهائياً (مطلوب من Google Play).
  Future<void> deleteAccount() async {
    await ApiClient.instance.delete('/auth/me');
    await _clear();
  }

  Future<void> _clear() async {
    _token = _user = null;
    await _storage.delete(key: _tokenKey);
    await _storage.delete(key: _userKey);
    notifyListeners();
  }
}
