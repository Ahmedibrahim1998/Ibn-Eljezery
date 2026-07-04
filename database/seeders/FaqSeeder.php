<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::query()->exists()) {
            return;
        }

        $faqs = [
            [
                'question_ar' => 'هل الحلقات حضورية أم عن بُعد؟', 'question_en' => 'Are the circles in-person or remote?',
                'answer_ar' => 'تتوفر لدينا حلقات حضورية في مقر المركز، وحلقات أونلاين عبر منصات معتمدة، ويمكنك اختيار ما يناسبك عند التسجيل.',
                'answer_en' => 'We offer both in-person circles at our center and online circles through approved platforms; you can choose what suits you when registering.',
            ],
            [
                'question_ar' => 'ما الأعمار التي يُقبل تسجيلها؟', 'question_en' => 'What ages are accepted for registration?',
                'answer_ar' => 'نقبل الطلاب من عمر ٦ سنوات فما فوق، مع وجود برامج خاصة للأطفال، وبرامج للكبار والنساء.',
                'answer_en' => 'We accept students aged 6 and above, with special programs for children, adults, and women.',
            ],
            [
                'question_ar' => 'هل توجد خصومات للأشقاء؟', 'question_en' => 'Are there discounts for siblings?',
                'answer_ar' => 'نعم، تتوفر خصومات خاصة للأشقاء وللمجموعات، يمكنك الاستفسار عنها عند التواصل مع إدارة المركز.',
                'answer_en' => 'Yes, special discounts are available for siblings and groups; you can inquire when contacting the administration.',
            ],
            [
                'question_ar' => 'كيف يتم تسديد الرسوم؟', 'question_en' => 'How are fees paid?',
                'answer_ar' => 'يتم التسديد شهريًا عبر تحويل بنكي أو دفع إلكتروني، ويتم إرسال الفاتورة ورقم الحساب بعد تأكيد التسجيل.',
                'answer_en' => 'Payment is monthly via bank transfer or electronic payment; the invoice and account number are sent after confirmation.',
            ],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::create([...$faq, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
