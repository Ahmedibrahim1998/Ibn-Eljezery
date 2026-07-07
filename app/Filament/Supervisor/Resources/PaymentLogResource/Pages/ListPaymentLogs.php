<?php

namespace App\Filament\Supervisor\Resources\PaymentLogResource\Pages;

use App\Filament\Supervisor\Resources\PaymentLogResource;
use Filament\Resources\Pages\ListRecords;

class ListPaymentLogs extends ListRecords
{
    protected static string $resource = PaymentLogResource::class;
}
