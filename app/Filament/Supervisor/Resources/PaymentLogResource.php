<?php

namespace App\Filament\Supervisor\Resources;

use App\Filament\Supervisor\Resources\PaymentLogResource\Pages;
use App\Models\Course;
use App\Models\PaymentLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentLogResource extends Resource
{
    protected static ?string $model = PaymentLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return trans('panel.payment_log.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.payment_log.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.payment_log.plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /** Only payments for the courses this supervisor manages. */
    public static function getEloquentQuery(): Builder
    {
        $courseIds = Course::where('supervisor_id', Auth::id())->pluck('id')->all();

        return parent::getEloquentQuery()->whereIn('course_id', $courseIds);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('paid_at')->label(trans('panel.payment_log.paid_at'))
                    ->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('student_name')->label(trans('panel.payment_log.student'))->searchable(),
                Tables\Columns\TextColumn::make('student_phone')->label(trans('panel.payment_log.phone'))->searchable(),
                Tables\Columns\TextColumn::make('course_title_ar')->label(trans('panel.payment_log.course'))
                    ->state(fn (PaymentLog $r): string => $r->localized('course_title'))->limit(24),
                Tables\Columns\TextColumn::make('month_number')->label(trans('panel.payment_log.month_number'))
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('classes_attended')->label(trans('panel.payment_log.classes_attended'))
                    ->badge()->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')->label(trans('panel.payment_log.course'))
                    ->options(fn () => Course::where('supervisor_id', Auth::id())->pluck('title_ar', 'id')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentLogs::route('/'),
        ];
    }
}
