<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Faq;
use App\Models\Feature;
use App\Models\Program;
use App\Models\Teacher;
use App\Models\Testimonial;
use Filament\Widgets\ChartWidget;

class ContentOverviewChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return trans('panel.charts.content_overview');
    }

    protected function getData(): array
    {
        return [
            'datasets' => [[
                'label' => trans('panel.charts.records_count'),
                'data' => [
                    Teacher::count(),
                    Program::count(),
                    Course::count(),
                    Testimonial::count(),
                    Faq::count(),
                    Feature::count(),
                ],
                'backgroundColor' => ['#198754', '#0ea5e9', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6'],
            ]],
            'labels' => [
                trans('panel.teacher.plural'),
                trans('panel.program.plural'),
                trans('panel.course.plural'),
                trans('panel.testimonial.plural'),
                trans('panel.faq.plural'),
                trans('panel.feature.plural'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
