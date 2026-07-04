<?php

namespace App\Filament\Teacher\Resources\CourseResource\RelationManagers;

use App\Enum\Course\CourseTypeEnum;
use App\Models\CourseSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return trans('panel.session.plural');
    }

    private function isOnline(): bool
    {
        return $this->getOwnerRecord()->type === CourseTypeEnum::ONLINE;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DateTimePicker::make('starts_at')->label(trans('panel.session.starts_at'))
                ->required()->seconds(false)->native(false),
            Forms\Components\TextInput::make('duration_minutes')->label(trans('panel.session.duration'))
                ->numeric()->default(60)->suffix(trans('panel.session.minutes')),
            Forms\Components\TextInput::make('capacity')->label(trans('panel.session.capacity'))
                ->numeric()->helperText(trans('panel.session.capacity_hint')),
            Forms\Components\Toggle::make('is_active')->label(trans('panel.common.is_active'))->default(true),

            // Offline: location
            Forms\Components\Group::make([
                Forms\Components\TextInput::make('location_ar')->label(trans('panel.session.location').' ('.trans('panel.common.ar').')'),
                Forms\Components\TextInput::make('location_en')->label(trans('panel.session.location').' ('.trans('panel.common.en').')'),
            ])->columns(2)->columnSpanFull()->visible(fn (): bool => ! $this->isOnline()),

            // Online: Zoom link (auto-generated; editable as fallback)
            Forms\Components\TextInput::make('zoom_join_url')->label(trans('panel.session.zoom_join_url'))
                ->url()->columnSpanFull()
                ->helperText(trans('panel.session.zoom_hint'))
                ->visible(fn (): bool => $this->isOnline()),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('starts_at')
            ->defaultSort('starts_at')
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')->label(trans('panel.session.starts_at'))->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')->label(trans('panel.session.duration'))->suffix(' '.trans('panel.session.minutes')),
                Tables\Columns\TextColumn::make('capacity')->label(trans('panel.session.capacity'))
                    ->formatStateUsing(fn ($state, CourseSession $record) => $state === null
                        ? trans('panel.session.unlimited')
                        : ($record->seatsLeft().' / '.$state)),
                Tables\Columns\IconColumn::make('zoom_join_url')->label('Zoom')->boolean()
                    ->trueIcon('heroicon-o-video-camera')->falseIcon('heroicon-o-minus')
                    ->visible(fn (): bool => $this->isOnline()),
                Tables\Columns\IconColumn::make('is_active')->label(trans('panel.common.is_active'))->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(trans('panel.session.add')),
            ])
            ->actions([
                Tables\Actions\Action::make('zoom')->label('Zoom')->icon('heroicon-o-video-camera')->color('success')
                    ->url(fn (CourseSession $record): ?string => $record->zoom_join_url, shouldOpenInNewTab: true)
                    ->visible(fn (CourseSession $record): bool => filled($record->zoom_join_url)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
