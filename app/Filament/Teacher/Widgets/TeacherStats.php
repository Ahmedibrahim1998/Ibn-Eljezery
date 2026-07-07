<?php

namespace App\Filament\Teacher\Widgets;

use App\Enum\Booking\BookingStatusEnum;
use App\Filament\Teacher\Resources\CourseResource;
use App\Models\Booking;
use App\Models\Course;
use App\Models\CourseSession;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class TeacherStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $teacherId = CourseResource::currentTeacherId();

        $courses = Course::where('teacher_id', $teacherId)->count();

        $upcoming = CourseSession::whereHas('course', fn (Builder $q) => $q->where('teacher_id', $teacherId))
            ->where('starts_at', '>=', now())
            ->count();

        $bookingsQuery = fn () => Booking::whereHas('course', fn (Builder $q) => $q->where('teacher_id', $teacherId));

        $pending = $bookingsQuery()->where('status', BookingStatusEnum::PENDING->value)->count();
        $confirmed = $bookingsQuery()->where('status', BookingStatusEnum::CONFIRMED->value)->count();

        return [
            Stat::make(trans('panel.widgets.teacher_courses'), $courses)->icon('heroicon-m-book-open')->color('info'),
            Stat::make(trans('panel.widgets.upcoming_sessions'), $upcoming)->icon('heroicon-m-calendar-days')->color('info'),
            Stat::make(trans('panel.widgets.pending_bookings'), $pending)->icon('heroicon-m-clock')->color($pending > 0 ? 'warning' : 'gray'),
            Stat::make(trans('panel.widgets.confirmed_bookings'), $confirmed)->icon('heroicon-m-check-circle')->color('success'),
        ];
    }
}
