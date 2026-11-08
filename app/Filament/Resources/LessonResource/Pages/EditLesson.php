<?php

namespace App\Filament\Resources\LessonResource\Pages;

use App\Enum\Learning\ContentStatusEnum;
use App\Filament\Resources\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    /** Fields whose change means the sharia review must happen again. */
    private const REVIEWED_FIELDS = ['title_ar', 'title_en', 'content_ar', 'content_en', 'audio_url', 'video_url'];

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    /**
     * Editing the text of an approved/published lesson sends it back to draft
     * (new version), so unreviewed text can never reach the app.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $reviewed = in_array($record->status, [ContentStatusEnum::APPROVED, ContentStatusEnum::PUBLISHED], true);

        $changed = collect(self::REVIEWED_FIELDS)
            ->contains(fn (string $field) => array_key_exists($field, $data) && $data[$field] !== $record->{$field});

        if ($reviewed && $changed) {
            $data['status'] = ContentStatusEnum::DRAFT;
            $data['version'] = $record->version + 1;
            $data['published_at'] = null;
        }

        return $data;
    }
}
