<?php

namespace App\Filament\Resources;

use App\Enum\Learning\ContentStatusEnum;
use App\Filament\Resources\LessonResource\Pages;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Lessons with the sharia-review workflow (docs/mobile-app/06):
 * writer: draft -> in_review; reviewer: approve / reject with notes; then publish.
 * Status is never edited directly in the form — only through the table actions.
 */
class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    /** Roles allowed to approve / reject / publish. super_admin is always allowed. */
    public const REVIEWER_ROLES = ['super_admin', 'sharia_reviewer'];

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.learning');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.lesson.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.lesson.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.lesson.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Lesson::query()->where('status', ContentStatusEnum::IN_REVIEW->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return trans('panel.lesson.pending_review');
    }

    public static function isReviewer(): bool
    {
        return auth()->user()?->hasAnyRole(self::REVIEWER_ROLES) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.lesson.section'))
                ->description(trans('panel.lesson.hint'))
                ->schema([
                    Forms\Components\Select::make('learning_subject_id')->label(trans('panel.lesson.subject'))
                        ->relationship('subject', 'title_ar')->required()->searchable()->preload()->native(false),
                    Forms\Components\TextInput::make('title_ar')->label(trans('panel.lesson.title').' ('.trans('panel.common.ar').')')->required()->maxLength(255),
                    Forms\Components\TextInput::make('title_en')->label(trans('panel.lesson.title').' ('.trans('panel.common.en').')')->maxLength(255),
                    Forms\Components\RichEditor::make('content_ar')->label(trans('panel.lesson.content').' ('.trans('panel.common.ar').')')->columnSpanFull(),
                    Forms\Components\RichEditor::make('content_en')->label(trans('panel.lesson.content').' ('.trans('panel.common.en').')')->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make(trans('panel.lesson.media'))->schema([
                Forms\Components\TextInput::make('audio_url')->label(trans('panel.lesson.audio_url'))->url()->maxLength(255),
                Forms\Components\TextInput::make('video_url')->label(trans('panel.lesson.video_url'))->url()->maxLength(255),
                Forms\Components\TextInput::make('duration_minutes')->label(trans('panel.lesson.duration'))->numeric()->minValue(1),
                Forms\Components\TextInput::make('sort_order')->label(trans('panel.common.sort_order'))->numeric()->default(0),
            ])->columns(2)->collapsible(),

            Forms\Components\Section::make(trans('panel.lesson.review_section'))
                ->schema([
                    Forms\Components\Placeholder::make('status_view')->label(trans('panel.lesson.status'))
                        ->content(fn (?Lesson $record) => ($record?->status ?? ContentStatusEnum::DRAFT)->getLabel()),
                    Forms\Components\Placeholder::make('version_view')->label(trans('panel.lesson.version'))
                        ->content(fn (?Lesson $record) => $record?->version ?? 1),
                    Forms\Components\Placeholder::make('reviewer_view')->label(trans('panel.lesson.reviewed_by'))
                        ->content(fn (?Lesson $record) => $record?->reviewer?->name ?? '—'),
                    Forms\Components\Placeholder::make('notes_view')->label(trans('panel.lesson.review_notes'))
                        ->content(fn (?Lesson $record) => $record?->review_notes ?: '—'),
                ])->columns(2)->hiddenOn('create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')->label(trans('panel.lesson.title'))->searchable()->sortable()->limit(50),
                Tables\Columns\TextColumn::make('subject.title_ar')->label(trans('panel.lesson.subject'))->badge()->color('gray'),
                Tables\Columns\TextColumn::make('subject.madhhab')->label(trans('panel.learning_subject.madhhab'))->badge(),
                Tables\Columns\TextColumn::make('status')->label(trans('panel.lesson.status'))->badge(),
                Tables\Columns\TextColumn::make('author.name')->label(trans('panel.lesson.author'))->toggleable(),
                Tables\Columns\TextColumn::make('published_at')->label(trans('panel.lesson.published_at'))->dateTime()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(trans('panel.lesson.status'))->options(ContentStatusEnum::class),
                Tables\Filters\SelectFilter::make('learning_subject_id')->label(trans('panel.lesson.subject'))->relationship('subject', 'title_ar'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('submit')->label(trans('panel.lesson.submit'))
                    ->icon('heroicon-o-paper-airplane')->color('warning')->requiresConfirmation()
                    ->visible(fn (Lesson $record) => in_array($record->status, [ContentStatusEnum::DRAFT, ContentStatusEnum::REJECTED], true))
                    ->action(fn (Lesson $record) => $record->update(['status' => ContentStatusEnum::IN_REVIEW])),

                Tables\Actions\Action::make('approve')->label(trans('panel.lesson.approve'))
                    ->icon('heroicon-o-check-badge')->color('info')->requiresConfirmation()
                    ->visible(fn (Lesson $record) => $record->status === ContentStatusEnum::IN_REVIEW && self::isReviewer())
                    ->action(fn (Lesson $record) => $record->update([
                        'status' => ContentStatusEnum::APPROVED,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),

                Tables\Actions\Action::make('reject')->label(trans('panel.lesson.reject'))
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (Lesson $record) => $record->status === ContentStatusEnum::IN_REVIEW && self::isReviewer())
                    ->form([
                        Forms\Components\Textarea::make('review_notes')->label(trans('panel.lesson.review_notes'))->required()->rows(4),
                    ])
                    ->action(fn (Lesson $record, array $data) => $record->update([
                        'status' => ContentStatusEnum::REJECTED,
                        'review_notes' => $data['review_notes'],
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),

                Tables\Actions\Action::make('publish')->label(trans('panel.lesson.publish'))
                    ->icon('heroicon-o-rocket-launch')->color('success')->requiresConfirmation()
                    ->visible(fn (Lesson $record) => $record->status === ContentStatusEnum::APPROVED && self::isReviewer())
                    ->action(fn (Lesson $record) => $record->update([
                        'status' => ContentStatusEnum::PUBLISHED,
                        'published_at' => now(),
                    ])),

                Tables\Actions\Action::make('unpublish')->label(trans('panel.lesson.unpublish'))
                    ->icon('heroicon-o-eye-slash')->color('gray')->requiresConfirmation()
                    ->visible(fn (Lesson $record) => $record->status === ContentStatusEnum::PUBLISHED && self::isReviewer())
                    ->action(fn (Lesson $record) => $record->update(['status' => ContentStatusEnum::APPROVED])),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
