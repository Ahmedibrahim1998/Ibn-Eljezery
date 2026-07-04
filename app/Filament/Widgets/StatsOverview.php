<?php

namespace App\Filament\Widgets;

use App\Enum\Lead\LeadStatusEnum;
use App\Models\Course;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Teacher;
use App\Models\Testimonial;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $newLeads = Lead::where('status', LeadStatusEnum::NEW->value)->count();

        return [
            Stat::make(trans('panel.widgets.new_leads'), $newLeads)
                ->description(trans('panel.widgets.new_leads_desc'))
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color($newLeads > 0 ? 'warning' : 'gray'),

            Stat::make(trans('panel.widgets.total_leads'), Lead::count())
                ->description(trans('panel.widgets.total_leads_desc'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make(trans('panel.widgets.teachers'), Teacher::count())
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make(trans('panel.widgets.programs'), Program::count())
                ->descriptionIcon('heroicon-m-rectangle-group')
                ->color('info'),

            Stat::make(trans('panel.widgets.courses'), Course::count())
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),

            Stat::make(trans('panel.widgets.testimonials'), Testimonial::count())
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),

            Stat::make(trans('panel.widgets.faqs'), Faq::count())
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('info'),
        ];
    }
}
