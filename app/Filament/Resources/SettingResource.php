<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.settings');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.setting.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.setting.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.setting.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->label(trans('panel.setting.key'))->disabled()->dehydrated(false),
            Forms\Components\TextInput::make('group')->label(trans('panel.setting.group'))->disabled()->dehydrated(false),
            Forms\Components\Textarea::make('value')->label(trans('panel.setting.value'))->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('group')
            ->defaultSort('group')
            ->columns([
                Tables\Columns\TextColumn::make('key')->label(trans('panel.setting.key'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('value')->label(trans('panel.setting.value'))->limit(60)->wrap(),
                Tables\Columns\TextColumn::make('group')->label(trans('panel.setting.group'))->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')->label(trans('panel.setting.group'))->options([
                    'general' => trans('panel.setting.groups.general'),
                    'hero' => trans('panel.setting.groups.hero'),
                    'stats' => trans('panel.setting.groups.stats'),
                    'about' => trans('panel.setting.groups.about'),
                    'memorization' => trans('panel.setting.groups.memorization'),
                    'contact' => trans('panel.setting.groups.contact'),
                    'nav' => trans('panel.setting.groups.nav'),
                    'sections' => trans('panel.setting.groups.sections'),
                    'forms' => trans('panel.setting.groups.forms'),
                    'footer' => trans('panel.setting.groups.footer'),
                    'programs' => trans('panel.setting.groups.programs'),
                    'limits' => trans('panel.setting.groups.limits'),
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
