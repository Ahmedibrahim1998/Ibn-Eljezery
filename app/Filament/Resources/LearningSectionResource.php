<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningSectionResource\Pages;
use App\Models\LearningSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LearningSectionResource extends Resource
{
    protected static ?string $model = LearningSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.learning');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.learning_section.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.learning_section.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.learning_section.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.learning_section.section'))
                ->description(trans('panel.learning_section.hint'))
                ->schema([
                    Forms\Components\TextInput::make('title_ar')->label(trans('panel.learning_section.title').' ('.trans('panel.common.ar').')')->required()->maxLength(255),
                    Forms\Components\TextInput::make('title_en')->label(trans('panel.learning_section.title').' ('.trans('panel.common.en').')')->maxLength(255),
                    Forms\Components\TextInput::make('slug')->label(trans('panel.learning_section.slug'))->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(100),
                    Forms\Components\TextInput::make('icon')->label(trans('panel.learning_section.icon'))->maxLength(100),
                    Forms\Components\Textarea::make('description_ar')->label(trans('panel.learning_section.description').' ('.trans('panel.common.ar').')')->rows(2),
                    Forms\Components\Textarea::make('description_en')->label(trans('panel.learning_section.description').' ('.trans('panel.common.en').')')->rows(2),
                    Forms\Components\ColorPicker::make('color')->label(trans('panel.learning_section.color')),
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
                Tables\Columns\TextColumn::make('title_ar')->label(trans('panel.learning_section.title'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label(trans('panel.learning_section.slug'))->badge()->color('gray'),
                Tables\Columns\TextColumn::make('subjects_count')->label(trans('panel.learning_section.subjects'))->counts('subjects')->badge()->color('info'),
                Tables\Columns\IconColumn::make('is_active')->label(trans('panel.common.is_active'))->boolean(),
            ])
            ->filters([
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
            'index' => Pages\ListLearningSections::route('/'),
            'create' => Pages\CreateLearningSection::route('/create'),
            'edit' => Pages\EditLearningSection::route('/{record}/edit'),
        ];
    }
}
