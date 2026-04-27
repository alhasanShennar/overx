<?php

namespace App\Filament\Resources\EarningPeriodResource\Pages;

use App\Filament\Resources\EarningPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEarningPeriod extends ViewRecord
{
    protected static string $resource = EarningPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
