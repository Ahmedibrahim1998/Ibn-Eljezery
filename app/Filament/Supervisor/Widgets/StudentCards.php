<?php

namespace App\Filament\Supervisor\Widgets;

use App\Enum\Booking\BookingStatusEnum;
use App\Models\Booking;
use App\Models\Course;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

/**
 * Supervisor home widget: shows the enrolled students of each supervised course
 * as cards (with today's state + subscription status) plus a small summary.
 */
class StudentCards extends Widget
{
    protected static string $view = 'filament.supervisor.widgets.student-cards';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getViewData(): array
    {
        $courseIds = Course::where('supervisor_id', Auth::id())->pluck('id');

        $courses = Course::whereIn('id', $courseIds)
            ->with(['bookings' => fn ($q) => $q->where('status', '!=', BookingStatusEnum::CANCELLED->value)->orderBy('name')])
            ->orderBy('title_ar')
            ->get();

        $totalStudents = 0;
        $presentToday = 0;
        $paymentDue = 0;

        $groups = $courses->map(function (Course $course) use (&$totalStudents, &$presentToday, &$paymentDue): array {
            $students = $course->bookings->map(function (Booking $b) use (&$presentToday, &$paymentDue): array {
                $today = $b->todayAttendance();
                $due = $b->paymentDue();

                if ($today?->checked_out_at) {
                    $state = 'left';
                    $color = 'info';
                } elseif ($today?->checked_in_at) {
                    $state = 'present';
                    $color = 'success';
                    $presentToday++;
                } else {
                    $state = 'not_today';
                    $color = 'gray';
                }

                if ($due) {
                    $paymentDue++;
                }

                return [
                    'name' => $b->name,
                    'phone' => $b->phone,
                    'attended' => $b->attendedDays(),
                    'due' => $due,
                    'state' => $state,
                    'color' => $color,
                ];
            });

            $totalStudents += $students->count();

            return [
                'title' => $course->localized('title'),
                'type' => $course->type,
                'students' => $students,
            ];
        });

        return [
            'groups' => $groups,
            'summary' => [
                'total' => $totalStudents,
                'present' => $presentToday,
                'due' => $paymentDue,
            ],
        ];
    }
}
