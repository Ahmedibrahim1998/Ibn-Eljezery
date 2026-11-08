<?php

namespace Database\Seeders;

use App\Models\LearningSection;
use Illuminate\Database\Seeder;

/**
 * The mobile app's first release has one learning section: الفقه.
 * Subjects (مالكي / شافعي) and lessons are added later from the panel.
 */
class LearningSectionSeeder extends Seeder
{
    public function run(): void
    {
        LearningSection::query()->updateOrCreate(
            ['slug' => 'fiqh'],
            [
                'title_ar' => 'الفقه',
                'title_en' => 'Fiqh',
                'description_ar' => 'أحكام العبادات والمعاملات على المذهب المالكي والشافعي',
                'description_en' => 'Rulings of worship and transactions in the Maliki and Shafii schools',
                'icon' => 'school',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );
    }
}
