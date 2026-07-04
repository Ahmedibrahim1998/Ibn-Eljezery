<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.faq.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.faq.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.faq.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.faq.section'))->schema([
                Forms\Components\TextInput::make('question_ar')->label(trans('panel.faq.question').' ('.trans('panel.common.ar').')')->required(),
                Forms\Components\TextInput::make('question_en')->label(trans('panel.faq.question').' ('.trans('panel.common.en').')'),
                Forms\Components\Textarea::make('answer_ar')->label(trans('panel.faq.answer').' ('.trans('panel.common.ar').')')->required()->rows(3),
                Forms\Components\Textarea::make('answer_en')->label(trans('panel.faq.answer').' ('.trans('panel.common.en').')')->rows(3),
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
                Tables\Columns\TextColumn::make('question_ar')->label(trans('panel.faq.question'))->searchable()->limit(50),
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
