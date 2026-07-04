<?php

namespace App\Filament\Teacher\Resources;

use App\Enum\Booking\AttendanceEnum;
use App\Enum\Booking\BookingStatusEnum;
use App\Filament\Teacher\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationLabel(): string
    {
        return trans('panel.booking.plural');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.booking.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.booking.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', BookingStatusEnum::PENDING->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * Only bookings that belong to the logged-in teacher's course sessions.
     */
    public static function getEloquentQuery(): Builder
    {
        $teacherId = \App\Filament\Teacher\Resources\CourseResource::currentTeacherId();

        return parent::getEloquentQuery()
            ->whereHas('session.course', fn (Builder $q) => $q->where('teacher_id', $teacherId));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label(trans('panel.booking.name'))->disabled(),
            Forms\Components\TextInput::make('phone')->label(trans('panel.booking.phone'))->disabled(),
            Forms\Components\TextInput::make('email')->label(trans('panel.booking.email'))->disabled(),
            Forms\Components\Textarea::make('notes')->label(trans('panel.booking.notes'))->disabled()->columnSpanFull(),
            Forms\Components\Select::make('status')->label(trans('panel.booking.status'))
                ->options(BookingStatusEnum::class)->required(),
            Forms\Components\Select::make('attendance')->label(trans('panel.booking.attendance'))
                ->options(AttendanceEnum::class)->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(trans('panel.booking.name'))->searchable(),
                Tables\Columns\TextColumn::make('phone')->label(trans('panel.booking.phone'))->searchable(),
                Tables\Columns\TextColumn::make('session.course.title_ar')->label(trans('panel.booking.course'))->limit(22),
                Tables\Columns\TextColumn::make('session.starts_at')->label(trans('panel.booking.session'))->dateTime('Y-m-d H:i'),
                Tables\Columns\TextColumn::make('status')->label(trans('panel.booking.status'))->badge(),
                Tables\Columns\TextColumn::make('attendance')->label(trans('panel.booking.attendance'))->badge(),
                Tables\Columns\TextColumn::make('attended_count')->label(trans('panel.booking.attended_count'))
                    ->badge()->color('success')
                    ->state(fn (Booking $record): int => static::attendedCountForPhone($record)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(trans('panel.booking.status'))->options(BookingStatusEnum::class),
                Tables\Filters\SelectFilter::make('attendance')->label(trans('panel.booking.attendance'))->options(AttendanceEnum::class),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')->label(trans('panel.booking.confirm'))
                    ->icon('heroicon-o-check-circle')->color('success')->button()->size('sm')
                    ->visible(fn (Booking $r) => $r->status !== BookingStatusEnum::CONFIRMED)
                    ->action(fn (Booking $r) => $r->update(['status' => BookingStatusEnum::CONFIRMED])),
                Tables\Actions\Action::make('cancel')->label(trans('panel.booking.cancel'))
                    ->icon('heroicon-o-x-circle')->color('danger')->button()->size('sm')->requiresConfirmation()
                    ->visible(fn (Booking $r) => $r->status !== BookingStatusEnum::CANCELLED)
                    ->action(fn (Booking $r) => $r->update(['status' => BookingStatusEnum::CANCELLED])),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('present')->label(trans('panel.booking.present'))
                        ->icon('heroicon-o-hand-raised')->color('success')
                        ->action(fn (Booking $r) => $r->update(['attendance' => AttendanceEnum::PRESENT])),
                    Tables\Actions\Action::make('absent')->label(trans('panel.booking.absent'))
                        ->icon('heroicon-o-no-symbol')->color('danger')
                        ->action(fn (Booking $r) => $r->update(['attendance' => AttendanceEnum::ABSENT])),
                    Tables\Actions\EditAction::make(),
                ])->label(trans('panel.booking.more'))->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_present')->label(trans('panel.booking.present'))
                        ->icon('heroicon-o-hand-raised')->color('success')->deselectRecordsAfterCompletion()
                        ->action(fn ($records) => $records->each->update(['attendance' => AttendanceEnum::PRESENT])),
                    Tables\Actions\BulkAction::make('mark_absent')->label(trans('panel.booking.absent'))
                        ->icon('heroicon-o-no-symbol')->color('danger')->deselectRecordsAfterCompletion()
                        ->action(fn ($records) => $records->each->update(['attendance' => AttendanceEnum::ABSENT])),
                ]),
            ]);
    }

    /**
     * How many sessions this student (matched by phone) attended across
     * the current teacher's courses.
     */
    private static function attendedCountForPhone(Booking $booking): int
    {
        return static::getEloquentQuery()
            ->where('phone', $booking->phone)
            ->where('attendance', AttendanceEnum::PRESENT->value)
            ->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
