<?php

namespace App\Filament\Resources\TradingEarningResource\Pages;

use App\Filament\Resources\TradingEarningResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTradingEarnings extends ListRecords
{
    protected static string $resource = TradingEarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
