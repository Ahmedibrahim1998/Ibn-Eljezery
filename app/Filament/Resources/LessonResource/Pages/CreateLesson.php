<?php

namespace App\Filament\Resources\LessonResource\Pages;

use App\Enum\Learning\ContentStatusEnum;
use App\Filament\Resources\LessonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;

    /** New lessons always start as a draft written by the current user. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = ContentStatusEnum::DRAFT;
        $data['author_id'] = auth()->id();

        return $data;
    }
}
