<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Logging out of any panel returns to the shared /portal page.
        $this->app->bind(
            \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class,
            \App\Http\Responses\Auth\LogoutResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Public listing pages use Bootstrap 5 pagination markup.
        Paginator::useBootstrapFive();

        // The brand logo is a square 1:1 image sized only by height, so its width
        // is unknown until it loads — causing the header (and login page) to jump.
        // Reserving the box via aspect-ratio keeps the layout stable on load/reload.
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => Blade::render('<style>.fi-logo{aspect-ratio:1/1;height:2.6rem;width:auto;}</style>'),
        );
    }
}
