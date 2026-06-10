<?php

namespace App\Filament\Resources\TradingEarningResource\Pages;

use App\Filament\Resources\TradingEarningResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTradingEarning extends EditRecord
{
    protected static string $resource = TradingEarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
