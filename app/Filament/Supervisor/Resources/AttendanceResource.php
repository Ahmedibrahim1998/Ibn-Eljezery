<?php

namespace App\Filament\Supervisor\Resources;

use App\Enum\Booking\StudentStatusEnum;
use App\Filament\Supervisor\Resources\AttendanceResource\Pages;
use App\Models\Booking;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
            Forms\Components\Select::make('student_status')->label(trans('panel.attendance.student_status'))
                ->options(StudentStatusEnum::class)->default(StudentStatusEnum::REGULAR)
                ->required()->live()->native(false)
                ->helperText(trans('panel.attendance.student_status_hint')),
            Forms\Components\TextInput::make('monthly_fee')->label(trans('panel.attendance.monthly_fee'))
                ->numeric()->minValue(0)->suffix(trans('panel.attendance.currency'))
                ->visible(function (Get $get): bool {
                    $status = $get('student_status');
                    $status = $status instanceof StudentStatusEnum ? $status : StudentStatusEnum::tryFrom((string) $status);

                    return ! ($status?->isExempt() ?? false);
                }),
        ])->columns(2);
    }

    /** Locale-aware 12-hour time label, e.g. "2:30 م" / "2:30 PM" (or —). */
    protected static function timeLabel($dt): string
    {
        return $dt ? $dt->locale(app()->getLocale())->translatedFormat('g:i A') : '—';
    }

    protected static function subscriptionState(Booking $r): string
    {
        if ($r->isExempt()) {
            return trans('panel.attendance.exempt');
        }
        if ($r->attendedDays() === 0) {
            return '—';
        }
        if ($r->paymentDue()) {
            return trans('panel.attendance.unpaid');
        }

        $month = $r->lastPaidMonthLabel();

        return $month
            ? trans('panel.attendance.paid_month', ['month' => $month])
            : trans('panel.attendance.up_to_date');
    }

    protected static function subscriptionColor(Booking $r): string
    {
        if ($r->isExempt() || $r->attendedDays() === 0) {
            return 'gray';
        }

        return $r->paymentDue() ? 'danger' : 'success';
    }

    protected static function feeLabel(Booking $r): string
    {
        if ($r->isExempt()) {
            return trans('panel.attendance.exempt');
        }

        return $r->monthly_fee
            ? number_format((float) $r->monthly_fee, 2).' '.trans('panel.attendance.currency')
            : '—';
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
                Tables\Columns\TextColumn::make('phone')->label(trans('panel.attendance.phone'))->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('student_status')->label(trans('panel.attendance.student_status'))->badge(),
                Tables\Columns\TextColumn::make('monthly_fee')->label(trans('panel.attendance.monthly_fee'))
                    ->state(fn (Booking $r): string => static::feeLabel($r))->toggleable(),
                Tables\Columns\TextColumn::make('today_state')->label(trans('panel.attendance.today_state'))->badge()
                    ->state(function (Booking $r): string {
                        $a = $r->todayAttendance();
                        if (! $a?->checked_in_at) {
                            return trans('panel.attendance.not_today');
                        }
                        // Show a "double" marker when today counted for two classes.
                        $double = (int) $a->classes_count === 2 ? ' '.trans('panel.attendance.double_tag') : '';
                        if ($a->checked_out_at) {
                            return trans('panel.attendance.left').$double;
                        }

                        return trans('panel.attendance.present').$double;
                    })
                    ->color(function (Booking $r): string {
                        $a = $r->todayAttendance();
                        if ($a?->checked_out_at) {
                            return 'info';
                        }

                        return $a?->checked_in_at ? 'success' : 'gray';
                    }),
                Tables\Columns\TextColumn::make('checked_in')->label(trans('panel.attendance.checked_in_at'))
                    ->state(fn (Booking $r): string => static::timeLabel($r->todayAttendance()?->checked_in_at)),
                Tables\Columns\TextColumn::make('checked_out')->label(trans('panel.attendance.checked_out_at'))
                    ->state(fn (Booking $r): string => static::timeLabel($r->todayAttendance()?->checked_out_at))->toggleable(),
                Tables\Columns\TextColumn::make('attended_days')->label(trans('panel.attendance.attended_days'))
                    ->badge()->color('success')->state(fn (Booking $r): int => $r->attendedDays()),
                Tables\Columns\TextColumn::make('subscription')->label(trans('panel.attendance.subscription'))->badge()
                    ->state(fn (Booking $r): string => static::subscriptionState($r))
                    ->color(fn (Booking $r): string => static::subscriptionColor($r)),
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
                            $a->update(['checked_in_at' => now(), 'classes_count' => 1]);
                        }
                    }),
                // "Double" class: one check-in that counts for two classes.
                Tables\Actions\Action::make('check_in_double')->label(trans('panel.attendance.check_in_double'))
                    ->icon('heroicon-o-plus-circle')->color('info')->button()->size('sm')
                    ->visible(fn (Booking $r): bool => ! optional($r->todayAttendance())->checked_in_at)
                    ->action(function (Booking $r): void {
                        $a = $r->attendances()->firstOrCreate(['attended_on' => today()]);
                        if (! $a->checked_in_at) {
                            $a->update(['checked_in_at' => now(), 'classes_count' => 2]);
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
                        Notification::make()->success()
                            ->title(trans('panel.attendance.paid_done', ['month' => $r->lastPaidMonthLabel()]))
                            ->send();
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
