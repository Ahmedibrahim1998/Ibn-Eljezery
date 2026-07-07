<?php

namespace App\Providers\Filament;

use App\Http\Middleware\Filament\Authenticate;
use App\Http\Middleware\Filament\SetFilamentLocale;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SupervisorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('supervisor')
            ->path('supervisor')
            ->login()
            ->brandName('لوحة مشرف الحضور')
            ->brandLogo(asset('assets/LOGO/logo.png'))
            ->brandLogoHeight('2.6rem')
            ->favicon(asset('assets/LOGO/logo.png'))
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label(fn (): string => app()->getLocale() === 'ar' ? 'English' : 'العربية')
                    ->icon('heroicon-o-language')
                    ->url(fn (): string => route('admin.locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar')),
            ])
            ->discoverResources(in: app_path('Filament/Supervisor/Resources'), for: 'App\\Filament\\Supervisor\\Resources')
            ->discoverPages(in: app_path('Filament/Supervisor/Pages'), for: 'App\\Filament\\Supervisor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Supervisor/Widgets'), for: 'App\\Filament\\Supervisor\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetFilamentLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
