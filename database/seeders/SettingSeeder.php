<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Brand
            ['general', 'brand_name_ar', 'مركز ابن الجزري'],
            ['general', 'brand_name_en', 'Ibn Al-Jazari Center'],

            // Hero
            ['hero', 'hero_title_ar', 'مركز ابن الجزري لتحفيظ القرآن الكريم'],
            ['hero', 'hero_title_en', 'Ibn Al-Jazari Quran Memorization Center'],
            ['hero', 'hero_lead_ar', 'حلقاتٌ قرآنية متميزة، إشراف علمي، متابعة مستمرة، ومناهج تربوية تعتني بحفظ كتاب الله فهمًا وتدبرًا.'],
            ['hero', 'hero_lead_en', "Distinctive Quran circles, scientific supervision, continuous follow-up, and educational curricula that care for memorizing Allah's book with understanding and contemplation."],

            // Stats
            ['stats', 'stat_1_number', '+250'],
            ['stats', 'stat_1_label_ar', 'حافظ/ة قيد المتابعة'],
            ['stats', 'stat_1_label_en', 'Memorizers under follow-up'],
            ['stats', 'stat_2_number', '+20'],
            ['stats', 'stat_2_label_ar', 'معلم ومعلمة مجازون'],
            ['stats', 'stat_2_label_en', 'Certified male & female teachers'],
            ['stats', 'stat_3_number', '+8'],
            ['stats', 'stat_3_label_ar', 'سنوات من العطاء'],
            ['stats', 'stat_3_label_en', 'Years of service'],

            // About
            ['about', 'about_p1_ar', 'مركز ابن الجزري هو مركز متخصص في تعليم القرآن الكريم وعلومه، يهدف إلى إعداد جيلٍ قرآني متقنٍ للحفظ والتلاوة، ملتزمٍ بهدي كتاب الله وسنّة نبيه ﷺ، عبر حلقاتٍ حضورية وعن بُعد بإشراف معلمين ومعلمات مجازين بالسند المتصل.'],
            ['about', 'about_p1_en', "Ibn Al-Jazari Center is a specialized center for teaching the Holy Quran and its sciences, aiming to prepare a Quranic generation proficient in memorization and recitation, through in-person and remote circles under the supervision of certified teachers with connected chains."],
            ['about', 'about_p2_ar', 'نعتمد مناهج مدروسة تراعي الفروق الفردية بين الطلاب، مع متابعة رقمية دقيقة لمستوى الحفظ والمراجعة، وتواصل مستمر مع ولي الأمر، ليبقى الطالب مرتبطًا بالقرآن قلبًا وقالبًا.'],
            ['about', 'about_p2_en', 'We adopt studied curricula that consider individual differences among students, with precise digital tracking, and continuous communication with parents.'],
            ['about', 'about_list_ar', "حلقات تحفيظ ومراجعة لجميع الأعمار.\nبرامج خاصة للإجازات بالسند في القراءات.\nبرامج تأسيس للقراءة (نور البيان/القاعدة النورانية).\nبرامج تربوية وإيمانية مرافقة للحفظ."],
            ['about', 'about_list_en', "Memorization and review circles for all ages.\nSpecial certification programs with chains.\nFoundation programs for reading (Nour Al-Bayan / Al-Qaidah Al-Nuraniyah).\nEducational and faith programs accompanying memorization."],

            // Memorization
            ['memorization', 'memorization_p1_ar', 'نؤمن أن حفظ القرآن رحلة حياة، لذلك وضعنا نظامًا دقيقًا يضمن ثبات الحفظ وجودة التلاوة، مع مراعاة وقت الطالب وظروفه.'],
            ['memorization', 'memorization_p1_en', "We believe that Quran memorization is a life journey, so we have established a precise system that ensures memorization stability and recitation quality."],
            ['memorization', 'memorization_steps_ar', "تقييم أولي لمستوى الطالب في الحفظ والتلاوة.\nوضع خطة حفظ ومراجعة تناسب عمره ووقته.\nمتابعة يومية للحفظ الجديد والمراجعة.\nاختبارات شهرية وفصلية لقياس التقدّم.\nتقارير دورية للطالب وولي الأمر."],
            ['memorization', 'memorization_steps_en', "Initial evaluation of the student's level.\nA memorization and review plan suited to their age and time.\nDaily follow-up of new memorization and review.\nMonthly and quarterly progress tests.\nPeriodic reports for student and parents."],
            ['memorization', 'memorization_p2_ar', 'كما نتيح للطلاب منصة إلكترونية لعرض تقدّمهم في الحفظ والمراجعة، مع تحفيز عبر الأوسمة والشهادات.'],
            ['memorization', 'memorization_p2_en', 'We also provide an electronic platform for students to track their progress, with motivation through badges and certificates.'],

            // Homepage limits (how many cards to show before "view all")
            ['limits', 'limit_teachers', '8'],
            ['limits', 'limit_programs', '6'],
            ['limits', 'limit_courses', '6'],
            ['limits', 'limit_testimonials', '6'],
            ['limits', 'limit_faqs', '8'],

            // Contact
            ['contact', 'whatsapp_phone', '+201067818406'],
            ['contact', 'contact_email', 'info@ibnaljazari-center.com'],
            ['contact', 'contact_location_ar', '( محافظة المنيا - مركز مطاي - مطاي البلد - شارع المحدة - مسجد الامام علي بن ابي طالب)'],
            ['contact', 'contact_location_en', '(Minya Governorate - Matay - Matay Al-Balad - Al-Mahda Street - Imam Ali bin Abi Talib Mosque)'],
        ];

        foreach ($settings as [$group, $key, $value]) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group, 'type' => 'text'],
            );
        }
    }
}
