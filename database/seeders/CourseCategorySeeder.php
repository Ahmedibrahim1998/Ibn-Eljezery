<?php

namespace Database\Seeders;

use App\Enum\Course\CourseTypeEnum;
use App\Models\CourseCategory;
use Illuminate\Database\Seeder;

class CourseCategorySeeder extends Seeder
{
    public function run(): void
    {
        if (CourseCategory::query()->exists()) {
            return;
        }

        $categories = [
            [
                'type' => CourseTypeEnum::OFFLINE,
                'title_ar' => 'دورة التلاوة والتحفيظ', 'title_en' => 'Recitation & Memorization',
                'description_ar' => 'حلقات حضورية لتلاوة القرآن وحفظه لجميع الأعمار داخل مقر المركز.',
                'description_en' => 'In-person circles for recitation and memorization, for all ages, at the center.',
            ],
            [
                'type' => CourseTypeEnum::OFFLINE,
                'title_ar' => 'دورة التجويد', 'title_en' => 'Tajweed Course',
                'description_ar' => 'تأسيس وإتقان أحكام التجويد عمليًا بإشراف مباشر.',
                'description_en' => 'Foundation and mastery of tajweed rules with direct supervision.',
            ],
            [
                'type' => CourseTypeEnum::ONLINE,
                'title_ar' => 'حلقات التحفيظ عن بُعد', 'title_en' => 'Online Memorization Circles',
                'description_ar' => 'حلقات تحفيظ مباشرة عبر الإنترنت يمكنك حضورها من أي مكان.',
                'description_en' => 'Live online memorization circles you can join from anywhere.',
            ],
            [
                'type' => CourseTypeEnum::ONLINE,
                'title_ar' => 'دورة التجويد المكثفة', 'title_en' => 'Intensive Tajweed',
                'description_ar' => 'دورة تجويد مكثفة عن بُعد مع متابعة فردية وشهادة حضور.',
                'description_en' => 'Intensive remote tajweed course with individual follow-up and a certificate.',
            ],
        ];

        foreach ($categories as $i => $category) {
            CourseCategory::create([...$category, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
