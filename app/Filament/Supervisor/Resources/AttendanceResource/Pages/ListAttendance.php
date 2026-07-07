<?php

namespace App\Filament\Supervisor\Resources\AttendanceResource\Pages;

use App\Filament\Supervisor\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendance extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(trans('panel.attendance.add_student')),
        ];
    }
}
