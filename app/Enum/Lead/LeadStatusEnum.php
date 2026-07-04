<?php

namespace App\Enum\Lead;

use App\Foundation\Enum\BasicEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadStatusEnum: string implements HasLabel, HasColor
{
    use BasicEnum;

    case NEW = 'new';
    case CONTACTED = 'contacted';
    case ENROLLED = 'enrolled';
    case REJECTED = 'rejected';

    public function getLabel(): string
    {
        return trans("enum.lead_status.{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NEW => 'info',
            self::CONTACTED => 'warning',
            self::ENROLLED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
