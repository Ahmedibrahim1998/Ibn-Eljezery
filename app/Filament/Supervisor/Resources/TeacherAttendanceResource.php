<?php

namespace App\Filament\Supervisor\Resources;

use App\Filament\Supervisor\Resources\TeacherAttendanceResource\Pages;
use App\Models\Teacher;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TeacherAttendanceResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return trans('panel.teacher_attendance.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.teacher_attendance.teacher');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.teacher_attendance.title');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    // Bypass the admin Teacher Shield policy (panel access is gated by role).
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }

    /** Only teachers who teach a course supervised by the logged-in user. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('courses', fn (Builder $q) => $q->where('supervisor_id', Auth::id()));
    }

    protected static function timeLabel($dt): string
    {
        return $dt ? $dt->locale(app()->getLocale())->translatedFormat('g:i A') : '—';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name_ar')
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label(trans('panel.teacher.photo'))->circular()->toggleable(),
                Tables\Columns\TextColumn::make('name_ar')->label(trans('panel.teacher_attendance.teacher'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('courses_list')->label(trans('panel.teacher_attendance.courses'))->toggleable()
                    ->state(fn (Teacher $r): string => $r->courses->where('supervisor_id', Auth::id())->pluck('title_ar')->implode('، ') ?: '—'),
                Tables\Columns\TextColumn::make('today_state')->label(trans('panel.attendance.today_state'))->badge()
                    ->state(function (Teacher $r): string {
                        $a = $r->todayAttendance();
                        if ($a?->checked_out_at) {
                            return trans('panel.attendance.left');
                        }

                        return $a?->checked_in_at ? trans('panel.attendance.present') : trans('panel.attendance.not_today');
                    })
                    ->color(function (Teacher $r): string {
                        $a = $r->todayAttendance();
                        if ($a?->checked_out_at) {
                            return 'info';
                        }

                        return $a?->checked_in_at ? 'success' : 'gray';
                    }),
                Tables\Columns\TextColumn::make('checked_in')->label(trans('panel.attendance.checked_in_at'))
                    ->state(fn (Teacher $r): string => static::timeLabel($r->todayAttendance()?->checked_in_at)),
                Tables\Columns\TextColumn::make('checked_out')->label(trans('panel.attendance.checked_out_at'))
                    ->state(fn (Teacher $r): string => static::timeLabel($r->todayAttendance()?->checked_out_at))->toggleable(),
                Tables\Columns\TextColumn::make('attended_days')->label(trans('panel.attendance.attended_days'))
                    ->badge()->color('success')->state(fn (Teacher $r): int => $r->attendedDays()),
            ])
            ->actions([
                Tables\Actions\Action::make('check_in')->label(trans('panel.attendance.check_in'))
                    ->icon('heroicon-o-arrow-right-on-rectangle')->color('success')->button()->size('sm')
                    ->visible(fn (Teacher $r): bool => ! optional($r->todayAttendance())->checked_in_at)
                    ->action(function (Teacher $r): void {
                        $a = $r->attendances()->firstOrCreate(['attended_on' => today()]);
                        if (! $a->checked_in_at) {
                            $a->update(['checked_in_at' => now()]);
                        }
                    }),
                Tables\Actions\Action::make('check_out')->label(trans('panel.attendance.check_out'))
                    ->icon('heroicon-o-arrow-left-on-rectangle')->color('warning')->button()->size('sm')
                    ->visible(fn (Teacher $r): bool => (bool) optional($r->todayAttendance())->checked_in_at
                        && ! optional($r->todayAttendance())->checked_out_at)
                    ->action(fn (Teacher $r) => optional($r->todayAttendance())->update(['checked_out_at' => now()])),
                Tables\Actions\Action::make('reset_today')->label(trans('panel.attendance.reset'))
                    ->icon('heroicon-o-arrow-uturn-left')->color('gray')
                    ->visible(fn (Teacher $r): bool => (bool) $r->todayAttendance())
                    ->action(fn (Teacher $r) => optional($r->todayAttendance())->delete()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('check_in_all')->label(trans('panel.attendance.check_in'))
                        ->icon('heroicon-o-arrow-right-on-rectangle')->color('success')->deselectRecordsAfterCompletion()
                        ->action(fn ($records) => $records->each(function (Teacher $r): void {
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
            'index' => Pages\ListTeacherAttendance::route('/'),
        ];
    }
}
