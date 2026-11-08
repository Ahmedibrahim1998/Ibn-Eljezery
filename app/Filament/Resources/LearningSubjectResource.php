<?php

namespace App\Filament\Resources;

use App\Enum\Learning\LevelEnum;
use App\Enum\Learning\MadhhabEnum;
use App\Filament\Resources\LearningSubjectResource\Pages;
use App\Models\LearningSubject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LearningSubjectResource extends Resource
{
    protected static ?string $model = LearningSubject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.learning');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.learning_subject.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.learning_subject.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.learning_subject.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.learning_subject.section'))->schema([
                Forms\Components\Select::make('learning_section_id')->label(trans('panel.learning_subject.learning_section'))
                    ->relationship('section', 'title_ar')->required()->preload()->native(false),
                Forms\Components\Select::make('madhhab')->label(trans('panel.learning_subject.madhhab'))
                    ->options(MadhhabEnum::class)->native(false),
                Forms\Components\TextInput::make('title_ar')->label(trans('panel.learning_subject.title').' ('.trans('panel.common.ar').')')->required()->maxLength(255),
                Forms\Components\TextInput::make('title_en')->label(trans('panel.learning_subject.title').' ('.trans('panel.common.en').')')->maxLength(255),
                Forms\Components\TextInput::make('author_ar')->label(trans('panel.learning_subject.author').' ('.trans('panel.common.ar').')')->maxLength(255),
                Forms\Components\TextInput::make('author_en')->label(trans('panel.learning_subject.author').' ('.trans('panel.common.en').')')->maxLength(255),
                Forms\Components\Textarea::make('description_ar')->label(trans('panel.learning_subject.description').' ('.trans('panel.common.ar').')')->rows(2),
                Forms\Components\Textarea::make('description_en')->label(trans('panel.learning_subject.description').' ('.trans('panel.common.en').')')->rows(2),
                Forms\Components\Select::make('level')->label(trans('panel.learning_subject.level'))
                    ->options(LevelEnum::class)->required()->default(LevelEnum::BEGINNER)->native(false),
                Forms\Components\FileUpload::make('cover_image')->label(trans('panel.learning_subject.cover_image'))->image()->directory('learning')->imageEditor(),
            ])->columns(2),

            Forms\Components\Section::make(trans('panel.common.settings_section'))->schema([
                Forms\Components\TextInput::make('sort_order')->label(trans('panel.common.sort_order'))->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label(trans('panel.common.is_active'))->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')->label(trans('panel.learning_subject.title'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('section.title_ar')->label(trans('panel.learning_subject.learning_section'))->badge()->color('gray'),
                Tables\Columns\TextColumn::make('madhhab')->label(trans('panel.learning_subject.madhhab'))->badge(),
                Tables\Columns\TextColumn::make('level')->label(trans('panel.learning_subject.level'))->badge(),
                Tables\Columns\TextColumn::make('lessons_count')->label(trans('panel.learning_subject.lessons'))->counts('lessons')->badge()->color('info'),
                Tables\Columns\IconColumn::make('is_active')->label(trans('panel.common.is_active'))->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('learning_section_id')->label(trans('panel.learning_subject.learning_section'))->relationship('section', 'title_ar'),
                Tables\Filters\SelectFilter::make('madhhab')->label(trans('panel.learning_subject.madhhab'))->options(MadhhabEnum::class),
                Tables\Filters\SelectFilter::make('level')->label(trans('panel.learning_subject.level'))->options(LevelEnum::class),
                Tables\Filters\TernaryFilter::make('is_active')->label(trans('panel.common.status_filter')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningSubjects::route('/'),
            'create' => Pages\CreateLearningSubject::route('/create'),
            'edit' => Pages\EditLearningSubject::route('/{record}/edit'),
        ];
    }
}
