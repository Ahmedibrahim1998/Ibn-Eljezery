/// الحساب اللي داخل بيه: طالب (بيسجّل من التطبيق برقم الموبايل) أو معلم (بنفس حساب لوحة المعلم).
class AppUser {
  const AppUser({
    required this.id,
    required this.name,
    this.phone,
    this.email,
    required this.isTeacher,
    this.teacherName,
  });

  final int id;
  final String name;
  final String? phone;
  final String? email;
  final bool isTeacher;
  final String? teacherName;

  factory AppUser.fromJson(Map<String, dynamic> json) => AppUser(
    id: json['id'],
    name: json['name'],
    phone: json['phone'],
    email: json['email'],
    isTeacher: json['role'] == 'teacher',
    teacherName: json['teacher']?['name'],
  );

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'phone': phone,
    'email': email,
    'role': isTeacher ? 'teacher' : 'student',
    if (teacherName != null) 'teacher': {'name': teacherName},
  };
}
