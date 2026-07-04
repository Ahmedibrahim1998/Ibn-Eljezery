<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        if (Feature::query()->exists()) {
            return;
        }

        $features = [
            ['📖', 'إجازات معتمدة', 'Certified Certifications', 'سلاسل سند متصلة إلى النبي ﷺ.', 'Connected chains to the Prophet ﷺ.'],
            ['💻', 'تعليم عن بُعد', 'Remote Learning', 'حلقات أونلاين عبر منصات موثوقة.', 'Online circles through trusted platforms.'],
            ['👨‍👩‍👧‍👦', 'متابعة الأسرة', 'Family Follow-up', 'تقارير دورية لولي الأمر.', 'Periodic reports for parents.'],
            ['⭐', 'بيئة تربوية', 'Educational Environment', 'عناية بالأخلاق والسلوك والقدوة.', 'Focus on ethics, behavior, and role modeling.'],
        ];

        foreach ($features as $i => [$icon, $titleAr, $titleEn, $descAr, $descEn]) {
            Feature::create([
                'icon' => $icon,
                'title_ar' => $titleAr,
                'title_en' => $titleEn,
                'description_ar' => $descAr,
                'description_en' => $descEn,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}
