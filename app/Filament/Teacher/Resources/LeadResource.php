<?php

namespace App\Filament\Teacher\Resources;

use App\Enum\Lead\LeadSourceEnum;
use App\Enum\Lead\LeadStatusEnum;
use App\Filament\Teacher\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return trans('panel.lead.course_requests');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.lead.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.lead.course_requests');
    }

    // Lead has an admin Shield policy; this panel only READS its own course
    // requests, so bypass that policy (panel access is already gated by role).
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', LeadStatusEnum::NEW->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** Only leads whose chosen course belongs to the logged-in teacher. */
    public static function getEloquentQuery(): Builder
    {
        $teacherId = \App\Filament\Teacher\Resources\CourseResource::currentTeacherId();

        return parent::getEloquentQuery()
            ->whereHas('course', fn (Builder $q) => $q->where('teacher_id', $teacherId));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label(trans('panel.lead.name'))->disabled(),
            Forms\Components\TextInput::make('phone')->label(trans('panel.lead.phone'))->disabled(),
            Forms\Components\TextInput::make('email')->label(trans('panel.lead.email'))->disabled(),
            Forms\Components\TextInput::make('course.title_ar')->label(trans('panel.lead.course'))->disabled(),
            Forms\Components\TextInput::make('age_group')->label(trans('panel.lead.age_group'))->disabled(),
            Forms\Components\TextInput::make('level')->label(trans('panel.lead.level'))->disabled(),
            Forms\Components\Textarea::make('message')->label(trans('panel.lead.message'))->disabled()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(trans('panel.lead.name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label(trans('panel.lead.phone'))->searchable(),
                Tables\Columns\TextColumn::make('course.title_ar')->label(trans('panel.lead.course'))->limit(22),
                Tables\Columns\TextColumn::make('status')->label(trans('panel.lead.status'))->badge(),
                Tables\Columns\TextColumn::make('created_at')->label(trans('panel.lead.date'))->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(trans('panel.lead.status'))->options(LeadStatusEnum::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
        ];
    }
}
