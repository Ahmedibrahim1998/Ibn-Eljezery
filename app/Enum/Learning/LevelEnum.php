<?php

namespace App\Enum\Learning;

use App\Foundation\Enum\BasicEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LevelEnum: string implements HasLabel, HasColor
{
    use BasicEnum;

    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';

    public function getLabel(): string
    {
        return trans("enum.level.{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::BEGINNER => 'success',
            self::INTERMEDIATE => 'warning',
            self::ADVANCED => 'danger',
        };
    }
}
