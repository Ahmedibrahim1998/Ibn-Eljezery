<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return trans('panel.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.teacher.nav');
    }

    public static function getModelLabel(): string
    {
        return trans('panel.teacher.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('panel.teacher.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(trans('panel.teacher.section'))->schema([
                Forms\Components\TextInput::make('name_ar')->label(trans('panel.teacher.name').' ('.trans('panel.common.ar').')')->required()->maxLength(255),
                Forms\Components\TextInput::make('name_en')->label(trans('panel.teacher.name').' ('.trans('panel.common.en').')')->maxLength(255),
                Forms\Components\TextInput::make('certification_ar')->label(trans('panel.teacher.certification').' ('.trans('panel.common.ar').')')->maxLength(255),
                Forms\Components\TextInput::make('certification_en')->label(trans('panel.teacher.certification').' ('.trans('panel.common.en').')')->maxLength(255),
                Forms\Components\Textarea::make('description_ar')->label(trans('panel.teacher.description').' ('.trans('panel.common.ar').')')->rows(3),
                Forms\Components\Textarea::make('description_en')->label(trans('panel.teacher.description').' ('.trans('panel.common.en').')')->rows(3),
                Forms\Components\TextInput::make('badge_ar')->label(trans('panel.teacher.badge').' ('.trans('panel.common.ar').')')->maxLength(255),
                Forms\Components\TextInput::make('badge_en')->label(trans('panel.teacher.badge').' ('.trans('panel.common.en').')')->maxLength(255),
            ])->columns(2),

            Forms\Components\Section::make(trans('panel.common.settings_section'))->schema([
                Forms\Components\FileUpload::make('photo')->label(trans('panel.teacher.photo'))->image()->directory('teachers')->imageEditor(),
                Forms\Components\TextInput::make('sort_order')->label(trans('panel.common.sort_order'))->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label(trans('panel.common.is_active'))->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label(trans('panel.teacher.photo'))->circular(),
                Tables\Columns\TextColumn::make('name_ar')->label(trans('panel.teacher.name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('certification_ar')->label(trans('panel.teacher.certification'))->limit(30),
                Tables\Columns\TextColumn::make('badge_ar')->label(trans('panel.teacher.badge'))->badge(),
                Tables\Columns\IconColumn::make('user_id')->label(trans('panel.teacher.has_login'))
                    ->boolean()->trueIcon('heroicon-o-key')->falseIcon('heroicon-o-lock-closed'),
                Tables\Columns\IconColumn::make('is_active')->label(trans('panel.common.is_active'))->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label(trans('panel.common.sort_order'))->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(trans('panel.common.status_filter')),
            ])
            ->actions([
                Tables\Actions\Action::make('login')
                    ->label(trans('panel.teacher.manage_login'))
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->fillForm(fn (Teacher $record): array => ['email' => $record->user?->email])
                    ->form([
                        Forms\Components\TextInput::make('email')->label(trans('panel.teacher.login_email'))
                            ->email()->required()
                            ->unique(table: User::class, column: 'email', ignorable: fn (Teacher $record) => $record->user),
                        Forms\Components\TextInput::make('password')->label(trans('panel.teacher.login_password'))
                            ->password()->revealable()
                            ->helperText(trans('panel.teacher.password_hint'))
                            ->rule('min:6'),
                    ])
                    ->action(function (Teacher $record, array $data): void {
                        $user = $record->user;

                        if (! $user) {
                            $user = User::create([
                                'name' => $record->name_ar,
                                'email' => $data['email'],
                                'password' => Hash::make($data['password'] ?: str()->random(12)),
                            ]);
                            $record->update(['user_id' => $user->id]);
                        } else {
                            $user->email = $data['email'];
                            if (! empty($data['password'])) {
                                $user->password = Hash::make($data['password']);
                            }
                            $user->save();
                        }

                        $user->syncRoles(['teacher']);

                        Notification::make()->success()->title(trans('panel.teacher.login_saved'))->send();
                    }),
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
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
