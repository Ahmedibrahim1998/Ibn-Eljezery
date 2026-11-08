<?php

namespace App\Filament\Resources\LearningSubjectResource\Pages;

use App\Filament\Resources\LearningSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningSubject extends EditRecord
{
    protected static string $resource = LearningSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
