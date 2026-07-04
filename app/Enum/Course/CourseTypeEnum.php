<?php

namespace App\Enum\Course;

use App\Foundation\Enum\BasicEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CourseTypeEnum: string implements HasLabel, HasColor
{
    use BasicEnum;

    case OFFLINE = 'offline';
    case ONLINE = 'online';

    public function getLabel(): string
    {
        return trans("enum.course_type.{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OFFLINE => 'warning',
            self::ONLINE => 'success',
        };
    }
}
