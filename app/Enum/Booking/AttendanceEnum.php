<?php

namespace App\Enum\Booking;

use App\Foundation\Enum\BasicEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AttendanceEnum: string implements HasLabel, HasColor
{
    use BasicEnum;

    case PENDING = 'pending';
    case PRESENT = 'present';
    case ABSENT = 'absent';

    public function getLabel(): string
    {
        return trans("enum.attendance.{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::PRESENT => 'success',
            self::ABSENT => 'danger',
        };
    }
}
