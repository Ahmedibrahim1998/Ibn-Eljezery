<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupervisorResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class SupervisorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.supervisor.plural');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.supervisor.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.supervisor.plural');
    }

    /** Only users with the supervisor role. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role('supervisor');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label(trans('panel.supervisor.name'))->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->label(trans('panel.supervisor.email'))
                ->email()->required()->unique(table: User::class, column: 'email', ignoreRecord: true),
            Forms\Components\TextInput::make('password')->label(trans('panel.supervisor.password'))
                ->password()->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                ->helperText(trans('panel.teacher.password_hint')),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(trans('panel.supervisor.name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label(trans('panel.supervisor.email'))->searchable(),
                Tables\Columns\TextColumn::make('supervised_courses_count')->label(trans('panel.supervisor.courses'))
                    ->counts('supervisedCourses')->badge()->color('info'),
                Tables\Columns\TextColumn::make('created_at')->label(trans('panel.booking.date'))->dateTime('Y-m-d')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupervisors::route('/'),
            'create' => Pages\CreateSupervisor::route('/create'),
            'edit' => Pages\EditSupervisor::route('/{record}/edit'),
        ];
    }
}
