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
                $attended = $b->attendedDays();
                $exempt = $b->isExempt();
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

                // Subscription label + color (exempt students never show a payment state).
                if ($exempt) {
                    $sub = trans('panel.attendance.exempt');
                    $subColor = 'gray';
                } elseif ($attended === 0) {
                    $sub = '—';
                    $subColor = 'gray';
                } elseif ($due) {
                    $sub = trans('panel.attendance.unpaid');
                    $subColor = 'danger';
                } else {
                    $month = $b->lastPaidMonthLabel();
                    $sub = $month ? trans('panel.attendance.paid_month', ['month' => $month]) : trans('panel.attendance.up_to_date');
                    $subColor = 'success';
                }

                $fmt = fn ($dt): ?string => $dt ? $dt->locale(app()->getLocale())->translatedFormat('g:i A') : null;

                return [
                    'name' => $b->name,
                    'phone' => $b->phone,
                    'attended' => $attended,
                    'state' => $state,
                    'color' => $color,
                    'status_label' => $b->student_status?->getLabel(),
                    'status_color' => $b->student_status?->getColor() ?? 'gray',
                    'exempt' => $exempt,
                    'fee' => $exempt || ! $b->monthly_fee
                        ? null
                        : number_format((float) $b->monthly_fee, 2).' '.trans('panel.attendance.currency'),
                    'checked_in' => $fmt($today?->checked_in_at),
                    'checked_out' => $fmt($today?->checked_out_at),
                    'sub' => $sub,
                    'sub_color' => $subColor,
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
