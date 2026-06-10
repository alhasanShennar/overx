<?php

namespace App\Filament\Resources\TradingContractResource\Pages;

use App\Filament\Resources\TradingContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTradingContract extends EditRecord
{
    protected static string $resource = TradingContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
