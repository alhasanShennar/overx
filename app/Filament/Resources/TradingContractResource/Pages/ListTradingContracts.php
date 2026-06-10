<?php

namespace App\Filament\Resources\TradingContractResource\Pages;

use App\Filament\Resources\TradingContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTradingContracts extends ListRecords
{
    protected static string $resource = TradingContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
