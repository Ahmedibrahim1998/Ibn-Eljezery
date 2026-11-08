<?php

namespace App\Enum\Learning;

use App\Foundation\Enum\BasicEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** The fiqh school a learning subject follows (the app's first release uses maliki + shafii). */
enum MadhhabEnum: string implements HasLabel, HasColor
{
    use BasicEnum;

    case MALIKI = 'maliki';
    case SHAFII = 'shafii';
    case HANAFI = 'hanafi';
    case HANBALI = 'hanbali';
    case GENERAL = 'general';

    public function getLabel(): string
    {
        return trans("enum.madhhab.{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MALIKI => 'success',
            self::SHAFII => 'info',
            self::HANAFI => 'warning',
            self::HANBALI => 'danger',
            self::GENERAL => 'gray',
        };
    }
}
