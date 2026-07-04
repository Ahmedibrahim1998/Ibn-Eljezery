<?php

namespace App\Filament\Resources;

use App\Enum\Lead\LeadSourceEnum;
use App\Enum\Lead\LeadStatusEnum;
use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.requests');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.lead.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.lead.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.lead.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', LeadStatusEnum::NEW->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.lead.applicant_section'))->schema([
                Forms\Components\TextInput::make('name')->label(trans('panel.lead.name'))->required(),
                Forms\Components\TextInput::make('phone')->label(trans('panel.lead.phone'))->tel()->required(),
                Forms\Components\TextInput::make('email')->label(trans('panel.lead.email'))->email(),
                Forms\Components\TextInput::make('age_group')->label(trans('panel.lead.age_group')),
                Forms\Components\TextInput::make('level')->label(trans('panel.lead.level')),
                Forms\Components\TextInput::make('program')->label(trans('panel.lead.program')),
                Forms\Components\Textarea::make('message')->label(trans('panel.lead.message'))->rows(3)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make(trans('panel.lead.followup_section'))->schema([
                Forms\Components\Select::make('source')->label(trans('panel.lead.source'))->options(LeadSourceEnum::class)->disabled(),
                Forms\Components\Select::make('status')->label(trans('panel.lead.status'))->options(LeadStatusEnum::class)
                    ->default(LeadStatusEnum::NEW)->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(trans('panel.lead.name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label(trans('panel.lead.phone'))->searchable(),
                Tables\Columns\TextColumn::make('program')->label(trans('panel.lead.program'))->limit(25),
                Tables\Columns\TextColumn::make('source')->label(trans('panel.lead.source'))->badge(),
                Tables\Columns\TextColumn::make('status')->label(trans('panel.lead.status'))->badge(),
                Tables\Columns\TextColumn::make('created_at')->label(trans('panel.lead.date'))->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(trans('panel.lead.status'))->options(LeadStatusEnum::class),
                Tables\Filters\SelectFilter::make('source')->label(trans('panel.lead.source'))->options(LeadSourceEnum::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(trans('panel.lead.view_edit')),
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
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
