<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        if (Teacher::query()->exists()) {
            return;
        }

        $teachers = [
            [
                'name_ar' => 'الشيخ أحمد الأنصاري', 'name_en' => 'Sheikh Ahmed Al-Ansari',
                'certification_ar' => 'إجازة برواية حفص عن عاصم', 'certification_en' => 'Certification in Hafs from Asim',
                'description_ar' => 'متخصص في برامج الحفظ المكثف للشباب، خبرة أكثر من 10 سنوات.', 'description_en' => 'Specialized in intensive memorization programs for youth, with over 10 years of experience.',
                'badge_ar' => 'حلقات حضورية', 'badge_en' => 'In-person circles',
            ],
            [
                'name_ar' => 'الشيخة مريم الزهراء', 'name_en' => 'Sheikha Maryam Al-Zahra',
                'certification_ar' => 'إجازة في عدة قراءات', 'certification_en' => 'Certification in multiple recitations',
                'description_ar' => 'متخصصة في تحفيظ الفتيات والنساء وإعداد الحافظات للإجازة.', 'description_en' => 'Specialized in memorization for girls and women and preparing them for certification.',
                'badge_ar' => 'حلقات أونلاين', 'badge_en' => 'Online circles',
            ],
            [
                'name_ar' => 'الشيخ خالد العُمري', 'name_en' => 'Sheikh Khalid Al-Omari',
                'certification_ar' => 'إجازة بالسند المتصل', 'certification_en' => 'Certification with connected chain',
                'description_ar' => 'مسؤول برامج المراجعة وضبط الحفظ للطلاب المتقدمين.', 'description_en' => 'Responsible for review programs and memorization accuracy for advanced students.',
                'badge_ar' => 'برامج متقدمة', 'badge_en' => 'Advanced programs',
            ],
            [
                'name_ar' => 'الأستاذة سارة الرفاعي', 'name_en' => 'Ms. Sara Al-Rifai',
                'certification_ar' => 'تربوية ومتخصصة في التأسيس', 'certification_en' => 'Educational specialist and foundation expert',
                'description_ar' => 'متخصصة في برامج الأطفال وتعليم نور البيان والقاعدة النورانية.', 'description_en' => "Specialized in children's programs and teaching Nour Al-Bayan and Al-Qaidah Al-Nuraniyah.",
                'badge_ar' => 'تأسيس الأطفال', 'badge_en' => "Children's foundation",
            ],
        ];

        foreach ($teachers as $i => $teacher) {
            Teacher::create([...$teacher, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
