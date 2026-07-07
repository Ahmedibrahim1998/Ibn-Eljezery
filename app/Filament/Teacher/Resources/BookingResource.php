<?php

namespace App\Filament\Teacher\Resources;

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
            ->whereHas('course', fn (Builder $q) => $q->where('teacher_id', $teacherId));
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
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(trans('panel.booking.name'))->searchable(),
                Tables\Columns\TextColumn::make('phone')->label(trans('panel.booking.phone'))->searchable(),
                Tables\Columns\TextColumn::make('course.title_ar')->label(trans('panel.booking.course'))->limit(22),
                Tables\Columns\TextColumn::make('status')->label(trans('panel.booking.status'))->badge(),
                Tables\Columns\TextColumn::make('created_at')->label(trans('panel.booking.date'))->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(trans('panel.booking.status'))->options(BookingStatusEnum::class),
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
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
