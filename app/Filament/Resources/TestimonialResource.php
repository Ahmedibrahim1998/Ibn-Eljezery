<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.testimonial.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.testimonial.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.testimonial.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.testimonial.section'))->schema([
                Forms\Components\Textarea::make('body_ar')->label(trans('panel.testimonial.body').' ('.trans('panel.common.ar').')')->required()->rows(3),
                Forms\Components\Textarea::make('body_en')->label(trans('panel.testimonial.body').' ('.trans('panel.common.en').')')->rows(3),
                Forms\Components\TextInput::make('author_name_ar')->label(trans('panel.testimonial.author_name').' ('.trans('panel.common.ar').')')->required(),
                Forms\Components\TextInput::make('author_name_en')->label(trans('panel.testimonial.author_name').' ('.trans('panel.common.en').')'),
                Forms\Components\TextInput::make('author_role_ar')->label(trans('panel.testimonial.author_role').' ('.trans('panel.common.ar').')'),
                Forms\Components\TextInput::make('author_role_en')->label(trans('panel.testimonial.author_role').' ('.trans('panel.common.en').')'),
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
                Tables\Columns\TextColumn::make('author_name_ar')->label(trans('panel.testimonial.author_name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('author_role_ar')->label(trans('panel.testimonial.author_role'))->limit(30),
                Tables\Columns\TextColumn::make('body_ar')->label(trans('panel.testimonial.body'))->limit(40),
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
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
