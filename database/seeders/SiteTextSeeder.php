<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SiteTextSeeder extends Seeder
{
    /**
     * Generate editable bilingual settings for every UI string in
     * lang/ar/site.php + lang/en/site.php, so all site texts become
     * manageable from the dashboard (with the lang files as fallback).
     */
    public function run(): void
    {
        $ar = Arr::dot((array) require lang_path('ar/site.php'));
        $en = Arr::dot((array) require lang_path('en/site.php'));

        foreach ($ar as $dotKey => $arValue) {
            $group = explode('.', $dotKey)[0]; // nav | hero | sections | contact | forms | footer | programs
            $base = 'site_'.str_replace('.', '_', $dotKey);

            Setting::updateOrCreate(
                ['key' => $base.'_ar'],
                ['value' => $arValue, 'group' => $group, 'type' => 'text'],
            );

            Setting::updateOrCreate(
                ['key' => $base.'_en'],
                ['value' => $en[$dotKey] ?? $arValue, 'group' => $group, 'type' => 'text'],
            );
        }
    }
}
