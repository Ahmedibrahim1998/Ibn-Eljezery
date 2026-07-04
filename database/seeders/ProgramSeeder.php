<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        if (Program::query()->exists()) {
            return;
        }

        $programs = [
            [
                'title_ar' => 'باقة المبتدئين', 'title_en' => 'Beginners Package',
                'subtitle_ar' => 'تأسيس القراءة وحفظ قصار السور', 'subtitle_en' => 'Reading foundation and short surahs memorization',
                'price' => 150, 'is_featured' => false, 'badge_ar' => null, 'badge_en' => null,
                'features' => [
                    ['ar' => '٣ حصص أسبوعيًا (حضور/أونلاين).', 'en' => '3 sessions weekly (in-person/online).'],
                    ['ar' => 'برنامج تأسيسي في مخارج الحروف.', 'en' => 'Foundation program in letter articulation.'],
                    ['ar' => 'حفظ جزء عمّ مع مراجعة مستمرة.', 'en' => 'Memorize Juz Amma with continuous review.'],
                    ['ar' => 'تقارير شهرية لولي الأمر.', 'en' => 'Monthly reports for parents.'],
                ],
            ],
            [
                'title_ar' => 'باقة الحفظ المنتظم', 'title_en' => 'Regular Memorization Package',
                'subtitle_ar' => 'لمن يرغب في حفظ مستمر ومتدرج', 'subtitle_en' => 'For continuous and gradual memorization',
                'price' => 250, 'is_featured' => true, 'badge_ar' => 'الأكثر اختيارًا', 'badge_en' => 'Most Popular',
                'features' => [
                    ['ar' => '٤ حصص أسبوعيًا.', 'en' => '4 sessions weekly.'],
                    ['ar' => 'خطة حفظ مخصصة بحسب مستواك.', 'en' => 'Customized memorization plan for your level.'],
                    ['ar' => 'تركيز على ضبط التلاوة وأحكام التجويد.', 'en' => 'Focus on recitation accuracy and tajweed rules.'],
                    ['ar' => 'لقاءات تربوية وإيمانية دورية.', 'en' => 'Periodic educational and faith meetings.'],
                ],
            ],
            [
                'title_ar' => 'باقة الحفظ المكثف', 'title_en' => 'Intensive Memorization Package',
                'subtitle_ar' => 'لمن لديهم هدف ختم القرآن أو الإجازة', 'subtitle_en' => 'For those aiming to complete the Quran or get certification',
                'price' => 400, 'is_featured' => false, 'badge_ar' => null, 'badge_en' => null,
                'features' => [
                    ['ar' => '٦ حصص أسبوعيًا.', 'en' => '6 sessions weekly.'],
                    ['ar' => 'برنامج حفظ ومراجعة مكثف.', 'en' => 'Intensive memorization and review program.'],
                    ['ar' => 'إعداد للإجازة بالسند لمن تأهّل.', 'en' => 'Preparation for certification for qualified students.'],
                    ['ar' => 'مقابلات تقييمية دورية مع المشرف العلمي.', 'en' => 'Periodic evaluation meetings with the scientific supervisor.'],
                ],
            ],
        ];

        foreach ($programs as $i => $program) {
            Program::create([
                ...$program,
                'currency_ar' => 'ر.س / شهر',
                'currency_en' => 'SAR / month',
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}
