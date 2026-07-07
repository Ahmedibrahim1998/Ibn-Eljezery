<?php

namespace App\Filament\Resources;

use App\Enum\Course\CourseTypeEnum;
use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.course.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.course.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.course.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.course.section'))->schema([
                Forms\Components\Select::make('type')->label(trans('panel.course.type'))
                    ->options(CourseTypeEnum::class)->required()->default(CourseTypeEnum::OFFLINE),
                Forms\Components\Select::make('teacher_id')->label(trans('panel.teacher.label'))
                    ->relationship('teacher', 'name_ar')->searchable()->preload(),
                Forms\Components\Select::make('supervisor_id')->label(trans('panel.supervisor.assign'))
                    ->options(fn () => \App\Models\User::role('supervisor')->pluck('name', 'id'))
                    ->searchable()->preload(),
                Forms\Components\TextInput::make('title_ar')->label(trans('panel.course.title').' ('.trans('panel.common.ar').')')->required()->maxLength(255),
                Forms\Components\TextInput::make('title_en')->label(trans('panel.course.title').' ('.trans('panel.common.en').')')->maxLength(255),
                Forms\Components\Textarea::make('description_ar')->label(trans('panel.course.description').' ('.trans('panel.common.ar').')')->rows(3),
                Forms\Components\Textarea::make('description_en')->label(trans('panel.course.description').' ('.trans('panel.common.en').')')->rows(3),
                Forms\Components\TextInput::make('badge_ar')->label(trans('panel.course.badge').' ('.trans('panel.common.ar').')'),
                Forms\Components\TextInput::make('badge_en')->label(trans('panel.course.badge').' ('.trans('panel.common.en').')'),
                Forms\Components\TextInput::make('duration_months')->label(trans('panel.course.duration_months'))
                    ->numeric()->minValue(1)->maxValue(60)->suffix(trans('panel.course.months'))
                    ->helperText(trans('panel.course.duration_hint')),
                Forms\Components\DatePicker::make('enrollment_deadline')->label(trans('panel.course.enrollment_deadline'))
                    ->native(false)->minDate(today())
                    ->helperText(trans('panel.course.enrollment_deadline_hint')),
            ])->columns(2),

            Forms\Components\Section::make(trans('panel.course.items_section'))->schema([
                Forms\Components\Repeater::make('items')->label(trans('panel.course.items'))->schema([
                    Forms\Components\TextInput::make('ar')->label(trans('panel.course.item_text').' ('.trans('panel.common.ar').')')->required(),
                    Forms\Components\TextInput::make('en')->label(trans('panel.course.item_text').' ('.trans('panel.common.en').')'),
                ])->columns(2)->defaultItems(1)->reorderable()->collapsible(),
            ]),

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
                Tables\Columns\TextColumn::make('title_ar')->label(trans('panel.course.title'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label(trans('panel.course.type'))->badge(),
                Tables\Columns\IconColumn::make('is_active')->label(trans('panel.common.is_active'))->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label(trans('panel.common.sort_order'))->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label(trans('panel.course.type'))->options(CourseTypeEnum::class),
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
