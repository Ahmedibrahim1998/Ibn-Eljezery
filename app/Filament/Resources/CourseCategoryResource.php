<?php

namespace App\Filament\Resources;

use App\Enum\Course\CourseTypeEnum;
use App\Filament\Resources\CourseCategoryResource\Pages;
use App\Models\CourseCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseCategoryResource extends Resource
{
    protected static ?string $model = CourseCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.course_category.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.course_category.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.course_category.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.course_category.section'))
                ->description(trans('panel.course_category.hint'))
                ->schema([
                    Forms\Components\Select::make('type')->label(trans('panel.course_category.type'))
                        ->options(CourseTypeEnum::class)->required()->default(CourseTypeEnum::OFFLINE)->native(false),
                    Forms\Components\TextInput::make('title_ar')->label(trans('panel.course_category.title').' ('.trans('panel.common.ar').')')->required()->maxLength(255),
                    Forms\Components\TextInput::make('title_en')->label(trans('panel.course_category.title').' ('.trans('panel.common.en').')')->maxLength(255),
                    Forms\Components\Textarea::make('description_ar')->label(trans('panel.course_category.description').' ('.trans('panel.common.ar').')')->rows(2),
                    Forms\Components\Textarea::make('description_en')->label(trans('panel.course_category.description').' ('.trans('panel.common.en').')')->rows(2),
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
                Tables\Columns\TextColumn::make('title_ar')->label(trans('panel.course_category.title'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label(trans('panel.course_category.type'))->badge(),
                Tables\Columns\TextColumn::make('groups_count')->label(trans('panel.course_category.groups'))->counts('groups')->badge()->color('info'),
                Tables\Columns\IconColumn::make('is_active')->label(trans('panel.common.is_active'))->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label(trans('panel.course_category.type'))->options(CourseTypeEnum::class),
                Tables\Filters\TernaryFilter::make('is_active')->label(trans('panel.common.status_filter')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseCategories::route('/'),
            'create' => Pages\CreateCourseCategory::route('/create'),
            'edit' => Pages\EditCourseCategory::route('/{record}/edit'),
        ];
    }
}
