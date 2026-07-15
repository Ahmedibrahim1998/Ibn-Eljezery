<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Site content & settings
            SettingSeeder::class,
            SiteTextSeeder::class,
            FeatureSeeder::class,
            TeacherSeeder::class,
            RoleSeeder::class,
            ProgramSeeder::class,
            CourseCategorySeeder::class,
            CourseSeeder::class,

            // Accounts (also links courses to a teacher + supervisor) — needs courses.
            UserSeeder::class,

            // Enrollment, schedule & attendance demo data — needs courses + accounts.
            CourseSessionSeeder::class,
            BookingSeeder::class,
            AttendanceSeeder::class,
            PaymentLogSeeder::class,

            // Remaining content
            LeadSeeder::class,
            TestimonialSeeder::class,
            FaqSeeder::class,
            WeeklyPlanRowSeeder::class,
        ]);
    }
}
