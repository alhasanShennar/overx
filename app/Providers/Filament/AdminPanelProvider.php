<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => [
                    50  => '#eaf2f9',
                    100 => '#d4e5f3',
                    200 => '#a3c8e7',
                    300 => '#70A9DC',
                    400 => '#5490c7',
                    500 => '#3D6FA8',
                    600 => '#325d90',
                    700 => '#213F7F',
                    800 => '#1a3268',
                    900 => '#122550',
                    950 => '#0b1838',
                ],
            ])
            ->brandLogo(asset('logo.png'))
            ->darkModeBrandLogo(asset('logo-dark.png'))
            ->brandLogoHeight('4rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\AdminStatsOverviewWidget::class,
                \App\Filament\Widgets\RevenueChartWidget::class,
                \App\Filament\Widgets\BtcEarnedChartWidget::class,
                \App\Filament\Widgets\AvgBtcPriceChartWidget::class,
                \App\Filament\Widgets\RevenuePerClientChartWidget::class,
                \App\Filament\Widgets\PeriodStatusChartWidget::class,
                \App\Filament\Widgets\CashoutVsStoredChartWidget::class,
                \App\Filament\Widgets\PendingRequestsWidget::class,
                \App\Filament\Widgets\ExpiringPeriodsWidget::class,
                \App\Filament\Widgets\RecentCashoutsWidget::class,
                \App\Filament\Widgets\LatestClientsWidget::class,
                \App\Filament\Widgets\LatestEarningPeriodsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
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
