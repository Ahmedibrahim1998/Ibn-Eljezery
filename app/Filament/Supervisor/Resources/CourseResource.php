<?php

namespace App\Filament\Supervisor\Resources;

use App\Filament\Supervisor\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return trans('panel.supervisor.courses');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.course.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.supervisor.courses');
    }

    /** Only the courses this supervisor is assigned to. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('supervisor_id', Auth::id());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')->label(trans('panel.course.title'))->searchable(),
                Tables\Columns\TextColumn::make('type')->label(trans('panel.course.type'))->badge(),
                Tables\Columns\TextColumn::make('teacher.name_ar')->label(trans('panel.teacher.label'))->placeholder('—'),
                Tables\Columns\TextColumn::make('sessions_count')->label(trans('panel.session.plural'))->counts('sessions')->badge(),
            ])
            ->actions([])
            ->paginated([10, 25]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
        ];
    }
}
