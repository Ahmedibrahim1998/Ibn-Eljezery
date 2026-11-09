# 04 — الـ Backend: الجداول ونقاط الـ API

> كل ما هنا يُضاف إلى مشروع Laravel الحالي `ibneljezery/` **بنفس أسلوبه الحالي**:
> Model + Repository/Interface + Service + DTO + Resource، والأعمدة ثنائية اللغة `*_ar` / `*_en`، و Enums تحت `App\Enum\{Domain}`، وترتيب بـ `sort_order` + `is_active` (مثل `Course`).

## 1. الجداول الجديدة

### 1.1 المحتوى الشرعي (هيكل: قسم ← مادة ← درس)
```
learning_sections            (الفقه، العقيدة، التجويد…)
  id, slug, title_ar, title_en, description_ar, description_en,
  icon, color, sort_order, is_active, timestamps

learning_subjects            (مثال: فقه الطهارة، متن الجزرية…)
  id, learning_section_id (FK), title_ar, title_en, description_ar, description_en,
  author_ar, author_en, madhhab (enum nullable: hanafi/maliki/shafii/hanbali/general),
  level (enum: beginner/intermediate/advanced), cover_image,
  sort_order, is_active, timestamps

lessons
  id, learning_subject_id (FK), title_ar, title_en,
  content_ar (longtext, HTML/Markdown), content_en,
  audio_url, video_url, duration_minutes,
  status (enum: draft/in_review/approved/published/rejected),
  author_id (FK users), reviewed_by (FK users, nullable), reviewed_at, review_notes,
  published_at, version (int), sort_order, timestamps, soft_deletes

lesson_questions             (اختبار قصير بعد الدرس — مرحلة 2)
  id, lesson_id, question_ar, question_en, options (json), correct_index, explanation_ar, sort_order
```

> **في الإصدار الأول:** الجداول الثلاثة الأولى فقط (`learning_sections`, `learning_subjects`, `lessons`) مع قسم "الفقه"، و `madhhab` يُستخدم فيه `maliki` و `shafii`.

### 1.2 الأذكار (المرحلة 2)
```
azkar_categories    id, slug, title_ar, title_en, icon, sort_order, is_active
azkar               id, azkar_category_id, text_ar, translation_en, repeat_count,
                    source_ar (التخريج), virtue_ar (الفضل), audio_url,
                    status (نفس enum المراجعة), sort_order, timestamps
```

### 1.3 بيانات التطبيق والمستخدم
```
app_devices         id, user_id (nullable), device_uuid, platform, fcm_token,
                    app_version, locale, last_seen_at, timestamps
user_bookmarks      id, user_id, type (enum: ayah/page/lesson), ref (string: "2:255" أو page:50 أو lesson:12),
                    note, color, timestamps
user_wird_marks     (علامات الورد — مزامنة فقط، المرحلة 2؛ في الإصدار الأول تتخزن على الموبايل بس — انظر 01 §2.1.1)
                    id, user_id, type (enum: new_tasmee/near_review/far_review/tarteel) unique مع user_id,
                    from_ayah_key ("2:1"), to_ayah_key ("2:5"), updated_at
user_reading_progress  id, user_id, last_page, last_ayah_key, khatma_started_at, daily_pages_goal, timestamps
app_versions        id, platform, min_supported_version, latest_version, force_update, notes_ar, timestamps
reciters            id, slug, name_ar, name_en, style, base_url, is_active, sort_order
content_sync_log    (لمزامنة الكاش: ما الذي تغيّر منذ آخر تحديث)
```

> **ملاحظة:** لا يوجد جدول لنص القرآن في السيرفر — النص مدمج في التطبيق (انظر 02 و 03).

## 2. الطبقات الجديدة (Namespaces)
| الطبقة | المسار |
|-------|-------|
| Models | `app/Models/LearningSection.php`, `LearningSubject.php`, `Lesson.php` (نفس مكان باقي الـ Models) |
| Enums | `app/Enum/Learning/ContentStatusEnum.php`, `MadhhabEnum.php`, `LevelEnum.php` |
| Repositories | `app/Repositories/Learning/LessonRepository.php` + Interface |
| Services | `app/Services/Api/V1/Learning/...` (موازي لـ `app/Services/Web/...` الموجود) |
| Controllers | `app/Http/Controllers/Api/V1/Learning/LessonController.php` |
| DTOs | `app/Http/DTOs/Api/V1/...` |
| Resources | `app/Http/Resources/Api/V1/...` |
| Filament | `app/Filament/Resources/Learning/...` |

## 3. نقاط الـ API (v1)
كل الردود بصيغة موحّدة، واللغة من هيدر `Accept-Language` (ar افتراضي).

### عامة (بدون تسجيل)
| Method | Endpoint | الوصف |
|-------|----------|-------|
| GET | `/api/v1/app/config` | إعدادات التطبيق، أقل إصدار مدعوم، روابط CDN |
| GET | `/api/v1/learning/sections` | الأقسام |
| GET | `/api/v1/learning/sections/{slug}/subjects` | مواد القسم |
| GET | `/api/v1/learning/subjects/{id}/lessons` | دروس المادة (عناوين فقط) |
| GET | `/api/v1/learning/lessons/{id}` | محتوى الدرس كامل |
| GET | `/api/v1/learning/sync?since={timestamp}` | كل ما تغيّر منذ آخر مزامنة (للكاش الأوفلاين) |
| GET | `/api/v1/azkar/categories` | تصنيفات الأذكار + الأذكار |
| GET | `/api/v1/reciters` | القرّاء المتاحون |
| POST | `/api/v1/devices` | تسجيل الجهاز + FCM token |

### المصادقة (Sanctum)
| Method | Endpoint |
|-------|----------|
| POST | `/api/v1/auth/register` |
| POST | `/api/v1/auth/login` |
| POST | `/api/v1/auth/logout` |
| DELETE | `/api/v1/auth/account` — **حذف الحساب (إلزامي من Google Play)** |

### للمستخدم المسجّل
| Method | Endpoint |
|-------|----------|
| GET/POST/DELETE | `/api/v1/me/bookmarks` |
| GET/PUT | `/api/v1/me/reading-progress` |
| GET | `/api/v1/me/courses` — كورساته في الأكاديمية (مرحلة 3، من `bookings`) |
| GET | `/api/v1/me/attendance` — حضوره (مرحلة 3) |

## 4. قواعد مهمة
- الـ API **لا تُرجع إلا المحتوى بحالة `published`**.
- كل رد قائمة يدعم `?page=` (Pagination).
- Rate limiting على endpoints المصادقة.
- `Cache` على endpoints المحتوى العامة (تتغير نادراً)، ويُمسح الكاش عند النشر عبر Observer (يوجد `app/Observers` بالفعل).
