<?php

namespace Database\Seeders;

use App\Enum\Booking\BookingStatusEnum;
use App\Models\Booking;
use App\Models\Course;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        if (Booking::query()->exists()) {
            return;
        }

        // A handful of enrolled students per course, so the supervisor panel has
        // students grouped under each course to take attendance for.
        $students = [
            ['name' => 'يوسف حسن', 'phone' => '01100000001'],
            ['name' => 'خديجة علي', 'phone' => '01100000002'],
            ['name' => 'عمر ياسين', 'phone' => '01100000003'],
            ['name' => 'سارة منصور', 'phone' => '01100000004'],
            ['name' => 'إبراهيم طارق', 'phone' => '01100000005'],
        ];

        foreach (Course::all() as $index => $course) {
            foreach ($students as $student) {
                Booking::create([
                    'course_id' => $course->id,
                    'name' => $student['name'],
                    // Keep phones unique per (course, student) so dedup logic is happy.
                    'phone' => $student['phone'].$index,
                    'email' => null,
                    'notes' => null,
                    'status' => BookingStatusEnum::CONFIRMED->value,
                    'months_paid' => 0,
                ]);
            }
        }
    }
}
