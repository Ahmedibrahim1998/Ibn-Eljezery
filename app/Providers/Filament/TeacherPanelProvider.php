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

class TeacherPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teacher')
            ->path('teacher')
            ->login()
            ->brandName('لوحة المعلم')
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
            ->discoverResources(in: app_path('Filament/Teacher/Resources'), for: 'App\\Filament\\Teacher\\Resources')
            ->discoverPages(in: app_path('Filament/Teacher/Pages'), for: 'App\\Filament\\Teacher\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Teacher/Widgets'), for: 'App\\Filament\\Teacher\\Widgets')
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
