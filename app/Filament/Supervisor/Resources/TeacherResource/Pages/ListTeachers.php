<?php

namespace App\Filament\Supervisor\Resources\TeacherResource\Pages;

use App\Filament\Supervisor\Resources\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
