<?php

namespace App\Filament\Widgets;

use App\Enum\Lead\LeadStatusEnum;
use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return trans('panel.charts.by_status');
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];
        $colors = [
            LeadStatusEnum::NEW->value => '#0ea5e9',
            LeadStatusEnum::CONTACTED->value => '#f59e0b',
            LeadStatusEnum::ENROLLED->value => '#22c55e',
            LeadStatusEnum::REJECTED->value => '#ef4444',
        ];
        $bg = [];

        foreach (LeadStatusEnum::cases() as $status) {
            $labels[] = $status->getLabel();
            $data[] = Lead::where('status', $status->value)->count();
            $bg[] = $colors[$status->value] ?? '#94a3b8';
        }

        return [
            'datasets' => [[
                'label' => trans('panel.charts.leads_count'),
                'data' => $data,
                'backgroundColor' => $bg,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
