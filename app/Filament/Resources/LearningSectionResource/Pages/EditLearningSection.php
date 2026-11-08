<?php

namespace App\Filament\Resources\LearningSectionResource\Pages;

use App\Filament\Resources\LearningSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningSection extends EditRecord
{
    protected static string $resource = LearningSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
