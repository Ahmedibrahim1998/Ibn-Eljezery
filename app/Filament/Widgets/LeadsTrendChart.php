<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class LeadsTrendChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return trans('panel.charts.leads_trend');
    }

    /**
     * Dynamic period filter (number of months back).
     */
    protected function getFilters(): ?array
    {
        return [
            '6' => trans('panel.charts.last_months', ['n' => 6]),
            '12' => trans('panel.charts.last_months', ['n' => 12]),
        ];
    }

    protected function getData(): array
    {
        $months = (int) ($this->filter ?? 6);

        $points = collect(range($months - 1, 0))->map(fn (int $i) => Carbon::now()->startOfMonth()->subMonths($i));

        $counts = $points->map(fn (Carbon $m) => Lead::whereYear('created_at', $m->year)
            ->whereMonth('created_at', $m->month)
            ->count());

        return [
            'datasets' => [
                [
                    'label' => trans('panel.charts.leads_count'),
                    'data' => $counts->all(),
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $points->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
