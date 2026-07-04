<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeatureResource\Pages;
use App\Models\Feature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.feature.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.feature.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.feature.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.feature.section'))->schema([
                Forms\Components\TextInput::make('icon')->label(trans('panel.feature.icon'))->placeholder('📖')->maxLength(20),
                Forms\Components\TextInput::make('title_ar')->label(trans('panel.feature.title').' ('.trans('panel.common.ar').')')->required(),
                Forms\Components\TextInput::make('title_en')->label(trans('panel.feature.title').' ('.trans('panel.common.en').')'),
                Forms\Components\TextInput::make('description_ar')->label(trans('panel.feature.description').' ('.trans('panel.common.ar').')'),
                Forms\Components\TextInput::make('description_en')->label(trans('panel.feature.description').' ('.trans('panel.common.en').')'),
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
                Tables\Columns\TextColumn::make('icon')->label(trans('panel.feature.icon')),
                Tables\Columns\TextColumn::make('title_ar')->label(trans('panel.feature.title'))->searchable(),
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
            'index' => Pages\ListFeatures::route('/'),
            'create' => Pages\CreateFeature::route('/create'),
            'edit' => Pages\EditFeature::route('/{record}/edit'),
        ];
    }
}
