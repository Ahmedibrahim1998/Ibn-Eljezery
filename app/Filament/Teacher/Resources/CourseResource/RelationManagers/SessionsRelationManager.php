<?php

namespace App\Filament\Teacher\Resources\CourseResource\RelationManagers;

use App\Enum\Course\CourseTypeEnum;
use App\Models\CourseSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

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
                Tables\Columns\IconColumn::make('zoom_join_url')->label('Zoom')->boolean()
                    ->trueIcon('heroicon-o-video-camera')->falseIcon('heroicon-o-minus')
                    ->visible(fn (): bool => $this->isOnline()),
                Tables\Columns\IconColumn::make('is_active')->label(trans('panel.common.is_active'))->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(trans('panel.session.add')),

                // Generate a whole run of sessions at once (e.g. weekly for N weeks)
                // instead of adding them one by one.
                Tables\Actions\Action::make('generate')
                    ->label(trans('panel.session.generate'))
                    ->icon('heroicon-o-squares-plus')
                    ->color('success')
                    ->modalHeading(trans('panel.session.generate'))
                    ->modalSubmitActionLabel(trans('panel.session.generate'))
                    ->form([
                        Forms\Components\DateTimePicker::make('starts_at')->label(trans('panel.session.first_at'))
                            ->required()->seconds(false)->native(false),
                        Forms\Components\TextInput::make('count')->label(trans('panel.session.count'))
                            ->numeric()->minValue(1)->maxValue(60)->default(8)->required(),
                        Forms\Components\Select::make('interval_days')->label(trans('panel.session.repeat'))
                            ->options([
                                1 => trans('panel.session.daily'),
                                2 => trans('panel.session.every_2_days'),
                                7 => trans('panel.session.weekly'),
                                14 => trans('panel.session.biweekly'),
                            ])->default(7)->required()->native(false),
                        Forms\Components\TextInput::make('duration_minutes')->label(trans('panel.session.duration'))
                            ->numeric()->default(60)->suffix(trans('panel.session.minutes')),
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('location_ar')->label(trans('panel.session.location').' ('.trans('panel.common.ar').')'),
                            Forms\Components\TextInput::make('location_en')->label(trans('panel.session.location').' ('.trans('panel.common.en').')'),
                        ])->columns(2)->visible(fn (): bool => ! $this->isOnline()),
                    ])
                    ->action(function (array $data): void {
                        $start = Carbon::parse($data['starts_at']);
                        $interval = (int) $data['interval_days'];
                        $count = (int) $data['count'];

                        for ($i = 0; $i < $count; $i++) {
                            $this->getOwnerRecord()->sessions()->create([
                                'starts_at' => $start->copy()->addDays($i * $interval),
                                'duration_minutes' => $data['duration_minutes'] ?? 60,
                                'location_ar' => $data['location_ar'] ?? null,
                                'location_en' => $data['location_en'] ?? null,
                                'is_active' => true,
                            ]);
                        }

                        Notification::make()->success()
                            ->title(trans('panel.session.generated', ['count' => $count]))
                            ->send();
                    }),
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
