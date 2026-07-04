<?php

namespace App\Filament\Widgets;

use App\Enum\Lead\LeadSourceEnum;
use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsBySourceChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return trans('panel.charts.by_source');
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        foreach (LeadSourceEnum::cases() as $source) {
            $labels[] = $source->getLabel();
            $data[] = Lead::where('source', $source->value)->count();
        }

        return [
            'datasets' => [[
                'label' => trans('panel.charts.leads_count'),
                'data' => $data,
                'backgroundColor' => ['#198754', '#f59e0b'],
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
