<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\RequiresAdminPermission;
use App\Support\AdminPermission;
use Filament\Pages\Page;

class PlatformHoldings extends Page
{
    use RequiresAdminPermission;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-pie';
    protected static string  $view            = 'filament.pages.platform-holdings';
    protected static ?string $navigationLabel = 'Platform Holdings';
    protected static ?int    $navigationSort  = 3;

    protected static function adminPermission(): ?string
    {
        return AdminPermission::VIEW_PLATFORM_HOLDINGS;
    }
}
