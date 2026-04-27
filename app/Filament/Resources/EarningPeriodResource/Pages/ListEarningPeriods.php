<?php

namespace App\Filament\Resources\EarningPeriodResource\Pages;

use App\Filament\Resources\EarningPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEarningPeriods extends ListRecords
{
    protected static string $resource = EarningPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
