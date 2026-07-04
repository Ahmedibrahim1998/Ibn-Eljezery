<?php

namespace App\Filament\Resources\WeeklyPlanRowResource\Pages;

use App\Filament\Resources\WeeklyPlanRowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeeklyPlanRows extends ListRecords
{
    protected static string $resource = WeeklyPlanRowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
