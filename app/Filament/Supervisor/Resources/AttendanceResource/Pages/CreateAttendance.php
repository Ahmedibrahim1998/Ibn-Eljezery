<?php

namespace App\Filament\Supervisor\Resources\AttendanceResource\Pages;

use App\Enum\Booking\BookingStatusEnum;
use App\Filament\Supervisor\Resources\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    /**
     * Students added by the supervisor are confirmed immediately.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = BookingStatusEnum::CONFIRMED->value;

        return $data;
    }
}
