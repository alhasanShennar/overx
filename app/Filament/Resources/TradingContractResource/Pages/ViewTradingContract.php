<?php

namespace App\Filament\Resources\TradingContractResource\Pages;

use App\Filament\Resources\TradingContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTradingContract extends ViewRecord
{
    protected static string $resource = TradingContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
