<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PlatformHoldings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-pie';
    protected static string  $view            = 'filament.pages.platform-holdings';
    protected static ?string $navigationLabel = 'Platform Holdings';
    protected static ?int    $navigationSort  = 3;

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
