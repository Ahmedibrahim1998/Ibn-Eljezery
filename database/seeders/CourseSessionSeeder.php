<?php

namespace Database\Seeders;

use App\Enum\Course\CourseTypeEnum;
use App\Models\Booking;
use App\Models\Course;
use App\Models\CourseSession;
use Illuminate\Database\Seeder;

class CourseSessionSeeder extends Seeder
{
    public function run(): void
    {
        if (CourseSession::query()->exists()) {
            return;
        }

        // Weekly schedule per course — one class a week, matching how the teacher
        // panel's "generate multiple sessions" action builds a run. We seed one
        // payment cycle's worth (8 weekly classes) starting next week at 6pm.
        $count = Booking::CLASSES_PER_MONTH;

        foreach (Course::all() as $course) {
            $online = $course->type === CourseTypeEnum::ONLINE;
            $first = now()->startOfWeek()->addWeek()->addDays(1)->setTime(18, 0); // next week, Tuesday 18:00

            for ($i = 0; $i < $count; $i++) {
                CourseSession::create([
                    'course_id' => $course->id,
                    'starts_at' => $first->copy()->addWeeks($i),
                    'duration_minutes' => 60,
                    'location_ar' => $online ? null : 'قاعة المركز الرئيسية',
                    'location_en' => $online ? null : 'Main center hall',
                    'is_active' => true,
                ]);
            }
        }
    }
}
