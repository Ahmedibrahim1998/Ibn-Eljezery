<?php

namespace Database\Seeders;

use App\Enum\Course\CourseTypeEnum;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        if (Course::query()->exists()) {
            return;
        }

        $courses = [
            [
                'type' => CourseTypeEnum::OFFLINE,
                'title_ar' => 'دورات حضورية (أوفلاين)', 'title_en' => 'In-person Courses (Offline)',
                'description_ar' => 'لأبناء وبنات الحي، في قاعات مجهّزة داخل المركز، بإشراف مباشر من المعلمين.',
                'description_en' => 'For neighborhood boys and girls, in equipped halls inside the center, with direct teacher supervision.',
                'badge_ar' => 'حضوري - في مقر المركز', 'badge_en' => 'In-person - At Center',
                'items' => [
                    ['ar' => 'دورات تلاوة وتحفيظ لجميع الأعمار.', 'en' => 'Recitation and memorization courses for all ages.'],
                    ['ar' => 'دورة تجويد تأسيسية ومتقدمة.', 'en' => 'Foundation and advanced tajweed courses.'],
                    ['ar' => 'أوقات صباحية ومسائية مرنة.', 'en' => 'Flexible morning and evening times.'],
                ],
            ],
            [
                'type' => CourseTypeEnum::ONLINE,
                'title_ar' => 'دورات أونلاين (عن بُعد)', 'title_en' => 'Online Courses (Remote)',
                'description_ar' => 'دروس مباشرة عبر منصات آمنة، مع تسجيل الحصص وإتاحة متابعتها لولي الأمر.',
                'description_en' => 'Live lessons through secure platforms, with session recording and parent follow-up.',
                'badge_ar' => 'أونلاين - عبر الإنترنت', 'badge_en' => 'Online - Via Internet',
                'items' => [
                    ['ar' => 'حلقات تحفيظ فردية وجماعية.', 'en' => 'Individual and group memorization circles.'],
                    ['ar' => 'دورات تجويد مكثفة وشهادات حضور.', 'en' => 'Intensive tajweed courses and attendance certificates.'],
                    ['ar' => 'إمكانية الانضمام من أي دولة.', 'en' => 'Ability to join from any country.'],
                ],
            ],
        ];

        foreach ($courses as $i => $course) {
            Course::create([...$course, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
