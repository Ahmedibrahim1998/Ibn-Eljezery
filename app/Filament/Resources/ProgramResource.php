<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.program.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.program.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.program.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.program.section'))->schema([
                Forms\Components\TextInput::make('title_ar')->label(trans('panel.program.title').' ('.trans('panel.common.ar').')')->required()->maxLength(255),
                Forms\Components\TextInput::make('title_en')->label(trans('panel.program.title').' ('.trans('panel.common.en').')')->maxLength(255),
                Forms\Components\TextInput::make('subtitle_ar')->label(trans('panel.program.subtitle').' ('.trans('panel.common.ar').')')->maxLength(255),
                Forms\Components\TextInput::make('subtitle_en')->label(trans('panel.program.subtitle').' ('.trans('panel.common.en').')')->maxLength(255),
                Forms\Components\TextInput::make('price')->label(trans('panel.program.price'))->numeric(),
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('currency_ar')->label(trans('panel.program.currency').' ('.trans('panel.common.ar').')')->default('ر.س / شهر'),
                    Forms\Components\TextInput::make('currency_en')->label(trans('panel.program.currency').' ('.trans('panel.common.en').')')->default('SAR / month'),
                ])->columns(2)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make(trans('panel.program.features_section'))->schema([
                Forms\Components\Repeater::make('features')->label(trans('panel.program.features'))->schema([
                    Forms\Components\TextInput::make('ar')->label(trans('panel.program.feature_text').' ('.trans('panel.common.ar').')')->required(),
                    Forms\Components\TextInput::make('en')->label(trans('panel.program.feature_text').' ('.trans('panel.common.en').')'),
                ])->columns(2)->defaultItems(1)->reorderable()->collapsible(),
            ]),

            Forms\Components\Section::make(trans('panel.common.settings_section'))->schema([
                Forms\Components\TextInput::make('badge_ar')->label(trans('panel.program.badge').' ('.trans('panel.common.ar').')'),
                Forms\Components\TextInput::make('badge_en')->label(trans('panel.program.badge').' ('.trans('panel.common.en').')'),
                Forms\Components\Toggle::make('is_featured')->label(trans('panel.program.is_featured')),
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
                Tables\Columns\TextColumn::make('title_ar')->label(trans('panel.program.title'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('price')->label(trans('panel.program.price'))->money('SAR')->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->label(trans('panel.program.is_featured'))->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label(trans('panel.common.is_active'))->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label(trans('panel.common.sort_order'))->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(trans('panel.common.status_filter')),
                Tables\Filters\TernaryFilter::make('is_featured')->label(trans('panel.program.is_featured')),
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
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
