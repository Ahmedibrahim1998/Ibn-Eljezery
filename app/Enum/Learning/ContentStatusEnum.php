<?php

namespace App\Enum\Learning;

use App\Foundation\Enum\BasicEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Sharia-review lifecycle of a lesson:
 * draft -> in_review -> approved -> published (or rejected back to the writer).
 * Only published lessons are ever returned by the API.
 */
enum ContentStatusEnum: string implements HasLabel, HasColor
{
    use BasicEnum;

    case DRAFT = 'draft';
    case IN_REVIEW = 'in_review';
    case APPROVED = 'approved';
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';

    public function getLabel(): string
    {
        return trans("enum.content_status.{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::IN_REVIEW => 'warning',
            self::APPROVED => 'info',
            self::PUBLISHED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
