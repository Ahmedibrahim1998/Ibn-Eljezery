<?php

namespace App\Filament\Teacher\Resources\BookingResource\Pages;

use App\Filament\Teacher\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        // Bookings are created by students from the public site, not here.
        return [];
    }
}
