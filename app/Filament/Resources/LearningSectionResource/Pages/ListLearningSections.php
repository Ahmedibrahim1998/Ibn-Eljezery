<?php

namespace App\Filament\Resources\LearningSectionResource\Pages;

use App\Filament\Resources\LearningSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningSections extends ListRecords
{
    protected static string $resource = LearningSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
