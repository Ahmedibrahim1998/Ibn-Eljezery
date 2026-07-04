<?php

namespace App\Utilities\Traits;

/**
 * Gives a model locale-aware access to bilingual "{field}_ar" / "{field}_en"
 * columns. Use $model->localized('title') to read the column matching the
 * current app locale.
 */
trait HasLocalizedAttributes
{
    public function localized(string $column): ?string
    {
        $value = $this->{localizedColumn($column)} ?? null;

        // Fall back to the English column when the localized one is empty.
        if (blank($value)) {
            $value = $this->{"{$column}_en"} ?? null;
        }

        return $value;
    }
}
