<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestLeads extends BaseWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return trans('panel.widgets.latest_leads');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Lead::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(trans('panel.lead.name')),
                Tables\Columns\TextColumn::make('phone')->label(trans('panel.lead.phone')),
                Tables\Columns\TextColumn::make('program')->label(trans('panel.lead.program'))->limit(20),
                Tables\Columns\TextColumn::make('source')->label(trans('panel.lead.source'))->badge(),
                Tables\Columns\TextColumn::make('status')->label(trans('panel.lead.status'))->badge(),
                Tables\Columns\TextColumn::make('created_at')->label(trans('panel.lead.date'))->dateTime('Y-m-d H:i'),
            ])
            ->paginated(false);
    }
}
