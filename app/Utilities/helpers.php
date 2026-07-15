<?php

use App\Models\Setting;
use Illuminate\Support\Facades\App;

if (! function_exists('localizedColumn')) {
    /**
     * Return the locale-aware column name for a bilingual field.
     *
     * e.g. localizedColumn('name') => 'name_ar' when the app locale is "ar",
     * otherwise 'name_en'. Falls back to the English column for any
     * non-Arabic locale.
     */
    function localizedColumn(string $column): string
    {
        $locale = App::getLocale();
        $suffix = $locale === 'ar' ? 'ar' : 'en';

        return "{$column}_{$suffix}";
    }
}

if (! function_exists('waUrl')) {
    /**
     * Build a WhatsApp (wa.me) link from any phone number. Strips spaces,
     * dashes and a leading "+"/"00" so "+20 100-123 4567" => wa.me/201001234567.
     */
    function waUrl(?string $phone): string
    {
        // Keep only digits (drops "+", spaces and dashes). The number should
        // already include its country code, exactly as WhatsApp expects.
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return 'https://wa.me/'.$digits;
    }
}

if (! function_exists('setting')) {
    /**
     * Read a site setting by key (cached).
     */
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('siteText')) {
    /**
     * Resolve a public-site UI string. Editable from the dashboard via a
     * setting keyed "site_{dotted_key_with_underscores}" (bilingual), and
     * falls back to the lang/{locale}/site.php translation when unset.
     *
     * e.g. siteText('nav.home') reads setting "site_nav_home_{ar|en}",
     * falling back to trans('site.nav.home').
     */
    function siteText(string $key): string
    {
        $settingKey = 'site_'.str_replace('.', '_', $key);

        return (string) localizedSetting($settingKey, trans("site.{$key}"));
    }
}

if (! function_exists('localizedSetting')) {
    /**
     * Read a bilingual setting, picking the "{key}_ar" / "{key}_en" variant
     * for the current locale, falling back to the English one.
     */
    function localizedSetting(string $key, ?string $default = null): ?string
    {
        $value = Setting::get(localizedColumn($key));

        if (blank($value)) {
            $value = Setting::get("{$key}_en");
        }

        return $value ?? $default;
    }
}
