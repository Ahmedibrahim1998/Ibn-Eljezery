<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeeklyPlanRowResource\Pages;
use App\Models\WeeklyPlanRow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WeeklyPlanRowResource extends Resource
{
    protected static ?string $model = WeeklyPlanRow::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.weekly_plan.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.weekly_plan.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.weekly_plan.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.weekly_plan.section'))->schema([
                Forms\Components\TextInput::make('day_ar')->label(trans('panel.weekly_plan.day').' ('.trans('panel.common.ar').')')->required(),
                Forms\Components\TextInput::make('day_en')->label(trans('panel.weekly_plan.day').' ('.trans('panel.common.en').')'),
                Forms\Components\TextInput::make('new_memorization_ar')->label(trans('panel.weekly_plan.new_memorization').' ('.trans('panel.common.ar').')'),
                Forms\Components\TextInput::make('new_memorization_en')->label(trans('panel.weekly_plan.new_memorization').' ('.trans('panel.common.en').')'),
                Forms\Components\TextInput::make('review_ar')->label(trans('panel.weekly_plan.review').' ('.trans('panel.common.ar').')'),
                Forms\Components\TextInput::make('review_en')->label(trans('panel.weekly_plan.review').' ('.trans('panel.common.en').')'),
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
                Tables\Columns\TextColumn::make('day_ar')->label(trans('panel.weekly_plan.day')),
                Tables\Columns\TextColumn::make('new_memorization_ar')->label(trans('panel.weekly_plan.new_memorization'))->limit(30),
                Tables\Columns\TextColumn::make('review_ar')->label(trans('panel.weekly_plan.review'))->limit(30),
                Tables\Columns\IconColumn::make('is_active')->label(trans('panel.common.is_active'))->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label(trans('panel.common.sort_order'))->sortable(),
            ])
            ->filters([
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
            'index' => Pages\ListWeeklyPlanRows::route('/'),
            'create' => Pages\CreateWeeklyPlanRow::route('/create'),
            'edit' => Pages\EditWeeklyPlanRow::route('/{record}/edit'),
        ];
    }
}
