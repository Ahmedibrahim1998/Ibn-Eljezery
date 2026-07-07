<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Booking;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        if (Attendance::query()->exists()) {
            return;
        }

        // Attended-class counts cycled across each course's students, so the demo
        // shows every subscription state: 8 & 10 -> payment due, 3 & 5 -> not yet,
        // 0 -> never attended.
        $counts = [8, 5, 3, 10, 0];

        foreach (Booking::orderBy('course_id')->orderBy('id')->get()->groupBy('course_id') as $bookings) {
            foreach ($bookings->values() as $i => $booking) {
                $days = $counts[$i % count($counts)];

                for ($d = 0; $d < $days; $d++) {
                    // One row per distinct past day (unique per booking+date).
                    $date = today()->subDays($days - $d);

                    Attendance::create([
                        'booking_id' => $booking->id,
                        'attended_on' => $date,
                        'checked_in_at' => $date->copy()->setTime(18, 0),
                        'checked_out_at' => $date->copy()->setTime(19, 0),
                    ]);
                }
            }
        }
    }
}
