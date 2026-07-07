<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\PaymentLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentLogSeeder extends Seeder
{
    public function run(): void
    {
        if (PaymentLog::query()->exists()) {
            return;
        }

        $supervisorId = User::role('supervisor')->value('id');

        // Record a payment for a subset of students who have already earned a
        // month (>= 8 attended classes), leaving others "payment due" for the demo.
        Booking::with('course', 'attendances')->get()
            ->filter(fn (Booking $b): bool => $b->monthsEarned() >= 1)
            ->groupBy('course_id')
            // Pay for just the first eligible student per course.
            ->each(fn ($bookings) => $bookings->first()?->recordPayment($supervisorId));
    }
}
