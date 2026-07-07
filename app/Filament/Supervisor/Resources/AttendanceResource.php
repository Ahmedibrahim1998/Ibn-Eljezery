<?php

namespace App\Filament\Supervisor\Resources;

use App\Filament\Supervisor\Resources\AttendanceResource\Pages;
use App\Models\Booking;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AttendanceResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return trans('panel.attendance.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.attendance.student');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.attendance.title');
    }

    /** IDs of the courses supervised by the logged-in user. */
    public static function supervisedCourseIds(): array
    {
        return Course::where('supervisor_id', Auth::id())->pluck('id')->all();
    }

    /** Enrollments within the supervisor's courses (excluding cancelled). */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('course_id', static::supervisedCourseIds())
            ->where('status', '!=', \App\Enum\Booking\BookingStatusEnum::CANCELLED->value);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('course_id')->label(trans('panel.attendance.course'))
                ->options(fn () => Course::whereIn('id', static::supervisedCourseIds())->pluck('title_ar', 'id'))
                ->searchable()->required(),
            Forms\Components\TextInput::make('name')->label(trans('panel.attendance.student'))->required(),
            Forms\Components\TextInput::make('phone')->label(trans('panel.attendance.phone'))->required(),
            Forms\Components\TextInput::make('email')->label(trans('panel.booking.email'))->email(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            // Students are grouped under their course so the supervisor takes
            // attendance one course at a time instead of one long mixed list.
            // The group header shows the course NAME + type, so several offline
            // (or several online) courses stay distinguishable by their name.
            ->groups([
                Group::make('course.title_ar')
                    ->label(trans('panel.attendance.course'))
                    ->getTitleFromRecordUsing(fn (Booking $record): string => trim(
                        ($record->course?->localized('title') ?? '—')
                        .' — '
                        .($record->course?->type?->getLabel() ?? '')
                    ))
                    ->collapsible(),
            ])
            ->defaultGroup('course.title_ar')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(trans('panel.attendance.student'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label(trans('panel.attendance.phone'))->searchable(),
                Tables\Columns\TextColumn::make('today_state')->label(trans('panel.attendance.today_state'))->badge()
                    ->state(function (Booking $r): string {
                        $a = $r->todayAttendance();
                        if ($a?->checked_out_at) {
                            return trans('panel.attendance.left');
                        }

                        return $a?->checked_in_at ? trans('panel.attendance.present') : trans('panel.attendance.not_today');
                    })
                    ->color(function (Booking $r): string {
                        $a = $r->todayAttendance();
                        if ($a?->checked_out_at) {
                            return 'info';
                        }

                        return $a?->checked_in_at ? 'success' : 'gray';
                    }),
                Tables\Columns\TextColumn::make('attended_days')->label(trans('panel.attendance.attended_days'))
                    ->badge()->color('success')->state(fn (Booking $r): int => $r->attendedDays()),
                Tables\Columns\TextColumn::make('subscription')->label(trans('panel.attendance.subscription'))->badge()
                    ->state(fn (Booking $r): string => $r->attendedDays() === 0
                        ? '—'
                        : ($r->paymentDue() ? trans('panel.attendance.unpaid') : trans('panel.attendance.up_to_date')))
                    ->color(fn (Booking $r): string => $r->paymentDue() ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')->label(trans('panel.attendance.course'))
                    ->options(fn () => Course::whereIn('id', static::supervisedCourseIds())->pluck('title_ar', 'id')),
            ])
            ->actions([
                Tables\Actions\Action::make('check_in')->label(trans('panel.attendance.check_in'))
                    ->icon('heroicon-o-arrow-right-on-rectangle')->color('success')->button()->size('sm')
                    ->visible(fn (Booking $r): bool => ! optional($r->todayAttendance())->checked_in_at)
                    ->action(function (Booking $r): void {
                        $a = $r->attendances()->firstOrCreate(['attended_on' => today()]);
                        if (! $a->checked_in_at) {
                            $a->update(['checked_in_at' => now()]);
                        }
                    }),
                Tables\Actions\Action::make('check_out')->label(trans('panel.attendance.check_out'))
                    ->icon('heroicon-o-arrow-left-on-rectangle')->color('warning')->button()->size('sm')
                    ->visible(fn (Booking $r): bool => (bool) optional($r->todayAttendance())->checked_in_at
                        && ! optional($r->todayAttendance())->checked_out_at)
                    ->action(fn (Booking $r) => optional($r->todayAttendance())->update(['checked_out_at' => now()])),
                Tables\Actions\Action::make('mark_paid')->label(trans('panel.attendance.mark_paid'))
                    ->icon('heroicon-o-banknotes')->color('primary')->button()->size('sm')->requiresConfirmation()
                    ->visible(fn (Booking $r): bool => $r->paymentDue())
                    ->action(function (Booking $r): void {
                        $r->recordPayment(Auth::id());
                        Notification::make()->success()->title(trans('panel.attendance.paid_done'))->send();
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('reset_today')->label(trans('panel.attendance.reset'))
                        ->icon('heroicon-o-arrow-uturn-left')->color('gray')
                        ->visible(fn (Booking $r): bool => (bool) $r->todayAttendance())
                        ->action(fn (Booking $r) => optional($r->todayAttendance())->delete()),
                    Tables\Actions\DeleteAction::make(),
                ])->label(trans('panel.booking.more'))->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('check_in_all')->label(trans('panel.attendance.check_in'))
                        ->icon('heroicon-o-arrow-right-on-rectangle')->color('success')->deselectRecordsAfterCompletion()
                        ->action(fn ($records) => $records->each(function (Booking $r): void {
                            $a = $r->attendances()->firstOrCreate(['attended_on' => today()]);
                            if (! $a->checked_in_at) {
                                $a->update(['checked_in_at' => now()]);
                            }
                        })),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendance::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
        ];
    }
}
