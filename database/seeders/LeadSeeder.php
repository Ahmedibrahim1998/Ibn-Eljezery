<?php

namespace Database\Seeders;

use App\Enum\Lead\LeadSourceEnum;
use App\Enum\Lead\LeadStatusEnum;
use App\Models\Lead;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        if (Lead::query()->exists()) {
            return;
        }

        $leads = [
            [
                'name' => 'أحمد عبد الله', 'phone' => '01000000001', 'email' => 'ahmed@example.com',
                'age_group' => 'الأطفال (6-12)', 'level' => 'مبتدئ', 'program' => 'تحفيظ القرآن',
                'message' => 'أرغب في تسجيل ابني في حلقة التحفيظ الصباحية.',
                'source' => LeadSourceEnum::HERO->value, 'status' => LeadStatusEnum::NEW->value,
            ],
            [
                'name' => 'فاطمة الزهراء', 'phone' => '01000000002', 'email' => 'fatima@example.com',
                'age_group' => 'الكبار (18+)', 'level' => 'متوسط', 'program' => 'دورة تجويد',
                'message' => 'هل تتوفر دورة تجويد مسائية عن بُعد؟',
                'source' => LeadSourceEnum::CONTACT->value, 'status' => LeadStatusEnum::CONTACTED->value,
            ],
            [
                'name' => 'محمد إبراهيم', 'phone' => '01000000003', 'email' => null,
                'age_group' => 'الشباب (13-17)', 'level' => 'متقدم', 'program' => 'الإجازة والسند',
                'message' => 'أريد إتمام حفظ القرآن والحصول على إجازة.',
                'source' => LeadSourceEnum::HERO->value, 'status' => LeadStatusEnum::ENROLLED->value,
            ],
            [
                'name' => 'مريم سعيد', 'phone' => '01000000004', 'email' => 'mariam@example.com',
                'age_group' => 'الأطفال (6-12)', 'level' => 'مبتدئ', 'program' => 'تحفيظ القرآن',
                'message' => 'استفسار عن المواعيد والرسوم.',
                'source' => LeadSourceEnum::CONTACT->value, 'status' => LeadStatusEnum::NEW->value,
            ],
        ];

        foreach ($leads as $lead) {
            Lead::create($lead);
        }
    }
}
