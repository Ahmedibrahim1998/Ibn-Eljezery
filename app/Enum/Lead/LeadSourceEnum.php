<?php

namespace App\Enum\Lead;

use App\Foundation\Enum\BasicEnum;
use Filament\Support\Contracts\HasLabel;

enum LeadSourceEnum: string implements HasLabel
{
    use BasicEnum;

    case HERO = 'hero';
    case CONTACT = 'contact';

    public function getLabel(): string
    {
        return trans("enum.lead_source.{$this->value}");
    }
}
