<?php

namespace App\Foundation\Enum;

/**
 * Shared helpers for backed string enums in the domain.
 */
trait BasicEnum
{
    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Map of value => translated label, for use in Filament selects/filters.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case instanceof \Filament\Support\Contracts\HasLabel
                ? $case->getLabel()
                : $case->value;
        }

        return $options;
    }
}
