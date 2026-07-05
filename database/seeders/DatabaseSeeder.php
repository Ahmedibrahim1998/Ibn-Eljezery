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
            SettingSeeder::class,
            SiteTextSeeder::class,
            FeatureSeeder::class,
            TeacherSeeder::class,
            UserSeeder::class,
            RoleSeeder::class,
            ProgramSeeder::class,
            CourseSeeder::class,
            TestimonialSeeder::class,
            FaqSeeder::class,
            WeeklyPlanRowSeeder::class,
        ]);
    }
}
