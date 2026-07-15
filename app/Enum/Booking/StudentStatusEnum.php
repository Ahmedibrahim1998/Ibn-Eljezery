<?php

namespace App\Enum\Booking;

use App\Foundation\Enum\BasicEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StudentStatusEnum: string implements HasLabel, HasColor
{
    use BasicEnum;

    case REGULAR = 'regular';   // عادي
    case ORPHAN = 'orphan';     // يتيم  (معفى)
    case POOR = 'poor';         // فقير  (معفى)
    case NEEDY = 'needy';       // مسكين
    case WEALTHY = 'wealthy';   // ميسور / غني

    public function getLabel(): string
    {
        return trans("enum.student_status.{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ORPHAN => 'info',
            self::POOR => 'warning',
            self::NEEDY => 'gray',
            self::WEALTHY => 'success',
            self::REGULAR => 'primary',
        };
    }

    /** Orphan and poor students are exempt from subscription fees. */
    public function isExempt(): bool
    {
        return in_array($this, [self::ORPHAN, self::POOR], true);
    }
}
