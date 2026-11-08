<?php

namespace App\Filament\Resources\LearningSubjectResource\Pages;

use App\Filament\Resources\LearningSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningSubjects extends ListRecords
{
    protected static string $resource = LearningSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
