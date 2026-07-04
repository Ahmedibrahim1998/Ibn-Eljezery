# مركز ابن الجزري لتحفيظ القرآن الكريم — Ibn Al-Jazari Center

موقع ديناميكي ثنائي اللغة (عربي/إنجليزي، RTL/LTR) لمركز تحفيظ قرآن، مبني بـ **Laravel 12** و **Filament 3**، مع لوحة تحكم للإدارة ولوحة خاصة للمعلمين، ونظام حجز دورات (حضوري/أونلاين) بتكامل **Zoom**.

A bilingual (Arabic/English) dynamic website for a Quran-memorization center, built with **Laravel 12** + **Filament 3**, featuring an admin panel, a dedicated teacher panel, and a course-booking system (in-person / online) with **Zoom** integration.

---

## المميزات · Features

- 🌐 **واجهة عامة ثنائية اللغة** (RTL/LTR) — كل النصوص تُدار من لوحة التحكم.
- 🧑‍💼 **لوحة تحكم للأدمن** (`/admin`) لإدارة كل المحتوى (المعلمون، الباقات، الدورات، الآراء، الأسئلة، الطلاب، الإعدادات) + إحصائيات ورسوم بيانية.
- 👨‍🏫 **لوحة تحكم للمعلمين** (`/teacher`) — كل معلم يدير دوراته ومواعيده وحجوزات طلابه فقط.
- 📅 **نظام حجز**: مواعيد للدورات + سعة مقاعد + منع الحجز المكرّر.
- 🎥 **تكامل Zoom** تلقائي للدورات الأونلاين (Server-to-Server OAuth).
- ✅ **حضور وغياب** مع حساب عدد حصص الحضور لكل طالب.
- 🔐 **أدوار وصلاحيات** عبر Filament Shield.

---

## المتطلبات · Requirements

| Tool | Version |
|------|---------|
| PHP | ^8.2 |
| Composer | 2.x |
| MySQL | 8.x |
| Node.js | 18+ (اختياري للأصول) |

الحزم الأساسية: `filament/filament ^3.3`، `filament-shield`، `laravel/sanctum`، `spatie/laravel-permission`، `spatie/laravel-route-attributes`، `wendelladriel/laravel-validated-dto`.

---

## التثبيت · Installation

```bash
# 1) شغّل MySQL (على Laragon مثلاً) وأنشئ قاعدة بيانات باسم ibneljezery

# 2) ثبّت الاعتماديات
composer install

# 3) جهّز البيئة
cp .env.example .env
php artisan key:generate

# 4) اضبط الاتصال بقاعدة البيانات في .env ثم:
php artisan migrate --seed

# 5) اربط مجلد التخزين (لصور المعلمين)
php artisan storage:link

# 6) شغّل المشروع
php artisan serve
```

> على ويندوز/Laragon: لو MySQL لا يبدأ بخطأ `1455` (ملف الترحيل صغير)، قلّل `innodb_buffer_pool_size` في `my.ini`.

---

## الدخول · Access

| الرابط | الوصف | بيانات الدخول الافتراضية |
|--------|-------|--------------------------|
| `/` | الموقع العام | — |
| `/admin` | لوحة الأدمن | `admin@ibneljezery.test` / `password` |
| `/teacher` | لوحة المعلم | تُنشأ من لوحة الأدمن (زر «حساب الدخول» في المعلمين) |

تبديل اللغة: في الموقع من زر اللغة بالنافبار، وفي اللوحة من قائمة المستخدم.

---

## تكامل Zoom (اختياري)

أنشئ تطبيق **Server-to-Server OAuth** من [Zoom Marketplace](https://marketplace.zoom.us) وأضف في `.env`:

```env
ZOOM_ACCOUNT_ID=your_account_id
ZOOM_CLIENT_ID=your_client_id
ZOOM_CLIENT_SECRET=your_client_secret
ZOOM_TIMEZONE=Africa/Cairo
```

عند حفظ موعد لدورة أونلاين، يُنشأ اجتماع Zoom تلقائيًا ويظهر رابطه. **بدون هذه المفاتيح** يعمل النظام بشكل طبيعي ويترك رابط الزوم فارغًا ليُضاف يدويًا.

---

## البنية المعمارية · Architecture

يتبع المشروع نمط **Repository / Service / DTO** فوق Eloquent:

```
app/
├── Enum/{Course,Lead,Booking}/        # التعدادات (backed enums)
├── Foundation/                        # Repository الأساسي + BasicEnum
├── Repositories/{Entity}/             # واجهة + تنفيذ لكل كيان
├── Services/Web/{Feature}/            # منطق الأعمال (Home, Lead, Booking)
│   └── ThirdParties/Zoom/             # ZoomService
├── Http/
│   ├── Controllers/Web/               # واجهات الموقع العام
│   └── DTOs/Web/                      # التحقق عبر Validated DTO
├── Filament/Resources/                # لوحة الأدمن
├── Filament/Teacher/                  # لوحة المعلم (Resources + Widgets)
├── Observers/                         # CourseSessionObserver (Zoom)
└── Models/                            # 12 موديل
```

- **ثنائية اللغة**: أعمدة `*_ar` / `*_en` + الدوال `localized()`، `localizedColumn()`، `setting()`، `siteText()`.
- **نصوص الموقع**: كلها قابلة للتعديل من لوحة الإعدادات (جدول `settings`).

---

## الكيانات الأساسية · Core Entities

`Teacher` · `Course` · `CourseSession` · `Booking` · `Program` · `Testimonial` · `Faq` · `Feature` · `WeeklyPlanRow` · `Lead` (الطلاب) · `Setting` · `User`

---

## أوامر مفيدة · Useful Commands

```bash
php artisan migrate:fresh --seed   # إعادة بناء القاعدة بالبيانات التجريبية
php artisan optimize:clear         # مسح كل الكاش
composer lint                      # تنسيق الكود (Pint) — إن تم تفعيله
```

هذا المشروع تعليمي، ويُبنى على اتفاقيات موثّقة في مجلد `../docs`.
