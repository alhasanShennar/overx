<?php

namespace App\Filament\Resources\CashoutResource\Pages;

use App\Filament\Resources\CashoutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashout extends EditRecord
{
    protected static string $resource = CashoutResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
