<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        if (Testimonial::query()->exists()) {
            return;
        }

        $testimonials = [
            [
                'body_ar' => 'ابني كان يجد صعوبة في النطق الصحيح للحروف، خلال أشهر قليلة تحسّن مستواه بشكل ملحوظ، وأصبح يحب حضور الحلقة.',
                'body_en' => 'My son had difficulty with correct pronunciation; within a few months his level improved noticeably, and he loves attending the circle.',
                'author_name_ar' => 'أم محمد', 'author_name_en' => 'Mother of Mohammed',
                'author_role_ar' => 'ولي أمر طالب في حلقة التأسيس', 'author_role_en' => 'Parent of a foundation-circle student',
            ],
            [
                'body_ar' => 'أنا الآن في مراجعة الجزء الأخير من الحفظ، والمتابعة دقيقة، والمعلم يعتني بتجويدي وتصحيح أخطائي.',
                'body_en' => 'I am now reviewing the last part of my memorization; the follow-up is precise and the teacher cares about my tajweed.',
                'author_name_ar' => 'عبدالرحمن', 'author_name_en' => 'Abdulrahman',
                'author_role_ar' => 'طالب في باقة الحفظ المكثف', 'author_role_en' => 'Student in the intensive package',
            ],
            [
                'body_ar' => 'أشكر القائمين على المركز على حسن التنظيم والتواصل، وتوفير حلقات تناسب أوقاتنا وظروف الأبناء.',
                'body_en' => 'I thank the center for the excellent organization and communication, and for circles that suit our schedules.',
                'author_name_ar' => 'أبو يوسف', 'author_name_en' => 'Abu Yusuf',
                'author_role_ar' => 'ولي أمر ثلاثة طلاب', 'author_role_en' => 'Parent of three students',
            ],
        ];

        foreach ($testimonials as $i => $testimonial) {
            Testimonial::create([...$testimonial, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
