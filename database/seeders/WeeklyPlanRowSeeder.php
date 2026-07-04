<?php

namespace Database\Seeders;

use App\Models\WeeklyPlanRow;
use Illuminate\Database\Seeder;

class WeeklyPlanRowSeeder extends Seeder
{
    public function run(): void
    {
        if (WeeklyPlanRow::query()->exists()) {
            return;
        }

        $rows = [
            ['الأحد', 'Sunday', '١ صفحة من سورة البقرة', '1 page from Surah Al-Baqarah', 'آخر ٣ صفحات تم حفظها', 'Last 3 memorized pages'],
            ['الإثنين', 'Monday', '١ صفحة من سورة البقرة', '1 page from Surah Al-Baqarah', '٥ صفحات سابقة', '5 previous pages'],
            ['الثلاثاء', 'Tuesday', '١ صفحة من سورة البقرة', '1 page from Surah Al-Baqarah', 'ربع حزب كامل', 'Complete quarter hizb'],
            ['الأربعاء', 'Wednesday', 'نصف صفحة مراجعة مركّزة', 'Half page focused review', 'حفظ الأسبوع كاملاً', "Complete week's memorization"],
            ['الخميس', 'Thursday', 'تثبيت الحفظ', 'Memorization consolidation', 'مراجعة عامة لما سبق', 'General review of previous content'],
        ];

        foreach ($rows as $i => [$dayAr, $dayEn, $newAr, $newEn, $reviewAr, $reviewEn]) {
            WeeklyPlanRow::create([
                'day_ar' => $dayAr, 'day_en' => $dayEn,
                'new_memorization_ar' => $newAr, 'new_memorization_en' => $newEn,
                'review_ar' => $reviewAr, 'review_en' => $reviewEn,
                'sort_order' => $i + 1, 'is_active' => true,
            ]);
        }
    }
}
