<?php

namespace App\Filament\Teacher\Resources\BookingResource\Pages;

use App\Filament\Teacher\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;
}
